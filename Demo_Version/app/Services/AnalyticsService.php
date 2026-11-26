<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * توليد كافة البيانات الإحصائية للرسوم البيانية والجداول من قاعدة البيانات
     *
     * @param string $cadreFilter all | academic | admin | hourly
     * @return array
     */
    public function getAnalyticsData(string $cadreFilter = 'all'): array
    {
        $employees = Employee::with([
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
            'qualifications.level',
        ])->where('is_active_record', true)->get();

        // إحصائيات عامة للبطاقات العليا قبل الفلترة
        $kpis = [
            'total' => $employees->count(),
            'academic' => 0,
            'admin' => 0,
            'hourly' => 0,
        ];

        $filteredEmployees = new Collection();

        foreach ($employees as $emp) {
            $rec = $emp->currentEmployment ?? $emp->latestEmployment;
            $catId = (int) ($rec?->employee_category_id ?? 1);
            $basis = $rec?->appointment_basis ?? '';
            $notes = $rec?->notes ?? '';

            $isHourly = ($catId === 7)
                || str_contains($basis, 'ساعات')
                || str_contains($basis, 'ساعة')
                || str_contains($notes, 'ساعات');

            if ($isHourly) {
                $kpis['hourly']++;
                $empType = 'hourly';
            } elseif (in_array($catId, [2, 3, 4], true)) {
                $kpis['admin']++;
                $empType = 'admin';
            } else {
                $kpis['academic']++;
                $empType = 'academic';
            }

            if ($cadreFilter === 'all' || $cadreFilter === $empType) {
                $filteredEmployees->push($emp);
            }
        }

        // تجميع بيانات المخططات التسعة
        $genderMap = ['ذكر' => 0, 'أنثى' => 0];
        $unitMap = [];
        $rankMap = [];
        $basisMap = [];
        $qualCurrentMap = [];
        $qualAtAppMap = [];
        $payrollStatusMap = [];
        $empStatusMap = [];
        $jobTitleMap = [];

        foreach ($filteredEmployees as $emp) {
            $rec = $emp->currentEmployment ?? $emp->latestEmployment;

            // 1. الجنس
            $isFemale = in_array(strtolower(trim($emp->gender ?? '')), ['female', 'f', 'أنثى', 'انثى'], true);
            $genderMap[$isFemale ? 'أنثى' : 'ذكر']++;

            // 2. الجهة / الكلية / الإدارة
            $unit = $rec?->organizationalUnit;
            $unitName = $unit?->parent?->unit_name_ar ?? $unit?->unit_name_ar ?? 'غير محدد';
            $unitMap[$unitName] = ($unitMap[$unitName] ?? 0) + 1;

            // 3. الرتبة العلمية / الدرجة الحالية
            $rankName = $rec?->academicRank?->rank_name_ar
                ?? $rec?->jobGrade?->grade_name_ar
                ?? ($rec?->payroll_job_grade ?: 'غير محدد');
            $rankMap[$rankName] = ($rankMap[$rankName] ?? 0) + 1;

            // 4. الموقف الوظيفي (مثبت، بدون راتب، إحلال...)
            $basisName = trim($rec?->appointment_basis ?? '') ?: 'غير محدد';
            $basisMap[$basisName] = ($basisMap[$basisName] ?? 0) + 1;

            // 5. المؤهل العلمي الحالي (الأعلى)
            $highestQual = $emp->qualifications
                ->sortByDesc(fn($q) => ($q->is_highest_qualification ? 100 : 0) + ($q->level?->level_order ?? 0))
                ->first();
            $qualName = $highestQual?->level?->level_name_ar ?? 'غير محدد';
            $qualCurrentMap[$qualName] = ($qualCurrentMap[$qualName] ?? 0) + 1;

            // 6. المؤهل عند التعيين
            $qualAppName = trim($rec?->qualification_at_appointment ?? '') ?: 'غير محدد';
            $qualAtAppMap[$qualAppName] = ($qualAtAppMap[$qualAppName] ?? 0) + 1;

            // 7. الحالة بكشف الراتب
            $paySt = trim($rec?->payroll_status ?? '') ?: 'غير محدد';
            $payrollStatusMap[$paySt] = ($payrollStatusMap[$paySt] ?? 0) + 1;

            // 8. الحالة الوظيفية (شؤون أكاديمية / إدارية)
            $eSt = $rec?->employmentStatus?->status_name_ar ?? 'غير محدد';
            $empStatusMap[$eSt] = ($empStatusMap[$eSt] ?? 0) + 1;

            // 9. الوظيفة / المسمى الوظيفي الحالي
            $jTitle = $rec?->jobTitle?->title_name_ar
                ?? (trim($rec?->payroll_job_title ?? '') ?: 'غير محدد');
            $jobTitleMap[$jTitle] = ($jobTitleMap[$jTitle] ?? 0) + 1;
        }

        return [
            'kpis' => $kpis,
            'filtered_total' => $filteredEmployees->count(),
            'charts' => [
                'gender' => $this->formatSeries($genderMap, false),
                'units' => $this->formatSeries($unitMap, true, 12),
                'ranks' => $this->formatSeries($rankMap, true, 10),
                'basis' => $this->formatSeries($basisMap, true, 6),
                'qual_current' => $this->formatSeries($qualCurrentMap, true, 10),
                'qual_at_app' => $this->formatSeries($qualAtAppMap, true, 10),
                'payroll_status' => $this->formatSeries($payrollStatusMap, true, 10),
                'emp_status' => $this->formatSeries($empStatusMap, true, 10),
                'job_titles' => $this->formatSeries($jobTitleMap, true, 10),
            ],
        ];
    }

    /**
     * تنسيق وترتيب السلاسل الإحصائية مع حساب النسب المئوية
     */
    private function formatSeries(array $map, bool $sortDesc = true, ?int $limit = null): array
    {
        if ($sortDesc) {
            arsort($map);
        }

        if ($limit !== null && count($map) > $limit) {
            $top = array_slice($map, 0, $limit, true);
            $others = array_sum(array_slice($map, $limit, null, true));
            if ($others > 0) {
                $top['أخرى'] = ($top['أخرى'] ?? 0) + $others;
            }
            $map = $top;
        }

        $total = array_sum($map);
        $items = [];
        foreach ($map as $label => $count) {
            $items[] = [
                'label' => $label,
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return [
            'labels' => array_keys($map),
            'counts' => array_values($map),
            'total' => $total,
            'items' => $items,
        ];
    }
}
