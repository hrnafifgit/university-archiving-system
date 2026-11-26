@extends('layouts.app')

@section('title', 'جامعة إب — لوحة المؤشرات المركزية')

@section('content')
<div class="flex-1 flex flex-col min-h-0 space-y-5">

    <!-- شريط ترويسة لوحة المؤشرات وبطاقات الـ KPI السريعة -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-900 font-bold shadow-xs">
                    <i class="fa-solid fa-chart-pie text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">المؤشرات والإحصائيات المركزية</h1>
                    <p class="text-xs text-slate-500 mt-0.5">تحليل بياني شامل لتوزيع الكادر الأكاديمي، الرتب العلمية، والمواقف الوظيفية</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('dashboard', ['refresh' => 1]) }}" class="px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-brand-900 border border-slate-200 font-bold rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer shadow-2xs" title="تحديث البيانات">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    <span>تحديث</span>
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-brand-900 hover:bg-brand-800 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>طباعة التقرير</span>
                </button>
            </div>
        </div>

        <!-- كروت المؤشرات السريعة (KPIs) تبرز الأرقام الحيوية بلمحة واحدة -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 pt-4">
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-bold block mb-1">إجمالي الكادر المقيد</span>
                    <span class="text-2xl font-black text-slate-900 font-mono">{{ $totalEmployees ?? $excelMatrices['gender']['total'] ?? 51 }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-brand-100/60 text-brand-900 flex items-center justify-center text-base">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-bold block mb-1">على رأس العمل</span>
                    <span class="text-2xl font-black text-emerald-700 font-mono">{{ $activeDutyCount ?? 7 }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100/60 text-emerald-800 flex items-center justify-center text-base">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>

            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-bold block mb-1">إيفاد وتفرغ وإجازات</span>
                    <span class="text-2xl font-black text-blue-700 font-mono">{{ $inactiveDutyCount ?? 44 }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100/60 text-blue-800 flex items-center justify-center text-base">
                    <i class="fa-solid fa-plane-departure"></i>
                </div>
            </div>

            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-bold block mb-1">الكادر الأكاديمي</span>
                    <span class="text-2xl font-black text-indigo-700 font-mono">{{ $academicCount ?? 51 }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100/60 text-indigo-800 flex items-center justify-center text-base">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- شبكة المخططات البيانية النقية (بدون جداول — مخططات عريضة واضحة ومقروءة 100%) -->
    
    <!-- القسم الأول: المخططان الرئيسيان الأكبر (الحالة بشؤون أعضاء هيئة التدريس + الرتب العلمية) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- 1. الحالة بشؤون أعضاء هيئة التدريس (أعمدة أفقية تقرأ كافة الفئات الـ 14 بوضوح تام بدون أي ميلان) -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-900 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-university"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">الحالة بشؤون أعضاء هيئة التدريس</h2>
                        <span class="text-[11px] text-slate-400">توزيع الكادر حسب الوضع الأكاديمي المعتمد</span>
                    </div>
                </div>
                <span class="px-3 py-1 bg-brand-50 text-brand-900 border border-brand-100 rounded-lg text-xs font-bold font-mono">
                    {{ count($excelMatrices['academicStatus']['labels'] ?? []) }} حالة
                </span>
            </div>
            <div class="relative w-full" style="height: {{ max(300, count($excelMatrices['academicStatus']['labels'] ?? []) * 32) }}px">
                <canvas id="chartAcademicStatus"></canvas>
            </div>
        </div>

        <!-- 2. الرتب الأكاديمية والدرجات العلمية (أعمدة أفقية واضحة ومقروءة) -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">الرتب الأكاديمية والدرجات العلمية</h2>
                        <span class="text-[11px] text-slate-400">تصنيف الكادر من أستاذ إلى معيد</span>
                    </div>
                </div>
                <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold font-mono">
                    إجمالي: {{ $excelMatrices['ranks']['total'] ?? 51 }}
                </span>
            </div>
            <div class="relative w-full" style="height: {{ max(300, count($excelMatrices['ranks']['labels'] ?? []) * 36) }}px">
                <canvas id="chartRanks"></canvas>
            </div>
        </div>

    </div>

    <!-- القسم الثاني: المخططات الثلاثية التكميلية (النوع الاجتماعي + المؤهلات العلمية + الحالة بكشف الراتب والموقف) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pb-6">

        <!-- 3. توزيع النوع الاجتماعي (الجنس) - Donut Chart نقي مع إحصائيات مدمجة -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-venus-mars"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">توزيع النوع الاجتماعي</h2>
                        <span class="text-[11px] text-slate-400">نسبة الذكور والإناث في الكادر</span>
                    </div>
                </div>
            </div>
            <div class="h-56 relative w-full flex items-center justify-center my-1">
                <canvas id="chartGender"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2.5 pt-3 border-t border-slate-100 text-center">
                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                    <span class="text-slate-500 text-[11px] font-bold block mb-0.5">ذكور</span>
                    <span class="text-brand-900 font-black font-mono text-base">{{ $excelMatrices['gender']['data'][0] ?? 45 }}</span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                    <span class="text-slate-500 text-[11px] font-bold block mb-0.5">إناث</span>
                    <span class="text-emerald-600 font-black font-mono text-base">{{ $excelMatrices['gender']['data'][1] ?? 6 }}</span>
                </div>
            </div>
        </div>

        <!-- 4. توزيع المؤهلات العلمية - أعمدة أفقية واضحة -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">المؤهلات العلمية المعتمدة</h2>
                        <span class="text-[11px] text-slate-400">دكتوراه، ماجستير، بكالوريوس</span>
                    </div>
                </div>
            </div>
            <div class="relative w-full" style="height: {{ max(280, count($excelMatrices['qualifications']['labels'] ?? []) * 30) }}px">
                <canvas id="chartQualifications"></canvas>
            </div>
        </div>

        <!-- 5. الحالة بكشف الراتب والموقف الفعلي - أعمدة أفقية واضحة -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">الحالة بكشف الراتب المالي</h2>
                        <span class="text-[11px] text-slate-400">مطابقة الكادر مع السجلات المالية</span>
                    </div>
                </div>
            </div>
            <div class="relative w-full" style="height: {{ max(280, count($excelMatrices['payroll']['labels'] ?? []) * 32) }}px">
                <canvas id="chartPayroll"></canvas>
            </div>
        </div>

    </div>

</div>

<!-- تحميل مكتبة Chart.js عبر CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = 'Cairo, sans-serif';
        Chart.defaults.color = '#334155';

        const chartColors = [
            '#14532d', '#15803d', '#16a34a', '#22c55e', '#4ade80',
            '#0f766e', '#0d9488', '#14b8a6', '#0284c7', '#2563eb',
            '#4f46e5', '#7c3aed', '#9333ea', '#c026d3'
        ];

        // 1. رسم الحالة بشؤون أعضاء هيئة التدريس (Horizontal Bar Chart أعمدة أفقية مقروءة 100%)
        const ctxAcad = document.getElementById('chartAcademicStatus');
        if (ctxAcad) {
            new Chart(ctxAcad, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($excelMatrices['academicStatus']['labels']) !!},
                    datasets: [{
                        label: 'عدد الكادر',
                        data: {!! json_encode($excelMatrices['academicStatus']['data']) !!},
                        backgroundColor: '#14532d',
                        hoverBackgroundColor: '#15803d',
                        borderRadius: 6,
                        barThickness: 15,
                        maxBarThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y', // أعمدة أفقية لقراءة المسميات العربية بالكامل
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Cairo', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Cairo', size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' العدد: ' + context.parsed.x + ' كادر';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f8fafc' },
                            ticks: {
                                stepSize: 1,
                                font: { family: 'Cairo', size: 10 }
                            }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Cairo', size: 11, weight: '600' },
                                color: '#1e293b',
                                autoSkip: false
                            }
                        }
                    }
                }
            });
        }

        // 2. رسم الرتب الأكاديمية (Horizontal Bar Chart أعمدة أفقية مريحة)
        const ctxRanks = document.getElementById('chartRanks');
        if (ctxRanks) {
            new Chart(ctxRanks, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($excelMatrices['ranks']['labels']) !!},
                    datasets: [{
                        label: 'عدد الكادر',
                        data: {!! json_encode($excelMatrices['ranks']['data']) !!},
                        backgroundColor: ['#14532d', '#166534', '#15803d', '#16a34a', '#22c55e', '#86efac'],
                        borderRadius: 6,
                        barThickness: 22,
                        maxBarThickness: 26
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Cairo', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Cairo', size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' العدد: ' + context.parsed.x + ' كادر';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f8fafc' },
                            ticks: { stepSize: 2, font: { family: 'Cairo', size: 10 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Cairo', size: 11, weight: '700' },
                                color: '#1e293b',
                                autoSkip: false
                            }
                        }
                    }
                }
            });
        }

        // 3. رسم النوع الاجتماعي (Doughnut Chart أنيق بمؤشرات واضحة)
        const ctxGender = document.getElementById('chartGender');
        if (ctxGender) {
            new Chart(ctxGender, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($excelMatrices['gender']['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($excelMatrices['gender']['data']) !!},
                        backgroundColor: ['#14532d', '#22c55e'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: 'Cairo', size: 11, weight: 'bold' },
                                boxWidth: 12,
                                padding: 12
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Cairo', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Cairo', size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = context.raw || 0;
                                    const pct = Math.round((val / total) * 100);
                                    return ` ${context.label}: ${val} كادر (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 4. رسم المؤهلات العلمية (Horizontal Bar Chart أعمدة أفقية)
        const ctxQual = document.getElementById('chartQualifications');
        if (ctxQual) {
            new Chart(ctxQual, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($excelMatrices['qualifications']['labels']) !!},
                    datasets: [{
                        label: 'عدد الكادر',
                        data: {!! json_encode($excelMatrices['qualifications']['data']) !!},
                        backgroundColor: '#1d4ed8',
                        hoverBackgroundColor: '#2563eb',
                        borderRadius: 6,
                        barThickness: 16,
                        maxBarThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Cairo', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Cairo', size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' العدد: ' + context.parsed.x + ' كادر';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f8fafc' },
                            ticks: { stepSize: 5, font: { family: 'Cairo', size: 10 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Cairo', size: 11, weight: '600' },
                                color: '#1e293b',
                                autoSkip: false
                            }
                        }
                    }
                }
            });
        }

        // 5. رسم الحالة بكشف الراتب (Horizontal Bar Chart)
        const ctxPay = document.getElementById('chartPayroll');
        if (ctxPay) {
            new Chart(ctxPay, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($excelMatrices['payroll']['labels']) !!},
                    datasets: [{
                        label: 'عدد الكادر',
                        data: {!! json_encode($excelMatrices['payroll']['data']) !!},
                        backgroundColor: '#7c3aed',
                        hoverBackgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        barThickness: 16,
                        maxBarThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Cairo', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Cairo', size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' العدد: ' + context.parsed.x + ' كادر';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f8fafc' },
                            ticks: { stepSize: 2, font: { family: 'Cairo', size: 10 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Cairo', size: 11, weight: '600' },
                                color: '#1e293b',
                                autoSkip: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
