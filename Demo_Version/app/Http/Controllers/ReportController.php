<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\OrganizationalUnit;
use App\Services\ReportMatrixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected ReportMatrixService $matrixService;

    public function __construct(ReportMatrixService $matrixService)
    {
        $this->matrixService = $matrixService;
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
                foreach ($employee->qualifications as $q) {
                    $lvl = mb_strtolower($q->level->level_code ?? '');
                    if (str_contains($lvl, 'doc') || str_contains($lvl, 'phd')) {
                        $qualificationsByLevel['phd'] = $q;
                    } elseif (str_contains($lvl, 'master') || str_contains($lvl, 'magister')) {
                        $qualificationsByLevel['master'] = $q;
                    } elseif (str_contains($lvl, 'bach') || str_contains($lvl, 'licence')) {
                        $qualificationsByLevel['bachelor'] = $q;
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

        // قوائم الحالات المرجعية للفلاتر
        $distinctPayrollStatuses = ['مستمر', 'منقطع', 'ايفاد', 'تفرغ علمي', 'اجازه بدون راتب', 'بالغي احد الاجلين', 'متوفي', 'اختلالات 2017', 'غير محدد'];
        $distinctPayrollGrades = ['نائب وزير', 'استاذ', 'استاذ مشارك', 'استاذ مساعد', 'مدرس', 'معيد', 'باحث', 'غير محدد'];
        $distinctAppointmentBases = ['مثبت', 'بدون راتب', 'خفض واضافة', 'احلال', 'بدل', 'غير محدد'];

        return view('reports.index', compact(
            'reportType',
            'matrixCode',
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

        $filename = "{$matrixCode}_matrix_export_" . date('Y_m_d') . ".csv";

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // عنوان المصفوفة
            fputcsv($handle, [$data['title']]);
            fputcsv($handle, []);

            // ترويسة الأعمدة
            $headers = array_merge([$data['rowHeader']], $data['columns'], ['الإجمالي']);
            fputcsv($handle, $headers);

            // صفوف الكليات
            foreach ($data['faculties'] as $f) {
                $fName = is_object($f) ? $f->unit_name_ar : (string)$f;
                $row = [$fName];
                foreach ($data['columns'] as $c) {
                    $row[] = $data['matrix'][$fName][$c] ?? 0;
                }
                $row[] = $data['rowTotals'][$fName] ?? 0;
                fputcsv($handle, $row);
            }

            // سطر الإجمالي العام
            $totalRow = ['الإجمالي العام'];
            foreach ($data['columns'] as $c) {
                $totalRow[] = $data['colTotals'][$c] ?? 0;
            }
            $totalRow[] = $data['grandTotal'];
            fputcsv($handle, $totalRow);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * تصدير كشف الكادر (19 عموداً) أو التقرير المخصص إلى CSV
     */
    public function export(Request $request)
    {
        $filename = "staff_report_export_" . date('Y_m_d') . ".csv";

        return new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // 19 عموداً
            $headers = [
                'م', 'الاسم', 'الجنس', 'تاريخ الميلاد', 'الموقف الوظيفي', 'الرقم الوطني', 'الرقم الوظيفي',
                'الحالة بكشف الراتب', 'الوظيفة بكشف الراتب', 'الدرجة بكشف الراتب',
                'تاريخ التعيين بالوظيفة أول مرة', 'الجهة المعين بها', 'تاريخ التعيين بالجامعة أكاديمي',
                'المؤهل عند التعيين بالجامعة أكاديمي', 'الدرجة المعين فيها', 'الجهة', 'القسم/الإدارة',
                'الدرجة الحالية', 'الوضع الحالي'
            ];
            fputcsv($handle, $headers);

            $query = Employee::with([
                'currentEmployment.organizationalUnit.parent',
                'currentEmployment.academicRank',
                'currentEmployment.jobGrade',
                'currentEmployment.employmentStatus',
            ])->where('is_active_record', true);

            // تطبيق فلاتر البحث في التصدير
            if ($request->filled('faculty_id') && $request->faculty_id !== 'all') {
                $fId = $request->faculty_id;
                $query->whereHas('currentEmployment', function ($q) use ($fId) {
                    $q->where('organizational_unit_id', $fId)
                      ->orWhereHas('organizationalUnit', fn($ou) => $ou->where('parent_unit_id', $fId));
                });
            }

            if ($request->filled('department_id')) {
                $dId = $request->department_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('organizational_unit_id', $dId));
            }

            if ($request->filled('rank_id')) {
                $rId = $request->rank_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('academic_rank_id', $rId));
            }

            if ($request->filled('status_id')) {
                $sId = $request->status_id;
                $query->whereHas('currentEmployment', fn($q) => $q->where('employment_status_id', $sId));
            }

            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('full_name_ar', 'ilike', "%{$s}%")
                      ->orWhere('employee_number', 'ilike', "%{$s}%")
                      ->orWhere('national_id', 'ilike', "%{$s}%");
                });
            }

            $employees = $query->orderBy('full_name_ar')->get();

            foreach ($employees as $idx => $emp) {
                $empRec = $emp->currentEmployment;
                $ou = $empRec?->organizationalUnit;
                $faculty = $ou?->parent ?? $ou;
                $dept = ($ou && $ou->parent_unit_id) ? $ou : null;

                $isFemale = in_array(strtolower(trim($emp->gender ?? '')), ['female', 'f', 'أنثى', 'انثى'], true);

                $row = [
                    $idx + 1,
                    $emp->full_name_ar,
                    $isFemale ? 'أنثى' : 'ذكر',
                    $emp->date_of_birth?->format('Y') ?? '',
                    $empRec?->appointment_basis ?? 'مثبت',
                    $emp->national_id ?? '',
                    $emp->employee_number ?? '',
                    $empRec?->payroll_status ?? 'غير محدد',
                    $empRec?->payroll_job_title ?? 'غير محدد',
                    $empRec?->payroll_job_grade ?? 'غير محدد',
                    $empRec?->first_appointment_date?->format('Y/m/d') ?? '',
                    'غير محدد',
                    $empRec?->academic_appointment_date?->format('Y/m/d') ?? '',
                    $empRec?->qualification_at_appointment ?? 'غير محدد',
                    $empRec?->initial_rank ?? 'معيد',
                    $faculty?->unit_name_ar ?? 'جامعة إب',
                    $dept?->unit_name_ar ?? 'عام',
                    $empRec?->academicRank?->rank_name_ar ?? 'غير محدد',
                    $empRec?->employmentStatus?->status_name_ar ?? 'غير محدد',
                ];
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function loadEmployeeData($employeeId)
    {
        return Employee::with([
            'currentEmployment.organizationalUnit.parent',
            'currentEmployment.academicRank',
            'currentEmployment.jobGrade',
            'currentEmployment.employmentStatus',
            'employmentRecords.organizationalUnit',
            'employmentRecords.academicRank',
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
