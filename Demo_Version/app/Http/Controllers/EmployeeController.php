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
use App\Models\JobTitle;
use App\Models\OrganizationalUnit;
use App\Models\QualificationLevel;
use App\Models\University;
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
            'currentEmployment.jobTitle',
            'currentEmployment.category',
            'latestEmployment.organizationalUnit.parent',
            'latestEmployment.academicRank',
            'latestEmployment.jobGrade',
            'latestEmployment.employmentStatus',
            'latestEmployment.jobTitle',
            'latestEmployment.category',
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
        $collegeId = $request->college_id ?? $request->faculty_id;
        if (!empty($collegeId)) {
            $query->whereHas('currentEmployment.organizationalUnit', function ($q) use ($collegeId) {
                $q->where('organizational_unit_id', $collegeId)
                  ->orWhere('parent_unit_id', $collegeId);
            });
        }

        // 3. تصفية القسم
        $unitId = $request->unit_id ?? $request->dept_id;
        if (!empty($unitId)) {
            $query->whereHas('currentEmployment', fn($q) => $q->where('organizational_unit_id', $unitId));
        }

        $activeUnit = null;
        if (!empty($unitId)) {
            $activeUnit = OrganizationalUnit::find($unitId);
        } elseif (!empty($collegeId)) {
            $activeUnit = OrganizationalUnit::find($collegeId);
        }

        // 4. تصفية نوع الكادر (أكاديمي / إداري / متقاعدين بالساعات)
        $cadreType = $request->cadre_type ?? $request->category_id;
        if (!empty($cadreType)) {
            if ($cadreType === 'academic' || $cadreType == '1') {
                $query->whereHas('currentEmployment', fn($q) => $q->where('employee_category_id', 1));
            } elseif ($cadreType === 'admin' || $cadreType == '2') {
                $query->whereHas('currentEmployment', fn($q) => $q->whereIn('employee_category_id', [2, 3, 4]));
            } elseif ($cadreType === 'hourly' || $cadreType == '7') {
                $query->whereHas('currentEmployment', function($q) {
                    $q->where('employee_category_id', 7)
                      ->orWhere('appointment_basis', 'like', '%ساعات%')
                      ->orWhere('appointment_basis', 'like', '%ساعة%')
                      ->orWhere('notes', 'like', '%ساعات%');
                });
            } else {
                $query->whereHas('currentEmployment', fn($q) => $q->where('employee_category_id', $cadreType));
            }
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

        // إحصائيات التبويبات الثلاثية لأنواع الكادر
        $academicCount = Employee::where('is_active_record', true)
            ->whereHas('currentEmployment', fn($q) => $q->where('employee_category_id', 1))
            ->count();
        $adminCount = Employee::where('is_active_record', true)
            ->whereHas('currentEmployment', fn($q) => $q->whereIn('employee_category_id', [2, 3, 4]))
            ->count();
        $hourlyCount = Employee::where('is_active_record', true)
            ->whereHas('currentEmployment', function($q) {
                $q->where('employee_category_id', 7)
                  ->orWhere('appointment_basis', 'like', '%ساعات%')
                  ->orWhere('appointment_basis', 'like', '%ساعة%')
                  ->orWhere('notes', 'like', '%ساعات%');
            })->count();
        $totalAllCount = Employee::where('is_active_record', true)->count();

        return view('employees.index', compact(
            'employees',
            'totalMatched',
            'colleges',
            'departments',
            'categories',
            'ranks',
            'statuses',
            'activeUnit',
            'cadreType',
            'academicCount',
            'adminCount',
            'hourlyCount',
            'totalAllCount'
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
        $jobTitles = JobTitle::where('is_active', true)->orderBy('title_name_ar')->get();
        $universities = University::orderBy('university_name_ar')->get();

        // توليد رقم القرار الإداري التلقائي
        $maxDecisionNum = EmployeeDecision::selectRaw("COALESCE(MAX(CAST(REGEXP_REPLACE(decision_number, '[^0-9]', '', 'g') AS INTEGER)), 202) as max_num")->value('max_num') ?? 202;
        $nextDecisionNumber = $maxDecisionNum + 1;

        return view('employees.create', compact(
            'colleges',
            'categories',
            'ranks',
            'grades',
            'statuses',
            'decisionTypes',
            'qualLevels',
            'docTypes',
            'jobTitles',
            'universities',
            'nextDecisionNumber'
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
            'employee_category_id' => 'nullable|exists:employee_categories,employee_category_id',
            'cadre_type' => 'nullable|in:academic,admin,hourly',
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

            // ملف الأرشيف (PDF أو صورة حتى 25MB)
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:25600',
            'document_type_id' => 'nullable|exists:document_types,document_type_id',
            'document_title' => 'nullable|string|max:250',
        ], [
            'full_name_ar.required' => 'يرجى إدخال اسم الكادر الرباعي كاملاً.',
            'organizational_unit_id.required' => 'يرجى اختيار الكلية والقسم التابع لهما الكادر.',
            'employee_category_id.required' => 'يرجى تحديد الفئة الوظيفية للكادر.',
            'employment_status_id.required' => 'يرجى تحديد الحالة الوظيفية.',
            'start_date.required' => 'يرجى إدخال تاريخ مباشرة العمل.',
            'document_file.uploaded' => 'تعذر رفع ملف الوثيقة، يرجى التأكد من أن حجم الملف لا يتجاوز 25 ميجابايت وصيغته PDF أو صورة.',
            'document_file.max' => 'حجم ملف الأرشيف يتجاوز الحد الأقصى المسموح به (25 ميجابايت).',
            'document_file.mimes' => 'نوع ملف الأرشيف غير مدعوم، يرجى رفع ملف PDF أو صورة (JPG, PNG).',
            'photo_file.max' => 'حجم الصورة الشخصية يتجاوز الحد الأقصى (10 ميجابايت).',
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

            // تحديد نوع الكادر وفئته المؤسسية
            $cadreType = $request->input('cadre_type', 'academic');
            $categoryId = $request->input('employee_category_id');
            if ($cadreType === 'admin') {
                $categoryId = 2; // إداري
            } elseif ($cadreType === 'hourly') {
                $categoryId = 7; // متقاعدين بالساعات
            } elseif ($cadreType === 'academic' || empty($categoryId)) {
                $categoryId = 1; // أكاديمي
            }

            // تجميع الملاحظات الإضافية الخاصة بنوع الكادر
            $customNotes = [];
            if ($cadreType === 'hourly') {
                if ($request->filled('hourly_hours')) $customNotes[] = "نصاب الساعات: " . $request->hourly_hours . " ساعة";
                if ($request->filled('hourly_rate')) $customNotes[] = "أجر الساعة: " . $request->hourly_rate . " ريال";
                if ($request->filled('academic_year')) $customNotes[] = "العام الجامعي: " . $request->academic_year;
                if ($request->filled('retirement_source')) $customNotes[] = "جهة التقاعد: " . $request->retirement_source;
            } elseif ($cadreType === 'admin') {
                if ($request->filled('admin_job_title')) $customNotes[] = "المسمى الإداري: " . $request->admin_job_title;
            }
            if ($request->filled('notes')) {
                $customNotes[] = $request->notes;
            }
            $finalNotes = !empty($customNotes) ? implode(' | ', $customNotes) : 'سجل التكليف الأولي عند إنشاء الملف في النظام';

            // تحديد أساس التكليف والقرار بحسب نوع الكادر
            $defaultBasis = match($cadreType) {
                'hourly' => 'تدريس بالساعات الإضافية',
                'admin' => 'تثبيت إداري رسمي',
                default => 'تثبيت رسمي'
            };
            $appointmentBasis = $request->filled('appointment_basis') ? $request->appointment_basis : $defaultBasis;

            $decisionSummary = match($cadreType) {
                'hourly' => 'قرار استعانة وتكليف بالساعات',
                'admin' => 'قرار تعيين وتسكين إداري',
                default => 'قرار تعيين / تكليف رسمي'
            };

            // 2. قرار إداري تأسيسي (مرقم تلقائياً أو وفق المدخل)
            $decisionId = null;
            $decisionNum = $request->filled('decision_number')
                ? trim($request->input('decision_number'))
                : (string)((EmployeeDecision::selectRaw("COALESCE(MAX(CAST(REGEXP_REPLACE(decision_number, '[^0-9]', '', 'g') AS INTEGER)), 202) as max_num")->value('max_num') ?? 202) + 1);

            $decision = EmployeeDecision::create([
                'employee_id' => $employee->employee_id,
                'decision_type_id' => $request->input('decision_type_id', 1),
                'decision_number' => $decisionNum,
                'issuing_authority' => $request->input('issuing_authority', 'رئيس جامعة إب'),
                'issue_date' => $request->input('issue_date', now()->toDateString()),
                'effective_start_date' => $validated['start_date'],
                'decision_status_id' => 1,
                'summary' => $request->input('decision_summary', $decisionSummary),
                'created_at' => now(),
            ]);
            $decisionId = $decision->decision_id;

            // تحديد المسمى بكشف الراتب
            $payrollTitle = $request->input('payroll_job_title');
            if ($cadreType === 'admin' && empty($payrollTitle) && $request->filled('admin_job_title')) {
                $payrollTitle = $request->admin_job_title;
            } elseif ($cadreType === 'hourly' && empty($payrollTitle)) {
                $payrollTitle = 'متقاعد بنظام الساعات';
            }

            // تحديد حالة الراتب
            $payrollStatus = $request->input('payroll_status');
            if ($cadreType === 'hourly' && empty($payrollStatus)) {
                $payrollStatus = 'متقاعد بالساعات';
            }

            // الرتبة الأكاديمية: null للإداري
            $academicRankId = ($cadreType === 'admin') ? null : ($validated['academic_rank_id'] ?? null);
            $academicAppDate = ($cadreType === 'admin') ? null : $request->input('academic_appointment_date', null);

            // 3. إنشاء سجل التوظيف الأولي (Employment Record التاريخي)
            $employmentRecord = EmploymentRecord::create([
                'employee_id' => $employee->employee_id,
                'employee_category_id' => $categoryId,
                'organizational_unit_id' => $validated['organizational_unit_id'],
                'job_grade_id' => $validated['job_grade_id'] ?? null,
                'academic_rank_id' => $academicRankId,
                'employment_status_id' => $validated['employment_status_id'],
                'start_date' => $validated['start_date'],
                'end_date' => null,
                'is_primary' => true,
                'appointment_basis' => $appointmentBasis,
                'first_appointment_date' => $request->input('first_appointment_date', null),
                'academic_appointment_date' => $academicAppDate,
                'qualification_at_appointment' => $request->input('qualification_at_appointment', null),
                'initial_rank' => $request->input('initial_rank', null),
                'payroll_status' => $payrollStatus,
                'payroll_job_title' => $payrollTitle,
                'payroll_job_grade' => $request->input('payroll_job_grade', null),
                'decision_id' => $decisionId,
                'notes' => $finalNotes,
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
            'currentEmployment.organizationalUnit.parent',
            'currentEmployment.academicRank',
            'currentEmployment.jobGrade',
            'currentEmployment.employmentStatus',
            'currentEmployment.category',
            'currentEmployment.decision',
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

    /**
     * تصدير بيانات الكوادر المفلترة إلى Excel / CSV مرمز بـ UTF-8 BOM
     */
    public function exportExcel(Request $request)
    {
        $query = Employee::with([
            'currentEmployment.organizationalUnit.parent',
            'currentEmployment.academicRank',
            'currentEmployment.jobGrade',
            'currentEmployment.employmentStatus',
            'currentEmployment.jobTitle',
            'currentEmployment.category',
        ])->where('is_active_record', true);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('full_name_ar', 'ilike', "%{$s}%")
                  ->orWhere('employee_number', 'ilike', "%{$s}%")
                  ->orWhere('national_id', 'ilike', "%{$s}%");
            });
        }

        if ($request->filled('college_id')) {
            $collegeId = $request->college_id;
            $query->whereHas('currentEmployment.organizationalUnit', function ($q) use ($collegeId) {
                $q->where('organizational_unit_id', $collegeId)
                  ->orWhere('parent_unit_id', $collegeId);
            });
        }

        if ($request->filled('dept_id')) {
            $deptId = $request->dept_id;
            $query->whereHas('currentEmployment', function ($q) use ($deptId) {
                $q->where('organizational_unit_id', $deptId);
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('currentEmployment', function ($q) use ($request) {
                $q->where('employee_category_id', $request->category_id);
            });
        }

        if ($request->filled('tab')) {
            if ($request->tab == 'academic') {
                $query->whereHas('currentEmployment', fn($q) => $q->where('employee_category_id', 1));
            } elseif ($request->tab == 'admin') {
                $query->whereHas('currentEmployment', fn($q) => $q->whereIn('employee_category_id', [2, 3, 4]));
            } elseif ($request->tab == 'hourly') {
                $query->whereHas('currentEmployment', fn($q) => $q->where('employee_category_id', 7));
            }
        }

        $employees = $query->orderBy('full_name_ar')->get();
        $fileName = 'دليل_كوادر_جامعة_إب_' . date('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($handle, [
                'الرقم الوظيفي',
                'الاسم الرباعي',
                'الرقم الوطني',
                'فئة الكادر',
                'الرتبة الأكاديمية',
                'المسمى الوظيفي',
                'الجهة / الكلية',
                'القسم العلمي',
                'الحالة الوظيفية',
                'الهاتف',
                'البريد الإلكتروني',
                'تاريخ الميلاد'
            ]);

            foreach ($employees as $emp) {
                $empRecord = $emp->currentEmployment ?? $emp->latestEmployment;
                $unit = $empRecord?->organizationalUnit;
                $collegeName = $unit?->parent ? $unit->parent->unit_name_ar : ($unit?->unit_name_ar ?? 'جامعة إب');
                $deptName = $unit?->parent ? $unit->unit_name_ar : '—';

                fputcsv($handle, [
                    $emp->employee_number,
                    $emp->full_name_ar,
                    $emp->national_id ?? '—',
                    $empRecord?->category?->category_name_ar ?? 'عام',
                    $empRecord?->academicRank?->rank_name_ar ?? '—',
                    $empRecord?->jobTitle?->title_name_ar ?? '—',
                    $collegeName,
                    $deptName,
                    $empRecord?->employmentStatus?->status_name_ar ?? 'مستمر',
                    $emp->phone_number ?? '—',
                    $emp->email ?? '—',
                    $emp->birth_date ? $emp->birth_date->format('Y-m-d') : '—',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * تحميل نموذج استيراد الكوادر الفارغ (CSV Template)
     */
    public function downloadImportTemplate()
    {
        $fileName = 'نموذج_استيراد_الكوادر.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'full_name_ar',
                'employee_number',
                'national_id',
                'gender',
                'category_name',
                'rank_name',
                'phone_number',
                'email'
            ]);

            // صفوف توضيحية
            fputcsv($handle, [
                'د. محمد أحمد علي القاضي',
                '100055',
                '1500000055',
                'M',
                'أكاديمي',
                'أستاذ مشارك',
                '777000111',
                'm.qadi@ibbuniv.edu.ye'
            ]);

            fputcsv($handle, [
                'أحمد صالح حميد',
                '200088',
                '1500000088',
                'M',
                'إداري',
                '—',
                '777222333',
                'a.saleh@ibbuniv.edu.ye'
            ]);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * استيراد دفعة موظفين من ملف CSV
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'import_file.required' => 'يرجى اختيار ملف CSV للاستيراد.',
            'import_file.mimes'    => 'يجب أن يكون الملف بصيغة CSV.',
            'import_file.max'      => 'الحد الأقصى لحجم الملف هو 10 ميجابايت.',
        ]);

        $file = $request->file('import_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'تعذر فتح وقراءة الملف المحدد.');
        }

        // قراءة الهيدر وتخطي BOM
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (!$header || count($header) < 2) {
            fclose($handle);
            return back()->with('error', 'هيكل الملف غير صالح. يرجى استخدام النموذج المعتمد.');
        }

        $importedCount = 0;
        $defaultUnit = \App\Models\OrganizationalUnit::first();

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row[0]) || empty($row[1])) {
                    continue; // سطر فارغ
                }

                $fullName = trim($row[0]);
                $empNumber = trim($row[1]);
                $nationalId = isset($row[2]) ? trim($row[2]) : null;
                $gender = isset($row[3]) && in_array(strtoupper(trim($row[3])), ['F', 'FEMALE', 'أنثى']) ? 'F' : 'M';
                $phone = isset($row[6]) ? trim($row[6]) : null;
                $email = isset($row[7]) ? trim($row[7]) : null;

                // التحقق من عدم التكرار
                $employee = Employee::where('employee_number', $empNumber)
                    ->orWhere(function($q) use ($nationalId) {
                        if ($nationalId) $q->where('national_id', $nationalId);
                    })->first();

                if (!$employee) {
                    $employee = Employee::create([
                        'employee_id' => (string) Str::uuid(),
                        'employee_number' => $empNumber,
                        'full_name_ar' => $fullName,
                        'national_id' => $nationalId,
                        'gender' => $gender,
                        'phone_number' => $phone,
                        'email' => $email,
                        'is_active_record' => true,
                    ]);

                    // إضافة سجل تسكين أساسي
                    if ($defaultUnit) {
                        \App\Models\EmploymentRecord::create([
                            'employment_record_id' => (string) Str::uuid(),
                            'employee_id' => $employee->employee_id,
                            'organizational_unit_id' => $defaultUnit->organizational_unit_id,
                            'employee_category_id' => 1,
                            'employment_status_id' => 1,
                            'start_date' => now()->toDateString(),
                            'is_current' => true,
                        ]);
                    }

                    $importedCount++;
                }
            }

            DB::commit();
            fclose($handle);
            Cache::forget('dashboard_core_metrics');

            return back()->with('success', "تم بنجاح استيراد وتسجيل ({$importedCount}) كادراً جديداً في النظام!");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'حدث خطأ أثناء معالجة بيانات الملف: ' . $e->getMessage());
        }
    }
}
