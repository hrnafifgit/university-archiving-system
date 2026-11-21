<?php

namespace App\Services;

use App\Models\AcademicRank;
use App\Models\EmploymentRecord;
use App\Models\EmploymentStatus;
use App\Models\OrganizationalUnit;
use Illuminate\Support\Facades\DB;

class ReportMatrixService
{
    /**
     * القائمة الرسمية المعتمدة لكليات ومراكز جامعة إب
     * مستعلمة ديناميكياً من قاعدة البيانات مع الحفاظ على الترتيب المؤسسي القياسي
     */
    public function getFaculties(): array
    {
        $dbFaculties = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center'])
            ->orderBy('unit_name_ar')
            ->pluck('unit_name_ar')
            ->toArray();

        $preferredOrder = [
            'كلية طب الاسنان',
            'كلية الاداب',
            'كلية الهندسة',
            'كلية العلوم التطبيقية',
            'كلية العلوم التطبيقية والتربوية النادرة',
            'كلية العلوم الادارية',
            'كلية التربية',
            'كلية الزراعة وعلوم الاغذية',
            'مركز الحاسوب',
            'مركز التعليم المستمر',
            'مركز الارشاد',
            'مركز اللغات',
            'الكلية النوعية بالسده',
            'كلية القانون',
            'مركز البيئة',
            'كلية الطب والعلوم الصحية',
        ];

        $ordered = [];
        // أولاً: ترتيب الجهات المعتمدة المتوفرة في قاعدة البيانات
        foreach ($preferredOrder as $po) {
            foreach ($dbFaculties as $dbf) {
                if ($this->isSameFaculty($po, $dbf) && !in_array($dbf, $ordered)) {
                    $ordered[] = $dbf;
                }
            }
        }

        // ثانياً: إدراج أي كليات أو مراكز جديدة تم إنشاؤها في قاعدة البيانات
        foreach ($dbFaculties as $dbf) {
            if (!in_array($dbf, $ordered)) {
                $ordered[] = $dbf;
            }
        }

        if (empty($ordered)) {
            $ordered = $preferredOrder;
        }

        if (!in_array('غير محدد', $ordered)) {
            $ordered[] = 'غير محدد';
        }

        return $ordered;
    }

    /**
     * تسوية وتوحيد مسميات الكليات والمراكز ديناميكياً
     */
    public function normalizeFacultyName(?string $name): string
    {
        if (empty($name)) {
            return 'غير محدد';
        }

        $name = trim($name);
        $faculties = $this->getFaculties();

        foreach ($faculties as $fac) {
            if ($this->isSameFaculty($name, $fac)) {
                return $fac;
            }
        }

        // فحص قاعدة البيانات في حال كانت الوحدة تابعة لكلية
        $dbUnit = OrganizationalUnit::where('unit_name_ar', $name)->first();
        if ($dbUnit) {
            if ($dbUnit->parent && $dbUnit->parent->unit_name_ar !== 'جامعة إب') {
                return $this->normalizeFacultyName($dbUnit->parent->unit_name_ar);
            }
            if (in_array($dbUnit->unit_type, ['faculty', 'college', 'center'])) {
                return $dbUnit->unit_name_ar;
            }
        }

        return 'غير محدد';
    }

    private function cleanArabic(string $str): string
    {
        $n = trim($str);
        $n = str_replace(['أ', 'إ', 'آ'], 'ا', $n);
        $n = str_replace('ة', 'ه', $n);
        $n = str_replace('ى', 'ي', $n);
        return $n;
    }

