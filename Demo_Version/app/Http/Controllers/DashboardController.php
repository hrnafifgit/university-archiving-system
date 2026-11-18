<?php

namespace App\Http\Controllers;

use App\Models\DigitalArchive;
use App\Models\OrganizationalEntity;
use App\Models\StaffBase;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStaff = StaffBase::count();
        $totalArchives = DigitalArchive::count();
        $academicStaffCount = StaffBase::where('staff_type', 'academic')->count();
        $adminStaffCount = StaffBase::where('staff_type', 'administrative')->count();
        $contractStaffCount = StaffBase::where('staff_type', 'contract')->count();

        // إحصائيات الكليات
        $colleges = OrganizationalEntity::withCount(['departments as staff_count' => function ($q) {
            $q->join('staff_base', 'departments_units.id', '=', 'staff_base.department_id');
        }])->get();

        // أحدث الوثائق والقرارات
        $recentArchives = DigitalArchive::with(['staff.department.entity', 'decision'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalStaff',
            'totalArchives',
            'academicStaffCount',
            'adminStaffCount',
            'contractStaffCount',
            'colleges',
            'recentArchives'
        ));
    }
}
