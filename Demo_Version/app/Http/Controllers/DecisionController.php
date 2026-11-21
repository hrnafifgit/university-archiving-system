<?php

namespace App\Http\Controllers;

use App\Models\DecisionStatus;
use App\Models\DecisionType;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
use App\Models\EmployeeAbsence;
use App\Models\EmployeeDecision;
use App\Services\AuditLogService;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DecisionController extends Controller
{
    /**
     * سجل القرارات الإدارية وحركات التفرغ والإيفاد
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'decisions');

        // استعلام القرارات
        $decisionsQuery = EmployeeDecision::with(['employee', 'type', 'status', 'documents']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $decisionsQuery->where(function ($q) use ($s) {
                $q->where('decision_number', 'ilike', "%{$s}%")
                  ->orWhere('summary', 'ilike', "%{$s}%")
                  ->orWhereHas('employee', function ($eq) use ($s) {
                      $eq->where('full_name_ar', 'ilike', "%{$s}%")
                         ->orWhere('employee_number', 'ilike', "%{$s}%");
                  });
            });
        }

        if ($request->filled('type_id')) {
            $decisionsQuery->where('decision_type_id', $request->type_id);
        }

        if ($request->filled('status_id')) {
            $decisionsQuery->where('decision_status_id', $request->status_id);
        }

        $decisions = $decisionsQuery->orderByDesc('issue_date')->paginate(15);

        // استعلام الإجازات والتفرغ
        $absencesQuery = EmployeeAbsence::with(['employee']);
        if ($request->filled('search_abs')) {
            $s = trim($request->search_abs);
            $absencesQuery->whereHas('employee', function ($eq) use ($s) {
                $eq->where('full_name_ar', 'ilike', "%{$s}%")
                   ->orWhere('employee_number', 'ilike', "%{$s}%");
            });
        }
        $absences = $absencesQuery->orderByDesc('start_date')->paginate(15, ['*'], 'abs_page');

        $decisionTypes = DecisionType::orderBy('type_name_ar')->get();
        $decisionStatuses = DecisionStatus::orderBy('status_name_ar')->get();
        $employees = Employee::orderBy('full_name_ar')->limit(100)->get(['employee_id', 'full_name_ar', 'employee_number']);

        return view('decisions.index', compact(
            'tab',
            'decisions',
            'absences',
            'decisionTypes',
            'decisionStatuses',
            'employees'
        ));
    }

    /**
     * تسجيل قرار إداري رسمي جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'decision_type_id' => 'required|exists:decision_types,decision_type_id',
            'decision_number' => 'required|string|max:100',
            'issuing_authority' => 'nullable|string|max:200',
            'issue_date' => 'required|date',
            'effective_start_date' => 'required|date',
            'effective_end_date' => 'nullable|date|after_or_equal:effective_start_date',
            'decision_status_id' => 'required|exists:decision_statuses,decision_status_id',
            'summary' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:51200',
        ]);

        $decision = EmployeeDecision::create([
            'employee_id' => $validated['employee_id'],
            'decision_type_id' => $validated['decision_type_id'],
            'decision_number' => $validated['decision_number'],
            'issuing_authority' => $validated['issuing_authority'] ?? 'رئاسة جامعة إب',
            'issue_date' => $validated['issue_date'],
            'effective_start_date' => $validated['effective_start_date'],
            'effective_end_date' => $validated['effective_end_date'] ?? null,
            'decision_status_id' => $validated['decision_status_id'],
            'summary' => $validated['summary'],
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
        ]);

        if ($request->hasFile('decision_file')) {
            DocumentStorageService::storeDecisionDocument($decision, $request->file('decision_file'));
        }

        AuditLogService::log('DECISION_CREATE', 'employee_decisions', $decision->decision_id, null, [
            'decision_number' => $decision->decision_number,
            'employee_id' => $decision->employee_id,
        ]);

        Cache::forget('dashboard_core_metrics');

        return redirect()->route('decisions.index', ['tab' => 'decisions'])
            ->with('success', 'تم توثيق وإصدار القرار الإداري في السجل الأكاديمي بنجاح.');
    }

    /**
     * تعديل بيانات قرار إداري رسمي قائم
     */
    public function update(Request $request, $id)
    {
        $decision = EmployeeDecision::findOrFail($id);

        $validated = $request->validate([
            'decision_type_id' => 'required|exists:decision_types,decision_type_id',
            'decision_number' => 'required|string|max:100',
            'issuing_authority' => 'nullable|string|max:200',
            'issue_date' => 'required|date',
            'effective_start_date' => 'required|date',
            'effective_end_date' => 'nullable|date|after_or_equal:effective_start_date',
            'decision_status_id' => 'required|exists:decision_statuses,decision_status_id',
            'summary' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:51200',
        ]);

        $decision->update([
            'decision_type_id' => $validated['decision_type_id'],
            'decision_number' => $validated['decision_number'],
            'issuing_authority' => $validated['issuing_authority'] ?? 'رئاسة جامعة إب',
            'issue_date' => $validated['issue_date'],
            'effective_start_date' => $validated['effective_start_date'],
            'effective_end_date' => $validated['effective_end_date'] ?? null,
            'decision_status_id' => $validated['decision_status_id'],
            'summary' => $validated['summary'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->hasFile('decision_file')) {
            DocumentStorageService::storeDecisionDocument($decision, $request->file('decision_file'));
        }

        Cache::forget('dashboard_core_metrics');

        return redirect()->route('decisions.index', ['tab' => 'decisions'])
            ->with('success', 'تم تعديل وتحديث القرار الإداري بنجاح.');
    }

    /**
     * تسجيل حركة إجازة / تفرغ علمي / إيفاد
     */
    public function storeAbsence(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'absence_type' => 'required|string|in:sabbatical,study_leave,unpaid_leave,sick_leave,delegation,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'destination_or_host' => 'nullable|string|max:250',
            'status' => 'required|string|in:active,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        EmployeeAbsence::create($validated);
        Cache::forget('dashboard_core_metrics');

        return redirect()->route('decisions.index', ['tab' => 'absences'])
            ->with('success', 'تم تقييد حركة الإجازة / التفرغ في ملف الكادر بنجاح.');
    }

    /**
     * تعديل حركة إجازة / تفرغ
     */
    public function updateAbsence(Request $request, $id)
    {
        $absence = EmployeeAbsence::findOrFail($id);

        $validated = $request->validate([
            'absence_type' => 'required|string|in:sabbatical,study_leave,unpaid_leave,sick_leave,delegation,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'destination_or_host' => 'nullable|string|max:250',
            'status' => 'required|string|in:active,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $absence->update($validated);
        Cache::forget('dashboard_core_metrics');

        return redirect()->route('decisions.index', ['tab' => 'absences'])
            ->with('success', 'تم تحديث حركة الإجازة / التفرغ بنجاح.');
    }

    /**
     * حذف حركة إجازة / تفرغ
     */
    public function destroyAbsence($id)
    {
        $absence = EmployeeAbsence::findOrFail($id);
        $absence->delete();

        AuditLogService::log('ABSENCE_DELETE', 'employee_absences', $id, null, null);
        Cache::forget('dashboard_core_metrics');

        return redirect()->route('decisions.index', ['tab' => 'absences'])
            ->with('success', 'تم حذف حركة الإجازة / التفرغ بنجاح.');
    }

    /**
     * حذف قرار إداري
     */
    public function destroy($id)
    {
        $decision = EmployeeDecision::findOrFail($id);
        
        AuditLogService::log('DECISION_DELETE', 'employee_decisions', $decision->decision_id, [
            'decision_number' => $decision->decision_number,
            'employee_id' => $decision->employee_id,
        ], null);

        $decision->delete();
        Cache::forget('dashboard_core_metrics');

        return redirect()->route('decisions.index')
            ->with('success', 'تم حذف القرار الإداري بنجاح.');
    }
}
