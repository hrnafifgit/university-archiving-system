@extends('layouts.app')

@section('title', 'لوحة التحكم | نظام الأرشفة الجامعي')
@section('page_title', 'لوحة التحكم')

@section('content')
<div class="space-y-6">

    <!-- أزرار الإجراءات السريعة في الأعلى -->
    <div class="flex items-center justify-between pb-2">
        <div class="flex items-center space-x-2 space-x-reverse text-xs text-slate-500">
            <span class="w-2 h-2 rounded-full bg-brand-900"></span>
            <span>نظام أرشفة وتوثيق كادر جامعة إب الموحد</span>
        </div>
        <div class="flex items-center space-x-2 space-x-reverse">
            <a href="{{ route('staff.create') }}" class="flex items-center bg-brand-900 hover:bg-brand-800 text-white font-bold px-3.5 py-2 rounded-xl text-xs transition shadow-sm">
                <i data-lucide="user-plus" class="w-3.5 h-3.5 ml-1.5"></i>
                <span>تسجيل كادر جديد</span>
            </a>
            <a href="{{ route('staff.index') }}" class="flex items-center bg-white hover:bg-slate-50 text-slate-700 font-bold px-3.5 py-2 rounded-xl text-xs border border-slate-200 transition">
                <i data-lucide="search" class="w-3.5 h-3.5 ml-1.5 text-slate-400"></i>
                <span>دليل واستعلام الكادر</span>
            </a>
        </div>
    </div>

    <!-- بطاقات المؤشرات الـ 4 الرئيسية - نمط أبيض بأخضر داكن وبدون حشو -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- 1. إجمالي الكادر -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 block mb-1">إجمالي الكادر</span>
                <span class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($totalStaff) }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-900 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- 2. الوثائق المؤرشفة -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 block mb-1">الوثائق المؤرشفة</span>
                <span class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($totalArchives) }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-900 flex items-center justify-center">
                <i data-lucide="file-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- 3. الكادر الأكاديمي -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 block mb-1">الكادر الأكاديمي</span>
                <span class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($academicStaffCount) }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-900 flex items-center justify-center">
                <i data-lucide="graduation-cap" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- 4. الكادر الإداري -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 block mb-1">الكادر الإداري</span>
                <span class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($adminStaffCount) }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-900 flex items-center justify-center">
                <i data-lucide="briefcase" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- المخطط البياني الموحد: توزيع الكادر حسب الكليات -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-slate-800 text-sm flex items-center">
                <i data-lucide="bar-chart-2" class="w-4 h-4 ml-2 text-brand-900"></i>
                <span>توزيع الكادر حسب الكليات والمراكز</span>
            </h3>
            <span class="text-xs text-brand-900 font-semibold bg-brand-50 px-2.5 py-1 rounded-lg">جامعة إب</span>
        </div>
        <div class="h-64">
            <canvas id="collegesChart"></canvas>
        </div>
    </div>

    <!-- جدول أحدث الوثائق والقرارات -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm flex items-center">
                <i data-lucide="clock" class="w-4 h-4 ml-2 text-brand-900"></i>
                <span>أحدث الوثائق والقرارات المضافة</span>
            </h3>
            <a href="{{ route('staff.index') }}" class="text-xs font-bold text-brand-900 hover:underline flex items-center">
                <span>عرض الكل</span>
                <i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-5">الكادر</th>
                        <th class="py-3 px-5">الكلية / الجهة</th>
                        <th class="py-3 px-5">نوع الوثيقة</th>
                        <th class="py-3 px-5">رقم القرار / التصنيف</th>
                        <th class="py-3 px-5">التاريخ</th>
                        <th class="py-3 px-5 text-center">معاينة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentArchives as $arch)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-5 font-bold text-slate-800 flex items-center">
                            <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-900 flex items-center justify-center font-bold text-xs ml-2.5">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <p>{{ $arch->staff->full_name }}</p>
                                <span class="text-[10px] text-slate-400 font-mono">رقم: {{ $arch->staff->job_number }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-5 text-slate-600">{{ $arch->staff->department->entity->name }}</td>
                        <td class="py-3 px-5">
                            <span class="text-brand-900 font-semibold">{{ $arch->document_title }}</span>
                        </td>
                        <td class="py-3 px-5 text-slate-600 font-mono">{{ $arch->decision ? $arch->decision->decision_number : $arch->tree_category }}</td>
                        <td class="py-3 px-5 text-slate-400 font-mono">{{ $arch->created_at->format('Y-m-d') }}</td>
                        <td class="py-3 px-5 text-center">
                            <a href="{{ route('staff.show', $arch->staff_id) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:text-brand-900 hover:bg-brand-50 transition" title="معاينة الملف">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">لا توجد وثائق مؤرشفة بعد.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const ctxColleges = document.getElementById('collegesChart').getContext('2d');
    new Chart(ctxColleges, {
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
</script>
@endpush
