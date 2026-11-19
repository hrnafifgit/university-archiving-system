@extends('layouts.app')

@section('title', 'لوحة التحكم | نظام الأرشفة الجامعي - جامعة إب')
@section('page_title', 'لوحة التحكم والقيادة')
@section('page_subtitle', 'مؤشرات وإحصائيات لحظية للأرشيف وكادر جامعة إب')

@section('content')
<!-- بانر الهوية الرسمية لجامعة إب -->
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-l from-slate-900 via-univ-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl border border-emerald-900/40">
    <!-- خلفية زخرفية -->
    <div class="absolute -left-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -right-10 -top-10 w-64 h-64 bg-univ-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center space-x-5 space-x-reverse text-right">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white p-2.5 flex items-center justify-center border-2 border-emerald-400/50 shadow-2xl shrink-0">
                <img src="{{ asset('images/ibb_logo.png') }}" alt="شعار جامعة إب" class="w-full h-full object-contain filter drop-shadow">
            </div>
            <div>
                <div class="flex items-center space-x-2 space-x-reverse mb-1">
                    <span class="text-xs font-bold text-emerald-400 tracking-wider">الجمهورية اليمنية • وزارة التعليم العالي والبحث العلمي</span>
                    <span class="text-[10px] bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full border border-emerald-500/30">تأسست 1996م</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white leading-tight">
                    جامعة إب — نظام الأرشفة والتوثيق الإلكتروني الموحد
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    منظومة التحول الرقمي الشامل لملفات الكادر الأكاديمي والإداري، وفهرسة وثائق القرارات والمؤهلات والترقيات وفق المعايير العالمية وهيكلية البيانات (3NF).
                </p>
                
                <div class="flex flex-wrap gap-2 mt-3 text-[11px] font-semibold text-slate-300">
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10 flex items-center">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 ml-1.5 text-emerald-400"></i>
                        18 كلية ومركز علمي
                    </span>
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10 flex items-center">
                        <i data-lucide="network" class="w-3.5 h-3.5 ml-1.5 text-emerald-400"></i>
                        69 قسماً وإدارة تابعة
                    </span>
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10 flex items-center">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 ml-1.5 text-emerald-400"></i>
                        تخزين رقمي مؤمن مشفر بـ UUID
                    </span>
                </div>
            </div>
        </div>

        <div class="flex md:flex-col gap-2.5 shrink-0 w-full md:w-auto">
            <a href="{{ route('staff.create') }}" class="flex-1 md:flex-none flex items-center justify-center bg-univ-600 hover:bg-univ-500 text-white font-bold px-5 py-3 rounded-xl text-xs shadow-lg shadow-univ-600/30 transition">
                <i data-lucide="user-plus" class="w-4 h-4 ml-1.5"></i>
                <span>تسجيل كادر جديد</span>
            </a>
            <a href="{{ route('staff.index') }}" class="flex-1 md:flex-none flex items-center justify-center bg-white/10 hover:bg-white/20 text-white font-bold px-5 py-3 rounded-xl text-xs border border-white/15 transition">
                <i data-lucide="search" class="w-4 h-4 ml-1.5"></i>
                <span>استعلام الكادر (bisho)</span>
            </a>
        </div>
    </div>
</div>

<!-- بطاقات المؤشرات الرئيسية -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- إجمالي الكادر -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">إجمالي كادر الجامعة</p>
            <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($totalStaff) }}</h3>
            <p class="text-xs text-univ-600 font-semibold mt-2 flex items-center">
                <i data-lucide="database" class="w-3.5 h-3.5 ml-1"></i>
                بيانات مطبقة لمعايير 3NF
            </p>
        </div>
        <div class="w-14 h-14 bg-emerald-50 text-univ-600 rounded-2xl flex items-center justify-center">
            <i data-lucide="users" class="w-7 h-7"></i>
        </div>
    </div>

    <!-- الوثائق المؤرشفة -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">الوثائق المؤرشفة (PDF)</p>
            <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($totalArchives) }}</h3>
            <p class="text-xs text-blue-600 font-semibold mt-2 flex items-center">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 ml-1"></i>
                مفهرسة ومشفرة بالـ UUID
            </p>
        </div>
        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
            <i data-lucide="file-check-2" class="w-7 h-7"></i>
        </div>
    </div>

    <!-- الكادر الأكاديمي -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">الكادر الأكاديمي</p>
            <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($academicStaffCount) }}</h3>
            <p class="text-xs text-slate-500 font-medium mt-2">
                (هيئة التدريس ومساعديهم)
            </p>
        </div>
        <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
            <i data-lucide="graduation-cap" class="w-7 h-7"></i>
        </div>
    </div>

    <!-- الكادر الإداري -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">الكادر الإداري والتقني</p>
            <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($adminStaffCount) }}</h3>
            <p class="text-xs text-slate-500 font-medium mt-2">
                (درجات الخدمة المدنية)
            </p>
        </div>
        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
            <i data-lucide="briefcase" class="w-7 h-7"></i>
        </div>
    </div>
