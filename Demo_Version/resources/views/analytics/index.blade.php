@extends('layouts.app')

@section('title', 'مركز المؤشرات والإحصائيات | جامعة إب')
@section('page_title', 'المؤشرات والإحصائيات')

@section('content')
<div class="space-y-6">

    <!-- شريط التبويبات العلوي الذكي (المكافئ لشاشات eh1 - eh8 في نظام الإكسل) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-2 shadow-sm overflow-x-auto">
        <div class="flex items-center space-x-1 space-x-reverse min-w-max text-xs font-semibold">
            
            <!-- 1. القسم والكلية (eh1) -->
            <a href="{{ route('analytics.index', ['tab' => 'departments']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'departments' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="building-2" class="w-4 h-4 ml-1.5 {{ $tab === 'departments' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>القسم والكلية</span>
            </a>

            <!-- 2. الحالة بكشف الراتب (eh6) -->
            <a href="{{ route('analytics.index', ['tab' => 'payroll_status']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'payroll_status' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="receipt" class="w-4 h-4 ml-1.5 {{ $tab === 'payroll_status' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>الحالة بكشف الراتب</span>
            </a>

            <!-- 3. الحالة ش أكاديمية (eh4) -->
            <a href="{{ route('analytics.index', ['tab' => 'academic_status']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'academic_status' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="graduation-cap" class="w-4 h-4 ml-1.5 {{ $tab === 'academic_status' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>الحالة ش أكاديمية</span>
            </a>

            <!-- 4. الدرجة بكشف الراتب (eh6) -->
            <a href="{{ route('analytics.index', ['tab' => 'payroll_grade']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'payroll_grade' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="layers" class="w-4 h-4 ml-1.5 {{ $tab === 'payroll_grade' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>الدرجة بكشف الراتب</span>
            </a>

            <!-- 5. الدرجة ش أكاديمية / الرتب العلمية (eh2) -->
            <a href="{{ route('analytics.index', ['tab' => 'academic_rank']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'academic_rank' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="award" class="w-4 h-4 ml-1.5 {{ $tab === 'academic_rank' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>الرتبة الأكاديمية</span>
            </a>

            <!-- 6. المؤهل العلمي (eh5) -->
            <a href="{{ route('analytics.index', ['tab' => 'qualifications']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'qualifications' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="book-open" class="w-4 h-4 ml-1.5 {{ $tab === 'qualifications' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>المؤهل العلمي</span>
            </a>

            <!-- 7. الموقف الوظيفي (eh4) -->
            <a href="{{ route('analytics.index', ['tab' => 'job_status']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'job_status' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="briefcase" class="w-4 h-4 ml-1.5 {{ $tab === 'job_status' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>الموقف الوظيفي</span>
            </a>

            <!-- 8. النوع الاجتماعي (eh3) -->
            <a href="{{ route('analytics.index', ['tab' => 'gender']) }}"
               class="flex items-center px-3.5 py-2 rounded-xl transition {{ $tab === 'gender' ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                <i data-lucide="user-check" class="w-4 h-4 ml-1.5 {{ $tab === 'gender' ? 'text-white' : 'text-slate-400' }}"></i>
                <span>النوع الاجتماعي (الجنس)</span>
            </a>

        </div>
    </div>

    <!-- ترويسة التبويب النشط -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200">
        <div>
            <h2 class="text-base font-bold text-slate-900 flex items-center">
                <i data-lucide="pie-chart" class="w-5 h-5 ml-2 text-brand-900"></i>
                <span>{{ $tabTitle }}</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">{{ $tabDescription }}</p>
        </div>
        <div class="flex items-center space-x-2 space-x-reverse text-xs">
            <span class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-700">
                إجمالي الكادر: <strong class="text-brand-900 font-bold">{{ $totalStaff }}</strong>
            </span>
            <button onclick="window.print()" class="px-3 py-1.5 bg-white border border-slate-200 hover:border-brand-900 text-slate-600 rounded-xl transition flex items-center">
                <i data-lucide="printer" class="w-3.5 h-3.5 ml-1.5 text-brand-900"></i>
                <span>طباعة</span>
            </button>
        </div>
    </div>

    <!-- شبكة العرض (جدول الإحصائيات مع ميزة Drill-Down + المخطط البياني) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- جدول الإحصائيات مع روابط المعاينة الفورية (Drill-Down) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">بيانات التوزيع والمطابقة</span>
                    <span class="text-[11px] text-slate-400">انقر على أي سطر لمعاينة الكادر المعني</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-semibold">
                                <th class="py-3 px-4">الفئة / البيان</th>
                                <th class="py-3 px-4 text-center">العدد</th>
                                <th class="py-3 px-4 text-center">النسبة</th>
                                <th class="py-3 px-4 text-center">معاينة وتصفية الكادر</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($data as $row)
                            <tr class="hover:bg-slate-50 transition group">
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800 block">{{ $row['label'] }}</span>
                                    @if(!empty($row['sublabel']))
                                    <span class="text-[11px] text-slate-400">{{ $row['sublabel'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">
                                    {{ $row['count'] }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-2 space-x-reverse">
                                        <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-brand-900 h-1.5 rounded-full" style="width: {{ min(100, $row['percentage']) }}%"></div>
                                        </div>
                                        <span class="font-mono text-[11px] text-slate-500 w-9 text-left">{{ $row['percentage'] }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ $row['drill_url'] }}" 
                                       title="تصفية واستعراض الكادر" 
                                       class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 group-hover:bg-brand-900 group-hover:border-brand-900 group-hover:text-white text-slate-600 transition text-[11px] font-semibold">
                                        <span>عرض الكادر</span>
                                        <i data-lucide="arrow-left" class="w-3 h-3 mr-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                    لا توجد سجلات مسجلة في هذا البعد الإحصائي حالياً.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ملخص الفوتر السفلي للجدول -->
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>إجمالي الفئات المعروضة: <strong>{{ count($data) }}</strong></span>
                <a href="{{ route('staff.index') }}" class="text-brand-900 hover:underline font-semibold flex items-center">
                    <span>الانتقال لدليل الكادر الكامل</span>
                    <i data-lucide="external-link" class="w-3 h-3 mr-1"></i>
                </a>
            </div>
        </div>

        <!-- المخطط البياني والتوصيف التنظيمي -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- كرت المخطط البياني التفاعلي -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">التمثيل البياني الموحد</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-900"></span>
                </div>
                
                <div class="h-64 relative">
                    <canvas id="analyticsChart"></canvas>
                </div>
            </div>

            <!-- بطاقة التوجيه المؤسسي والأكاديمي -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                <div class="flex items-center space-x-2 space-x-reverse text-brand-900 font-bold text-xs">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>ميزة الاستعلام المعمق (Drill-Down)</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    على عكس أوراق الإكسل السابقة (<span class="font-mono text-slate-800 font-bold">eh1-eh8</span>) التي كانت تقدم أرقاماً صامتة، يتيح لك هذا المركز النقر على أي فئة ليتم نقلك فوراً إلى الدليل مع تطبيق التصفية التلقائية لعرض الملفات والوثائق المرتبطة مباشرة.
                </p>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>جامعة إب - شؤون الأرشفة</span>
                    <span class="font-mono">SQL Live Query</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('analyticsChart');
        if (!ctx) return;

        const labels = @json($chartLabels);
        const dataValues = @json($chartValues);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'عدد الكادر',
                    data: dataValues,
                    backgroundColor: '#14532d',
                    borderRadius: 6,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                return ' العدد: ' + context.parsed.y + ' عضو';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { family: 'Cairo', size: 10 }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: {
                            font: { family: 'Cairo', size: 10 }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
