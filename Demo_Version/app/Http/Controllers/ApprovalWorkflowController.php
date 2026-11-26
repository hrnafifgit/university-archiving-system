<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransaction;
use App\Services\AuditLogService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ApprovalWorkflowController extends Controller
{
    /**
     * شاشة مسار الاعتماد والتأكيد المتسلسل (Confirm / Approval Workflow)
     */
    public function index(Request $request)
    {
        PermissionService::ensureSeeded();

        $query = WorkflowTransaction::with('steps')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->get();

        $selectedTxId = $request->query('tx', $transactions->first()?->transaction_id);
        $activeTx = $transactions->firstWhere('transaction_id', $selectedTxId) ?? $transactions->first();

        $demoUsers = AppUser::with('roles')->orderBy('username')->get();
        $currentRoleName = Session::get('logged_role_name', 'مدير نظام');

        return view('workflow.index', compact(
            'transactions',
            'activeTx',
            'demoUsers',
            'currentRoleName'
        ));
    }

    /**
     * إنشاء معاملة جديدة (مسودة أو إرسال مباشر للاعتماد)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:hourly_contract,budget,decision,employee_update',
            'title' => 'required|string|max:250',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
            'department_name' => 'nullable|string|max:150',
            'submit_mode' => 'required|in:draft,submit',
        ]);

        $userId = Session::get('logged_user_id');
        $actorName = Session::get('logged_display_name') ?? Session::get('logged_username', 'admin');
        $isSubmit = $validated['submit_mode'] === 'submit';

        DB::beginTransaction();
        try {
            $code = 'WF-' . date('Y') . '-' . str_pad((string) (WorkflowTransaction::count() + 1), 3, '0', STR_PAD_LEFT);

            $tx = WorkflowTransaction::create([
                'transaction_code' => $code,
                'transaction_type' => $validated['transaction_type'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'department_name' => $validated['department_name'] ?? 'الإدارة العامة',
                'created_by' => $userId,
                'created_by_name' => $actorName,
                'current_step' => $isSubmit ? 2 : 1,
                'status' => $isSubmit ? 'pending_dept_head' : 'draft',
            ]);

            $stepsDef = [
                [1, 'الإدخال والحفظ المبدئي', 'إداري'],
                [2, 'تأكيد رئيس القسم', 'رئيس قسم'],
                [3, 'تأكيد مدير الإدارة', 'مدير إدارة'],
                [4, 'اعتماد الأمين العام', 'أمين عام'],
            ];

            foreach ($stepsDef as [$num, $title, $reqRole]) {
                $isStep1 = ($num === 1);
                WorkflowStep::create([
                    'transaction_id' => $tx->transaction_id,
                    'step_number' => $num,
                    'step_title' => $title,
                    'required_role' => $reqRole,
                    'actor_id' => ($isStep1 && $isSubmit) ? $userId : null,
                    'actor_name' => ($isStep1 && $isSubmit) ? $actorName : null,
                    'action' => ($isStep1 && $isSubmit) ? 'submitted' : 'pending',
                    'notes' => ($isStep1 && $isSubmit) ? 'تم إنشاء وإرسال المعاملة للاعتماد.' : null,
                    'acted_at' => ($isStep1 && $isSubmit) ? now() : null,
                ]);
            }

            DB::commit();
            AuditLogService::log('WORKFLOW_CREATED', 'workflow_transactions', $tx->transaction_id, null, ['code' => $code, 'status' => $tx->status]);

            return redirect()->route('workflow.index', ['tx' => $tx->transaction_id])
                ->with('success', $isSubmit ? "تم إرسال المعاملة ({$code}) إلى رئيس القسم بنجاح." : "تم حفظ المعاملة ({$code}) كمسودة.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'تعذر إنشاء المعاملة: ' . $e->getMessage());
        }
    }

    /**
     * تنفيذ إجراء في مسار الاعتماد (تأكيد Confirm / إرجاع للتعديل / إرسال مسودة)
     */
    public function act(Request $request, $id)
    {
        $tx = WorkflowTransaction::with('steps')->findOrFail($id);
        $actionType = $request->input('action_type'); // submit | confirm | return
        $notes = trim((string) $request->input('notes', ''));

        $userId = Session::get('logged_user_id');
        $actorName = Session::get('logged_display_name') ?? Session::get('logged_username', 'admin');
        $roleName = Session::get('logged_role_name', 'مدير نظام');

        if ($actionType === 'return' && empty($notes)) {
            return redirect()->route('workflow.index', ['tx' => $id])
                ->with('error', 'يجب كتابة سبب الإرجاع للتعديل بشكل إلزامي.');
        }

        if ($actionType === 'submit') {
            $step1 = $tx->steps->firstWhere('step_number', 1);
            if ($step1) {
                $step1->update([
                    'actor_id' => $userId,
                    'actor_name' => $actorName,
                    'action' => 'submitted',
                    'notes' => $notes ?: 'تم إرسال المعاملة للاعتماد المتسلسل.',
                    'acted_at' => now(),
                ]);
            }
            $tx->update([
                'current_step' => 2,
                'status' => 'pending_dept_head',
                'return_reason' => null,
            ]);

            return redirect()->route('workflow.index', ['tx' => $id])
                ->with('success', 'تم إرسال المعاملة بنجاح إلى مرحلة تأكيد رئيس القسم.');
        }

        $currentStepNum = $tx->current_step;
        $stepRecord = $tx->steps->firstWhere('step_number', $currentStepNum);

        if (!$stepRecord) {
            return redirect()->route('workflow.index', ['tx' => $id])
                ->with('error', 'هذه المعاملة مكتملة أو لا توجد مرحلة معلقة حالياً.');
        }

        // التحقق من الصلاحية والدور
        $isSuperAdmin = ($roleName === 'مدير نظام' || $roleName === 'مدير النظام');
        $permModule = match ($tx->transaction_type) {
            'budget' => 'budget',
            'hourly_contract' => 'hourly_contracts',
            default => 'employees',
        };

        if (!$isSuperAdmin && (!PermissionService::hasPerm("{$permModule}.confirm") || $roleName !== $stepRecord->required_role)) {
            return redirect()->route('workflow.index', ['tx' => $id])
                ->with('error', "ليس لديك صلاحية تنفيذ هذا الإجراء في هذه المرحلة (المطلوب: {$stepRecord->required_role}).");
        }

        if ($actionType === 'return') {
            $stepRecord->update([
                'actor_id' => $userId,
                'actor_name' => $actorName,
                'action' => 'returned',
                'notes' => $notes,
                'acted_at' => now(),
            ]);
            $tx->update([
                'current_step' => 1,
                'status' => 'returned',
                'return_reason' => $notes,
            ]);

            AuditLogService::log('WORKFLOW_RETURNED', 'workflow_transactions', $tx->transaction_id, null, ['step' => $currentStepNum, 'reason' => $notes]);

            return redirect()->route('workflow.index', ['tx' => $id])
                ->with('success', 'تم إرجاع المعاملة للتعديل وتوثيق السبب في سجل التوقيعات.');
        }

        if ($actionType === 'confirm') {
            $stepRecord->update([
                'actor_id' => $userId,
                'actor_name' => $actorName,
                'action' => 'confirmed',
                'notes' => $notes ?: 'تم التأكيد والاعتماد وفق الصلاحيات.',
                'acted_at' => now(),
            ]);

            $nextStep = $currentStepNum + 1;
            $nextStatus = match ($nextStep) {
                3 => 'pending_admin_manager',
                4 => 'pending_sec_general',
                default => 'approved',
            };

            $tx->update([
                'current_step' => $nextStep,
                'status' => $nextStatus,
            ]);

            AuditLogService::log('WORKFLOW_CONFIRMED', 'workflow_transactions', $tx->transaction_id, null, ['step' => $currentStepNum, 'next_status' => $nextStatus]);

            return redirect()->route('workflow.index', ['tx' => $id])
                ->with('success', $nextStatus === 'approved' ? 'تم الاعتماد النهائي للمعاملة بنجاح!' : 'تم التأكيد (Confirm) وترحيل المعاملة للمرحلة التالية.');
        }

        return redirect()->route('workflow.index', ['tx' => $id]);
    }
}
