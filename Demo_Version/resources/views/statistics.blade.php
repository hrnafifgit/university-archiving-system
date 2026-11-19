@extends('layouts.app')

@section('title', 'شاشة الإحصائيات والمؤشرات الجامعية الشاملة | جامعة إب')
@section('page_title', 'شاشة الإحصائيات والمؤشرات الجامعية الشاملة')

@section('content')
<div class="space-y-6">

    <!-- شريط الإجراءات والعودة للوحة التحكم -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3 space-x-reverse">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-900 flex items-center justify-center font-bold">
                <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900">التحليل الإحصائي الرقمي الموحد لكادر جامعة إب</h2>
                <p class="text-[11px] text-slate-400">بيانات مستخرجة لحظياً ومباشرة من قاعدة بيانات النظام المعتمدة</p>
            </div>
        </div>

        <div class="flex items-center space-x-2 space-x-reverse">
            <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center transition shadow-sm">
                <i data-lucide="printer" class="w-4 h-4 ml-1.5"></i>
                <span>طباعة الإحصائيات</span>
            </button>
            <a href="{{ route('dashboard') }}" class="bg-brand-900 hover:bg-brand-800 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center transition shadow-sm">
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5"></i>
                <span>العودة للوحة التحكم الرئيسية</span>
            </a>
        </div>
    </div>

    <!-- أزرار التصفية والتنقل التفاعلية داخل شاشة الإحصائيات (Interactive Filter Buttons) -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
        <div class="flex items-center space-x-2 space-x-reverse min-w-max text-xs font-bold">
            <button onclick="switchStatTab('overview')" id="btn-tab-overview" class="stat-tab-btn py-2 px-4 rounded-xl bg-brand-900 text-white transition flex items-center">
                <i data-lucide="layout-grid" class="w-4 h-4 ml-1.5"></i>
                <span>المؤشرات العامة والمخططات</span>
            </button>
            <button onclick="switchStatTab('colleges')" id="btn-tab-colleges" class="stat-tab-btn py-2 px-4 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition flex items-center">
                <i data-lucide="building-2" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                <span>إحصائية الكليات والمراكز (18 جهة)</span>
            </button>
            <button onclick="switchStatTab('ranks')" id="btn-tab-ranks" class="stat-tab-btn py-2 px-4 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition flex items-center">
                <i data-lucide="award" class="w-4 h-4 ml-1.5 text-purple-700"></i>
                <span>الرتب والدرجات الأكاديمية</span>
            </button>
            <button onclick="switchStatTab('qualifications')" id="btn-tab-qualifications" class="stat-tab-btn py-2 px-4 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition flex items-center">
                <i data-lucide="graduation-cap" class="w-4 h-4 ml-1.5 text-blue-700"></i>
                <span>المؤهلات العلمية (دكتوراه / ماجستير)</span>
            </button>
            <button onclick="switchStatTab('demographics')" id="btn-tab-demographics" class="stat-tab-btn py-2 px-4 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition flex items-center">
                <i data-lucide="users" class="w-4 h-4 ml-1.5 text-emerald-700"></i>
                <span>النوع الاجتماعي والموقف الوظيفي</span>
            </button>
        </div>
    </div>

    <!-- بطاقات المؤشرات الرقمية العليا (KPIs) -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- إجمالي الكادر -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-slate-500 block mb-1">إجمالي الكادر المقيد</span>
            <span class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">{{ number_format($totalStaff) }}</span>
            <span class="text-[10px] text-slate-400 block mt-1">كافة السجلات المسجلة</span>
        </div>

        <!-- كادر أكاديمي -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-emerald-700 block mb-1">الكادر الأكاديمي</span>
            <span class="text-2xl sm:text-3xl font-black text-emerald-700 font-mono">{{ number_format($academicStaffCount) }}</span>
            <span class="text-[10px] text-emerald-600/80 block mt-1">هيئة التدريس ومساعدوهم</span>
        </div>

        <!-- كادر إداري -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-blue-700 block mb-1">الكادر الإداري والفني</span>
            <span class="text-2xl sm:text-3xl font-black text-blue-700 font-mono">{{ number_format($adminStaffCount) }}</span>
            <span class="text-[10px] text-blue-600/80 block mt-1">موظفو الإدارات والكليات</span>
        </div>

        <!-- متعاقد بالساعات -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-amber-700 block mb-1">متعاقد بالساعات</span>
            <span class="text-2xl sm:text-3xl font-black text-amber-700 font-mono">{{ number_format($contractStaffCount) }}</span>
            <span class="text-[10px] text-amber-600/80 block mt-1">عقود تدريس فصلية</span>
        </div>

        <!-- إجمالي الوثائق -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden col-span-2 lg:col-span-1">
            <span class="text-xs font-bold text-purple-700 block mb-1">الوثائق المؤرشفة</span>
            <span class="text-2xl sm:text-3xl font-black text-purple-700 font-mono">{{ number_format($totalArchives) }}</span>
            <span class="text-[10px] text-purple-600/80 block mt-1">قرارات وملفات PDF</span>
        </div>
    </div>

    <!-- 1. لوح المؤشرات العامة والمخططات التفاعلية -->
    <div id="stat-panel-overview" class="stat-panel space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- مخطط توزيع الكادر حسب الكليات -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm lg:col-span-2 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm flex items-center">
                            <i data-lucide="bar-chart-2" class="w-4 h-4 ml-2 text-brand-900"></i>
                            <span>توزيع أعداد الكادر حسب الكليات والمراكز الرسمية</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">مقارنة أعداد الكادر المقيد في كل جهة أكاديمية</p>
                    </div>
                    <span class="text-xs font-bold text-brand-900 bg-brand-50 px-2.5 py-1 rounded-lg">جامعة إب</span>
                </div>
                <div class="h-72">
                    <canvas id="collegesChart"></canvas>
                </div>
            </div>

            <!-- مخطط النسبة المئوية لتصنيف الكادر -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm flex items-center">
                        <i data-lucide="pie-chart" class="w-4 h-4 ml-2 text-brand-900"></i>
                        <span>النسبة المئوية لتصنيف الكادر</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">أكاديمي / إداري / متعاقد</p>
                </div>
                <div class="h-60 relative flex items-center justify-center my-2">
                    <canvas id="staffTypeChart"></canvas>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center pt-3 border-t border-slate-100 text-xs">
                    <div>
                        <span class="block w-2.5 h-2.5 rounded-full bg-emerald-600 mx-auto mb-1"></span>
                        <span class="text-[11px] text-slate-500 font-bold block">أكاديمي</span>
                        <span class="font-mono font-bold text-slate-800">{{ $totalStaff > 0 ? round(($academicStaffCount/$totalStaff)*100, 1) : 0 }}%</span>
                    </div>
                    <div>
                        <span class="block w-2.5 h-2.5 rounded-full bg-blue-600 mx-auto mb-1"></span>
                        <span class="text-[11px] text-slate-500 font-bold block">إداري</span>
                        <span class="font-mono font-bold text-slate-800">{{ $totalStaff > 0 ? round(($adminStaffCount/$totalStaff)*100, 1) : 0 }}%</span>
                    </div>
                    <div>
                        <span class="block w-2.5 h-2.5 rounded-full bg-amber-500 mx-auto mb-1"></span>
                        <span class="text-[11px] text-slate-500 font-bold block">متعاقد</span>
                        <span class="font-mono font-bold text-slate-800">{{ $totalStaff > 0 ? round(($contractStaffCount/$totalStaff)*100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- 2. لوح إحصائيات الكليات والمراكز التفصيلية -->
    <div id="stat-panel-colleges" class="stat-panel hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">جدول إحصائيات الكليات والمراكز (18 جهة)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">توزيع الأقسام والكادر المسجل رسمياً</p>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                    إجمالي الجهات: {{ $colleges->count() }} جهة
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">اسم الكلية / المركز</th>
                            <th class="py-3 px-4">النوع</th>
                            <th class="py-3 px-4 text-center">الأقسام التابعة</th>
                            <th class="py-3 px-4 text-center">الكادر المقيد</th>
                            <th class="py-3 px-4 text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($colleges as $college)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-bold text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $college->name }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $college->entity_type == 'كلية' ? 'bg-emerald-50 text-emerald-800' : 'bg-blue-50 text-blue-800' }}">
                                    {{ $college->entity_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-600">{{ $college->departments->count() }} قسم</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-brand-900 text-sm">{{ $college->staff_count ?? 0 }}</td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('staff.index') }}?entity_id={{ $college->id }}" class="text-brand-900 hover:underline font-bold text-xs inline-flex items-center">
                                    <span>عرض الكادر</span>
                                    <i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. لوح الرتب والدرجات الأكاديمية -->
    <div id="stat-panel-ranks" class="stat-panel hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-base">توزيع أعضاء هيئة التدريس حسب الرتبة العلمية</h3>
                <p class="text-xs text-slate-400 mt-0.5">تصنيف الرتب المعتمدة في اللائحة الأكاديمية لجامعة إب</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($academicRanks as $rank)
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <span class="w-9 h-9 rounded-xl bg-purple-100 text-purple-800 font-bold flex items-center justify-center text-xs">
                            {{ $loop->iteration }}
                        </span>
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">{{ $rank->name }}</span>
                            <span class="text-[10px] text-slate-400 font-semibold">ترتيب الأقدمية: {{ $rank->seniority_order }}</span>
                        </div>
                    </div>
                    <span class="text-sm font-black text-purple-800 bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 font-mono">
                        {{ $rank->academic_details_count }} عضو
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 4. لوح المؤهلات العلمية -->
    <div id="stat-panel-qualifications" class="stat-panel hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-base">توزيع الكوادر بحسب المؤهل العلمي المحقق</h3>
                <p class="text-xs text-slate-400 mt-0.5">أعلى درجة علمية مسجلة وموثقة بالشهادات الأكاديمية</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                @forelse($qualificationStats as $stat)
                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                    <span class="text-xs font-bold text-slate-500 block mb-1">المؤهل العلمي</span>
                    <span class="text-base font-black text-slate-800 block">{{ $stat->degree_type }}</span>
                    <div class="text-3xl font-black text-blue-700 font-mono mt-2">{{ $stat->count }}</div>
                </div>
                @empty
                <div class="col-span-4 text-center py-8 text-xs text-slate-400">لا توجد مؤهلات مسجلة بعد</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 5. لوح النوع الاجتماعي والموقف الوظيفي -->
    <div id="stat-panel-demographics" class="stat-panel hidden space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- النوع الاجتماعي -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-900 text-sm mb-4 flex items-center">
                    <i data-lucide="users" class="w-4 h-4 ml-2 text-brand-900"></i>
                    <span>توزيع الكادر حسب النوع الاجتماعي (الجنس)</span>
                </h3>

                @php
                    $males = $genderStats->where('gender', 'ذكر')->first()->count ?? 0;
                    $females = $genderStats->where('gender', 'أنثى')->first()->count ?? 0;
                    $totalG = $males + $females;
                @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <span class="text-xs font-bold text-slate-500 block">الذكور</span>
                        <span class="text-2xl font-black text-slate-900 font-mono mt-1 block">{{ $males }}</span>
                        <span class="text-[10px] text-slate-400 mt-1 block">{{ $totalG > 0 ? round(($males/$totalG)*100, 1) : 0 }}%</span>
                    </div>
                    <div class="p-4 bg-pink-50 rounded-xl border border-pink-200 text-center">
                        <span class="text-xs font-bold text-pink-700 block">الإناث</span>
                        <span class="text-2xl font-black text-pink-700 font-mono mt-1 block">{{ $females }}</span>
                        <span class="text-[10px] text-pink-500 mt-1 block">{{ $totalG > 0 ? round(($females/$totalG)*100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- الموقف الوظيفي -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-900 text-sm mb-4 flex items-center">
                    <i data-lucide="briefcase" class="w-4 h-4 ml-2 text-brand-900"></i>
                    <span>توزيع الكادر حسب الموقف الوظيفي</span>
                </h3>

                <div class="space-y-3">
                    @forelse($jobStatusStats as $stat)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">{{ $stat->job_status ?? 'غير محدد' }}</span>
                        <span class="text-xs font-black text-brand-900 font-mono bg-white px-3 py-1 rounded-lg border border-slate-200">
                            {{ $stat->count }} كادر
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-6 text-xs text-slate-400">لا توجد بيانات موقف وظيفي</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
    function switchStatTab(tabId) {
        // إخفاء كل الألواح
        document.querySelectorAll('.stat-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        // إعادة ضبط الأزرار
        document.querySelectorAll('.stat-tab-btn').forEach(btn => {
            btn.classList.remove('bg-brand-900', 'text-white');
            btn.classList.add('bg-slate-100', 'text-slate-700');
        });

        // إظهار اللوح المحدد
        const targetPanel = document.getElementById('stat-panel-' + tabId);
        if (targetPanel) {
            targetPanel.classList.remove('hidden');
        }

        // تفعيل الزر المحدد
        const targetBtn = document.getElementById('btn-tab-' + tabId);
        if (targetBtn) {
            targetBtn.classList.remove('bg-slate-100', 'text-slate-700');
            targetBtn.classList.add('bg-brand-900', 'text-white');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // مخطط الكليات
        const ctxColleges = document.getElementById('collegesChart');
        if (ctxColleges && typeof Chart !== 'undefined') {
            new Chart(ctxColleges.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($colleges->pluck('name')) !!},
                    datasets: [{
                        label: 'عدد الكادر',
                        data: {!! json_encode($colleges->pluck('staff_count')) !!},
                        backgroundColor: '#14532d',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // مخطط تصنيف الكادر
        const ctxType = document.getElementById('staffTypeChart');
        if (ctxType && typeof Chart !== 'undefined') {
            new Chart(ctxType.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['أكاديمي', 'إداري', 'متعاقد'],
                    datasets: [{
                        data: [
                            {{ $academicStaffCount > 0 ? $academicStaffCount : 1 }},
                            {{ $adminStaffCount > 0 ? $adminStaffCount : 1 }},
                            {{ $contractStaffCount > 0 ? $contractStaffCount : 1 }}
                        ],
                        backgroundColor: ['#16a34a', '#2563eb', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endpush
@endsection
