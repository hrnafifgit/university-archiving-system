@php
    $kpis = $analyticsData['kpis'] ?? ['total' => 0, 'academic' => 0, 'admin' => 0, 'hourly' => 0];
    $charts = $analyticsData['charts'] ?? [];
@endphp

<div class="flex-1 flex flex-col gap-3 min-h-0 overflow-y-auto pr-0.5 pb-6 custom-scrollbar">

    <!-- ترويسة الطباعة الرسمية (تظهر فقط عند الطباعة) -->
    <div class="hidden print:flex items-center justify-between border-b-2 border-[#14532d] pb-3 mb-4">
        <div>
            <h1 class="text-lg font-black text-slate-900">جامعة إب — نظام الأرشفة الإلكتروني</h1>
            <p class="text-xs text-slate-600 font-bold">لوحة الإحصائيات التحليلية والمخططات البيانية</p>
        </div>
        <div class="text-left text-xs font-mono text-slate-600">
            <div>التاريخ: {{ date('Y-m-d') }}</div>
            <div>الإجمالي: {{ $analyticsData['filtered_total'] ?? 0 }}</div>
        </div>
    </div>

    <!-- 1. شريط الفلترة العلوي + مؤشرات الكادر -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 px-5 flex flex-wrap items-center justify-between gap-3 shadow-xs shrink-0 select-none print:hidden">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-black text-slate-500 flex items-center gap-1.5 ml-1">
                <i class="fa-solid fa-filter text-[#14532d]"></i> الفئة:
            </span>
            <a href="{{ route('reports.index', ['type' => 'analytics', 'cadre' => 'all']) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border {{ $analyticsCadre === 'all' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                <i class="fa-solid fa-users text-[11px]"></i>
                <span>الكل</span>
                <span class="font-mono px-1.5 py-0.5 rounded-md text-[10px] {{ $analyticsCadre === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200/70 text-slate-700' }}">{{ $kpis['total'] }}</span>
            </a>
            <a href="{{ route('reports.index', ['type' => 'analytics', 'cadre' => 'academic']) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border {{ $analyticsCadre === 'academic' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                <i class="fa-solid fa-graduation-cap text-[11px]"></i>
                <span>أكاديمي</span>
                <span class="font-mono px-1.5 py-0.5 rounded-md text-[10px] {{ $analyticsCadre === 'academic' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">{{ $kpis['academic'] }}</span>
            </a>
            <a href="{{ route('reports.index', ['type' => 'analytics', 'cadre' => 'admin']) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border {{ $analyticsCadre === 'admin' ? 'bg-blue-900 text-white border-blue-900 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                <i class="fa-solid fa-briefcase text-[11px]"></i>
                <span>إداري</span>
                <span class="font-mono px-1.5 py-0.5 rounded-md text-[10px] {{ $analyticsCadre === 'admin' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">{{ $kpis['admin'] }}</span>
            </a>
            <a href="{{ route('reports.index', ['type' => 'analytics', 'cadre' => 'hourly']) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border {{ $analyticsCadre === 'hourly' ? 'bg-amber-700 text-white border-amber-700 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                <i class="fa-solid fa-clock text-[11px]"></i>
                <span>بالساعات</span>
                <span class="font-mono px-1.5 py-0.5 rounded-md text-[10px] {{ $analyticsCadre === 'hourly' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $kpis['hourly'] }}</span>
            </a>
        </div>

        <div class="text-xs font-bold text-slate-500 flex items-center gap-2">
            <i class="fa-solid fa-chart-simple text-[#14532d]"></i>
            <span>العينة المعروضة: <strong class="text-slate-900 font-mono text-sm">{{ $analyticsData['filtered_total'] ?? 0 }}</strong></span>
        </div>
    </div>

    <!-- 2. الصف الأول: الدوائر البيانية (الجنس + الموقف الوظيفي) + الرتب العلمية -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        
        <!-- بطاقة 1: الجنس (دائرة بيانية) -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-venus-mars text-blue-600"></i>
                    <span>توزيع الجنس</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartGender', 'توزيع_الجنس')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="h-52 relative flex items-center justify-center">
                <canvas id="chartGender"></canvas>
            </div>
            <div class="border-t border-slate-100 pt-2 space-y-1 text-xs">
                @foreach($charts['gender']['items'] ?? [] as $item)
                    <div class="flex items-center justify-between px-1">
                        <span class="font-bold text-slate-700">{{ $item['label'] }}</span>
                        <span class="font-mono font-bold text-slate-900">{{ $item['count'] }} <span class="text-slate-400 font-normal">({{ $item['percent'] }}%)</span></span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- بطاقة 2: الموقف الوظيفي (دائرة بيانية) -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-purple-600"></i>
                    <span>الموقف الوظيفي</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartBasis', 'الموقف_الوظيفي')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="h-52 relative flex items-center justify-center">
                <canvas id="chartBasis"></canvas>
            </div>
            <div class="border-t border-slate-100 pt-2 space-y-1 text-xs max-h-28 overflow-y-auto custom-scrollbar">
                @foreach($charts['basis']['items'] ?? [] as $item)
                    <div class="flex items-center justify-between px-1">
                        <span class="font-bold text-slate-700 truncate">{{ $item['label'] }}</span>
                        <span class="font-mono font-bold text-slate-900 shrink-0">{{ $item['count'] }} <span class="text-slate-400 font-normal">({{ $item['percent'] }}%)</span></span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- بطاقة 3: الرتب والدرجات العلمية (أعمدة أفقية) -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-award text-amber-600"></i>
                    <span>الرتب والدرجات الحالية</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartRanks', 'الرتب_والدرجات')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="relative" style="height: {{ max(240, count($charts['ranks']['labels'] ?? []) * 32) }}px">
                <canvas id="chartRanks"></canvas>
            </div>
        </div>

    </div>

    <!-- 3. الصف الثاني: الجهات والكليات + المسميات الوظيفية (أعمدة أفقية واضحة تماماً) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        <!-- بطاقة 4: التوزيع حسب الجهة / الكلية / الإدارة -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-[#14532d]"></i>
                    <span>التوزيع حسب الجهة والإدارة</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartUnits', 'التوزيع_حسب_الجهة')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="relative" style="height: {{ max(280, count($charts['units']['labels'] ?? []) * 32) }}px">
                <canvas id="chartUnits"></canvas>
            </div>
        </div>

        <!-- بطاقة 5: المسميات والوظائف الحالية -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-id-badge text-indigo-600"></i>
                    <span>التوزيع حسب الوظيفة الحالية</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartJobTitles', 'الوظيفة_الحالية')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="relative" style="height: {{ max(280, count($charts['job_titles']['labels'] ?? []) * 32) }}px">
                <canvas id="chartJobTitles"></canvas>
            </div>
        </div>

    </div>

    <!-- 4. الصف الثالث: المؤهل العلمي الحالي + المؤهل عند التعيين -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        <!-- بطاقة 6: المؤهل العلمي الحالي -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap text-emerald-700"></i>
                    <span>المؤهل العلمي الحالي</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartQualCurrent', 'المؤهل_الحالي')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="relative" style="height: {{ max(260, count($charts['qual_current']['labels'] ?? []) * 32) }}px">
                <canvas id="chartQualCurrent"></canvas>
            </div>
        </div>

        <!-- بطاقة 7: المؤهل عند التعيين -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-certificate text-amber-600"></i>
                    <span>المؤهل عند التعيين</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartQualAtApp', 'المؤهل_عند_التعيين')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="relative" style="height: {{ max(260, count($charts['qual_at_app']['labels'] ?? []) * 32) }}px">
                <canvas id="chartQualAtApp"></canvas>
            </div>
        </div>

    </div>

    <!-- 5. الصف الرابع: الحالة بكشف الراتب + الحالة الوظيفية -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        <!-- بطاقة 8: الحالة بكشف الراتب -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-money-check-dollar text-teal-600"></i>
                    <span>الحالة بكشف الراتب</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartPayrollStatus', 'الحالة_بكشف_الراتب')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="relative" style="height: {{ max(260, count($charts['payroll_status']['labels'] ?? []) * 32) }}px">
                <canvas id="chartPayrollStatus"></canvas>
            </div>
        </div>

        <!-- بطاقة 9: الحالة الوظيفية (شؤون الكادر) -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-dot text-rose-600"></i>
                    <span>الحالة الوظيفية (شؤون الكادر)</span>
                </h3>
                <button type="button" onclick="downloadChartImage('chartEmpStatus', 'الحالة_الوظيفية')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer print:hidden" title="تحميل صورة">
                    <i class="fa-solid fa-download text-[11px]"></i>
                </button>
            </div>
            <div class="relative" style="height: {{ max(260, count($charts['emp_status']['labels'] ?? []) * 32) }}px">
                <canvas id="chartEmpStatus"></canvas>
            </div>
        </div>

    </div>

</div>

<script src="{{ asset('js/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const analyticsChartsData = @json($charts);

    // إعداد الخط العربي القياسي الواضح لجميع المخططات
    Chart.defaults.font.family = "'Cairo', 'Segoe UI', Tahoma, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#334155';

    // إضافة مدمجة لرسم الأرقام مباشرة في نهاية الأعمدة الأفقية وبجوار الشرائح الدائرية
    const valueLabelsPlugin = {
        id: 'valueLabelsPlugin',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            const meta = chart.getDatasetMeta(0);
            if (!meta || !meta.data) return;

            ctx.save();
            ctx.font = "bold 12px 'Cairo', 'Segoe UI', sans-serif";

            if (chart.config.type === 'bar' && chart.config.options.indexAxis === 'y') {
                ctx.fillStyle = '#0f172a';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                meta.data.forEach((bar, index) => {
                    const val = chart.data.datasets[0].data[index];
                    if (val !== undefined && val !== null) {
                        ctx.fillText(val, bar.x + 6, bar.y);
                    }
                });
            } else if (chart.config.type === 'doughnut') {
                const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                meta.data.forEach((arc, index) => {
                    const val = chart.data.datasets[0].data[index];
                    if (val > 0 && total > 0 && (val / total) >= 0.06) {
                        const pos = arc.tooltipPosition();
                        ctx.fillStyle = '#ffffff';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(val, pos.x, pos.y);
                    }
                });
            }
            ctx.restore();
        }
    };

    Chart.register(valueLabelsPlugin);

    // دالة موحدة لإنشاء مخطط دائري (Doughnut)
    function renderDoughnut(canvasId, series, colors) {
        const el = document.getElementById(canvasId);
        if (!el || !series) return;
        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: series.labels,
                datasets: [{
                    data: series.counts,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 12, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.raw}`
                        }
                    }
                }
            }
        });
    }

    // دالة موحدة لإنشاء مخطط أعمدة أفقية (Horizontal Bar) لقراءة عربية مثالية
    function renderHorizontalBar(canvasId, series, barColor) {
        const el = document.getElementById(canvasId);
        if (!el || !series) return;
        const maxVal = Math.max(...(series.counts.length ? series.counts : [1]));
        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels: series.labels,
                datasets: [{
                    data: series.counts,
                    backgroundColor: barColor,
                    borderRadius: 6,
                    barPercentage: 0.72,
                    categoryPercentage: 0.85
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { right: 32, left: 6, top: 4, bottom: 4 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: (ctx) => ` العدد: ${ctx.raw}`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        suggestedMax: maxVal + Math.max(2, Math.ceil(maxVal * 0.15)),
                        grid: { color: '#f1f5f9' },
                        ticks: { precision: 0, font: { size: 11 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            autoSkip: false,
                            font: { size: 12, weight: 'bold' },
                            color: '#1e293b'
                        }
                    }
                }
            }
        });
    }

    // رسم كافة المخططات التسعة
    renderDoughnut('chartGender', analyticsChartsData.gender, ['#0284c7', '#e11d48']);
    renderDoughnut('chartBasis', analyticsChartsData.basis, ['#14532d', '#d97706', '#7c3aed', '#0284c7', '#64748b', '#0d9488']);
    renderHorizontalBar('chartRanks', analyticsChartsData.ranks, '#d97706');
    renderHorizontalBar('chartUnits', analyticsChartsData.units, '#14532d');
    renderHorizontalBar('chartJobTitles', analyticsChartsData.job_titles, '#4f46e5');
    renderHorizontalBar('chartQualCurrent', analyticsChartsData.qual_current, '#059669');
    renderHorizontalBar('chartQualAtApp', analyticsChartsData.qual_at_app, '#ea580c');
    renderHorizontalBar('chartPayrollStatus', analyticsChartsData.payroll_status, '#0d9488');
    renderHorizontalBar('chartEmpStatus', analyticsChartsData.emp_status, '#be123c');
});

function downloadChartImage(canvasId, fileName) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    // رسم خلفية بيضاء قبل الحفظ لضمان وضوح الصورة
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = canvas.width;
    tempCanvas.height = canvas.height;
    const ctx = tempCanvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
    ctx.drawImage(canvas, 0, 0);

    const link = document.createElement('a');
    link.download = fileName + '_' + new Date().toISOString().slice(0, 10) + '.png';
    link.href = tempCanvas.toDataURL('image/png', 1.0);
    link.click();
}
</script>
