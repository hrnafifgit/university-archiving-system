<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected StatisticsService $statisticsService
    ) {}

    /**
     * عرض مركز الإحصائيات والتحليلات الأكاديمية والأرشيفية الشامل في واجهة موحدة
     */
    public function index(Request $request)
    {
        $filters = [
            'college_id' => $request->get('college_id'),
            'rank_id'    => $request->get('rank_id'),
            'period'     => $request->get('period', 'all'),
        ];

        $forceRefresh = $request->boolean('refresh');

        $metrics = $this->statisticsService->getDashboardMetrics($filters, $forceRefresh);

        return view('dashboard', $metrics);
    }
}
