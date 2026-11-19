<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\DigitalArchive;
use App\Models\OrganizationalEntity;
use App\Models\Qualification;
use App\Models\StaffBase;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * لوحة التحكم والعمليات الرئيسية (مركز الأزرار التشغيلية دون حشو الإحصائيات)
     */
    public function index()
    {
        $totalStaff = StaffBase::count();
        $totalArchives = DigitalArchive::count();
        $academicStaffCount = StaffBase::where('staff_type', 'academic')->count();
        $adminStaffCount = StaffBase::where('staff_type', 'administrative')->count();
        $contractStaffCount = StaffBase::where('staff_type', 'contract')->count();
        $collegesCount = OrganizationalEntity::count();

        // أحدث الكوادر المقيدة
        $recentStaff = StaffBase::with(['department.entity'])->latest()->take(4)->get();

        return view('dashboard', compact(
            'totalStaff',
            'totalArchives',
            'academicStaffCount',
            'adminStaffCount',
            'contractStaffCount',
            'collegesCount',
            'recentStaff'
        ));
    }

    /**
     * شاشة الإحصائيات والمؤشرات الجامعية الشاملة (شاشة منفصلة متكاملة)
     */
    public function statistics()
    {
        $totalStaff = StaffBase::count();
        $totalArchives = DigitalArchive::count();
        $academicStaffCount = StaffBase::where('staff_type', 'academic')->count();
        $adminStaffCount = StaffBase::where('staff_type', 'administrative')->count();
        $contractStaffCount = StaffBase::where('staff_type', 'contract')->count();

        // توزيع الكادر حسب الكليات والمراكز
        $colleges = OrganizationalEntity::with(['departments' => function($q) {
            $q->withCount('staff');
        }])->withCount(['departments as staff_count' => function ($q) {
            $q->join('staff_base', 'departments_units.id', '=', 'staff_base.department_id');
        }])->get();

        // الرتب الأكاديمية
        $academicRanks = AcademicRank::withCount('academicDetails')->orderBy('seniority_order')->get();

        // المؤهلات العلمية
        $qualificationStats = Qualification::select('degree_type', DB::raw('count(*) as count'))
            ->groupBy('degree_type')
            ->get();

        // توزيع الجنس (ذكور / إناث)
        $genderStats = StaffBase::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get();

        // توزيع الموقف الوظيفي
        $jobStatusStats = StaffBase::select('job_status', DB::raw('count(*) as count'))
            ->groupBy('job_status')
            ->get();

        return view('statistics', compact(
            'totalStaff',
            'totalArchives',
            'academicStaffCount',
            'adminStaffCount',
            'contractStaffCount',
            'colleges',
            'academicRanks',
            'qualificationStats',
            'genderStats',
            'jobStatusStats'
        ));
    }
}
