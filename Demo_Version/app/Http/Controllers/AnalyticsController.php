<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\AcademicDetail;
use App\Models\AdministrativeDetail;
use App\Models\DepartmentUnit;
use App\Models\JobGrade;
use App\Models\OrganizationalEntity;
use App\Models\Qualification;
use App\Models\StaffBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * مركز المؤشرات والإحصائيات التفاعلي الموحد (Tabbed Analytics)
     * المكافئ الحديث لشاشات الإكسل الثمانية المخصصة (eh1 إلى eh8)
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'departments');
        $validTabs = [
            'departments',      // eh1: بحسب القسم والكلية
            'payroll_status',   // eh6: بحسب الحالة بكشف الراتب
            'academic_status',  // eh4: بحسب الحالة شؤون أكاديمية
            'payroll_grade',    // eh6: بحسب الدرجة بكشف الراتب
            'academic_rank',    // eh2: بحسب الدرجة شؤون أكاديمية (الرتب العلمية)
            'qualifications',   // eh5: بحسب المؤهل العلمي
            'job_status',       // eh4: بحسب الموقف الوظيفي
            'gender',           // eh3: بحسب النوع الاجتماعي (الجنس)
        ];

        if (!in_array($tab, $validTabs)) {
            $tab = 'departments';
        }

        $totalStaff = StaffBase::count();
        $academicStaffCount = StaffBase::where('staff_type', 'academic')->count();
        $adminStaffCount = StaffBase::where('staff_type', 'administrative')->count();

        $data = [];
        $chartLabels = [];
        $chartValues = [];
        $tabTitle = '';
        $tabDescription = '';
        $drillParam = '';

        switch ($tab) {
            case 'departments':
                $tabTitle = 'إحصائية الكادر بحسب القسم والكلية';
                $tabDescription = 'توزيع القوة الأكاديمية والإدارية على مختلف الكليات والأقسام العلمية (مكافئ شاشة eh1).';
                
                // جلب الأقسام ذات الكادر الفعلي أو جميع الأقسام مع أعدادها
                $departments = DepartmentUnit::with('entity')
                    ->withCount('staff')
                    ->orderByDesc('staff_count')
                    ->get();

                $data = $departments->map(function ($dept) use ($totalStaff) {
                    $cnt = $dept->staff_count;
                    $percent = $totalStaff > 0 ? round(($cnt / $totalStaff) * 100, 1) : 0;
                    return [
                        'label' => $dept->name,
                        'sublabel' => $dept->entity ? $dept->entity->name : 'جامعة إب',
                        'count' => $cnt,
                        'percentage' => $percent,
                        'drill_url' => route('staff.index', ['department_id' => $dept->id]),
                    ];
                });

                // تجميع أعلى 8 أقسام للمخطط
                $topDepts = $departments->where('staff_count', '>', 0)->take(8);
                $chartLabels = $topDepts->pluck('name')->toArray();
                $chartValues = $topDepts->pluck('staff_count')->toArray();
                break;

            case 'payroll_status':
                $tabTitle = 'إحصائية الكادر بحسب الحالة بكشف الراتب';
                $tabDescription = 'مطابقة كشوفات الصرف والراتب المالي الفعلي (مكافئ شاشة eh6).';

                $statuses = ['مثبت', 'مفرغ للدراسة', 'إجازة تفرغ براتب', 'منتدب', 'خفض وإضافة', 'إحلال', 'بدل', 'بلغ أجل التقاعد', 'موقف'];
                $counts = StaffBase::select('job_status', DB::raw('count(*) as count'))
                    ->groupBy('job_status')
                    ->pluck('count', 'job_status')
                    ->toArray();

                $data = collect($statuses)->map(function ($st) use ($counts, $totalStaff) {
                    $cnt = $counts[$st] ?? 0;
                    $percent = $totalStaff > 0 ? round(($cnt / $totalStaff) * 100, 1) : 0;
                    return [
                        'label' => $st,
                        'sublabel' => 'حالة كشف الراتب',
                        'count' => $cnt,
                        'percentage' => $percent,
                        'drill_url' => route('staff.index', ['job_status' => $st]),
                    ];
                });

                $chartLabels = $data->pluck('label')->toArray();
                $chartValues = $data->pluck('count')->toArray();
                break;

            case 'academic_status':
                $tabTitle = 'إحصائية الحالة بحسب الشؤون الأكاديمية';
                $tabDescription = 'الوضع الأكاديمي الحقيقي للهيئة التدريسية (مكافئ شاشة eh4).';

                $acadStatuses = ['مستمر', 'تفرغ علمي', 'موفد بالخارج', 'موفد بالداخل', 'إجازة بدون راتب', 'مهمة وطنية'];
                $counts = AcademicDetail::select('academic_status', DB::raw('count(*) as count'))
                    ->groupBy('academic_status')
                    ->pluck('count', 'academic_status')
                    ->toArray();

                $baseCount = $academicStaffCount > 0 ? $academicStaffCount : 1;
                $data = collect($acadStatuses)->map(function ($st) use ($counts, $baseCount) {
                    $cnt = $counts[$st] ?? 0;
                    $percent = round(($cnt / $baseCount) * 100, 1);
                    return [
                        'label' => $st,
                        'sublabel' => 'أعضاء هيئة التدريس',
                        'count' => $cnt,
                        'percentage' => $percent,
                        'drill_url' => route('staff.index', ['staff_type' => 'academic', 'academic_status' => $st]),
                    ];
                });

                $chartLabels = $data->pluck('label')->toArray();
                $chartValues = $data->pluck('count')->toArray();
                break;

            case 'payroll_grade':
                $tabTitle = 'إحصائية الكادر بحسب الدرجة بكشف الراتب (المالية)';
                $tabDescription = 'توزيع الدرجات الوظيفية والمالية للكادر الإداري والفني (مكافئ شاشة eh6).';

                $grades = JobGrade::all();
                $counts = AdministrativeDetail::select('job_grade_id', DB::raw('count(*) as count'))
                    ->groupBy('job_grade_id')
                    ->pluck('count', 'job_grade_id')
                    ->toArray();

                $baseCount = $adminStaffCount > 0 ? $adminStaffCount : 1;
                $data = $grades->map(function ($grade) use ($counts, $baseCount) {
                    $cnt = $counts[$grade->id] ?? 0;
                    $percent = round(($cnt / $baseCount) * 100, 1);
                    return [
                        'label' => $grade->grade_name,
                        'sublabel' => $grade->executive_level ?? 'كشف المرتب المالي',
                        'count' => $cnt,
                        'percentage' => $percent,
                        'drill_url' => route('staff.index', ['staff_type' => 'administrative', 'job_grade_id' => $grade->id]),
                    ];
                });

                $chartLabels = $data->pluck('label')->toArray();
                $chartValues = $data->pluck('count')->toArray();
                break;

            case 'academic_rank':
                $tabTitle = 'إحصائية الدرجة العلمية (الرتب الأكاديمية)';
                $tabDescription = 'توزيع أعضاء هيئة التدريس حسب الألقاب العلمية المعتمدة (مكافئ شاشة eh2).';

                $ranks = AcademicRank::orderBy('seniority_order')->get();
                $counts = AcademicDetail::select('rank_id', DB::raw('count(*) as count'))
                    ->groupBy('rank_id')
                    ->pluck('count', 'rank_id')
                    ->toArray();

                $baseCount = $academicStaffCount > 0 ? $academicStaffCount : 1;
                $data = $ranks->map(function ($rank) use ($counts, $baseCount) {
                    $cnt = $counts[$rank->id] ?? 0;
                    $percent = round(($cnt / $baseCount) * 100, 1);
                    return [
                        'label' => $rank->name,
                        'sublabel' => 'رتبة أكاديمية بجامعة إب',
                        'count' => $cnt,
                        'percentage' => $percent,
                        'drill_url' => route('staff.index', ['staff_type' => 'academic', 'rank_id' => $rank->id]),
                    ];
                });

                $chartLabels = $data->pluck('label')->toArray();
                $chartValues = $data->pluck('count')->toArray();
                break;

            case 'qualifications':
                $tabTitle = 'إحصائية الكادر بحسب المؤهل العلمي';
                $tabDescription = 'مستويات الدرجات العلمية المحققة (دكتوراه، ماجستير، بكالوريوس) (مكافئ شاشة eh5).';

                $degrees = ['دكتوراه', 'ماجستير', 'بورد', 'بكالوريوس', 'دبلوم عالي'];
                $data = collect($degrees)->map(function ($deg) use ($totalStaff) {
                    $cnt = StaffBase::whereHas('qualifications', function ($q) use ($deg) {
                        $q->where('degree_type', 'like', "%{$deg}%");
                    })->count();
                    $percent = $totalStaff > 0 ? round(($cnt / $totalStaff) * 100, 1) : 0;
                    return [
                        'label' => $deg,
                        'sublabel' => 'المؤهل العلمي المسجل بالأرشيف',
                        'count' => $cnt,
                        'percentage' => $percent,
                        'drill_url' => route('staff.index', ['degree_type' => $deg]),
                    ];
                });

                $chartLabels = $data->pluck('label')->toArray();
                $chartValues = $data->pluck('count')->toArray();
                break;

            case 'job_status':
                $tabTitle = 'إحصائية الكادر بحسب الموقف الوظيفي';
                $tabDescription = 'موقف الخدمة الفعلية بالجامعة (على رأس العمل، منتدب، إعارة) (مكافئ شاشة eh4).';

                $statuses = ['مثبت', 'موفد بالخارج', 'موفد بالداخل', 'إجازة تفرغ علمي', 'منتدب', 'إجازة بدون راتب', 'مهمة وطنية', 'مفرغ للدراسة'];
                $counts = StaffBase::select('job_status', DB::raw('count(*) as count'))
                    ->groupBy('job_status')
                    ->pluck('count', 'job_status')
                    ->toArray();

                $data = collect($statuses)->map(function ($st) use ($counts, $totalStaff) {
                    $cnt = $counts[$st] ?? 0;
                    $percent = $totalStaff > 0 ? round(($cnt / $totalStaff) * 100, 1) : 0;
                    return [
                        'label' => $st,
                        'sublabel' => 'الموقف الوظيفي المعتمد',
                        'count' => $cnt,
                        'percentage' => $percent,
                        'drill_url' => route('staff.index', ['job_status' => $st]),
                    ];
                });

                $chartLabels = $data->pluck('label')->toArray();
                $chartValues = $data->pluck('count')->toArray();
                break;

            case 'gender':
                $tabTitle = 'إحصائية الكادر بحسب النوع الاجتماعي (الجنس)';
                $tabDescription = 'نسب وأعداد أعضاء الكادر من الذكور والإناث في الجامعة (مكافئ شاشة eh3).';

                $maleCount = StaffBase::where('gender', 'ذكر')->count();
                $femaleCount = StaffBase::where('gender', 'أنثى')->count();

                $malePercent = $totalStaff > 0 ? round(($maleCount / $totalStaff) * 100, 1) : 0;
                $femalePercent = $totalStaff > 0 ? round(($femaleCount / $totalStaff) * 100, 1) : 0;

                $data = collect([
                    [
                        'label' => 'ذكور',
                        'sublabel' => 'أعضاء هيئة التدريس والكادر',
                        'count' => $maleCount,
                        'percentage' => $malePercent,
                        'drill_url' => route('staff.index', ['gender' => 'ذكر']),
                    ],
                    [
                        'label' => 'إناث',
                        'sublabel' => 'عضوات هيئة التدريس والكادر',
                        'count' => $femaleCount,
                        'percentage' => $femalePercent,
                        'drill_url' => route('staff.index', ['gender' => 'أنثى']),
                    ]
                ]);

                $chartLabels = ['ذكور', 'إناث'];
                $chartValues = [$maleCount, $femaleCount];
                break;
        }

        return view('analytics.index', compact(
            'tab',
            'tabTitle',
            'tabDescription',
            'totalStaff',
            'academicStaffCount',
            'adminStaffCount',
            'data',
            'chartLabels',
            'chartValues'
        ));
    }
}
