<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\DecisionType;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\EmployeeDecision;
use App\Models\EmployeeQualification;
use App\Models\EmploymentRecord;
use App\Models\EmploymentStatus;
use App\Models\JobGrade;
use App\Models\OrganizationalUnit;
use App\Models\QualificationLevel;
use App\Services\AuditLogService;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * دليل واستعلام الكادر المتعدد المعايير
     */
    public function index(Request $request)
    {
        $query = Employee::with([
            'photoDocument.currentVersion',
            'currentEmployment.organizationalUnit.parent',
            'currentEmployment.academicRank',
            'currentEmployment.jobGrade',
            'currentEmployment.employmentStatus',
            'currentEmployment.category',
        ])
        ->withCount('documents')
        ->where('is_active_record', true);

        // 1. بحث نصي سريع (الاسم، الرقم الوظيفي، الرقم الوطني، الهاتف)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name_ar', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // 2. تصفية الكلية / الجهة العليا
        if ($request->filled('college_id')) {
            $collegeId = $request->college_id;
            $query->whereHas('currentEmployment.organizationalUnit', function ($q) use ($collegeId) {
                $q->where('organizational_unit_id', $collegeId)
                  ->orWhere('parent_unit_id', $collegeId);
            });
        }

        // 3. تصفية القسم
        if ($request->filled('unit_id')) {
            $query->whereHas('currentEmployment', fn($q) => $q->where('organizational_unit_id', $request->unit_id));
        }

        // 4. تصفية الفئة
        if ($request->filled('category_id')) {
            $query->whereHas('currentEmployment', fn($q) => $q->where('employee_category_id', $request->category_id));
        }

        // 5. تصفية الرتبة
        if ($request->filled('rank_id')) {
            $query->whereHas('currentEmployment', fn($q) => $q->where('academic_rank_id', $request->rank_id));
        }

        // 6. تصفية الحالة
        if ($request->filled('status_id')) {
            $query->whereHas('currentEmployment', fn($q) => $q->where('employment_status_id', $request->status_id));
        }

        // 7. تصفية الجنس
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $perPage = 10;
        $employees = $query->latest('created_at')->paginate($perPage)->withQueryString();
        $totalMatched = $employees->total();

        // في حال كان الطلب عبر AJAX للتمرير اللانهائي (Infinite Scroll)
        if ($request->ajax() || $request->has('ajax')) {
            $startIndex = ($employees->currentPage() - 1) * $perPage;
            $html = view('employees._rows', [
                'employees' => $employees,
                'startIndex' => $startIndex,
                'isAppend' => true,
            ])->render();

            return response()->json([
                'html' => $html,
                'hasMore' => $employees->hasMorePages(),
                'nextPage' => $employees->currentPage() + 1,
                'loadedCount' => min($employees->currentPage() * $perPage, $employees->total()),
                'total' => $employees->total(),
            ]);
        }

        // القوائم المرجعية للفلاتر
        $colleges = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center', 'administration'])->orderBy('unit_name_ar')->get();
        $departments = OrganizationalUnit::where('unit_type', 'department')->orderBy('unit_name_ar')->get();
        $categories = EmployeeCategory::all();
        $ranks = AcademicRank::orderBy('rank_order')->get();
        $statuses = EmploymentStatus::all();

        return view('employees.index', compact(
            'employees',
            'totalMatched',
            'colleges',
            'departments',
            'categories',
            'ranks',
            'statuses'
        ));
    }

    /**
     * نموذج إضافة موظف جديد (معالج ذكي 4 خطوات)
     */
    public function create()
    {
        $colleges = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center', 'administration'])
            ->with(['children' => fn($q) => $q->where('unit_type', 'department')])
            ->orderBy('unit_name_ar')
            ->get();

        $categories = EmployeeCategory::all();
        $ranks = AcademicRank::orderBy('rank_order')->get();
        $grades = JobGrade::all();
        $statuses = EmploymentStatus::all();
        $decisionTypes = DecisionType::all();
        $qualLevels = QualificationLevel::orderBy('level_order')->get();
        $docTypes = DocumentType::where('is_active', true)->get();

        return view('employees.create', compact(
            'colleges',
            'categories',
            'ranks',
            'grades',
            'statuses',
            'decisionTypes',
            'qualLevels',
            'docTypes'
        ));
    }

    /**
     * حفظ وتخزين الموظف وسجلاته التأسيسية
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name_ar' => 'required|string|max:250',
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number',
            'national_id' => 'nullable|string|max:50|unique:employees,national_id',
            'gender' => 'required|in:male,female',
            'phone_number' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:254',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:180',
            
            // بيانات التكليف والوظيفة
            'organizational_unit_id' => 'required|exists:organizational_units,organizational_unit_id',
            'employee_category_id' => 'required|exists:employee_categories,employee_category_id',
            'academic_rank_id' => 'nullable|exists:academic_ranks,academic_rank_id',
            'job_grade_id' => 'nullable|exists:job_grades,job_grade_id',
            'employment_status_id' => 'required|exists:employment_statuses,employment_status_id',
            'start_date' => 'required|date',
            'appointment_basis' => 'nullable|string|max:100',

            // بيانات كشف الراتب وتاريخ التعيين الرسمي
            'first_appointment_date' => 'nullable|date',
            'academic_appointment_date' => 'nullable|date',
            'qualification_at_appointment' => 'nullable|string|max:150',
            'initial_rank' => 'nullable|string|max:100',
            'payroll_status' => 'nullable|string|max:100',
            'payroll_job_title' => 'nullable|string|max:150',
            'payroll_job_grade' => 'nullable|string|max:100',
            'photo_file' => 'nullable|image|max:10240',
            'university_name' => 'nullable|string|max:200',

            // ملف الأرشيف (PDF أو صورة)
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:51200',
            'document_type_id' => 'nullable|exists:document_types,document_type_id',
            'document_title' => 'nullable|string|max:250',
        ]);

        DB::beginTransaction();
        try {
            // توحيد حفظ الجنس معيارياً
            $rawGender = strtolower(trim($validated['gender'] ?? 'male'));
            $cleanGender = in_array($rawGender, ['female', 'f', 'أنثى', 'انثى'], true) ? 'female' : 'male';

            // 1. إنشاء الموظف
            $employee = Employee::create([
                'employee_number' => $validated['employee_number'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'full_name_ar' => $validated['full_name_ar'],
                'gender' => $cleanGender,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'place_of_birth' => $validated['place_of_birth'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $request->input('address', null),
                'notes' => $request->input('notes', null),
                'is_active_record' => true,
            ]);

            // 1.1 حفظ الصورة الشخصية إن وجدت
            if ($request->hasFile('photo_file')) {
                DocumentStorageService::storeEmployeePhoto($employee, $request->file('photo_file'));
            }

            // 2. قرار إداري تأسيسي (إن وجد)
            $decisionId = null;
            if ($request->filled('decision_number')) {
                $decision = EmployeeDecision::create([
                    'employee_id' => $employee->employee_id,
                    'decision_type_id' => $request->input('decision_type_id', 1),
                    'decision_number' => $request->input('decision_number'),
                    'issuing_authority' => $request->input('issuing_authority', 'رئيس جامعة إب'),
                    'issue_date' => $request->input('issue_date', now()->toDateString()),
                    'effective_start_date' => $validated['start_date'],
                    'decision_status_id' => 1,
                    'summary' => $request->input('decision_summary', 'قرار تعيين / تكليف رسمي'),
                    'created_at' => now(),
                ]);
                $decisionId = $decision->decision_id;
            }

            // 3. إنشاء سجل التوظيف الأولي (Employment Record التاريخي)
            $employmentRecord = EmploymentRecord::create([
                'employee_id' => $employee->employee_id,
                'employee_category_id' => $validated['employee_category_id'],
                'organizational_unit_id' => $validated['organizational_unit_id'],
                'job_grade_id' => $validated['job_grade_id'] ?? null,
                'academic_rank_id' => $validated['academic_rank_id'] ?? null,
                'employment_status_id' => $validated['employment_status_id'],
                'start_date' => $validated['start_date'],
                'end_date' => null,
                'is_primary' => true,
                'appointment_basis' => $validated['appointment_basis'] ?? 'تثبيت رسمي',
                'first_appointment_date' => $request->input('first_appointment_date', null),
                'academic_appointment_date' => $request->input('academic_appointment_date', null),
                'qualification_at_appointment' => $request->input('qualification_at_appointment', null),
                'initial_rank' => $request->input('initial_rank', null),
                'payroll_status' => $request->input('payroll_status', null),
                'payroll_job_title' => $request->input('payroll_job_title', null),
                'payroll_job_grade' => $request->input('payroll_job_grade', null),
                'decision_id' => $decisionId,
                'notes' => 'سجل التكليف الأولي عند إنشاء الملف في النظام',
                'created_at' => now(),
            ]);

            // 4. تسجيل المؤهل الأكاديمي (إن وجد)
            if ($request->filled('qualification_level_id')) {
                $univId = null;
                if ($request->filled('university_name')) {
                    $u = \App\Models\University::firstOrCreate(
                        ['university_name_ar' => trim($request->input('university_name'))]
                    );
                    $univId = $u->university_id;
                }

                EmployeeQualification::create([
                    'employee_id' => $employee->employee_id,
                    'qualification_level_id' => $request->input('qualification_level_id'),
                    'degree_title' => $request->input('degree_title', 'مؤهل أكاديمي معتمد'),
                    'university_id' => $univId,
                    'grade_or_gpa' => $request->input('grade_or_gpa', null),
                    'graduation_date' => $request->input('graduation_date', null),
                    'is_highest_qualification' => true,
                    'verification_status' => 'verified',
                    'notes' => $request->input('qual_notes', null),
                    'created_at' => now(),
                ]);
            }

            // 5. حفظ وثيقة الأرشيف عبر الخدمة المركزية الموحدة
            if ($request->hasFile('document_file')) {
                $document = DocumentStorageService::storeEmployeeDocument($employee, $request->file('document_file'), [
                    'document_type_id' => $request->input('document_type_id'),
                    'document_title' => $request->input('document_title'),
                    'document_number' => $request->input('decision_number'),
                    'is_confidential' => true,
                ]);

                // ربط الوثيقة بسجل التوظيف
                $employmentRecord->documents()->attach($document->document_id);
            }

            AuditLogService::log('EMPLOYEE_CREATE', 'employees', $employee->employee_id, null, [
                'full_name_ar' => $employee->full_name_ar,
                'employee_number' => $employee->employee_number,
            ]);

            // إبطال كاش لوحة التحكم لتحديث الأرقام والإحصائيات لحظياً
            Cache::forget('dashboard_core_metrics');

            DB::commit();

            return redirect()->route('employees.show', $employee->employee_id)
                ->with('success', 'تم حفظ وتأسيس ملف الموظف وسجلاته الرسمية بنجاح!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage());
        }
    }

    /**
     * الملف الموحد 360° مع السجل التاريخي الكامل وشجرة الأرشيف
     */
    public function show($id)
    {
        $employee = Employee::with([
            'photoDocument.currentVersion',
            'employmentRecords.organizationalUnit.parent',
            'employmentRecords.category',
            'employmentRecords.academicRank',
            'employmentRecords.jobGrade',
            'employmentRecords.employmentStatus',
            'employmentRecords.decision',
            'employmentRecords.documents.currentVersion',
            'qualifications.level',
            'qualifications.university',
            'qualifications.country',
            'decisions.type',
            'decisions.status',
            'decisions.documents.currentVersion',
            'documents.currentVersion',
            'documents.documentType',
            'absences.decision'
        ])->findOrFail($id);

        // جلب القوائم المرجعية لتغذية النوافذ المنبثقة للعمليات المباشرة
        $colleges = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center', 'administration'])
            ->with(['children' => fn($q) => $q->where('unit_type', 'department')])
            ->orderBy('unit_name_ar')
            ->get();

        $categories = EmployeeCategory::all();
        $ranks = AcademicRank::orderBy('rank_order')->get();
        $grades = JobGrade::all();
        $statuses = EmploymentStatus::all();
        $decisionTypes = DecisionType::all();
        $decisionStatuses = \App\Models\DecisionStatus::all();
        $qualLevels = QualificationLevel::orderBy('level_order')->get();
        $docTypes = DocumentType::where('is_active', true)->get();
        $universities = \App\Models\University::orderBy('university_name_ar')->get();
        $countries = \App\Models\Country::orderBy('country_name_ar')->get();

        return view('employees.show', compact(
            'employee',
            'colleges',
            'categories',
            'ranks',
            'grades',
            'statuses',
            'decisionTypes',
            'decisionStatuses',
            'qualLevels',
            'docTypes',
            'universities',
            'countries'
        ));
    }

    /**
     * حذف موظف
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['is_active_record' => false]);

        AuditLogService::log('EMPLOYEE_DELETE', 'employees', $employee->employee_id, [
            'is_active_record' => true,
        ], [
            'is_active_record' => false,
            'full_name_ar' => $employee->full_name_ar,
        ]);

        // إبطال كاش لوحة التحكم لتحديث الأرقام والإحصائيات لحظياً
        Cache::forget('dashboard_core_metrics');

        return redirect()->route('employees.index')->with('success', 'تم أرشفة وتعطيل سجل الموظف بنجاح!');
    }
}