</div>

<!-- المخططات البيانية وتوزيع الكادر -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h4 class="font-bold text-slate-800 text-base">توزيع الكادر حسب الكليات والمراكز</h4>
                <p class="text-xs text-slate-400">بيانات الكليات الأكثر كثافة بالوثائق المؤرشفة</p>
            </div>
            <span class="text-xs font-bold text-univ-700 bg-univ-50 px-2.5 py-1 rounded-lg">إحصائية لحظية</span>
        </div>
        <div class="h-64">
            <canvas id="collegesChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-bold text-slate-800 text-base">تصنيف الكادر (Subtypes)</h4>
            <i data-lucide="pie-chart" class="w-4 h-4 text-slate-400"></i>
        </div>
        <div class="h-52 flex items-center justify-center">
            <canvas id="staffTypeChart"></canvas>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-3 text-center text-xs">
            <div>
                <span class="block font-bold text-slate-700">{{ $academicStaffCount }}</span>
                <span class="text-slate-400">أكاديمي</span>
            </div>
            <div>
                <span class="block font-bold text-slate-700">{{ $adminStaffCount }}</span>
                <span class="text-slate-400">إداري</span>
            </div>
            <div>
                <span class="block font-bold text-slate-700">{{ $contractStaffCount }}</span>
                <span class="text-slate-400">ساعات/عقد</span>
            </div>
        </div>
    </div>
</div>

<!-- جدول أحدث الوثائق والقرارات -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h4 class="font-bold text-slate-800 text-base">أحدث الوثائق والقرارات المؤرشفة</h4>
            <p class="text-xs text-slate-400">سجل القرارات والشهادات المضافة لملفات الكادر مؤخراً</p>
        </div>
        <a href="{{ route('staff.index') }}" class="text-xs font-bold text-univ-700 hover:text-univ-800 flex items-center">
            <span>فتح دليل الكادر الكامل</span>
            <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs font-bold border-b border-slate-100">
                <tr>
                    <th class="py-4 px-6">عضو الكادر</th>
                    <th class="py-4 px-6">الجهة والكلية</th>
                    <th class="py-4 px-6">نوع الوثيقة</th>
                    <th class="py-4 px-6">القرار أو التصنيف</th>
                    <th class="py-4 px-6">تاريخ الأرشفة</th>
                    <th class="py-4 px-6 text-center">معاينة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentArchives as $arch)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="py-4 px-6 font-bold text-slate-800 flex items-center">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-univ-700 flex items-center justify-center font-bold text-xs ml-3">
                            {{ mb_substr($arch->staff->full_name, 0, 2) }}
                        </div>
                        <div>
                            <p>{{ $arch->staff->full_name }}</p>
                            <span class="text-xs text-slate-400 font-normal">رقم: {{ $arch->staff->job_number }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-slate-600">{{ $arch->staff->department->entity->name }}</td>
                    <td class="py-4 px-6">
                        <span class="bg-blue-50 text-blue-700 text-xs px-2.5 py-1 rounded-md font-bold">{{ $arch->document_title }}</span>
                    </td>
                    <td class="py-4 px-6 text-slate-600">{{ $arch->decision ? $arch->decision->decision_number : $arch->tree_category }}</td>
                    <td class="py-4 px-6 text-slate-500 text-xs">{{ $arch->created_at->format('Y-m-d') }}</td>
                    <td class="py-4 px-6 text-center">
                        <a href="{{ route('staff.show', $arch->staff_id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-univ-50 hover:text-univ-600 transition text-slate-600">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-slate-400">لا توجد وثائق مؤرشفة بعد.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // رسم بياني لتوزيع الكليات
    const ctxColleges = document.getElementById('collegesChart').getContext('2d');
    new Chart(ctxColleges, {
        type: 'bar',
        data: {
            labels: {!! json_encode($colleges->pluck('name')) !!},
            datasets: [{
                label: 'عدد الكادر',
                data: {!! json_encode($colleges->pluck('staff_count')) !!},
                backgroundColor: '#248874',
                borderRadius: 8
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

    // رسم دائري لتصنيف الكادر
    const ctxType = document.getElementById('staffTypeChart').getContext('2d');
    new Chart(ctxType, {
        type: 'doughnut',
        data: {
            labels: ['أكاديمي', 'إداري', 'متعاقد'],
            datasets: [{
                data: [{{ $academicStaffCount }}, {{ $adminStaffCount }}, {{ $contractStaffCount }}],
                backgroundColor: ['#248874', '#031a3d', '#f4bf00'],
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
</script>
@endpush