    private function isSameFaculty(string $a, string $b): bool
    {
        $ca = $this->cleanArabic($a);
        $cb = $this->cleanArabic($b);
        if ($ca === $cb) return true;

        $keywords = [
            'طب الاسنان', 'الاداب', 'الهندسه', 'النادره', 'العلوم التطبيقيه',
            'العلوم الاداريه', 'التربيه', 'الزراعه', 'التعليم المستمر',
            'الحاسوب', 'الارشاد', 'اللغات', 'بالسده', 'القانون', 'البيئه', 'الطب والعلوم الصحيه'
        ];

        foreach ($keywords as $kw) {
            if (str_contains($ca, $kw) && str_contains($cb, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * eh1: إحصائية الكادر في الكليات والمراكز بحسب القسم
     */
    public function getMatrixEh1()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                'ou.unit_name_ar as dept_name',
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'dept_name')
            ->get();

        $allDepts = OrganizationalUnit::where('unit_type', 'department')
            ->orWhere('unit_name_ar', 'عام')
            ->orderBy('unit_name_ar')
            ->pluck('unit_name_ar')
            ->unique()
            ->values()
            ->toArray();

        if (!in_array('عام', $allDepts)) {
            array_unshift($allDepts, 'عام');
        }

        return $this->formatMatrix('eh1', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب القسم', 'القسم', $records, $faculties, $allDepts, 'dept_name');
    }

    /**
     * eh2: إحصائية الكادر في الكليات والمراكز بحسب الحالة بكشف الراتب (الزر 2 في الإكسل)
     */
    public function getMatrixEh2()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                DB::raw("COALESCE(NULLIF(er.payroll_status, ''), 'غير محدد') as col_val"),
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'col_val')
            ->get();

        $columns = [
            'منقطع', 'ايفاد', 'تفرغ علمي', 'اجازه بدون راتب',
            'بالغي احد الاجلين', 'متوفي', 'اختلالات 2017', 'غير محدد'
        ];

        return $this->formatMatrix('eh2', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب الحالة بكشف الراتب', 'الحالة بكشف الراتب', $records, $faculties, $columns, 'col_val');
    }

    /**
     * eh3: إحصائية الكادر في الكليات والمراكز بحسب الحالة ش اكاديمية (الزر 3 في الإكسل)
     */
    public function getMatrixEh3()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->leftJoin('employment_statuses as es', 'er.employment_status_id', '=', 'es.employment_status_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                DB::raw("COALESCE(es.status_name_ar, 'غير محدد') as col_val"),
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'col_val')
            ->get();

        $columns = [
            'موجود', 'منقطع', 'موفد بالخارج', 'موفد بالداخل', 'موفد',
            'اجازة تفرغ علمي', 'اجازة تفرغ براتب', 'اجازة بدون راتب',
            'منقول من الجامعة', 'مهمة وطنية', 'بلغ اجل التقاعد', 'متوفي',
            'مفصول', 'غير موجود', 'مريض', 'غير محدد'
        ];

        return $this->formatMatrix('eh3', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب الحالة ش اكاديمية', 'الحالة ش اكاديمية', $records, $faculties, $columns, 'col_val');
    }

    /**
     * eh4: إحصائية الكادر في الكليات والمراكز بحسب الدرجة بكشف الراتب (الزر 4 في الإكسل)
     */
    public function getMatrixEh4()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                DB::raw("COALESCE(NULLIF(er.payroll_job_grade, ''), 'غير محدد') as col_val"),
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'col_val')
            ->get();

        $columns = ['نائب وزير', 'استاذ', 'استاذ مشارك', 'استاذ مساعد', 'مدرس', 'معيد', 'باحث', 'غير محدد'];

        return $this->formatMatrix('eh4', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب الدرجة بكشف الراتب', 'الدرجة بكشف الراتب', $records, $faculties, $columns, 'col_val');
    }

    /**
     * eh5: إحصائية الكادر في الكليات والمراكز بحسب الدرجة ش اكاديمية (الزر 5 في الإكسل)
     */
    public function getMatrixEh5()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->leftJoin('academic_ranks as ar', 'er.academic_rank_id', '=', 'ar.academic_rank_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                DB::raw("COALESCE(ar.rank_name_ar, 'غير محدد') as col_val"),
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'col_val')
            ->get();

        $columns = ['أستاذ', 'أستاذ مشارك', 'أستاذ مساعد', 'مدرس', 'معيد', 'باحث', 'غير محدد'];

        return $this->formatMatrix('eh5', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب الدرجة ش اكاديمية', 'الدرجة ش اكاديمية', $records, $faculties, $columns, 'col_val');
    }

    /**
     * eh6: إحصائية الكادر في الكليات والمراكز بحسب المؤهل (الزر 6 في الإكسل)
     */
    public function getMatrixEh6()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                DB::raw("COALESCE(NULLIF(er.qualification_at_appointment, ''), 'غير محدد') as col_val"),
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'col_val')
            ->get();

