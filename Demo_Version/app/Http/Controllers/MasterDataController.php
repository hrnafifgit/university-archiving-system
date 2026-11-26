<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\Country;
use App\Models\DecisionType;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\EmployeeDecision;
use App\Models\EmployeeQualification;
use App\Models\EmploymentRecord;
use App\Models\EmploymentStatus;
use App\Models\JobGrade;
use App\Models\JobTitle;
use App\Models\OrganizationalUnit;
use App\Models\University;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MasterDataController extends Controller
{
    /**
     * لوحة إدارة الهيكل والبيانات المرجعية الرئيسية
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'org_units');

        // 1. الهيكل التنظيمي (الكليات والأقسام)
        $rootUniv = OrganizationalUnit::whereNull('parent_unit_id')->first();
        $faculties = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center', 'administration'])
            ->with([
                'children' => function($q) {
                    $q->orderBy('unit_name_ar')
                      ->with([
                          'employmentRecords.employee.documents.currentVersion',
                          'employmentRecords.employee.currentEmployment.academicRank',
                      ]);
                },
                'employmentRecords.employee.documents.currentVersion',
                'employmentRecords.employee.currentEmployment.academicRank',
            ])
            ->orderBy('unit_name_ar')
            ->get();

        // علاقات الكادر المشتركة لتحسين الأداء وتفادي استعلامات N+1
        $empRelations = [
            'employmentRecords.employee.currentEmployment.organizationalUnit.parent',
            'employmentRecords.employee.currentEmployment.academicRank',
            'employmentRecords.employee.currentEmployment.jobGrade',
            'employmentRecords.employee.currentEmployment.employmentStatus',
            'employmentRecords.employee.currentEmployment.jobTitle',
            'employmentRecords.employee.latestEmployment.organizationalUnit.parent',
            'employmentRecords.employee.latestEmployment.academicRank',
            'employmentRecords.employee.latestEmployment.jobGrade',
            'employmentRecords.employee.latestEmployment.employmentStatus',
            'employmentRecords.employee.latestEmployment.jobTitle',
        ];

        // 2. الرتب العلمية
        $ranks = AcademicRank::with($empRelations)->orderBy('rank_order')->get();

        // 3. درجات الخدمة المدنية
        $grades = JobGrade::with($empRelations)->orderBy('grade_code')->get();

        // 4. الحالات الوظيفية
        $statuses = EmploymentStatus::with($empRelations)->orderBy('status_name_ar')->get();

        // 5. أنواع الوثائق
        $documentTypes = DocumentType::with(['documents.currentVersion', 'documents.employee'])->orderBy('type_name_ar')->get();

        // 6. أنواع القرارات
        $decisionTypes = DecisionType::with(['decisions.employee'])->orderBy('type_name_ar')->get();

        // 7. المسميات الوظيفية
        if (JobTitle::count() === 0) {
            $distinctTitles = EmploymentRecord::whereNotNull('payroll_job_title')
                ->where('payroll_job_title', '!=', '')
                ->pluck('payroll_job_title')
                ->unique();
            foreach ($distinctTitles as $t) {
                if ($trimmed = trim($t)) {
                    $jtRecord = JobTitle::firstOrCreate(['title_name_ar' => $trimmed], ['is_active' => true]);
                    EmploymentRecord::where('payroll_job_title', $t)->whereNull('job_title_id')->update(['job_title_id' => $jtRecord->job_title_id]);
                }
            }
        }
        $jobTitles = JobTitle::with($empRelations)->orderBy('title_name_ar')->get();

        // 8. الجامعات والدول
        $universities = University::with(['country', 'qualifications.employee'])->orderBy('university_name_ar')->get();
        $countries = Country::orderBy('country_name_ar')->get();

        return view('master_data.index', compact(
            'tab',
            'rootUniv',
            'faculties',
            'ranks',
            'grades',
            'statuses',
            'documentTypes',
            'decisionTypes',
            'jobTitles',
            'universities',
            'countries'
        ));
    }

    // =========================================================================
    // 1. الكليات والأقسام والهيكل التنظيمي (Organizational Units)
    // =========================================================================

    public function storeUnit(Request $request)
    {
        $validated = $request->validate([
            'unit_name_ar' => 'required|string|max:200',
            'unit_code' => 'required|string|max:50|unique:organizational_units,unit_code',
            'unit_type' => 'required|in:faculty,college,center,administration,department,office,other',
            'parent_unit_id' => 'nullable|exists:organizational_units,organizational_unit_id',
        ]);

        $unit = OrganizationalUnit::create([
            'unit_name_ar' => $validated['unit_name_ar'],
            'unit_code' => strtoupper($validated['unit_code']),
            'unit_type' => $validated['unit_type'],
            'parent_unit_id' => $validated['parent_unit_id'] ?? null,
            'is_active' => true,
            'created_at' => now(),
        ]);

        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('UNIT_CREATE', 'organizational_units', $unit->organizational_unit_id, null, [
            'unit_name_ar' => $unit->unit_name_ar,
            'unit_code' => $unit->unit_code,
        ]);

        return redirect()->route('master_data.index', ['tab' => 'org_units'])
            ->with('success', 'تمت إضافة الوحدة التنظيمية بالهيكل الجامعي بنجاح.');
    }

    public function updateUnit(Request $request, $id)
    {
        $unit = OrganizationalUnit::findOrFail($id);

        $validated = $request->validate([
            'unit_name_ar' => 'required|string|max:200',
            'unit_code' => 'required|string|max:50|unique:organizational_units,unit_code,' . $unit->organizational_unit_id . ',organizational_unit_id',
            'unit_type' => 'required|in:faculty,college,center,administration,department,office,other',
            'parent_unit_id' => 'nullable|exists:organizational_units,organizational_unit_id',
        ]);

        if (!empty($validated['parent_unit_id']) && $validated['parent_unit_id'] === $unit->organizational_unit_id) {
            return redirect()->back()->with('error', 'لا يمكن تعيين الوحدة كجهة أم تابعة لنفسها.');
        }

        $oldName = $unit->unit_name_ar;
        $unit->update([
            'unit_name_ar' => $validated['unit_name_ar'],
            'unit_code' => strtoupper($validated['unit_code']),
            'unit_type' => $validated['unit_type'],
            'parent_unit_id' => $validated['parent_unit_id'] ?? null,
        ]);

        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('UNIT_UPDATE', 'organizational_units', $unit->organizational_unit_id, [
            'unit_name_ar' => $oldName,
        ], [
            'unit_name_ar' => $unit->unit_name_ar,
            'unit_code' => $unit->unit_code,
        ]);

        return redirect()->route('master_data.index', ['tab' => 'org_units'])
            ->with('success', 'تم تحديث بيانات الوحدة التنظيمية بنجاح.');
    }

    public function destroyUnit($id)
    {
        $unit = OrganizationalUnit::findOrFail($id);

        if ($unit->children()->count() > 0) {
            return redirect()->route('master_data.index', ['tab' => 'org_units'])
                ->with('error', 'لا يمكن حذف هذه الجهة لوجود أقسام تابعة لها بالهيكل. يرجى حذف أو نقل الأقسام أولاً.');
        }

        if (EmploymentRecord::where('organizational_unit_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'org_units'])
                ->with('error', 'لا يمكن حذف هذه الوحدة التنظيمية لارتباطها بسجلات وظيفية لكادر مسجلين بها.');
        }

        $unitName = $unit->unit_name_ar;
        $unit->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('UNIT_DELETE', 'organizational_units', $id, ['unit_name_ar' => $unitName], null);

        return redirect()->route('master_data.index', ['tab' => 'org_units'])
            ->with('success', 'تم حذف الوحدة التنظيمية بنجاح.');
    }

    // =========================================================================
    // 2. الرتب العلمية (Academic Ranks)
    // =========================================================================

    public function storeRank(Request $request)
    {
        $validated = $request->validate([
            'rank_code' => 'required|string|max:50|unique:academic_ranks,rank_code',
            'rank_name_ar' => 'required|string|max:120|unique:academic_ranks,rank_name_ar',
            'rank_name_en' => 'nullable|string|max:120',
            'rank_order' => 'required|integer',
        ]);

        $rank = AcademicRank::create([
            'rank_code' => strtoupper($validated['rank_code']),
            'rank_name_ar' => $validated['rank_name_ar'],
            'rank_name_en' => $validated['rank_name_en'] ?? null,
            'rank_order' => $validated['rank_order'],
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('RANK_CREATE', 'academic_ranks', $rank->academic_rank_id, null, $validated);

        return redirect()->route('master_data.index', ['tab' => 'ranks'])
            ->with('success', 'تمت إضافة الرتبة الأكاديمية بنجاح.');
    }

    public function updateRank(Request $request, $id)
    {
        $rank = AcademicRank::findOrFail($id);

        $validated = $request->validate([
            'rank_code' => 'required|string|max:50|unique:academic_ranks,rank_code,' . $rank->academic_rank_id . ',academic_rank_id',
            'rank_name_ar' => 'required|string|max:120|unique:academic_ranks,rank_name_ar,' . $rank->academic_rank_id . ',academic_rank_id',
            'rank_name_en' => 'nullable|string|max:120',
            'rank_order' => 'required|integer',
        ]);

        $oldName = $rank->rank_name_ar;
        $rank->update([
            'rank_code' => strtoupper($validated['rank_code']),
            'rank_name_ar' => $validated['rank_name_ar'],
            'rank_name_en' => $validated['rank_name_en'] ?? null,
            'rank_order' => $validated['rank_order'],
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('RANK_UPDATE', 'academic_ranks', $rank->academic_rank_id, ['rank_name_ar' => $oldName], $validated);

        return redirect()->route('master_data.index', ['tab' => 'ranks'])
            ->with('success', 'تم تحديث الرتبة العلمية بنجاح.');
    }

    public function destroyRank($id)
    {
        $rank = AcademicRank::findOrFail($id);

        if (EmploymentRecord::where('academic_rank_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'ranks'])
                ->with('error', 'لا يمكن حذف هذه الرتبة العلمية لوجود كادر مسجلين بها في سجلات التوظيف.');
        }

        $name = $rank->rank_name_ar;
        $rank->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('RANK_DELETE', 'academic_ranks', $id, ['rank_name_ar' => $name], null);

        return redirect()->route('master_data.index', ['tab' => 'ranks'])
            ->with('success', 'تم حذف الرتبة العلمية بنجاح.');
    }

    // =========================================================================
    // 3. الدرجات الوظيفية (Job Grades)
    // =========================================================================

    public function storeGrade(Request $request)
    {
        $validated = $request->validate([
            'grade_code' => 'required|string|max:50|unique:job_grades,grade_code',
            'grade_name_ar' => 'required|string|max:100|unique:job_grades,grade_name_ar',
            'description' => 'nullable|string|max:255',
        ]);

        $grade = JobGrade::create([
            'grade_code' => strtoupper($validated['grade_code']),
            'grade_name_ar' => $validated['grade_name_ar'],
            'description' => $validated['description'] ?? null,
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('GRADE_CREATE', 'job_grades', $grade->job_grade_id, null, $validated);

        return redirect()->route('master_data.index', ['tab' => 'grades'])
            ->with('success', 'تمت إضافة الدرجة الوظيفية بنجاح.');
    }

    public function updateGrade(Request $request, $id)
    {
        $grade = JobGrade::findOrFail($id);

        $validated = $request->validate([
            'grade_code' => 'required|string|max:50|unique:job_grades,grade_code,' . $grade->job_grade_id . ',job_grade_id',
            'grade_name_ar' => 'required|string|max:100|unique:job_grades,grade_name_ar,' . $grade->job_grade_id . ',job_grade_id',
            'description' => 'nullable|string|max:255',
        ]);

        $oldName = $grade->grade_name_ar;
        $grade->update([
            'grade_code' => strtoupper($validated['grade_code']),
            'grade_name_ar' => $validated['grade_name_ar'],
            'description' => $validated['description'] ?? null,
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('GRADE_UPDATE', 'job_grades', $grade->job_grade_id, ['grade_name_ar' => $oldName], $validated);

        return redirect()->route('master_data.index', ['tab' => 'grades'])
            ->with('success', 'تم تحديث الدرجة الوظيفية بنجاح.');
    }

    public function destroyGrade($id)
    {
        $grade = JobGrade::findOrFail($id);

        if (EmploymentRecord::where('job_grade_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'grades'])
                ->with('error', 'لا يمكن حذف هذه الدرجة الوظيفية لوجود سجلات وظيفية لكادر مرتبطين بها.');
        }

        $name = $grade->grade_name_ar;
        $grade->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('GRADE_DELETE', 'job_grades', $id, ['grade_name_ar' => $name], null);

        return redirect()->route('master_data.index', ['tab' => 'grades'])
            ->with('success', 'تم حذف الدرجة الوظيفية بنجاح.');
    }

    // =========================================================================
    // 4. أنواع الوثائق الأرشيفية (Document Types)
    // =========================================================================

    public function storeDocType(Request $request)
    {
        $validated = $request->validate([
            'type_code' => 'required|string|max:60|unique:document_types,type_code',
            'type_name_ar' => 'required|string|max:160|unique:document_types,type_name_ar',
            'applies_to' => 'required|in:employee,qualification,decision,employment,general',
        ]);

        $docType = DocumentType::create([
            'type_code' => strtoupper($validated['type_code']),
            'type_name_ar' => $validated['type_name_ar'],
            'applies_to' => $validated['applies_to'],
            'is_active' => true,
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('DOCTYPE_CREATE', 'document_types', $docType->document_type_id, null, $validated);

        return redirect()->route('master_data.index', ['tab' => 'doc_types'])
            ->with('success', 'تمت إضافة تصنيف الوثيقة الجديد بنجاح.');
    }

    public function updateDocType(Request $request, $id)
    {
        $docType = DocumentType::findOrFail($id);

        $validated = $request->validate([
            'type_code' => 'required|string|max:60|unique:document_types,type_code,' . $docType->document_type_id . ',document_type_id',
            'type_name_ar' => 'required|string|max:160|unique:document_types,type_name_ar,' . $docType->document_type_id . ',document_type_id',
            'applies_to' => 'required|in:employee,qualification,decision,employment,general',
        ]);

        $oldName = $docType->type_name_ar;
        $docType->update([
            'type_code' => strtoupper($validated['type_code']),
            'type_name_ar' => $validated['type_name_ar'],
            'applies_to' => $validated['applies_to'],
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('DOCTYPE_UPDATE', 'document_types', $docType->document_type_id, ['type_name_ar' => $oldName], $validated);

        return redirect()->route('master_data.index', ['tab' => 'doc_types'])
            ->with('success', 'تم تحديث تصنيف الوثيقة بنجاح.');
    }

    public function destroyDocType($id)
    {
        $docType = DocumentType::findOrFail($id);

        if (Document::where('document_type_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'doc_types'])
                ->with('error', 'لا يمكن حذف هذا التصنيف لوجود وثائق مؤرشفة مسجلة به.');
        }

        $name = $docType->type_name_ar;
        $docType->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('DOCTYPE_DELETE', 'document_types', $id, ['type_name_ar' => $name], null);

        return redirect()->route('master_data.index', ['tab' => 'doc_types'])
            ->with('success', 'تم حذف تصنيف الوثيقة بنجاح.');
    }

    // =========================================================================
    // 5. الحالات الوظيفية (Employment Statuses)
    // =========================================================================

    public function storeStatus(Request $request)
    {
        $validated = $request->validate([
            'status_code' => 'required|string|max:50|unique:employment_statuses,status_code',
            'status_name_ar' => 'required|string|max:120|unique:employment_statuses,status_name_ar',
            'is_active_status' => 'required|boolean',
        ]);

        $status = EmploymentStatus::create([
            'status_code' => strtoupper($validated['status_code']),
            'status_name_ar' => $validated['status_name_ar'],
            'is_active_status' => (bool)$validated['is_active_status'],
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('STATUS_CREATE', 'employment_statuses', $status->employment_status_id, null, $validated);

        return redirect()->route('master_data.index', ['tab' => 'statuses'])
            ->with('success', 'تمت إضافة الحالة الوظيفية بنجاح.');
    }

    public function updateStatus(Request $request, $id)
    {
        $status = EmploymentStatus::findOrFail($id);

        $validated = $request->validate([
            'status_code' => 'required|string|max:50|unique:employment_statuses,status_code,' . $status->employment_status_id . ',employment_status_id',
            'status_name_ar' => 'required|string|max:120|unique:employment_statuses,status_name_ar,' . $status->employment_status_id . ',employment_status_id',
            'is_active_status' => 'required|boolean',
        ]);

        $oldName = $status->status_name_ar;
        $status->update([
            'status_code' => strtoupper($validated['status_code']),
            'status_name_ar' => $validated['status_name_ar'],
            'is_active_status' => (bool)$validated['is_active_status'],
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('STATUS_UPDATE', 'employment_statuses', $status->employment_status_id, ['status_name_ar' => $oldName], $validated);

        return redirect()->route('master_data.index', ['tab' => 'statuses'])
            ->with('success', 'تم تحديث الحالة الوظيفية بنجاح.');
    }

    public function destroyStatus($id)
    {
        $status = EmploymentStatus::findOrFail($id);

        if (EmploymentRecord::where('employment_status_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'statuses'])
                ->with('error', 'لا يمكن حذف هذه الحالة الوظيفية لارتباطها بموظفين وكادر مسجلين بها.');
        }

        $name = $status->status_name_ar;
        $status->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('STATUS_DELETE', 'employment_statuses', $id, ['status_name_ar' => $name], null);

        return redirect()->route('master_data.index', ['tab' => 'statuses'])
            ->with('success', 'تم حذف الحالة الوظيفية بنجاح.');
    }

    // =========================================================================
    // 6. المسميات الوظيفية (Job Titles)
    // =========================================================================

    public function storeJobTitle(Request $request)
    {
        $validated = $request->validate([
            'title_name_ar' => 'required|string|max:180|unique:job_titles,title_name_ar',
            'title_name_en' => 'nullable|string|max:180',
        ]);

        $jt = JobTitle::create([
            'title_name_ar' => $validated['title_name_ar'],
            'title_name_en' => $validated['title_name_en'] ?? null,
            'is_active' => true,
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('JOBTITLE_CREATE', 'job_titles', $jt->job_title_id, null, $validated);

        return redirect()->route('master_data.index', ['tab' => 'job_titles'])
            ->with('success', 'تمت إضافة المسمى الوظيفي بنجاح.');
    }

    public function updateJobTitle(Request $request, $id)
    {
        $jobTitle = JobTitle::findOrFail($id);

        $validated = $request->validate([
            'title_name_ar' => 'required|string|max:180|unique:job_titles,title_name_ar,' . $jobTitle->job_title_id . ',job_title_id',
            'title_name_en' => 'nullable|string|max:180',
        ]);

        $oldName = $jobTitle->title_name_ar;
        $jobTitle->update([
            'title_name_ar' => $validated['title_name_ar'],
            'title_name_en' => $validated['title_name_en'] ?? null,
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('JOBTITLE_UPDATE', 'job_titles', $jobTitle->job_title_id, ['title_name_ar' => $oldName], $validated);

        return redirect()->route('master_data.index', ['tab' => 'job_titles'])
            ->with('success', 'تم تحديث المسمى الوظيفي بنجاح.');
    }

    public function destroyJobTitle($id)
    {
        $jobTitle = JobTitle::findOrFail($id);

        if (EmploymentRecord::where('job_title_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'job_titles'])
                ->with('error', 'لا يمكن حذف هذا المسمى الوظيفي لارتباطه بسجلات وظيفية لكادر مسجلين به.');
        }

        $name = $jobTitle->title_name_ar;
        $jobTitle->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('JOBTITLE_DELETE', 'job_titles', $id, ['title_name_ar' => $name], null);

        return redirect()->route('master_data.index', ['tab' => 'job_titles'])
            ->with('success', 'تم حذف المسمى الوظيفي بنجاح.');
    }

    // =========================================================================
    // 7. أنواع القرارات الإدارية (Decision Types)
    // =========================================================================

    public function storeDecisionType(Request $request)
    {
        $validated = $request->validate([
            'type_code' => 'required|string|max:50|unique:decision_types,type_code',
            'type_name_ar' => 'required|string|max:150|unique:decision_types,type_name_ar',
            'requires_end_date' => 'nullable|boolean',
        ]);

        $dt = DecisionType::create([
            'type_code' => strtoupper($validated['type_code']),
            'type_name_ar' => $validated['type_name_ar'],
            'requires_end_date' => $request->has('requires_end_date'),
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('DECISIONTYPE_CREATE', 'decision_types', $dt->decision_type_id, null, $validated);

        return redirect()->route('master_data.index', ['tab' => 'decision_types'])
            ->with('success', 'تمت إضافة نوع القرار الإداري بنجاح.');
    }

    public function updateDecisionType(Request $request, $id)
    {
        $decisionType = DecisionType::findOrFail($id);

        $validated = $request->validate([
            'type_code' => 'required|string|max:50|unique:decision_types,type_code,' . $decisionType->decision_type_id . ',decision_type_id',
            'type_name_ar' => 'required|string|max:150|unique:decision_types,type_name_ar,' . $decisionType->decision_type_id . ',decision_type_id',
            'requires_end_date' => 'nullable|boolean',
        ]);

        $oldName = $decisionType->type_name_ar;
        $decisionType->update([
            'type_code' => strtoupper($validated['type_code']),
            'type_name_ar' => $validated['type_name_ar'],
            'requires_end_date' => $request->has('requires_end_date'),
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('DECISIONTYPE_UPDATE', 'decision_types', $decisionType->decision_type_id, ['type_name_ar' => $oldName], $validated);

        return redirect()->route('master_data.index', ['tab' => 'decision_types'])
            ->with('success', 'تم تحديث نوع القرار الإداري بنجاح.');
    }

    public function destroyDecisionType($id)
    {
        $decisionType = DecisionType::findOrFail($id);

        if (EmployeeDecision::where('decision_type_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'decision_types'])
                ->with('error', 'لا يمكن حذف نوع القرار لوجود قرارات إدارية مسجلة بهذا النوع.');
        }

        $name = $decisionType->type_name_ar;
        $decisionType->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('DECISIONTYPE_DELETE', 'decision_types', $id, ['type_name_ar' => $name], null);

        return redirect()->route('master_data.index', ['tab' => 'decision_types'])
            ->with('success', 'تم حذف نوع القرار الإداري بنجاح.');
    }

    // =========================================================================
    // 8. الجامعات (Universities)
    // =========================================================================

    public function storeUniversity(Request $request)
    {
        $validated = $request->validate([
            'university_name_ar' => 'required|string|max:220|unique:universities,university_name_ar',
            'country_id' => 'nullable|exists:countries,country_id',
        ]);

        $univ = University::create([
            'university_name_ar' => $validated['university_name_ar'],
            'country_id' => $validated['country_id'] ?? null,
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('UNIVERSITY_CREATE', 'universities', $univ->university_id, null, $validated);

        return redirect()->route('master_data.index', ['tab' => 'universities'])
            ->with('success', 'تمت إضافة الجامعة بنجاح.');
    }

    public function updateUniversity(Request $request, $id)
    {
        $university = University::findOrFail($id);

        $validated = $request->validate([
            'university_name_ar' => 'required|string|max:220|unique:universities,university_name_ar,' . $university->university_id . ',university_id',
            'country_id' => 'nullable|exists:countries,country_id',
        ]);

        $oldName = $university->university_name_ar;
        $university->update([
            'university_name_ar' => $validated['university_name_ar'],
            'country_id' => $validated['country_id'] ?? null,
        ]);

        Cache::forget('dashboard_core_metrics');
        AuditLogService::log('UNIVERSITY_UPDATE', 'universities', $university->university_id, ['university_name_ar' => $oldName], $validated);

        return redirect()->route('master_data.index', ['tab' => 'universities'])
            ->with('success', 'تم تحديث بيانات الجامعة بنجاح.');
    }

    public function destroyUniversity($id)
    {
        $university = University::findOrFail($id);

        if (EmployeeQualification::where('university_id', $id)->exists()) {
            return redirect()->route('master_data.index', ['tab' => 'universities'])
                ->with('error', 'لا يمكن حذف هذه الجامعة لوجود مؤهلات أكاديمية للكادر مسجلة صادرة عنها.');
        }

        $name = $university->university_name_ar;
        $university->delete();
        Cache::forget('dashboard_core_metrics');

        AuditLogService::log('UNIVERSITY_DELETE', 'universities', $id, ['university_name_ar' => $name], null);

        return redirect()->route('master_data.index', ['tab' => 'universities'])
            ->with('success', 'تم حذف الجامعة بنجاح.');
    }

    /**
     * إحصائيات تفصيلية كاملة للكلية أو القسم أو المركز (Unit Statistics API)
     */
    public function getUnitStats(Request $request, $id)
    {
        $unit = OrganizationalUnit::with([
            'children.employmentRecords.employee.currentEmployment.academicRank',
            'children.employmentRecords.employee.currentEmployment.employmentStatus',
            'children.employmentRecords.employee.currentEmployment.category',
            'children.employmentRecords.employee.documents',
            'employmentRecords.employee.currentEmployment.academicRank',
            'employmentRecords.employee.currentEmployment.employmentStatus',
            'employmentRecords.employee.currentEmployment.category',
            'employmentRecords.employee.documents',
            'parent'
        ])->findOrFail($id);

        $isFaculty = in_array($unit->unit_type, ['faculty', 'college', 'center', 'administration']);

        // جميع سجلات الكادر المرتبطة بالجهة أو بأقسامها التابعة
        $allRecords = $unit->employmentRecords;
        if ($isFaculty) {
            $allRecords = $allRecords->merge($unit->children->flatMap->employmentRecords);
        }

        $employees = $allRecords->map(fn($r) => $r->employee)->filter()->unique('employee_id');
        $documents = $allRecords->flatMap(fn($r) => $r->employee?->documents ?? collect())->unique('document_id');

        // قائمة الأقسام مع إحصائيات كل قسم
        $departments = [];
        if ($isFaculty) {
            foreach ($unit->children as $child) {
                $cEmployees = $child->employmentRecords->map(fn($r) => $r->employee)->filter()->unique('employee_id');
                $cDocs = $child->employmentRecords->flatMap(fn($r) => $r->employee?->documents ?? collect())->unique('document_id');
                $departments[] = [
                    'id' => $child->organizational_unit_id,
                    'name' => $child->unit_name_ar,
                    'code' => $child->unit_code ?? '—',
                    'staff_count' => $cEmployees->count(),
                    'docs_count' => $cDocs->count(),
                ];
            }
        }

        // توزيع الرتب الأكاديمية
        $ranksBreakdown = [];
        foreach ($employees as $emp) {
            $rankName = $emp->currentEmployment?->academicRank?->rank_name_ar ?? 'بدون رتبة محددة';
            $ranksBreakdown[$rankName] = ($ranksBreakdown[$rankName] ?? 0) + 1;
        }

        // توزيع الحالات الوظيفية
        $statusBreakdown = [];
        foreach ($employees as $emp) {
            $statusName = $emp->currentEmployment?->employmentStatus?->status_name_ar ?? 'غير محدد';
            $statusBreakdown[$statusName] = ($statusBreakdown[$statusName] ?? 0) + 1;
        }

        // توزيع الفئات الوظيفية
        $categoryBreakdown = [];
        foreach ($employees as $emp) {
            $catName = $emp->currentEmployment?->category?->category_name_ar ?? 'غير مصنف';
            $categoryBreakdown[$catName] = ($categoryBreakdown[$catName] ?? 0) + 1;
        }

        // إحصائيات النوع الاجتماعي
        $maleCount = $employees->where('gender', 'male')->count();
        $femaleCount = $employees->where('gender', 'female')->count();

        // إحصاء كادر على رأس العمل
        $activeStaffCount = 0;
        foreach ($employees as $emp) {
            $stName = $emp->currentEmployment?->employmentStatus?->status_name_ar ?? '';
            if (str_contains($stName, 'على رأس') || str_contains($stName, 'مباشر') || str_contains($stName, 'نشط')) {
                $activeStaffCount++;
            }
        }
        if ($activeStaffCount === 0 && $employees->count() > 0) {
            $activeStaffCount = $employees->count();
        }

        return response()->json([
            'unit' => [
                'id' => $unit->organizational_unit_id,
                'name' => $unit->unit_name_ar,
                'code' => $unit->unit_code ?? '—',
                'type' => $unit->unit_type,
                'parent_name' => $unit->parent?->unit_name_ar ?? 'جامعة إب',
                'is_faculty' => $isFaculty,
            ],
            'stats' => [
                'departments_count' => count($departments),
                'staff_count' => $employees->count(),
                'active_staff_count' => $activeStaffCount,
                'documents_count' => $documents->count(),
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'ranks' => $ranksBreakdown,
                'statuses' => $statusBreakdown,
                'categories' => $categoryBreakdown,
                'departments' => $departments,
            ]
        ]);
    }
}
