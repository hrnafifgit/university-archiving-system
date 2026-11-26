@extends('layouts.app')

@section('title', 'جامعة إب - دليل وسجلات الكادر')

@section('content')
<div class="flex-1 flex flex-col gap-3 min-h-0 pb-0">

    <!-- 1. شريط البحث والعمليات الموحد بالأخضر الداكن والأبيض -->
    <div class="bg-white border border-slate-200 p-4 px-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-xs shrink-0 rounded-2xl select-none">
        
        <!-- نموذج الفلترة والبحث السريع -->
        <form id="filter-form" method="GET" action="{{ route('employees.index') }}" class="flex flex-wrap items-center gap-3 flex-1 min-w-0 text-sm">
            
            <!-- حقل البحث اللحظي المريح -->
            <div class="relative w-80 md:w-96 lg:w-[420px] shrink-0">
                <input type="text" name="search" id="filter_search" value="{{ request('search') }}" 
                       placeholder="بحث بالاسم، الرقم الوظيفي، أو الرقم الوطني..." 
                       class="w-full bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-900 focus:bg-white pr-10 font-medium rounded-xl shadow-2xs transition">
                <i class="fa-solid fa-search absolute right-3.5 top-3.5 text-slate-400 text-sm"></i>
            </div>

            <!-- الكلية / الجهة الأساسية -->
            <div class="w-56 md:w-64 shrink-0">
                <select name="college_id" id="filter_college" class="w-full bg-slate-50 border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:border-brand-900 font-medium rounded-xl shadow-2xs transition">
                    <option value="">-- كافة الكليات والجهات --</option>
                    @foreach($colleges as $col)
                        <option value="{{ $col->organizational_unit_id }}" {{ request('college_id') == $col->organizational_unit_id ? 'selected' : '' }}>
                            {{ $col->unit_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request('unit_id') || request('dept_id'))
                <input type="hidden" name="unit_id" value="{{ request('unit_id') ?? request('dept_id') }}">
            @endif

            @if(request('cadre_type'))
                <input type="hidden" name="cadre_type" value="{{ request('cadre_type') }}">
            @endif

            <!-- أزرار التصفية -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition flex items-center gap-2 cursor-pointer shadow-xs whitespace-nowrap">
                    <i class="fa-solid fa-filter text-xs"></i> بحث
                </button>
                @if(request('search') || request('college_id') || request('faculty_id') || request('unit_id') || request('dept_id'))
                    <a href="{{ route('employees.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2.5 px-3.5 rounded-xl text-sm transition border border-slate-200 text-center shrink-0 shadow-2xs" title="إلغاء التصفية">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                    </a>
                @endif
            </div>

        </form>

        <!-- الإجراءات والعداد -->
        <div class="flex items-center gap-2 shrink-0 whitespace-nowrap">
            <span class="text-xs md:text-sm bg-slate-50 text-slate-700 font-semibold px-3.5 py-2 rounded-xl border border-slate-200 font-mono shrink-0">
                <span id="header-loaded-count" class="font-bold text-brand-900">{{ min(10, $totalMatched) }}</span> / <span id="header-total-count">{{ $totalMatched }}</span> كادر
            </span>

            <!-- زر تصدير Excel -->
            <a href="{{ route('employees.export.excel', request()->all()) }}" 
               class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-3.5 py-2.5 rounded-xl text-xs md:text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer shrink-0" 
               title="تصدير النتائج المفلترة إلى Excel">
                <i class="fa-solid fa-file-excel text-xs"></i>
                <span class="hidden sm:inline">تصدير Excel</span>
            </a>

            @if(\App\Services\PermissionService::hasPerm('employees.create'))
            <!-- زر استيراد الكوادر -->
            <button type="button" onclick="document.getElementById('importEmployeesModal').classList.remove('hidden')" 
                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-2.5 rounded-xl text-xs md:text-sm transition border border-slate-200 flex items-center gap-1.5 shadow-2xs cursor-pointer shrink-0" 
                    title="استيراد دفعة كوادر من ملف Excel / CSV">
                <i class="fa-solid fa-file-import text-xs text-brand-900"></i>
                <span class="hidden sm:inline">استيراد</span>
            </button>

            <a href="{{ route('employees.create') }}{{ (request('college_id') || request('faculty_id') || request('unit_id') || request('dept_id')) ? '?faculty_id=' . (request('college_id') ?? request('faculty_id')) . '&dept_id=' . (request('unit_id') ?? request('dept_id')) : '' }}" class="bg-brand-900 hover:bg-brand-800 text-white font-bold px-4 py-2.5 rounded-xl text-xs md:text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer shrink-0">
                <i class="fa-solid fa-plus text-xs"></i> إضافة كادر
            </a>
            @endif
        </div>

    </div>

    <!-- تبويبات تقسيم وتصفية الكادر: كافة الكوادر / كادر أكاديمي / كادر إداري / متقاعدين بالساعات -->
    @php
        $currentCadre = request('cadre_type');
        $baseParams = request()->except(['cadre_type', 'page']);
    @endphp
    <div class="flex items-center gap-2 overflow-x-auto select-none shrink-0 pb-0.5">
        <a href="{{ route('employees.index', array_merge($baseParams)) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border {{ empty($currentCadre) ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-users text-xs"></i>
            <span>كافة الكوادر</span>
            <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono {{ empty($currentCadre) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $totalAllCount }}</span>
        </a>

        <a href="{{ route('employees.index', array_merge($baseParams, ['cadre_type' => 'academic'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border {{ $currentCadre === 'academic' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-graduation-cap text-xs {{ $currentCadre === 'academic' ? 'text-emerald-300' : 'text-emerald-700' }}"></i>
            <span>كادر أكاديمي</span>
            <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono {{ $currentCadre === 'academic' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-800' }}">{{ $academicCount }}</span>
        </a>

        <a href="{{ route('employees.index', array_merge($baseParams, ['cadre_type' => 'admin'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border {{ $currentCadre === 'admin' ? 'bg-blue-900 text-white border-blue-900 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-briefcase text-xs {{ $currentCadre === 'admin' ? 'text-blue-300' : 'text-blue-700' }}"></i>
            <span>كادر إداري</span>
            <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono {{ $currentCadre === 'admin' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-800' }}">{{ $adminCount }}</span>
        </a>

        <a href="{{ route('employees.index', array_merge($baseParams, ['cadre_type' => 'hourly'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border {{ $currentCadre === 'hourly' ? 'bg-amber-800 text-white border-amber-800 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-clock text-xs {{ $currentCadre === 'hourly' ? 'text-amber-300' : 'text-amber-700' }}"></i>
            <span>متقاعدين بالساعات</span>
            <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono {{ $currentCadre === 'hourly' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-800' }}">{{ $hourlyCount }}</span>
        </a>
    </div>

    @if(isset($activeUnit) && $activeUnit)
    <div class="mb-3 px-4 py-2.5 bg-indigo-50/90 border border-indigo-200 text-indigo-900 rounded-xl flex items-center justify-between text-xs font-semibold shadow-2xs">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-users text-indigo-600 text-sm"></i>
            <span>تصفية مخصصة للكادر التابع لـ: <strong class="text-indigo-950 font-bold">{{ $activeUnit->unit_name_ar }}</strong></span>
            <span class="bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full text-[11px] font-mono font-bold">({{ $totalMatched }} كادر)</span>
        </div>
        <a href="{{ route('employees.index') }}" class="text-[11px] bg-white border border-indigo-300 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition inline-flex items-center gap-1 font-bold">
            <i class="fa-solid fa-xmark"></i> عرض كافة الكوادر
        </a>
    </div>
    @endif

    <!-- جدول السجلات النقي -->
    <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden relative">
        
        <div id="table-scroll-container" class="flex-1 overflow-y-auto min-h-0 relative no-scrollbar">
            <table class="w-full text-right text-sm border-collapse">
                <thead class="bg-slate-50 text-slate-800 font-bold sticky top-0 z-20 select-none text-sm border-b border-slate-200 shadow-2xs">
                    <tr>
                        <th class="py-3.5 px-4 w-14 text-center text-slate-500 font-bold">#</th>
                        <th class="py-3.5 px-4 w-32 text-center font-mono text-slate-700">الرقم الوظيفي</th>
                        <th class="py-3.5 px-4 text-slate-900">الاسم الرباعي</th>
                        <th class="py-3.5 px-4 w-52 text-slate-700">الكلية / الجهة</th>
                        <th class="py-3.5 px-4 w-48 text-slate-700">القسم</th>
                        <th class="py-3.5 px-4 w-36 text-slate-700">الرتبة</th>
                        <th class="py-3.5 px-4 w-32 text-center text-slate-700">الحالة</th>
                        <th class="py-3.5 px-4 w-24 text-center text-slate-700">الوثائق</th>
                        <th class="py-3.5 px-4 w-32 text-center text-slate-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="employees-tbody" class="divide-y divide-slate-100 font-medium text-slate-800 text-sm">
                    @include('employees._rows', ['employees' => $employees, 'startIndex' => 0])
                </tbody>
            </table>

            <!-- مؤشر التحميل -->
            <div id="scroll-spinner-container" class="hidden py-3 bg-slate-50 border-t border-slate-200 text-center text-xs font-bold text-slate-600 flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-notch fa-spin text-brand-900 text-sm"></i>
                <span>جاري تحميل بيانات إضافية...</span>
            </div>
        </div>

        <!-- مؤشر التمرير السفلي -->
        <button type="button" id="scroll-down-indicator" onclick="scrollTableDown()" 
                class="hidden absolute bottom-3 left-1/2 -translate-x-1/2 z-30 transition-all duration-300 bg-white hover:bg-slate-50 text-slate-600 hover:text-brand-900 border border-slate-200 rounded-full w-8 h-8 shadow-sm flex items-center justify-center cursor-pointer select-none"
                title="توجد بيانات إضافية للتمرير">
            <i class="fa-solid fa-chevron-down text-brand-900 text-xs animate-subtle-bounce"></i>
        </button>

    </div>

</div>

<!-- نظام التمرير اللانهائي الذكي -->
<script>
    let nextPage = 2;
    let hasMorePages = {{ $employees->hasMorePages() ? 'true' : 'false' }};
    let isLoading = false;
    const totalRecords = {{ $totalMatched }};
    const scrollContainer = document.getElementById('table-scroll-container');
    const tbody = document.getElementById('employees-tbody');
    const spinner = document.getElementById('scroll-spinner-container');
    const headerLoadedCount = document.getElementById('header-loaded-count');
    const scrollIndicator = document.getElementById('scroll-down-indicator');

    function updateScrollIndicator() {
        if (!scrollIndicator || !scrollContainer) return;
        const canScroll = scrollContainer.scrollHeight > scrollContainer.clientHeight + 10;
        const isNearBottom = (scrollContainer.scrollTop + scrollContainer.clientHeight) >= (scrollContainer.scrollHeight - 25);
        if (canScroll && (!isNearBottom || hasMorePages)) {
            scrollIndicator.classList.remove('hidden');
        } else {
            scrollIndicator.classList.add('hidden');
        }
    }

    function scrollTableDown() {
        if (!scrollContainer) return;
        scrollContainer.scrollBy({ top: 220, behavior: 'smooth' });
    }

    function getFilterParams() {
        const form = document.getElementById('filter-form') || document.querySelector('form');
        const urlParams = form ? new URLSearchParams(new FormData(form)) : new URLSearchParams(window.location.search);
        urlParams.set('page', nextPage);
        urlParams.set('ajax', '1');
        return urlParams.toString();
    }

    function checkAndFillViewport() {
        if (!scrollContainer || !hasMorePages || isLoading) return;
        if (scrollContainer.scrollHeight <= scrollContainer.clientHeight + 30) {
            loadMoreEmployees();
        }
    }

    async function loadMoreEmployees() {
        if (!hasMorePages || isLoading) return;
        isLoading = true;
        if (spinner) spinner.classList.remove('hidden');

        try {
            const queryUrl = `{{ route('employees.index') }}?${getFilterParams()}`;
            const response = await fetch(queryUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('فشل جلب البيانات');

            const data = await response.json();

            if (data.html && data.html.trim().length > 0) {
                tbody.insertAdjacentHTML('beforeend', data.html);
                nextPage = data.nextPage;
                hasMorePages = data.hasMore;
                if (headerLoadedCount) headerLoadedCount.textContent = data.loadedCount;
                setTimeout(() => {
                    checkAndFillViewport();
                    updateScrollIndicator();
                }, 150);
            } else {
                hasMorePages = false;
                updateScrollIndicator();
            }
        } catch (error) {
            console.error('Error loading employees:', error);
        } finally {
            isLoading = false;
            if (spinner) spinner.classList.add('hidden');
        }
    }

    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            const nearBottom = scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 80;
            if (nearBottom && hasMorePages && !isLoading) {
                loadMoreEmployees();
            }
            updateScrollIndicator();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            checkAndFillViewport();
            updateScrollIndicator();
        }, 200);
    });

    window.addEventListener('resize', updateScrollIndicator);
</script>

<!-- Modal استيراد الكوادر من Excel / CSV -->
<div id="importEmployeesModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-scale-up font-cairo">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-file-excel text-emerald-700 text-base"></i> استيراد دفعة كوادر من ملف Excel / CSV
            </span>
            <button onclick="document.getElementById('importEmployeesModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold text-xl cursor-pointer transition">&times;</button>
        </div>
        <form method="POST" action="{{ route('employees.import.excel') }}" enctype="multipart/form-data" class="p-6 text-sm space-y-4">
            @csrf
            
            <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-3.5 text-xs text-emerald-950">
                <div class="font-bold mb-1 flex items-center gap-1.5 text-emerald-900">
                    <i class="fa-solid fa-circle-info text-emerald-600"></i> إرشادات الاستيراد:
                </div>
                <p class="leading-relaxed text-emerald-800">
                    يجب أن يكون الملف بصيغة <strong>CSV</strong> ويحتوي على الأعمدة: (الاسم، الرقم الوظيفي، الرقم الوطني، الفئة).
                </p>
                <div class="mt-2 pt-2 border-t border-emerald-200/60">
                    <a href="{{ route('employees.import.template') }}" class="text-brand-900 hover:underline font-bold flex items-center gap-1">
                        <i class="fa-solid fa-cloud-arrow-down text-xs"></i>
                        <span>تحميل النموذج المعتمد الجاهز للتعبئة (.csv)</span>
                    </a>
                </div>
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">اختر ملف CSV المعبأ: <span class="text-rose-600">*</span></label>
                <input type="file" name="import_file" required accept=".csv,text/csv" 
                       class="w-full bg-slate-50 border border-slate-200 p-2 text-xs file:py-1.5 file:px-3 file:bg-emerald-700 file:text-white file:border-0 file:rounded-lg file:text-xs rounded-xl">
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('importEmployeesModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold cursor-pointer transition">إلغاء</button>
                <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 cursor-pointer shadow-xs transition">
                    <i class="fa-solid fa-file-import text-xs"></i> بدء الاستيراد
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
