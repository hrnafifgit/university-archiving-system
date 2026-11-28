<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\OrganizationalUnit;
use App\Services\AnalyticsService;
use App\Services\ReportMatrixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected ReportMatrixService $matrixService;
    protected AnalyticsService $analyticsService;

    public function __construct(ReportMatrixService $matrixService, AnalyticsService $analyticsService)
    {
        $this->matrixService = $matrixService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * مركز التقارير والإحصائيات الرسمي (مطابق لنظام إكسل UAS)
     */
    public function index(Request $request)
    {
        $reportType = $request->query('type', 'individual'); // individual | roster_19 | custom_builder | matrices
        $matrixCode = $request->query('matrix', 'star1'); // star1 | eh1 .. eh8

        // البيانات المرجعية المشتركة
        $faculties = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center'])->orderBy('unit_name_ar')->get();
        $departments = OrganizationalUnit::where('unit_type', 'department')->orWhere('unit_name_ar', 'عام')->orderBy('unit_name_ar')->get();
        $ranks = AcademicRank::orderBy('rank_order')->get();
        $statuses = EmploymentStatus::orderBy('status_name_ar')->get();

        // 1. التقرير الفردي (بيان الحالة الأكاديمية والوظيفية - prnn)
        $employee = null;
        $employeeList = collect();
        $qualificationsByLevel = ['bachelor' => null, 'master' => null, 'phd' => null];
        $latestDecision = null;

        if ($reportType === 'individual') {
            $employeesQuery = Employee::orderBy('full_name_ar');
            if ($request->filled('search_emp')) {
                $s = trim($request->search_emp);
                $employeesQuery->where(function ($q) use ($s) {
                    $q->where('full_name_ar', 'ilike', "%{$s}%")
                      ->orWhere('employee_number', 'ilike', "%{$s}%");
                });
            }
            $employeeList = $employeesQuery->limit(200)->get(['employee_id', 'full_name_ar', 'employee_number']);

            $employeeId = $request->query('employee_id');
            if ($employeeId) {
                $employee = $this->loadEmployeeData($employeeId);
            }
            if (!$employee) {
                $firstEmp = Employee::first();
                if ($firstEmp) {
                    $employee = $this->loadEmployeeData($firstEmp->employee_id);
                }
            }

            if ($employee) {
                // ترتيب المؤهلات بحسب رتبة المؤهل الرسمية لضمان إسنادها بدقة
                $sortedQuals = $employee->qualifications->sortByDesc(function ($q) {
                    return $q->level?->level_order ?? 0;
                });

                foreach ($sortedQuals as $q) {
                    $lvl = mb_strtolower($q->level->level_code ?? '');
                    $lvlName = $q->level->level_name_ar ?? '';
                    $order = (int)($q->level?->level_order ?? 0);

                    if (str_contains($lvl, 'doc') || str_contains($lvl, 'phd') || str_contains($lvlName, 'دكتور') || $order >= 4) {
                        if (!$qualificationsByLevel['phd'] || $q->is_highest_qualification) {
                            $qualificationsByLevel['phd'] = $q;
                        }
                    } elseif (str_contains($lvl, 'master') || str_contains($lvl, 'magister') || str_contains($lvlName, 'ماجستير') || $order === 3 || $order === 2) {
                        if (!$qualificationsByLevel['master'] || $q->is_highest_qualification) {
                            $qualificationsByLevel['master'] = $q;
                        }
                    } elseif (str_contains($lvl, 'bach') || str_contains($lvl, 'licence') || str_contains($lvlName, 'بكالوريوس') || str_contains($lvlName, 'ليسانس') || $order === 1) {
                        if (!$qualificationsByLevel['bachelor'] || $q->is_highest_qualification) {
                            $qualificationsByLevel['bachelor'] = $q;
                        }
                    }
                }

                // إسناد أي مؤهلات إضافية غير مسندة للخانات الشاغرة
                foreach ($sortedQuals as $q) {
                    if ($q !== $qualificationsByLevel['phd'] && $q !== $qualificationsByLevel['master'] && $q !== $qualificationsByLevel['bachelor']) {
                        if (!$qualificationsByLevel['bachelor']) {
                            $qualificationsByLevel['bachelor'] = $q;
                        } elseif (!$qualificationsByLevel['master']) {
                            $qualificationsByLevel['master'] = $q;
                        } elseif (!$qualificationsByLevel['phd']) {
                            $qualificationsByLevel['phd'] = $q;
                        }
                    }
                }

                $latestDecision = $employee->decisions->sortByDesc('issue_date')->first();
            }
        }

        // 2. كشف بيانات الكادر التدريسي المفلتر الشامل (19 عموداً - Sheet2)
        $rosterEmployees = collect();
        $appliedFilters = [];

        if ($reportType === 'roster_19') {
            $query = Employee::with([
                'currentEmployment.organizationalUnit.parent',
                'currentEmployment.academicRank',
                'currentEmployment.jobGrade',
                'currentEmployment.employmentStatus',
                'employmentRecords.organizationalUnit.parent',
                'employmentRecords.academicRank',
                'employmentRecords.jobGrade',
                'employmentRecords.employmentStatus',
                'qualifications.level'
            ]);

            // الفلترة بالكلية
            if ($request->filled('faculty_id')) {
                $fId = $request->faculty_id;
                $query->whereHas('currentEmployment', function ($q) use ($fId) {
                    $q->where('organizational_unit_id', $fId)
                      ->orWhereHas('organizationalUnit', fn($ou) => $ou->where('parent_unit_id', $fId));
                });
                $fName = OrganizationalUnit::find($fId)?->unit_name_ar;
                if ($fName) $appliedFilters['الجهة / الكلية'] = $fName;
            }

            // الفلترة بالقسم
            if ($request->filled('department_id')) {
                $dId = $request->department_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('organizational_unit_id', $dId));
                $dName = OrganizationalUnit::find($dId)?->unit_name_ar;
                if ($dName) $appliedFilters['القسم'] = $dName;
            }

            // الرتبة الأكاديمية
            if ($request->filled('rank_id')) {
                $rId = $request->rank_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('academic_rank_id', $rId));
                $rName = AcademicRank::find($rId)?->rank_name_ar;
                if ($rName) $appliedFilters['الدرجة الحالية'] = $rName;
            }

            // الوضع الحالي الأكاديمي
            if ($request->filled('status_id')) {
                $stId = $request->status_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('employment_status_id', $stId));
                $stName = EmploymentStatus::find($stId)?->status_name_ar;
                if ($stName) $appliedFilters['الوضع الحالي'] = $stName;
            }

            // الموقف الوظيفي
            if ($request->filled('appointment_basis')) {
                $basis = $request->appointment_basis;
                $query->whereHas('currentEmployment', fn($q) => $q->where('appointment_basis', $basis));
                $appliedFilters['الموقف الوظيفي'] = $basis;
            }

            // الحالة بكشف الراتب
            if ($request->filled('payroll_status')) {
                $pst = $request->payroll_status;
                $query->whereHas('currentEmployment', fn($q) => $q->where('payroll_status', $pst));
                $appliedFilters['الحالة بكشف الراتب'] = $pst;
            }

            // الدرجة بكشف الراتب
            if ($request->filled('payroll_job_grade')) {
                $pjg = $request->payroll_job_grade;
                $query->whereHas('currentEmployment', fn($q) => $q->where('payroll_job_grade', $pjg));
                $appliedFilters['الدرجة بكشف الراتب'] = $pjg;
            }

            // الجنس
            if ($request->filled('gender')) {
                $g = $request->gender;
                $query->where('gender', $g);
                $appliedFilters['الجنس'] = ($g === 'F') ? 'أنثى' : 'ذكر';
            }

            // البحث بالاسم
            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('full_name_ar', 'ilike', "%{$s}%")
                      ->orWhere('employee_number', 'ilike', "%{$s}%")
                      ->orWhere('national_id', 'ilike', "%{$s}%");
                });
                $appliedFilters['بحث'] = $s;
            }

            $rosterEmployees = $query->orderBy('full_name_ar')->get();
        }

        // 3. منشئ التقارير المخصص الديناميكي (ReportForm -> Repj1)
        $customReportData = [];
        $availableColumns = [
            'serial' => 'م',
            'full_name_ar' => 'الاسم',
            'gender' => 'الجنس',
            'date_of_birth' => 'تاريخ الميلاد',
            'appointment_basis' => 'الموقف الوظيفي',
            'national_id' => 'الرقم الوطني',
            'employee_number' => 'الرقم الوظيفي',
            'payroll_status' => 'الحالة بكشف الراتب',
            'payroll_job_title' => 'الوظيفة بكشف الراتب',
            'payroll_job_grade' => 'الدرجة بكشف الراتب',
            'first_appointment_date' => 'تاريخ التعيين بالوظيفة لاول مرة',
            'initial_appointing_entity' => 'الجهة المعين بها',
            'academic_appointment_date' => 'تاريخ التعين بالجامعة اكاديمي',
            'qualification_at_appointment' => 'المؤهل عند التعيين بالجامعة اكاديمي',
            'initial_rank' => 'الدرجة المعين فيها',
            'faculty_name' => 'الجهة',
            'dept_name' => 'الادارة/القسم',
            'academic_rank' => 'الدرجة الحالية',
            'academic_status' => 'الوضع الحالي',
            'bachelor_major' => 'تخصص بك',
            'bachelor_grade' => 'تقدير بك',
            'bachelor_date' => 'تاريخ بك',
            'bachelor_university' => 'جامعة بك',
            'bachelor_country' => 'الدولة بك',
            'master_major' => 'تخصص مج',
            'master_date' => 'تاريخ مج',
            'master_university' => 'جامعة مج',
            'master_country' => 'الدولة مج',
            'phd_major' => 'تخصص دك',
            'phd_date' => 'تاريخ دك',
            'phd_university' => 'جامعة دك',
            'phd_country' => 'الدولة دك',
            'phone_number' => 'رقم التليفون',
            'doc_name' => 'اسم الملف الالكتروني',
            'doc_link' => 'الرابط',
            'place_of_birth' => 'محل الميلاد',
            'auto_link_file' => 'اسم ملف الربط التلقائي',
            'last_decision_number' => 'رقم اخر قرار',
            'decision_type' => 'نوعة',
            'decision_date' => 'تاريخ صدوره',
            'decision_end_date' => 'تاريخ انتهائه',
            'decision_status' => 'حالة القرار',
            'notes' => 'ملاحظات',
            'specialization' => 'التخصص الدقيق',
        ];

        $selectedColumns = $request->input('columns', [
            'serial', 'full_name_ar', 'gender', 'appointment_basis', 'national_id',
            'employee_number', 'payroll_status', 'dept_name', 'academic_rank', 'academic_status'
        ]);

        $selectedFacultyName = 'كافة كليات ومراكز الجامعة';
        if ($request->filled('faculty_id') && $request->faculty_id !== 'all') {
            $matchedFac = OrganizationalUnit::find($request->faculty_id);
            if ($matchedFac) {
                $selectedFacultyName = $matchedFac->unit_name_ar;
            }
        }

        if ($reportType === 'custom_builder') {
            $cQuery = Employee::with([
                'currentEmployment.organizationalUnit.parent',
                'currentEmployment.academicRank',
                'currentEmployment.jobGrade',
                'currentEmployment.employmentStatus',
                'qualifications.level',
                'qualifications.country',
                'qualifications.university',
                'qualifications.specialization',
                'decisions.type',
                'decisions.status',
                'documents'
            ])->where('is_active_record', true);

            if ($request->filled('faculty_id') && $request->faculty_id !== 'all') {
                $fId = $request->faculty_id;
                $cQuery->whereHas('currentEmployment', function ($q) use ($fId) {
                    $q->where('organizational_unit_id', $fId)
                      ->orWhereHas('organizationalUnit', fn($ou) => $ou->where('parent_unit_id', $fId));
                });
            }

            $rawList = $cQuery->orderBy('full_name_ar')->get();

            foreach ($rawList as $idx => $emp) {
                $empRec = $emp->currentEmployment;
                $ou = $empRec?->organizationalUnit;
                $faculty = $ou?->parent ?? $ou;
                $dept = ($ou && $ou->parent_unit_id) ? $ou : null;

                $bachelor = $emp->qualifications->first(fn($q) => str_contains(mb_strtolower($q->level->level_code ?? ''), 'bach') || str_contains(mb_strtolower($q->level->level_name_ar ?? ''), 'بكالوريوس'));
                $master = $emp->qualifications->first(fn($q) => str_contains(mb_strtolower($q->level->level_code ?? ''), 'master') || str_contains(mb_strtolower($q->level->level_name_ar ?? ''), 'ماجستير'));
                $phd = $emp->qualifications->first(fn($q) => str_contains(mb_strtolower($q->level->level_code ?? ''), 'phd') || str_contains(mb_strtolower($q->level->level_name_ar ?? ''), 'دكتوراه'));
                $lastDec = $emp->decisions->sortByDesc('issue_date')->first();
                $firstDoc = $emp->documents->first();

                $isFemale = in_array(strtolower(trim($emp->gender ?? '')), ['female', 'f', 'أنثى', 'انثى'], true);

                $row = [
                    'serial' => $idx + 1,
                    'full_name_ar' => $emp->full_name_ar,
                    'gender' => $isFemale ? 'انثى' : 'ذكر',
                    'date_of_birth' => $emp->date_of_birth?->format('Y') ?? '',
                    'appointment_basis' => $empRec?->appointment_basis ?? 'مثبت',
                    'national_id' => $emp->national_id ?? '',
                    'employee_number' => $emp->employee_number ?? '',
                    'payroll_status' => $empRec?->payroll_status ?? 'غير محدد',
                    'payroll_job_title' => $empRec?->payroll_job_title ?? 'غير محدد',
                    'payroll_job_grade' => $empRec?->payroll_job_grade ?? 'غير محدد',
                    'first_appointment_date' => $empRec?->first_appointment_date?->format('Y/m/d') ?? '',
                    'initial_appointing_entity' => 'غير محدد',
                    'academic_appointment_date' => $empRec?->academic_appointment_date?->format('Y/m/d') ?? '',
                    'qualification_at_appointment' => $empRec?->qualification_at_appointment ?? 'غير محدد',
                    'initial_rank' => $empRec?->initial_rank ?? 'معيد',
                    'faculty_name' => $faculty?->unit_name_ar ?? 'جامعة إب',
                    'dept_name' => $dept?->unit_name_ar ?? 'عام',
                    'academic_rank' => $empRec?->academicRank?->rank_name_ar ?? 'غير محدد',
                    'academic_status' => $empRec?->employmentStatus?->status_name_ar ?? 'غير محدد',
                    'bachelor_major' => $bachelor?->degree_title ?? $bachelor?->specialization?->specialization_name_ar ?? '',
                    'bachelor_grade' => $bachelor?->grade_or_gpa ?? '',
                    'bachelor_date' => $bachelor?->graduation_date?->format('Y') ?? '',
                    'bachelor_university' => $bachelor?->university?->university_name_ar ?? '',
                    'bachelor_country' => $bachelor?->country?->country_name_ar ?? '',
                    'master_major' => $master?->degree_title ?? $master?->specialization?->specialization_name_ar ?? '',
                    'master_date' => $master?->graduation_date?->format('Y') ?? '',
                    'master_university' => $master?->university?->university_name_ar ?? '',
                    'master_country' => $master?->country?->country_name_ar ?? '',
                    'phd_major' => $phd?->degree_title ?? $phd?->specialization?->specialization_name_ar ?? '',
                    'phd_date' => $phd?->graduation_date?->format('Y') ?? '',
                    'phd_university' => $phd?->university?->university_name_ar ?? '',
                    'phd_country' => $phd?->country?->country_name_ar ?? '',
                    'phone_number' => $emp->phone_number ?? '',
                    'doc_name' => $firstDoc?->document_number ? ($firstDoc->document_number . '.PDF') : ($emp->employee_number . '.PDF'),
                    'doc_link' => 'فتح الملف',
                    'place_of_birth' => $emp->place_of_birth ?? 'إب',
                    'auto_link_file' => $emp->employee_number ?? '',
                    'last_decision_number' => $lastDec?->decision_number ?? '',
                    'decision_type' => $lastDec?->type?->type_name_ar ?? '',
                    'decision_date' => $lastDec?->issue_date?->format('Y/m/d') ?? '',
                    'decision_end_date' => $lastDec?->effective_end_date?->format('Y/m/d') ?? '',
                    'decision_status' => $lastDec?->status?->status_name_ar ?? 'ساري',
                    'notes' => $emp->notes ?? '',
                    'specialization' => $phd?->degree_title ?? ($master?->degree_title ?? ($bachelor?->degree_title ?? '')),
                ];
                $customReportData[] = $row;
            }
        }

        // 4. مركز الإحصائيات والمصفوفات الثمانية (eh1 .. eh8 + star1)
        $matrixData = null;
        if ($reportType === 'matrices') {
            switch ($matrixCode) {
                case 'eh1':
                    $matrixData = $this->matrixService->getMatrixEh1();
                    break;
                case 'eh2':
                    $matrixData = $this->matrixService->getMatrixEh2();
                    break;
                case 'eh3':
                    $matrixData = $this->matrixService->getMatrixEh3();
                    break;
                case 'eh4':
                    $matrixData = $this->matrixService->getMatrixEh4();
                    break;
                case 'eh5':
                    $matrixData = $this->matrixService->getMatrixEh5();
                    break;
                case 'eh6':
                    $matrixData = $this->matrixService->getMatrixEh6();
                    break;
                case 'eh7':
                    $matrixData = $this->matrixService->getMatrixEh7();
                    break;
                case 'eh8':
                    $matrixData = $this->matrixService->getMatrixEh8();
                    break;
                case 'star1':
                default:
                    $matrixData = $this->matrixService->getSummaryStar1();
                    break;
            }
        }

        // 5. لوحة الإحصائيات البيانية التفاعلية (Analytics Dashboard)
        $analyticsCadre = $request->query('cadre', 'all');
        $analyticsData = null;
        $countryUniStats = collect();
        $facultyDeptBreakdown = collect();
        $analyticsCountries = collect();
        $analyticsUniversities = collect();
        $analyticsQualLevels = collect();
        $selectedAnalyticsFacultyId = $request->query('analytics_faculty_id');
        if ($reportType === 'analytics') {
            $analyticsData = $this->analyticsService->getAnalyticsData($analyticsCadre);
            $analyticsCountries = \App\Models\Country::orderBy('country_name_ar')->get();
            $analyticsUniversities = \App\Models\University::orderBy('university_name_ar')->get();
            $analyticsQualLevels = \App\Models\QualificationLevel::orderBy('level_order')->get();

            $cuQuery = DB::table('employee_qualifications as eq')
                ->leftJoin('countries as c', 'c.country_id', '=', 'eq.country_id')
                ->leftJoin('universities as u', 'u.university_id', '=', 'eq.university_id')
                ->join('qualification_levels as ql', 'ql.qualification_level_id', '=', 'eq.qualification_level_id')
                ->selectRaw("COALESCE(c.country_name_ar, 'غير محدد') as country_name, COALESCE(u.university_name_ar, 'غير محدد') as university_name, ql.level_name_ar as level_name, COUNT(DISTINCT eq.employee_id) as emp_count")
                ->groupBy('c.country_name_ar', 'u.university_name_ar', 'ql.level_name_ar')
                ->orderByDesc('emp_count');
            if ($request->filled('stat_country_id')) {
                $cuQuery->where('eq.country_id', $request->stat_country_id);
            }
            if ($request->filled('stat_university_id')) {
                $cuQuery->where('eq.university_id', $request->stat_university_id);
            }
            if ($request->filled('stat_qual_level_id')) {
                $cuQuery->where('eq.qualification_level_id', $request->stat_qual_level_id);
            }
            $countryUniStats = $cuQuery->limit(50)->get();

            // إحصائية الكلية والأقسام (أكاديمي / إداري / أستاذ دكتور / دكتوراه / موفد / معيد)
            $fdQuery = DB::table('employment_records as er')
                ->join('organizational_units as ou', 'ou.organizational_unit_id', '=', 'er.organizational_unit_id')
                ->join('employee_categories as ec', 'ec.employee_category_id', '=', 'er.employee_category_id')
                ->leftJoin('academic_ranks as ar', 'ar.academic_rank_id', '=', 'er.academic_rank_id')
                ->leftJoin('employment_statuses as es', 'es.employment_status_id', '=', 'er.employment_status_id')
                ->where('er.is_primary', true)
                ->whereNull('er.end_date');
            if ($selectedAnalyticsFacultyId) {
                $fdQuery->where(function ($q) use ($selectedAnalyticsFacultyId) {
                    $q->where('ou.organizational_unit_id', $selectedAnalyticsFacultyId)
                      ->orWhere('ou.parent_unit_id', $selectedAnalyticsFacultyId);
                });
            }
            $facultyDeptBreakdown = $fdQuery->selectRaw("
                ou.unit_name_ar as unit_name,
                COUNT(DISTINCT CASE WHEN ec.category_code ILIKE '%academic%' OR ec.category_name_ar LIKE '%أكاديمي%' THEN er.employee_id END) as academic_count,
                COUNT(DISTINCT CASE WHEN ec.category_code ILIKE '%admin%' OR ec.category_name_ar LIKE '%إداري%' THEN er.employee_id END) as admin_count,
                COUNT(DISTINCT CASE WHEN ar.rank_name_ar LIKE '%أستاذ دكتور%' OR ar.rank_code = 'PROF' THEN er.employee_id END) as prof_count,
                COUNT(DISTINCT CASE WHEN ar.rank_name_ar LIKE '%معيد%' THEN er.employee_id END) as ta_count,
                COUNT(DISTINCT CASE WHEN es.status_name_ar LIKE '%موفد%' OR es.status_name_ar LIKE '%إيفاد%' THEN er.employee_id END) as dispatched_count
            ")
            ->groupBy('ou.unit_name_ar')
            ->orderBy('ou.unit_name_ar')
            ->get();

            $contractFilter = $request->query('contract_filter', 'all');
            $contractsQuery = DB::table('contractor_contracts as cc')
                ->join('employees as e', 'e.employee_id', '=', 'cc.employee_id')
                ->leftJoin('contractors as c', 'c.employee_id', '=', 'cc.employee_id')
                ->select(
                    'cc.*',
                    'e.full_name_ar',
                    'e.employee_number',
                    'c.job_classification'
                )
                ->orderBy('cc.contract_end_date');
            if ($contractFilter === 'expired') {
                $contractsQuery->whereNotNull('cc.contract_end_date')->where('cc.contract_end_date', '<', date('Y-m-d'));
            } elseif ($contractFilter === 'active') {
                $contractsQuery->where(function ($q) {
                    $q->whereNull('cc.contract_end_date')->orWhere('cc.contract_end_date', '>=', date('Y-m-d'));
                });
            }
            $contractorContractsList = $contractsQuery->limit(50)->get();
        }

        // قوائم الحالات المرجعية للفلاتر
        $distinctPayrollStatuses = ['مستمر', 'منقطع', 'ايفاد', 'تفرغ علمي', 'اجازه بدون راتب', 'بالغي احد الاجلين', 'متوفي', 'اختلالات 2017', 'غير محدد'];
        $distinctPayrollGrades = ['نائب وزير', 'استاذ', 'استاذ مشارك', 'استاذ مساعد', 'مدرس', 'معيد', 'باحث', 'غير محدد'];
        $distinctAppointmentBases = ['مثبت', 'بدون راتب', 'خفض واضافة', 'احلال', 'بدل', 'غير محدد'];

        return view('reports.index', compact(
            'reportType',
            'matrixCode',
            'analyticsCadre',
            'analyticsData',
            'countryUniStats',
            'facultyDeptBreakdown',
            'analyticsCountries',
            'analyticsUniversities',
            'analyticsQualLevels',
            'selectedAnalyticsFacultyId',
            'contractorContractsList',
            'employee',
            'employeeList',
            'qualificationsByLevel',
            'latestDecision',
            'faculties',
            'departments',
            'ranks',
            'statuses',
            'rosterEmployees',
            'appliedFilters',
            'availableColumns',
            'selectedColumns',
            'selectedFacultyName',
            'customReportData',
            'matrixData',
            'distinctPayrollStatuses',
            'distinctPayrollGrades',
            'distinctAppointmentBases'
        ));
    }

    /**
     * تصدير مصفوفة إحصائية إلى CSV / Excel
     */
    /**
     * تصدير مصفوفة إحصائية إلى ملف إكسل منسق رسمي (.xls)
     */
    public function exportMatrix(Request $request, $matrixCode)
    {
        switch ($matrixCode) {
            case 'eh1': $data = $this->matrixService->getMatrixEh1(); break;
            case 'eh2': $data = $this->matrixService->getMatrixEh2(); break;
            case 'eh3': $data = $this->matrixService->getMatrixEh3(); break;
            case 'eh4': $data = $this->matrixService->getMatrixEh4(); break;
            case 'eh5': $data = $this->matrixService->getMatrixEh5(); break;
            case 'eh6': $data = $this->matrixService->getMatrixEh6(); break;
            case 'eh7': $data = $this->matrixService->getMatrixEh7(); break;
            case 'eh8': $data = $this->matrixService->getMatrixEh8(); break;
            default:
                return redirect()->route('reports.index', ['type' => 'matrices', 'matrix' => 'star1']);
        }

        $filename = "{$matrixCode}_matrix_export_" . date('Y_m_d') . ".xls";

        return new StreamedResponse(function () use ($data, $matrixCode) {
            $handle = fopen('php://output', 'w');

            if ($matrixCode === 'eh1' && !empty($data['collegeGroups'])) {
                $output = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
                $output .= '<head>';
                $output .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
                $output .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>إحصائيات الأقسام الأكاديمية</x:Name><x:WorksheetOptions><x:DisplayRightToLeft/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
                $output .= '<style>';
                $output .= 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; direction: rtl; }';
                $output .= 'table { border-collapse: collapse; width: 100%; direction: rtl; }';
                $output .= '.main-title { font-size: 14pt; font-weight: bold; text-align: center; color: #14532d; padding: 10px 0; }';
                $output .= 'th { background-color: #14532d; color: #ffffff; font-weight: bold; border: 1px solid #0f3d21; text-align: center; vertical-align: middle; padding: 8px 5px; font-size: 10pt; }';
                $output .= 'td { border: 1px solid #cbd5e1; padding: 6px 5px; font-size: 9.5pt; vertical-align: middle; text-align: center; }';
                $output .= '.college-header { background-color: #fce4d6; font-weight: bold; font-size: 11pt; color: #000; text-align: right; padding: 8px 10px; }';
                $output .= '.subtotal-row td { background-color: #d9e1f2; font-weight: bold; color: #000; }';
                $output .= '.grand-total-row td { background-color: #14532d; color: #ffffff; font-weight: bold; font-size: 11pt; }';
                $output .= '</style>';
                $output .= '</head>';
                $output .= '<body dir="rtl">';
                $output .= '<table dir="rtl" border="1">';
                $output .= '<tr><td colspan="12" class="main-title" style="border: none;">جامعة إب - إحصائية الكادر التدريسي بحسب الكليات والأقسام الأكاديمية والرتب</td></tr>';
                $output .= '<tr><td colspan="12" style="border: none; text-align: center; color: #64748b; font-size: 9pt; padding-bottom: 8px;">إجمالي الكادر: ' . $data['totalStaff'] . ' كادر | تاريخ الاستخراج: ' . date('Y/m/d H:i') . '</td></tr>';

                $output .= '<tr>';
                $output .= '<th>الكلية / المركز</th>';
                $output .= '<th>م</th>';
                $output .= '<th>اسم القسم الأكاديمي</th>';
                $output .= '<th>أستاذ دكتور</th>';
                $output .= '<th>أستاذ مشارك</th>';
                $output .= '<th>أستاذ مساعد</th>';
                $output .= '<th>مدرس</th>';
                $output .= '<th>معيد</th>';
                $output .= '<th>غير محدد</th>';
                $output .= '<th>ذكور</th>';
                $output .= '<th>إناث</th>';
                $output .= '<th style="background-color: #166534;">إجمالي القسم</th>';
                $output .= '</tr>';
                fwrite($handle, $output);

                $globalProf = 0; $globalAssoc = 0; $globalAssist = 0; $globalLect = 0; $globalTa = 0; $globalOther = 0; $globalMale = 0; $globalFemale = 0;

                foreach ($data['collegeGroups'] as $fName => $group) {
                    $depts = $group['departments'] ?? [];
                    if (empty($depts)) continue;

                    $cProf = 0; $cAssoc = 0; $cAssist = 0; $cLect = 0; $cTa = 0; $cOther = 0; $cMale = 0; $cFemale = 0;
                    $deptIdx = 1;

                    foreach ($depts as $deptName => $d) {
                        $cProf += $d['prof']; $cAssoc += $d['assoc_prof']; $cAssist += $d['assist_prof']; $cLect += $d['lecturer']; $cTa += $d['ta']; $cOther += $d['other_rank'];
                        $cMale += $d['male']; $cFemale += $d['female'];

                        $rowHtml = '<tr>';
                        $rowHtml .= '<td style="text-align: right; font-weight: bold; background-color: #f8fafc;">' . htmlspecialchars($fName, ENT_QUOTES, 'UTF-8') . '</td>';
                        $rowHtml .= '<td style="font-family: monospace;">' . $deptIdx++ . '</td>';
                        $rowHtml .= '<td style="text-align: right; font-weight: bold;">' . htmlspecialchars($deptName, ENT_QUOTES, 'UTF-8') . '</td>';
                        $rowHtml .= '<td>' . $d['prof'] . '</td>';
                        $rowHtml .= '<td>' . $d['assoc_prof'] . '</td>';
                        $rowHtml .= '<td>' . $d['assist_prof'] . '</td>';
                        $rowHtml .= '<td>' . $d['lecturer'] . '</td>';
                        $rowHtml .= '<td>' . $d['ta'] . '</td>';
                        $rowHtml .= '<td>' . $d['other_rank'] . '</td>';
                        $rowHtml .= '<td>' . $d['male'] . '</td>';
                        $rowHtml .= '<td>' . $d['female'] . '</td>';
                        $rowHtml .= '<td style="font-weight: bold; background-color: #f0fdf4; color: #166534;">' . $d['total'] . '</td>';
                        $rowHtml .= '</tr>';
                        fwrite($handle, $rowHtml);
                    }

                    // سطر إجمالي الكلية
                    $subHtml = '<tr class="subtotal-row">';
                    $subHtml .= '<td colspan="3" style="text-align: right; padding-right: 15px;">إجمالي ' . htmlspecialchars($fName, ENT_QUOTES, 'UTF-8') . '</td>';
                    $subHtml .= '<td>' . $cProf . '</td>';
                    $subHtml .= '<td>' . $cAssoc . '</td>';
                    $subHtml .= '<td>' . $cAssist . '</td>';
                    $subHtml .= '<td>' . $cLect . '</td>';
                    $subHtml .= '<td>' . $cTa . '</td>';
                    $subHtml .= '<td>' . $cOther . '</td>';
                    $subHtml .= '<td>' . $cMale . '</td>';
                    $subHtml .= '<td>' . $cFemale . '</td>';
                    $subHtml .= '<td style="font-weight: bold; color: #1e3a8a;">' . $group['total'] . '</td>';
                    $subHtml .= '</tr>';
                    fwrite($handle, $subHtml);

                    $globalProf += $cProf; $globalAssoc += $cAssoc; $globalAssist += $cAssist; $globalLect += $cLect; $globalTa += $cTa; $globalOther += $cOther;
                    $globalMale += $cMale; $globalFemale += $cFemale;
                }

                // سطر الإجمالي العام للجامعة
                $grandHtml = '<tr class="grand-total-row">';
                $grandHtml .= '<td colspan="3" style="text-align: center;">الإجمالي العام لكافة الكليات والمراكز</td>';
                $grandHtml .= '<td>' . $globalProf . '</td>';
                $grandHtml .= '<td>' . $globalAssoc . '</td>';
                $grandHtml .= '<td>' . $globalAssist . '</td>';
                $grandHtml .= '<td>' . $globalLect . '</td>';
                $grandHtml .= '<td>' . $globalTa . '</td>';
                $grandHtml .= '<td>' . $globalOther . '</td>';
                $grandHtml .= '<td>' . $globalMale . '</td>';
                $grandHtml .= '<td>' . $globalFemale . '</td>';
                $grandHtml .= '<td>' . $data['grandTotal'] . '</td>';
                $grandHtml .= '</tr>';
                $grandHtml .= '</table></body></html>';
                fwrite($handle, $grandHtml);
                fclose($handle);
                return;
            }

            $colCount = count($data['columns']) + 2;

            $output = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            $output .= '<head>';
            $output .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            $output .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>المصفوفة الإحصائية</x:Name><x:WorksheetOptions><x:DisplayRightToLeft/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            $output .= '<style>';
            $output .= 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; direction: rtl; }';
            $output .= 'table { border-collapse: collapse; width: 100%; direction: rtl; }';
            $output .= '.main-title { font-size: 14pt; font-weight: bold; text-align: center; color: #14532d; padding: 10px 0; }';
            $output .= 'th { background-color: #14532d; color: #ffffff; font-weight: bold; border: 1px solid #0f3d21; text-align: center; vertical-align: middle; padding: 9px 6px; font-size: 11pt; }';
            $output .= 'td { border: 1px solid #cbd5e1; padding: 7px 6px; font-size: 10pt; vertical-align: middle; text-align: center; }';
            $output .= '.faculty-cell { text-align: right; font-weight: bold; color: #0f172a; padding-right: 12px; }';
            $output .= '.total-row td { background-color: #e2e8f0; font-weight: bold; color: #0f172a; }';
            $output .= '.even td { background-color: #f8fafc; }';
            $output .= '</style>';
            $output .= '</head>';
            $output .= '<body dir="rtl">';
            $output .= '<table dir="rtl" border="1">';

            // الترويسة
            $output .= '<tr><td colspan="' . $colCount . '" class="main-title" style="border: none;">جامعة إب - ' . htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
            $output .= '<tr><td colspan="' . $colCount . '" style="border: none; text-align: center; color: #64748b; font-size: 9pt; padding-bottom: 8px;">تاريخ الاستخراج: ' . date('Y/m/d H:i') . '</td></tr>';

            // صف العناوين
            $output .= '<tr>';
            $output .= '<th>' . htmlspecialchars($data['rowHeader'], ENT_QUOTES, 'UTF-8') . '</th>';
            foreach ($data['columns'] as $c) {
                $output .= '<th>' . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $output .= '<th style="background-color: #166534;">الإجمالي</th>';
            $output .= '</tr>';

            fwrite($handle, $output);

            // صفوف الكليات
            foreach ($data['faculties'] as $idx => $f) {
                $fName = is_object($f) ? $f->unit_name_ar : (string)$f;
                $rowClass = ($idx % 2 === 1) ? ' class="even"' : '';
                $rowHtml = "<tr{$rowClass}>";
                $rowHtml .= '<td class="faculty-cell">' . htmlspecialchars($fName, ENT_QUOTES, 'UTF-8') . '</td>';
                foreach ($data['columns'] as $c) {
                    $rowHtml .= '<td>' . ($data['matrix'][$fName][$c] ?? 0) . '</td>';
                }
                $rowHtml .= '<td style="font-weight: bold; background-color: #f1f5f9;">' . ($data['rowTotals'][$fName] ?? 0) . '</td>';
                $rowHtml .= '</tr>';
                fwrite($handle, $rowHtml);
            }

            // سطر الإجمالي العام
            $totalHtml = '<tr class="total-row">';
            $totalHtml .= '<td class="faculty-cell" style="background-color: #cbd5e1; font-weight: bold;">الإجمالي العام</td>';
            foreach ($data['columns'] as $c) {
                $totalHtml .= '<td>' . ($data['colTotals'][$c] ?? 0) . '</td>';
            }
            $totalHtml .= '<td style="background-color: #cbd5e1; font-weight: bold; color: #14532d;">' . $data['grandTotal'] . '</td>';
            $totalHtml .= '</tr>';
            $totalHtml .= '</table></body></html>';

            fwrite($handle, $totalHtml);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ]);
    }

    /**
     * تصدير كشف الكادر الشامل (19 عموداً) أو التقرير المخصص إلى ملف إكسل منسق رسمي (.xls)
     */
    public function export(Request $request)
    {
        $filename = "كشف_الكادر_الشامل_" . date('Y_m_d_His') . ".xls";

        return new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');

            $query = Employee::with([
                'currentEmployment.organizationalUnit.parent',
                'currentEmployment.academicRank',
                'currentEmployment.jobGrade',
                'currentEmployment.employmentStatus',
                'employmentRecords.organizationalUnit.parent',
                'employmentRecords.academicRank',
                'employmentRecords.jobGrade',
                'employmentRecords.employmentStatus',
            ])->where('is_active_record', true);

            // تطبيق كافة فلاتر البحث في التصدير
            $filterDescriptions = [];

            if ($request->filled('faculty_id') && $request->faculty_id !== 'all') {
                $fId = $request->faculty_id;
                $query->whereHas('currentEmployment', function ($q) use ($fId) {
                    $q->where('organizational_unit_id', $fId)
                      ->orWhereHas('organizationalUnit', fn($ou) => $ou->where('parent_unit_id', $fId));
                });
                $fac = OrganizationalUnit::find($fId);
                if ($fac) $filterDescriptions[] = 'الكلية: ' . $fac->unit_name_ar;
            }

            if ($request->filled('department_id')) {
                $dId = $request->department_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('organizational_unit_id', $dId));
                $dept = OrganizationalUnit::find($dId);
                if ($dept) $filterDescriptions[] = 'القسم: ' . $dept->unit_name_ar;
            }

            if ($request->filled('rank_id')) {
                $rId = $request->rank_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('academic_rank_id', $rId));
                $rnk = AcademicRank::find($rId);
                if ($rnk) $filterDescriptions[] = 'الرتبة: ' . $rnk->rank_name_ar;
            }

            if ($request->filled('status_id')) {
                $sId = $request->status_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('employment_status_id', $sId));
                $st = EmploymentStatus::find($sId);
                if ($st) $filterDescriptions[] = 'الوضع الحالي: ' . $st->status_name_ar;
            }

            if ($request->filled('appointment_basis')) {
                $basis = $request->appointment_basis;
                $query->whereHas('currentEmployment', fn($q) => $q->where('appointment_basis', $basis));
                $filterDescriptions[] = 'الموقف الوظيفي: ' . $basis;
            }

            if ($request->filled('payroll_status')) {
                $pst = $request->payroll_status;
                $query->whereHas('currentEmployment', fn($q) => $q->where('payroll_status', $pst));
                $filterDescriptions[] = 'الحالة بالراتب: ' . $pst;
            }

            if ($request->filled('gender')) {
                $g = $request->gender;
                $query->where('gender', $g);
                $filterDescriptions[] = 'الجنس: ' . ($g === 'F' ? 'إناث' : 'ذكور');
            }

            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('full_name_ar', 'ilike', "%{$s}%")
                      ->orWhere('employee_number', 'ilike', "%{$s}%")
                      ->orWhere('national_id', 'ilike', "%{$s}%");
                });
                $filterDescriptions[] = 'بحث: ' . $s;
            }

            $employees = $query->orderBy('full_name_ar')->get();

            $output = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            $output .= '<head>';
            $output .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            $output .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>كشف الكادر الشامل</x:Name><x:WorksheetOptions><x:DisplayRightToLeft/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            $output .= '<style>';
            $output .= 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; direction: rtl; }';
            $output .= 'table { border-collapse: collapse; width: 100%; direction: rtl; }';
            $output .= '.title-row { font-size: 15pt; font-weight: bold; text-align: center; color: #14532d; padding: 10px 0; }';
            $output .= '.subtitle-row { font-size: 10pt; color: #475569; text-align: center; padding-bottom: 6px; }';
            $output .= '.filters-banner { font-size: 9pt; background-color: #f1f5f9; color: #1e293b; padding: 6px 10px; border: 1px solid #cbd5e1; }';
            $output .= 'th { background-color: #14532d; color: #ffffff; font-weight: bold; border: 1px solid #0f3d21; text-align: center; vertical-align: middle; padding: 9px 5px; font-size: 10.5pt; }';
            $output .= 'td { border: 1px solid #cbd5e1; padding: 7px 6px; font-size: 9.5pt; vertical-align: middle; }';
            $output .= '.text-center { text-align: center; }';
            $output .= '.text-right { text-align: right; }';
            $output .= '.text-str { mso-number-format: "\@"; text-align: center; }';
            $output .= '.even { background-color: #f8fafc; }';
            $output .= '</style>';
            $output .= '</head>';
            $output .= '<body dir="rtl">';
            $output .= '<table dir="rtl" border="1">';

            // ترويسة التقرير الرسمية
            $output .= '<tr><td colspan="19" class="title-row" style="border: none;">جامعة إب - الإدارة العامة للشؤون الأكاديمية</td></tr>';
            $output .= '<tr><td colspan="19" class="subtitle-row" style="border: none;">كشف بيانات الكادر التدريسي الشامل (19 عموداً) | إجمالي السجلات: ' . $employees->count() . ' كادر | تاريخ الاستخراج: ' . date('Y/m/d H:i') . '</td></tr>';

            if (!empty($filterDescriptions)) {
                $output .= '<tr><td colspan="19" class="filters-banner">الفلاتر المطبقة: ' . htmlspecialchars(implode(' | ', $filterDescriptions), ENT_QUOTES, 'UTF-8') . '</td></tr>';
            }
            $output .= '<tr><td colspan="19" style="border: none; height: 6px;"></td></tr>';

            // ترويسة الأعمدة الـ 19 الرسمية
            $headers = [
                'م', 'الاسم الرباعي', 'الجنس', 'تاريخ الميلاد', 'الموقف الوظيفي', 'الرقم الوطني', 'الرقم الوظيفي',
                'الحالة بكشف الراتب', 'الوظيفة بكشف الراتب', 'الدرجة بكشف الراتب',
                'تعيين أول مرة', 'الجهة المعين بها', 'تعيين أكاديمي',
                'المؤهل عند التعيين', 'الدرجة المعين فيها', 'الجهة (الكلية)', 'الإدارة / القسم',
                'الدرجة الحالية', 'الوضع الحالي'
            ];
            $output .= '<tr>';
            foreach ($headers as $h) {
                $output .= '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $output .= '</tr>';

            fwrite($handle, $output);

            foreach ($employees as $idx => $emp) {
                $empRec = $emp->active_employment ?? $emp->currentEmployment ?? $emp->latestEmployment ?? $emp->employmentRecords->sortByDesc('start_date')->first();
                $isFemale = in_array(strtolower(trim($emp->gender ?? '')), ['female', 'f', 'أنثى', 'انثى'], true);

                $rowClass = ($idx % 2 === 1) ? ' class="even"' : '';

                $rowHtml = "<tr{$rowClass}>";
                $rowHtml .= '<td class="text-center" style="font-weight: bold;">' . ($idx + 1) . '</td>';
                $rowHtml .= '<td class="text-right" style="font-weight: bold; color: #0f172a;">' . htmlspecialchars($emp->full_name_ar ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-center">' . ($isFemale ? 'أنثى' : 'ذكر') . '</td>';
                $rowHtml .= '<td class="text-str">' . ($emp->date_of_birth ? $emp->date_of_birth->format('Y') : '—') . '</td>';
                $rowHtml .= '<td class="text-center">' . htmlspecialchars($empRec?->appointment_basis ?? 'مثبت', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-str">' . htmlspecialchars($emp->national_id ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-str" style="font-weight: bold; color: #14532d;">' . htmlspecialchars($emp->employee_number ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-center" style="font-weight: bold; color: #14532d;">' . htmlspecialchars($empRec?->payroll_status ?? 'غير محدد', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-right">' . htmlspecialchars($empRec?->payroll_job_title ?? 'غير محدد', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-center">' . htmlspecialchars($empRec?->payroll_job_grade ?? 'غير محدد', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-str">' . ($empRec?->first_appointment_date ? $empRec->first_appointment_date->format('Y-m-d') : '—') . '</td>';
                $rowHtml .= '<td class="text-right">' . htmlspecialchars($empRec?->initial_appointing_entity ?? 'غير محدد', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-str">' . ($empRec?->academic_appointment_date ? $empRec->academic_appointment_date->format('Y-m-d') : '—') . '</td>';
                $rowHtml .= '<td class="text-right">' . htmlspecialchars($empRec?->qualification_at_appointment ?? ($emp->qualifications->first()?->level?->level_name_ar ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-center">' . htmlspecialchars($empRec?->initial_rank ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-right" style="font-weight: bold; color: #1e293b;">' . htmlspecialchars($emp->college_name ?? 'جامعة إب', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-right">' . htmlspecialchars($emp->department_name ?? 'عام', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-center" style="font-weight: bold; color: #14532d;">' . htmlspecialchars($emp->rank_name ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '<td class="text-center" style="font-weight: bold; color: #166534;">' . htmlspecialchars($emp->status_name ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $rowHtml .= '</tr>';

                fwrite($handle, $rowHtml);
            }

            fwrite($handle, '</table></body></html>');
            fclose($handle);
        }, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ]);
    }

    private function loadEmployeeData($employeeId)
    {
        return Employee::with([
            'currentEmployment.organizationalUnit.parent',
            'currentEmployment.academicRank',
            'currentEmployment.jobGrade',
            'currentEmployment.employmentStatus',
            'latestEmployment.organizationalUnit.parent',
            'latestEmployment.academicRank',
            'latestEmployment.jobGrade',
            'latestEmployment.employmentStatus',
            'employmentRecords.organizationalUnit.parent',
            'employmentRecords.academicRank',
            'employmentRecords.jobGrade',
            'employmentRecords.employmentStatus',
            'employmentRecords.category',
            'qualifications.level',
            'qualifications.university',
            'qualifications.country',
            'qualifications.specialization',
            'decisions.type',
            'decisions.status',
            'documents.versions'
        ])->find($employeeId);
    }
}
