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
        $decisionsQuery = EmployeeDecision::with(['employee', 'employees', 'type', 'status', 'documents']);

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

        if ($request->filled('year')) {
            $decisionsQuery->where('decision_year', (int) $request->year);
        }

        $decisions = $decisionsQuery->orderByDesc('issue_date')->paginate(15);

        $currentYear = (int) date('Y');
        $maxByYear = EmployeeDecision::selectRaw('decision_year, MAX(CAST(NULLIF(REGEXP_REPLACE(COALESCE(annual_number, \'\'), \'[^0-9]\', \'\', \'g\'), \'\') AS INTEGER)) as max_seq')
            ->whereNotNull('decision_year')
            ->groupBy('decision_year')
            ->pluck('max_seq', 'decision_year')
            ->toArray();
        $nextAnnualByYear = [];
        for ($y = $currentYear - 1; $y <= $currentYear + 2; $y++) {
            $nextAnnualByYear[$y] = ((int) ($maxByYear[$y] ?? 0)) + 1;
        }
        $availableYears = EmployeeDecision::whereNotNull('decision_year')
            ->distinct()->orderByDesc('decision_year')->pluck('decision_year')->toArray();
        if (!in_array($currentYear, $availableYears, true)) {
            array_unshift($availableYears, $currentYear);
        }

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
            'employees',
            'nextAnnualByYear',
            'availableYears'
        ));
    }

    /**
     * تسجيل قرار إداري رسمي جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,employee_id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,employee_id',
            'decision_type_id' => 'required|exists:decision_types,decision_type_id',
            'decision_number' => 'required|string|max:100',
            'annual_number' => 'nullable|string|max:50',
            'decision_year' => 'nullable|integer|min:1990|max:2100',
            'issuing_authority' => 'nullable|string|max:200',
            'issue_date' => 'required|date',
            'effective_start_date' => 'required|date',
            'effective_end_date' => 'nullable|date|after_or_equal:effective_start_date',
            'decision_status_id' => 'required|exists:decision_statuses,decision_status_id',
            'summary' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:51200',
        ]);

        $employeeIds = collect($validated['employee_ids'] ?? [])
            ->when(!empty($validated['employee_id']), fn($c) => $c->prepend($validated['employee_id']))
            ->unique()
            ->values();

        if ($employeeIds->isEmpty()) {
            return back()->withErrors(['employee_id' => 'يرجى تحديد موظف واحد على الأقل للقرار.'])->withInput();
        }

        $decisionYear = (int) ($validated['decision_year'] ?? date('Y', strtotime($validated['issue_date'])));
        $annualNumber = $validated['annual_number'] ?? null;
        if (empty($annualNumber)) {
            $maxSeq = (int) EmployeeDecision::where('decision_year', $decisionYear)
                ->selectRaw("COALESCE(MAX(CAST(NULLIF(REGEXP_REPLACE(COALESCE(annual_number, ''), '[^0-9]', '', 'g'), '') AS INTEGER)), 0) as max_seq")
                ->value('max_seq');
            $annualNumber = (string) ($maxSeq + 1);
        }

        $decision = EmployeeDecision::create([
            'employee_id' => $employeeIds->first(),
            'decision_type_id' => $validated['decision_type_id'],
            'decision_number' => $validated['decision_number'],
            'annual_number' => $annualNumber,
            'decision_year' => $decisionYear,
            'issuing_authority' => $validated['issuing_authority'] ?? 'رئاسة جامعة إب',
            'issue_date' => $validated['issue_date'],
            'effective_start_date' => $validated['effective_start_date'],
            'effective_end_date' => $validated['effective_end_date'] ?? null,
            'decision_status_id' => $validated['decision_status_id'],
            'is_active' => ((int) $validated['decision_status_id'] === 1),
            'summary' => $validated['summary'],
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
        ]);

        $decision->employees()->sync($employeeIds->all());

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

        $roleName = (string) session('logged_role_name', '');
        $username = strtolower((string) session('logged_username', ''));
        $isSysAdmin = ($username === 'admin' || str_contains($roleName, 'مدير النظام') || $roleName === 'مدير نظام');
        if ($decision->decision_year && (int) $decision->decision_year < (int) date('Y') && !$isSysAdmin) {
            return back()->with('error', 'سجل القرارات لهذه السنة مقفل لانتهاء العام، ولا يمكن تعديله إلا من إدارة النظام.');
        }

        $validated = $request->validate([
            'decision_type_id' => 'required|exists:decision_types,decision_type_id',
            'decision_number' => 'required|string|max:100',
            'annual_number' => 'nullable|string|max:50',
            'decision_year' => 'nullable|integer|min:1990|max:2100',
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
            'annual_number' => $validated['annual_number'] ?? $decision->annual_number,
            'decision_year' => $validated['decision_year'] ?? $decision->decision_year,
            'issuing_authority' => $validated['issuing_authority'] ?? 'رئاسة جامعة إب',
            'issue_date' => $validated['issue_date'],
            'effective_start_date' => $validated['effective_start_date'],
            'effective_end_date' => $validated['effective_end_date'] ?? null,
            'decision_status_id' => $validated['decision_status_id'],
            'is_active' => ((int) $validated['decision_status_id'] === 1),
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
