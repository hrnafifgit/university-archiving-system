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

    /**
     * محرك البحث الشامل السريع (Ctrl + K Command Palette)
     */
    public function globalSearch(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([
                'employees' => [],
                'documents' => [],
                'units'     => []
            ]);
        }

        // 1. بحث الكوادر (أكاديمي وإداري)
        $employees = \App\Models\Employee::with(['currentEmployment.organizationalUnit'])
            ->where('is_active_record', true)
            ->where(function ($sub) use ($q) {
                $sub->where('full_name_ar', 'ilike', "%{$q}%")
                    ->orWhere('employee_number', 'ilike', "%{$q}%")
                    ->orWhere('national_id', 'ilike', "%{$q}%");
            })
            ->limit(6)
            ->get(['employee_id', 'full_name_ar', 'employee_number', 'national_id'])
            ->map(function ($e) {
                $unit = $e->currentEmployment?->organizationalUnit?->unit_name_ar ?? 'جامعة إب';
                return [
                    'id'    => $e->employee_id,
                    'title' => $e->full_name_ar,
                    'meta'  => 'رقم وظيفي: ' . $e->employee_number . ' | ' . $unit,
                    'url'   => route('employees.show', $e->employee_id),
                    'type'  => 'employee',
                    'icon'  => 'fa-user'
                ];
            });

        // 2. بحث الوثائق والمستودع الرقمي
        $documents = \App\Models\Document::with(['employee', 'documentType', 'currentVersion'])
            ->where(function ($sub) use ($q) {
                $sub->where('document_title', 'ilike', "%{$q}%")
                    ->orWhere('document_number', 'ilike', "%{$q}%")
                    ->orWhere('description', 'ilike', "%{$q}%");
            })
            ->limit(6)
            ->get()
            ->map(function ($d) {
                $emp = $d->employee?->full_name_ar ?? 'سجل عام';
                $v = $d->currentVersion;
                $url = $v ? route('archive.preview', $v->document_version_id) : route('archive.index', ['search' => $d->document_title]);
                return [
                    'id'    => $d->document_id,
                    'title' => $d->document_title,
                    'meta'  => 'قيد: ' . ($d->document_number ?? '—') . ' | الكادر: ' . $emp . ' (' . ($d->documentType?->type_name_ar ?? 'عام') . ')',
                    'url'   => $url,
                    'type'  => 'document',
                    'icon'  => 'fa-file-pdf'
                ];
            });

        // 3. بحث الكليات والأقسام
        $units = \App\Models\OrganizationalUnit::with('parent')
            ->where('is_active', true)
            ->where('unit_name_ar', 'ilike', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(function ($u) {
                $parent = $u->parent?->unit_name_ar;
                $typeLabel = $u->unit_type == 'department' ? 'قسم علمي' : 'كلية / إدارة';
                return [
                    'id'    => $u->organizational_unit_id,
                    'title' => $u->unit_name_ar,
                    'meta'  => $typeLabel . ($parent ? ' تابع لـ ' . $parent : ''),
                    'url'   => route('master_data.index') . '?unit_id=' . $u->organizational_unit_id,
                    'type'  => 'unit',
                    'icon'  => $u->unit_type == 'department' ? 'fa-diagram-project' : 'fa-university'
                ];
            });

        return response()->json([
            'employees' => $employees,
            'documents' => $documents,
            'units'     => $units
        ]);
    }
}