        $columns = ['دكتوراه', 'دكتوراه مهنية', 'ماجستير', 'ماجستير مهني', 'بورد', 'بكالوريوس', 'غير محدد'];

        return $this->formatMatrix('eh6', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب المؤهل', 'المؤهل', $records, $faculties, $columns, 'col_val');
    }

    /**
     * eh7: إحصائية الكادر في الكليات والمراكز بحسب الموقف الوظيفي (الزر 7 في الإكسل)
     */
    public function getMatrixEh7()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                DB::raw("COALESCE(NULLIF(er.appointment_basis, ''), 'غير محدد') as col_val"),
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'col_val')
            ->get();

        $columns = ['مثبت', 'بدون راتب', 'خفض واضافة', 'احلال', 'بدل', 'غير محدد'];

        return $this->formatMatrix('eh7', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب الموقف الوظيفي', 'الموقف الوظيفي', $records, $faculties, $columns, 'col_val');
    }

    /**
     * eh8: إحصائية الكادر في الكليات والمراكز بحسب الجنس (الزر 8 في الإكسل)
     */
    public function getMatrixEh8()
    {
        $faculties = $this->getFaculties();

        $records = DB::table('employment_records as er')
            ->join('employees as e', 'er.employee_id', '=', 'e.employee_id')
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('organizational_units as parent', 'ou.parent_unit_id', '=', 'parent.organizational_unit_id')
            ->where('er.is_primary', true)
            ->whereNull('er.end_date')
            ->where('e.is_active_record', true)
            ->select([
                DB::raw("CASE 
                    WHEN ou.unit_type IN ('faculty', 'college', 'center') THEN ou.unit_name_ar 
                    WHEN parent.unit_name_ar != 'جامعة إب' AND parent.unit_name_ar IS NOT NULL THEN parent.unit_name_ar
                    ELSE ou.unit_name_ar 
                END as raw_faculty_name"),
                DB::raw("CASE 
                    WHEN LOWER(TRIM(e.gender)) IN ('female', 'f', 'أنثى', 'انثى') THEN 'انثى' 
                    ELSE 'ذكر' 
                END as col_val"),
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('raw_faculty_name', 'col_val')
            ->get();

        $columns = ['ذكر', 'انثى'];

        return $this->formatMatrix('eh8', 'احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب الجنس', 'الجنس', $records, $faculties, $columns, 'col_val');
    }

    /**
     * star1: لوحة الإجماليات والمؤشرات المركزية
     */
    public function getSummaryStar1()
    {
        $eh8 = $this->getMatrixEh8();
        $eh5 = $this->getMatrixEh5(); // الدرجة ش اكاديمية
        $eh2 = $this->getMatrixEh2(); // الحالة بالراتب
        $eh3 = $this->getMatrixEh3(); // الحالة ش اكاديمية
        $eh7 = $this->getMatrixEh7(); // الموقف الوظيفي
        $eh6 = $this->getMatrixEh6(); // المؤهل

        return [
            'id' => 'star1',
            'code' => 'star1',
            'title' => 'احصائيات اجمالية ومخططات بيانية',
            'totalStaff' => DB::table('employees')->where('is_active_record', true)->count(),
            'genderStats' => $eh8['colTotals'],
            'rankStats' => $eh5['colTotals'],
            'payrollStats' => $eh2['colTotals'],
            'academicStatusStats' => $eh3['colTotals'],
            'appointmentBasisStats' => $eh7['colTotals'],
            'qualificationStats' => $eh6['colTotals'],
        ];
    }

    /**
     * دالة مساعدة لتشكيل مصفوفات الـ Cross-tabulation
     */
    private function formatMatrix($id, $title, $colHeader, $records, array $faculties, array $columns, $valueField)
    {
        $matrix = [];
        $colTotals = array_fill_keys($columns, 0);
        $rowTotals = array_fill_keys($faculties, 0);

        // تجميع السجلات
        foreach ($records as $r) {
            $facName = $this->normalizeFacultyName($r->raw_faculty_name ?? null);
            $colVal = trim((string)($r->{$valueField} ?? 'غير محدد'));

            $matchedCol = $this->matchColumn($colVal, $columns);
            $totalCount = (int)$r->total;

            $matrix[$facName][$matchedCol] = ($matrix[$facName][$matchedCol] ?? 0) + $totalCount;
            $rowTotals[$facName] = ($rowTotals[$facName] ?? 0) + $totalCount;
            $colTotals[$matchedCol] = ($colTotals[$matchedCol] ?? 0) + $totalCount;
        }

        // تجهيز بيانات الرسم البياني (Bar Chart Data)
        $chartDatasets = [];
        $palette = [
            'منقطع' => '#4682B4',
            'ايفاد' => '#8B0000',
            'تفرغ علمي' => '#6B8E23',
            'اجازه بدون راتب' => '#483D8B',
            'بالغي احد الاجلين' => '#20B2AA',
            'متوفي' => '#D2691E',
            'اختلالات 2017' => '#9370DB',
            'غير محدد' => '#E9967A',
            'ذكر' => '#2B5797',
            'انثى' => '#B91D47',
            'أستاذ' => '#B91D47',
            'أستاذ مشارك' => '#D2691E',
            'أستاذ مساعد' => '#FF8C00',
            'مدرس' => '#2B5797',
            'معيد' => '#00A300',
            'باحث' => '#60A917',
            'دكتوراه' => '#990000',
            'ماجستير' => '#FF6600',
            'بكالوريوس' => '#008080',
            'مثبت' => '#7B68EE',
            'بدون راتب' => '#3CB371',
        ];

        $defaultColors = [
            '#2b5797', '#b91d47', '#ff8c00', '#00a300', '#60a917', 
            '#4682b4', '#8b0000', '#483d8b', '#20b2aa', '#d2691e', '#9370db'
        ];

        foreach ($columns as $ci => $colName) {
            $dataForCol = [];
            foreach ($faculties as $fac) {
                $dataForCol[] = $matrix[$fac][$colName] ?? 0;
            }
            $color = $palette[$colName] ?? ($defaultColors[$ci % count($defaultColors)]);
            $chartDatasets[] = [
                'label' => $colName,
                'data' => $dataForCol,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'borderWidth' => 1,
            ];
        }

        return [
            'id' => $id,
            'code' => $id,
            'title' => $title,
            'rowHeader' => 'الجهة',
            'colHeader' => $colHeader,
            'faculties' => $faculties,
            'columns' => $columns,
            'matrix' => $matrix,
            'rowTotals' => $rowTotals,
            'colTotals' => $colTotals,
            'grandTotal' => array_sum($rowTotals),
            'chartLabels' => $faculties,
            'chartDatasets' => $chartDatasets,
        ];
    }

    /**
     * مطابقة وتوحيد قيمة العمود
     */
    private function matchColumn($val, array $validCols)
    {
        $val = trim($val);
        if (in_array($val, $validCols)) {
            return $val;
        }

        $vNorm = str_replace(['أ', 'إ', 'آ'], 'ا', $val);
        $vNorm = str_replace('ة', 'ه', $vNorm);

        foreach ($validCols as $c) {
            $cNorm = str_replace(['أ', 'إ', 'آ'], 'ا', $c);
            $cNorm = str_replace('ة', 'ه', $cNorm);
            if ($vNorm === $cNorm) {
                return $c;
            }
        }

        return in_array('غير محدد', $validCols) ? 'غير محدد' : $validCols[0];
    }
}
