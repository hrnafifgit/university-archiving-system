@extends('layouts.app')

@section('title', 'جامعة إب - المستودع الرقمي والأرشيف الإلكتروني')

@section('content')
<div class="flex-1 flex flex-col min-h-0 gap-3.5 pb-2 font-cairo">

    <!-- 1. شريط المؤشرات والإحصائيات السريعة (KPIs) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-3 shrink-0 select-none">
        <!-- إجمالي الوثائق -->
        <a href="{{ route('archive.index') }}" 
           class="bg-white border {{ !request('status') && !request('confidential') ? 'border-brand-800 ring-2 ring-brand-800/10' : 'border-slate-200' }} hover:border-brand-700 p-3.5 rounded-2xl shadow-2xs flex items-center gap-2.5 transition group cursor-pointer"
           title="عرض كافة الوثائق النشطة بدون تصفية">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-900 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-folder-tree text-base"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] text-slate-500 font-medium">إجمالي الوثائق</div>
                <div class="text-base font-bold text-slate-900 font-mono">{{ number_format($totalDocsCount) }}</div>
            </div>
        </a>

        <!-- مؤرشف رقمياً -->
        <a href="{{ route('archive.index', array_merge(request()->except('page'), ['status' => 'digital'])) }}" 
           class="bg-white border {{ request('status') === 'digital' ? 'border-emerald-600 ring-2 ring-emerald-600/10' : 'border-slate-200' }} hover:border-emerald-500 p-3.5 rounded-2xl shadow-2xs flex items-center gap-2.5 transition group cursor-pointer"
           title="تصفية الوثائق المؤرشفة رقمياً فقط">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-file-circle-check text-base"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] text-slate-500 font-medium">مؤرشف رقمياً</div>
                <div class="text-base font-bold text-emerald-700 font-mono">{{ number_format($digitalDocsCount) }}</div>
            </div>
        </a>

        <!-- قيود ورقية تنتظر الرفع -->
        <a href="{{ route('archive.index', array_merge(request()->except('page'), ['status' => 'paper'])) }}" 
           class="bg-white border {{ request('status') === 'paper' ? 'border-amber-500 ring-2 ring-amber-500/10' : 'border-slate-200' }} hover:border-amber-400 p-3.5 rounded-2xl shadow-2xs flex items-center gap-2.5 transition group cursor-pointer"
           title="تصفية القيود الورقية التي تنتظر رفع نسختها الرقمية">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-clock-rotate-left text-base"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] text-slate-500 font-medium">قيد ورقي</div>
                <div class="text-base font-bold text-amber-600 font-mono">{{ number_format($paperDocsCount) }}</div>
            </div>
        </a>

        <!-- وثائق سرية ومحمية -->
        <a href="{{ route('archive.index', array_merge(request()->except('page'), ['confidential' => '1'])) }}" 
           class="bg-white border {{ request('confidential') === '1' ? 'border-purple-600 ring-2 ring-purple-600/10' : 'border-slate-200' }} hover:border-purple-500 p-3.5 rounded-2xl shadow-2xs flex items-center gap-2.5 transition group cursor-pointer"
           title="تصفية الوثائق السرية والمحمية">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-user-shield text-base"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] text-slate-500 font-medium">وثائق سرية</div>
                <div class="text-base font-bold text-purple-700 font-mono">{{ number_format($confidentialDocsCount) }}</div>
            </div>
        </a>

        <!-- سلة المحذوفات (Recycle Bin) -->
        <a href="{{ route('archive.index', array_merge(request()->except('page'), ['status' => 'trash'])) }}" 
           class="bg-white border {{ request('status') === 'trash' ? 'border-rose-600 ring-2 ring-rose-600/10' : 'border-slate-200' }} hover:border-rose-500 p-3.5 rounded-2xl shadow-2xs flex items-center gap-2.5 transition group cursor-pointer"
           title="عرض سلة محذوفات الأرشيف">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-trash-can text-base"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] text-slate-500 font-medium">سلة المحذوفات</div>
                <div class="text-base font-bold text-rose-700 font-mono">{{ number_format($trashedDocsCount) }}</div>
            </div>
        </a>

        <!-- حجم التخزين المستهلك -->
        <div class="hidden lg:flex bg-white border border-slate-200 p-3.5 rounded-2xl shadow-2xs items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-hard-drive text-base"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] text-slate-500 font-medium">سعة المستودع</div>
                @php
                    $mb = $totalStorageBytes / (1024 * 1024);
                    $storageDisplay = $mb > 1024 ? number_format($mb / 1024, 2) . ' GB' : number_format($mb, 1) . ' MB';
                @endphp
                <div class="text-sm font-bold text-slate-800 font-mono">{{ $storageDisplay }}</div>
            </div>
        </div>
    </div>

    <!-- 2. شريط البحث والفلترة ومفتاح تبديل العرض -->
    <div class="bg-white border border-slate-200 p-3 px-4 rounded-2xl shadow-2xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 shrink-0 select-none">
        
        <!-- نموذج البحث والتصفية -->
        <form method="GET" action="{{ route('archive.index') }}" class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
            
            <!-- حقل البحث -->
            <div class="relative w-full sm:w-56 md:w-64 lg:w-72">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="ابحث بالعنوان، رقم القيد، الكادر..." 
                       class="w-full bg-slate-50 border border-slate-200 px-3.5 py-2 text-xs md:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-900 focus:bg-white pr-9 font-medium rounded-xl transition shadow-2xs">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 text-xs"></i>
                @if(request('search'))
                    <a href="{{ route('archive.index', request()->except('search')) }}" class="absolute left-3 top-2 text-slate-400 hover:text-rose-600 transition" title="مسح البحث">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </a>
                @endif
            </div>

            <!-- تصنيف الوثيقة -->
            <div class="w-32 md:w-36">
                <select name="type_id" class="w-full bg-slate-50 border border-slate-200 px-2.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-brand-900 font-semibold rounded-xl transition shadow-2xs">
                    <option value="">-- كافة التصنيفات --</option>
                    @foreach($documentTypes as $dt)
                        <option value="{{ $dt->document_type_id }}" {{ request('type_id') == $dt->document_type_id ? 'selected' : '' }}>
                            {{ $dt->type_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- فلترة الكلية / الجهة -->
            <div class="w-36 md:w-44">
                <select name="college_id" id="archive_college_select" onchange="onCollegeFilterChange(this.value)" 
                        class="w-full bg-slate-50 border border-slate-200 px-2.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-brand-900 font-semibold rounded-xl transition shadow-2xs">
                    <option value="">-- كافة الكليات / الجهات --</option>
                    @foreach($colleges as $c)
                        <option value="{{ $c->organizational_unit_id }}" {{ (request('college_id') == $c->organizational_unit_id || request('faculty_id') == $c->organizational_unit_id) ? 'selected' : '' }}>
                            {{ $c->unit_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- فلترة القسم التابع -->
            <div class="w-32 md:w-40">
                <select name="dept_id" id="archive_dept_select" 
                        class="w-full bg-slate-50 border border-slate-200 px-2.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-brand-900 font-semibold rounded-xl transition shadow-2xs">
                    <option value="">-- كافة الأقسام --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->organizational_unit_id }}" data-parent="{{ $d->parent_unit_id }}" {{ (request('dept_id') == $d->organizational_unit_id || request('unit_id') == $d->organizational_unit_id) ? 'selected' : '' }}>
                            {{ $d->unit_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if(request('confidential'))
                <input type="hidden" name="confidential" value="{{ request('confidential') }}">
            @endif

            <!-- زر تصفية وإلغاء -->
            <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white font-bold py-2 px-3 rounded-xl text-xs transition flex items-center gap-1 cursor-pointer shadow-xs">
                <i class="fa-solid fa-filter text-xs"></i> بحث
            </button>

            @if(request()->anyFilled(['search', 'type_id', 'status', 'confidential', 'dept_id', 'unit_id', 'college_id', 'faculty_id']))
                <a href="{{ route('archive.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-2.5 rounded-xl text-xs transition border border-slate-200 text-center" title="إعادة ضبط الكل">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif

        </form>

        <!-- يمين الشريط: الإجراءات وأزرار التصدير وتبديل العرض -->
        <div class="flex items-center gap-2 shrink-0 justify-between sm:justify-end">
            <!-- تبديل العرض (Grid / Table) -->
            <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 shadow-2xs">
                <button type="button" id="btn-view-grid" onclick="setViewMode('grid')" 
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer text-slate-600 hover:text-slate-900" title="عرض الشبكة والبطاقات">
                    <i class="fa-solid fa-border-all text-xs"></i>
                </button>
                <button type="button" id="btn-view-table" onclick="setViewMode('table')" 
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer text-slate-600 hover:text-slate-900" title="عرض الجدول المكتبي">
                    <i class="fa-solid fa-list-ul text-xs"></i>
                </button>
            </div>

            <!-- زر تصدير Excel -->
            <a href="{{ route('archive.export.excel', request()->all()) }}" 
               class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-3 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer" 
               title="تصدير الوثائق المفلترة إلى ملف Excel">
                <i class="fa-solid fa-file-excel text-xs"></i>
                <span class="hidden md:inline">تصدير Excel</span>
            </a>

            @if(\App\Services\PermissionService::hasPerm('archive.create'))
            <!-- زر أرشفة جماعية (Bulk Upload) -->
            <button type="button" onclick="document.getElementById('bulkUploadModal').classList.remove('hidden')" 
                    class="bg-brand-50 hover:bg-brand-100 text-brand-900 border border-brand-200 font-bold px-3 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-2xs cursor-pointer" 
                    title="أرشفة دفعة ملفات متعددة (سحب وإفلات)">
                <i class="fa-solid fa-folder-plus text-xs"></i>
                <span class="hidden md:inline">أرشفة جماعية</span>
            </button>

            <!-- زر أرشفة وثيقة فردية -->
            <button type="button" onclick="document.getElementById('directUploadModal').classList.remove('hidden')" 
                    class="bg-brand-900 hover:bg-brand-800 text-white font-bold px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                <span>أرشفة وثيقة</span>
            </button>
            @endif
        </div>

    </div>

    <!-- شارة تنبيه عند التواجد في سلة المحذوفات -->
    @if(request('status') === 'trash')
    <div class="px-4 py-2.5 bg-rose-50 border border-rose-200 text-rose-950 rounded-xl flex items-center justify-between text-xs font-semibold shadow-2xs">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-trash-can text-rose-600 text-sm"></i>
            <span>أنت تستعرض حالياً: <strong class="font-bold underline">سلة محذوفات الأرشيف</strong>. يمكنك استعادة أي وثيقة للمستودع أو حذفها نهائياً.</span>
        </div>
        <a href="{{ route('archive.index') }}" class="text-xs bg-white border border-rose-300 text-rose-900 hover:bg-rose-100 px-3 py-1 rounded-lg transition inline-flex items-center gap-1 font-bold">
            <i class="fa-solid fa-arrow-left"></i> العودة للمستودع النشط
        </a>
    </div>
    @endif

    <!-- شارة تنبيه للفلترة النشطة حسب كلية أو قسم -->
    @if(isset($activeUnit) && $activeUnit && request('status') !== 'trash')
    <div class="px-4 py-2 bg-brand-50 border border-brand-200 text-brand-950 rounded-xl flex items-center justify-between text-xs font-semibold shadow-2xs">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-university text-brand-900 text-sm"></i>
            <span>الوثائق الخاصة بـ: <strong class="text-brand-950 font-bold underline">{{ $activeUnit->unit_name_ar }}</strong></span>
            <span class="bg-brand-100 text-brand-900 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold">({{ $documents->total() }} وثيقة)</span>
        </div>
        <a href="{{ route('archive.index') }}" class="text-xs bg-white border border-brand-300 text-brand-900 hover:bg-brand-100 px-2.5 py-1 rounded-lg transition inline-flex items-center gap-1 font-bold">
            <i class="fa-solid fa-xmark"></i> إظهار كافة المستودع
        </a>
    </div>
    @endif

    <!-- 3. مساحة عرض الوثائق (تبديل بين البطاقات والجدول) -->
    <div class="flex-1 min-h-0 flex flex-col">

        <!-- أ. عرض البطاقات العصرية (Grid View) -->
        <div id="archive-grid-view" class="flex-1 overflow-y-auto no-scrollbar pr-1 hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-3.5 pb-4">
                @forelse($documents as $doc)
                    @php 
                        $v = $doc->currentVersion ?? $doc->versions->first(); 
                        $hasFile = $v && $v->file_size_bytes > 0;
                        $orgUnit = $doc->employee?->employmentRecords?->first()?->organizationalUnit?->unit_name_ar;
                        $isTrash = request('status') === 'trash';
                    @endphp
                    <div class="bg-white border {{ $isTrash ? 'border-rose-200 hover:border-rose-400' : 'border-slate-200 hover:border-brand-700/60' }} rounded-2xl p-4 shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between group relative overflow-hidden cursor-pointer"
                         onclick="openInspectorDrawer({{ json_encode($doc) }})">
                        
                        <!-- الشريط العلوي للبطاقة -->
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="bg-slate-100 text-slate-700 border border-slate-200 px-2.5 py-0.5 rounded-lg text-[11px] font-bold truncate max-w-[150px]">
                                    {{ $doc->documentType?->type_name_ar ?? 'عام' }}
                                </span>
                                <div class="flex items-center gap-1">
                                    @if($doc->is_confidential)
                                        <span class="bg-purple-100 text-purple-800 w-5 h-5 rounded-md flex items-center justify-center text-[10px]" title="وثيقة سرية">
                                            <i class="fa-solid fa-lock"></i>
                                        </span>
                                    @endif
                                    <span class="text-[11px] font-mono text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">
                                        #{{ $doc->document_number ?? '—' }}
                                    </span>
                                </div>
                            </div>

                            <!-- الأيقونة والعنوان -->
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $hasFile ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                    @if($hasFile)
                                        <i class="fa-regular fa-file-pdf text-xl"></i>
                                    @else
                                        <i class="fa-regular fa-file-lines text-xl"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-slate-900 text-sm leading-snug line-clamp-2 group-hover:text-brand-900 transition" title="{{ $doc->document_title }}">
                                        {{ $doc->document_title }}
                                    </h4>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1 font-mono">
                                        <i class="fa-regular fa-calendar text-[10px]"></i>
                                        <span>{{ $doc->created_at ? $doc->created_at->format('Y-m-d') : '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- معلومات الكادر والجهة -->
                            <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-150 mb-3 text-xs">
                                <div class="flex items-center gap-1.5 text-slate-800 font-bold truncate">
                                    <i class="fa-regular fa-user text-slate-400 text-xs shrink-0"></i>
                                    @if($doc->employee)
                                        <span class="truncate">{{ $doc->employee->full_name_ar }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono font-normal">({{ $doc->employee->employee_number }})</span>
                                    @else
                                        <span class="text-slate-400 font-normal">سجل عام</span>
                                    @endif
                                </div>
                                @if($orgUnit)
                                    <div class="text-[10px] text-slate-500 font-medium truncate mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-building-columns text-slate-400 text-[10px]"></i>
                                        <span class="truncate">{{ $orgUnit }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- شريط الحالة والإجراءات السريعة -->
                        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs select-none">
                            <!-- شارة الحالة -->
                            <div>
                                @if($hasFile)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg text-[11px] font-bold">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>{{ number_format($v->file_size_bytes / 1024, 0) }} KB</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-lg text-[11px] font-semibold">
                                        <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                                        <span>قيد ورقي</span>
                                    </span>
                                @endif
                            </div>

                            <!-- أزرار الإجراءات -->
                            <div class="flex items-center gap-1" onclick="event.stopPropagation()">
                                @if($isTrash)
                                    <!-- استعادة من سلة المحذوفات -->
                                    <form method="POST" action="{{ route('archive.restore', $doc->document_id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center transition shadow-2xs" title="استعادة الوثيقة">
                                            <i class="fa-solid fa-rotate-left text-xs"></i>
                                        </button>
                                    </form>
                                    <!-- حذف نهائي -->
                                    <form method="POST" action="{{ route('archive.force_delete', $doc->document_id) }}" onsubmit="return confirm('تحذير: هل أنت متأكد من حذف هذه الوثيقة نهائياً؟ لا يمكن التراجع!')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition shadow-2xs" title="حذف نهائي">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </form>
                                @else
                                    @if($v)
                                        <a href="{{ route('archive.preview', $v->document_version_id) }}" 
                                           target="_blank"
                                           class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 hover:text-brand-900 border border-slate-200 flex items-center justify-center transition shadow-2xs" 
                                           title="معاينة الملف">
                                            <i class="fa-regular fa-eye text-xs"></i>
                                        </a>
                                        @if($hasFile)
                                            <a href="{{ route('archive.download', $v->document_version_id) }}" 
                                               class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 hover:text-brand-900 border border-slate-200 flex items-center justify-center transition shadow-2xs" 
                                               title="تنزيل الملف">
                                                <i class="fa-solid fa-download text-xs"></i>
                                            </a>
                                        @endif
                                    @endif
                                    <button type="button" onclick="upgradeDocVersion({{ json_encode($doc) }})" 
                                            class="w-7 h-7 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-900 border border-brand-200 flex items-center justify-center transition shadow-2xs" 
                                            title="ترقية / رفع إصدار جديد">
                                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                                    </button>
                                    <!-- زر الحذف وسلة المحذوفات -->
                                    <form method="POST" action="{{ route('archive.destroy', $doc->document_id) }}" onsubmit="return confirm('هل تريد نقل هذه الوثيقة إلى سلة المحذوفات؟')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 flex items-center justify-center transition shadow-2xs" title="نقل إلى سلة المحذوفات">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                        <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-2 block"></i>
                        <div class="text-sm font-semibold">لا توجد وثائق مؤرشفة مطابقة لمعايير البحث والتصفية.</div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ب. عرض الجدول المكتبي (Table View) -->
        <div id="archive-table-view" class="flex-1 bg-white border border-slate-200 shadow-2xs flex flex-col min-h-0 rounded-2xl overflow-hidden relative hidden">
            <div id="archive-scroll-container" class="flex-1 overflow-y-auto min-h-0 relative no-scrollbar">
                <table class="w-full text-right text-sm border-collapse">
                    <thead class="bg-slate-50 text-slate-800 font-bold sticky top-0 z-20 select-none text-xs md:text-sm border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3.5 w-12 text-center font-mono text-slate-400 font-bold">#</th>
                            <th class="py-3 px-3.5 min-w-[240px] text-slate-900">عنوان وموضوع الوثيقة</th>
                            <th class="py-3 px-3.5 w-40 text-slate-700">التصنيف</th>
                            <th class="py-3 px-3.5 w-52 text-slate-700">الكادر والجهة</th>
                            <th class="py-3 px-3.5 w-28 text-center font-mono text-slate-700">رقم القيد</th>
                            <th class="py-3 px-3.5 w-36 text-center text-slate-700">الحالة الرقمية</th>
                            <th class="py-3 px-3.5 w-28 text-center text-slate-700">التاريخ</th>
                            <th class="py-3 px-3.5 w-36 text-center text-slate-700">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800 text-xs md:text-sm">
                        @forelse($documents as $index => $doc)
                            @php 
                                $v = $doc->currentVersion ?? $doc->versions->first(); 
                                $hasFile = $v && $v->file_size_bytes > 0;
                                $isTrash = request('status') === 'trash';
                            @endphp
                            <tr class="hover:bg-brand-50/40 transition cursor-pointer {{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/30' }}"
                                onclick="openInspectorDrawer({{ json_encode($doc) }})">
                                <td class="py-3 px-3.5 text-center text-slate-400 font-mono text-xs">{{ $documents->firstItem() + $index }}</td>
                                <td class="py-3 px-3.5">
                                    <div class="font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fa-regular {{ $hasFile ? 'fa-file-pdf text-red-600' : 'fa-file-lines text-slate-400' }} text-base shrink-0"></i>
                                        <span class="truncate max-w-[280px]" title="{{ $doc->document_title }}">{{ $doc->document_title }}</span>
                                        @if($doc->is_confidential)
                                            <span class="text-purple-700 text-xs" title="سرية"><i class="fa-solid fa-lock text-[10px]"></i></span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold border border-slate-200">
                                        {{ $doc->documentType?->type_name_ar ?? 'عام' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5" onclick="event.stopPropagation()">
                                    @if($doc->employee)
                                        <a href="{{ route('employees.show', $doc->employee->employee_id) }}" class="font-bold text-brand-900 hover:underline flex items-center gap-1">
                                            <span class="truncate max-w-[160px]">{{ $doc->employee->full_name_ar }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono font-normal">({{ $doc->employee->employee_number }})</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-normal">سجل عام</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center font-mono text-slate-600 whitespace-nowrap text-xs">
                                    {{ $doc->document_number ?? '—' }}
                                </td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                    @if($hasFile)
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 px-2.5 py-0.5 rounded-lg text-xs font-bold">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                            <span>{{ number_format($v->file_size_bytes / 1024, 0) }} KB</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-lg text-xs font-semibold">
                                            <span>قيد ورقي</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center font-mono text-slate-600 text-xs whitespace-nowrap">
                                    {{ $doc->created_at ? $doc->created_at->format('Y-m-d') : '—' }}
                                </td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-center gap-1">
                                        @if($isTrash)
                                            <form method="POST" action="{{ route('archive.restore', $doc->document_id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="w-7 h-7 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg border border-emerald-200 transition text-xs font-bold inline-flex items-center justify-center shadow-2xs" title="استعادة">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('archive.force_delete', $doc->document_id) }}" onsubmit="return confirm('تحذير: هل أنت متأكد من الحذف النهائي؟')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-7 h-7 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200 transition text-xs font-bold inline-flex items-center justify-center shadow-2xs" title="حذف نهائي">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        @else
                                            @if($v)
                                                <a href="{{ route('archive.preview', $v->document_version_id) }}" 
                                                   target="_blank"
                                                   class="w-7 h-7 bg-white hover:bg-slate-100 text-slate-700 hover:text-brand-900 rounded-lg border border-slate-200 transition text-xs font-bold inline-flex items-center justify-center shadow-2xs" 
                                                   title="معاينة الوثيقة">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>
                                                @if($hasFile)
                                                    <a href="{{ route('archive.download', $v->document_version_id) }}" 
                                                       class="w-7 h-7 bg-white hover:bg-slate-100 text-slate-700 hover:text-brand-900 rounded-lg border border-slate-200 transition text-xs font-bold inline-flex items-center justify-center shadow-2xs" 
                                                       title="تنزيل الملف">
                                                        <i class="fa-solid fa-download"></i>
                                                    </a>
                                                @endif
                                            @endif
                                            <button type="button" onclick="upgradeDocVersion({{ json_encode($doc) }})" 
                                                    class="w-7 h-7 bg-brand-50 hover:bg-brand-100 text-brand-900 rounded-lg border border-brand-200 transition text-xs font-bold inline-flex items-center justify-center shadow-2xs cursor-pointer" 
                                                    title="ترقية أو رفع إصدار">
                                                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                                            </button>
                                            <form method="POST" action="{{ route('archive.destroy', $doc->document_id) }}" onsubmit="return confirm('هل تريد نقل هذه الوثيقة إلى سلة المحذوفات؟')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-7 h-7 bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-lg border border-slate-200 transition text-xs inline-flex items-center justify-center shadow-2xs" title="نقل إلى سلة المحذوفات">
                                                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-16 text-center text-slate-400 font-medium bg-white">
                                    <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-2 block"></i>
                                    لا توجد وثائق مؤرشفة مطابقة للبحث أو التصفية.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- 4. شريط الترقيم الصفحي (Pagination) -->
    <div class="shrink-0 pt-1 select-none">
        {{ $documents->links() }}
    </div>

</div>

<!-- 5. درج المعاينة الجانبي التفاعلي (Slide-Over Inspector Drawer) مع سجل الرقابة -->
<div id="inspectorDrawer" class="hidden fixed inset-0 z-50 overflow-hidden select-none">
    <!-- خلفية معتمة -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" onclick="closeInspectorDrawer()"></div>

    <div class="fixed inset-y-0 left-0 max-w-full flex pl-0 sm:pl-10">
        <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col border-r border-slate-200">
            
            <!-- ترويسة الدرج -->
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-900 text-white flex items-center justify-center text-sm">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">تفاصيل الوثيقة الأرشيفية</h3>
                        <p class="text-[11px] text-slate-400 font-mono" id="drawer_doc_number">—</p>
                    </div>
                </div>
                <button type="button" onclick="closeInspectorDrawer()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- محتوى الدرج -->
            <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs font-medium text-slate-700 no-scrollbar">
                
                <!-- عنوان الوثيقة وشارة التصنيف -->
                <div class="bg-brand-50/50 border border-brand-100 rounded-2xl p-4">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span id="drawer_doc_type" class="bg-brand-900 text-white px-2.5 py-0.5 rounded-md text-[11px] font-bold"></span>
                        <span id="drawer_confidential_badge" class="hidden bg-purple-100 text-purple-800 border border-purple-200 px-2 py-0.5 rounded-md text-[10px] font-bold">
                            <i class="fa-solid fa-lock"></i> وثيقة سرية
                        </span>
                    </div>
                    <h2 id="drawer_doc_title" class="text-base font-bold text-slate-900 leading-snug"></h2>
                </div>

                <!-- أزرار الإجراءات السريعة -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a id="drawer_btn_preview" href="#" target="_blank" 
                       class="bg-brand-900 hover:bg-brand-800 text-white py-2.5 px-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-xs text-xs">
                        <i class="fa-regular fa-eye"></i> فتح ومعاينة
                    </a>
                    <a id="drawer_btn_download" href="#" 
                       class="bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 py-2.5 px-3 rounded-xl font-bold flex items-center justify-center gap-2 transition text-xs">
                        <i class="fa-solid fa-download"></i> تنزيل الملف
                    </a>
                </div>

                <!-- بيانات الكادر المعني -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2">
                    <div class="text-[11px] font-bold text-slate-400 uppercase">الكادر الوظيفي المعني:</div>
                    <div class="flex items-center justify-between">
                        <div>
                            <div id="drawer_emp_name" class="font-bold text-slate-900 text-sm"></div>
                            <div id="drawer_emp_num" class="text-slate-500 font-mono text-[11px] mt-0.5"></div>
                        </div>
                        <a id="drawer_emp_link" href="#" class="text-brand-900 hover:underline text-xs font-bold flex items-center gap-1">
                            <span>الملف الشخصي</span>
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- مواصفات الوثيقة والإصدار الرقمي -->
                <div class="border border-slate-200 rounded-2xl p-4 space-y-2.5">
                    <div class="text-[11px] font-bold text-slate-400 uppercase">المواصفات الفنية للنسخة:</div>
                    
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">حالة الأرشفة:</span>
                        <span id="drawer_status_tag" class="font-bold"></span>
                    </div>

                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">حجم الملف:</span>
                        <span id="drawer_file_size" class="font-mono font-bold text-slate-800"></span>
                    </div>

                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">تاريخ التسجيل:</span>
                        <span id="drawer_created_at" class="font-mono text-slate-800"></span>
                    </div>

                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">جهة الإصدار:</span>
                        <span id="drawer_issuing_authority" class="font-semibold text-slate-800"></span>
                    </div>

                    <div class="pt-1">
                        <span class="text-slate-500 block mb-1">البصمة الرقمية للتحقق (SHA-256):</span>
                        <div class="bg-slate-100 p-2 rounded-lg font-mono text-[10px] break-all text-slate-600 select-all border border-slate-200" id="drawer_checksum">
                            —
                        </div>
                    </div>
                </div>

                <!-- 🌟 سجل الحركات والرقابة (Audit Trail) للوثيقة -->
                <div class="border border-slate-200 rounded-2xl p-4 space-y-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-timeline text-brand-900"></i> سجل تدقيق الوثيقة (Audit Trail):
                        </span>
                        <span id="drawer_audit_loading" class="hidden text-[10px] text-slate-400"><i class="fa-solid fa-spinner fa-spin"></i> جاري التحميل...</span>
                    </div>
                    <div id="drawer_audit_trail_list" class="space-y-2 max-h-48 overflow-y-auto no-scrollbar">
                        <div class="text-[11px] text-slate-400 py-2 text-center">لا توجد حركات مسجلة حالياً.</div>
                    </div>
                </div>

                <!-- الوصف والملاحظات -->
                <div class="border border-slate-200 rounded-2xl p-4">
                    <div class="text-[11px] font-bold text-slate-400 uppercase mb-1">ملاحظات أرشيفية:</div>
                    <p id="drawer_description" class="text-slate-600 leading-relaxed text-xs">لا توجد ملاحظات إضافية مسجلة.</p>
                </div>

                <!-- زر الترقية المباشر من الدرج -->
                <div class="pt-2">
                    <button type="button" id="drawer_btn_upgrade" 
                            class="w-full bg-emerald-700 hover:bg-emerald-800 text-white py-2.5 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition cursor-pointer text-xs shadow-xs">
                        <i class="fa-solid fa-cloud-arrow-up"></i> رفع نسخة إلكترونية أو ترقية الإصدار
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Modal 1: أرشفة وثيقة فردية -->
<div id="directUploadModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-lg flex flex-col shadow-2xl rounded-2xl overflow-hidden animate-scale-up font-cairo">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between shrink-0">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up text-brand-900 text-base"></i> أرشفة وثيقة جديدة في المستودع الرقمي
            </span>
            <button onclick="document.getElementById('directUploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold text-xl cursor-pointer transition">&times;</button>
        </div>
        <form method="POST" action="{{ route('archive.store') }}" enctype="multipart/form-data" class="p-6 text-sm space-y-4">
            @csrf
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">الكادر المعني بالوثيقة: <span class="text-rose-600">*</span></label>
                <select name="employee_id" required class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs focus:outline-none focus:border-brand-900 font-medium rounded-xl">
                    <option value="">-- حدد الكادر الوظيفي --</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->employee_id }}">{{ $e->full_name_ar }} ({{ $e->employee_number }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold mb-1 text-slate-800 text-xs">تصنيف الوثيقة: <span class="text-rose-600">*</span></label>
                    <select name="document_type_id" required class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs focus:outline-none focus:border-brand-900 font-medium rounded-xl">
                        @foreach($documentTypes as $dt)
                            <option value="{{ $dt->document_type_id }}">{{ $dt->type_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1 text-slate-800 text-xs">رقم الوثيقة / القيد:</label>
                    <input type="text" name="document_number" placeholder="رقم القيد أو القرار..." class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs focus:outline-none focus:border-brand-900 font-mono rounded-xl">
                </div>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">عنوان وموضوع الوثيقة: <span class="text-rose-600">*</span></label>
                <input type="text" name="document_title" required placeholder="مثال: شهادة دكتوراه، قرار ترقية، وثيقة هوية..." class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs focus:outline-none focus:border-brand-900 font-medium rounded-xl">
            </div>
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">ملف الوثيقة (PDF أو صورة حتى 25MB): <span class="text-rose-600">*</span></label>
                <input type="file" name="document_file" required accept="application/pdf,image/*" class="w-full bg-slate-50 border border-slate-200 p-2 text-xs file:py-1.5 file:px-3 file:bg-brand-900 file:text-white file:border-0 file:rounded-lg file:text-xs text-xs rounded-xl">
            </div>
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">وصف أو ملاحظات أرشيفية:</label>
                <textarea name="description" rows="2" placeholder="أي بيانات توثيقية إضافية..." class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs focus:outline-none focus:border-brand-900 rounded-xl"></textarea>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="conf_check" name="is_confidential" value="1" class="rounded text-brand-900 border-slate-300 w-4 h-4">
                <label for="conf_check" class="text-slate-800 font-medium cursor-pointer text-xs">وثيقة سرية / محمية</label>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('directUploadModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold cursor-pointer transition">إلغاء</button>
                <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 cursor-pointer shadow-xs transition">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i> حفظ وأرشفة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: الأرشفة الجماعية بالسحب والإفلات (Bulk Upload) -->
<div id="bulkUploadModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-lg flex flex-col shadow-2xl rounded-2xl overflow-hidden animate-scale-up font-cairo">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between shrink-0">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-brand-900 text-base"></i> أرشفة جماعية بالسحب والإفلات (Bulk Upload)
            </span>
            <button onclick="document.getElementById('bulkUploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold text-xl cursor-pointer transition">&times;</button>
        </div>
        <form method="POST" action="{{ route('archive.bulk_upload') }}" enctype="multipart/form-data" class="p-6 text-sm space-y-4">
            @csrf
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">الكادر المعني بالدفعة: <span class="text-rose-600">*</span></label>
                <select name="employee_id" required class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs focus:outline-none focus:border-brand-900 font-medium rounded-xl">
                    <option value="">-- حدد الكادر الوظيفي --</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->employee_id }}">{{ $e->full_name_ar }} ({{ $e->employee_number }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">التصنيف الأساسي للملفات: <span class="text-rose-600">*</span></label>
                <select name="document_type_id" required class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs focus:outline-none focus:border-brand-900 font-medium rounded-xl">
                    @foreach($documentTypes as $dt)
                        <option value="{{ $dt->document_type_id }}">{{ $dt->type_name_ar }}</option>
                    @endforeach
                </select>
            </div>

            <!-- منطقة السحب والإفلات الجماعية -->
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">حدد أو اسحب الملفات (حتى 20 ملف PDF): <span class="text-rose-600">*</span></label>
                <div id="bulkDropZone" class="border-2 border-dashed border-brand-300 hover:border-brand-700 bg-brand-50/30 rounded-2xl p-6 text-center transition cursor-pointer"
                     onclick="document.getElementById('bulk_file_input').click()">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-brand-900 mb-2 block"></i>
                    <p class="text-xs font-bold text-slate-800">اسحب وأفلت الملفات هنا، أو انقر للاختيار</p>
                    <p class="text-[11px] text-slate-500 mt-1">يدعم ملفات PDF وصور حتى 25MB للملف الواحد</p>
                </div>
                <input type="file" id="bulk_file_input" name="files[]" multiple required accept="application/pdf,image/*" class="hidden" onchange="handleBulkFilesSelected(this)">
                <div id="bulkSelectedFilesList" class="mt-2.5 space-y-1.5 max-h-36 overflow-y-auto no-scrollbar text-xs"></div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="bulk_conf" name="is_confidential" value="1" class="rounded text-brand-900 border-slate-300 w-4 h-4">
                <label for="bulk_conf" class="text-slate-800 font-medium cursor-pointer text-xs">تعيين هذه الدفعة كملفات سرية ومحمية</label>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('bulkUploadModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold cursor-pointer transition">إلغاء</button>
                <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 cursor-pointer shadow-xs transition">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i> بدء الأرشفة الجماعية
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: رفع إصدار أحدث v+ لوثيقة موجودة -->
<div id="upgradeVersionModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-md flex flex-col shadow-2xl rounded-2xl overflow-hidden animate-scale-up font-cairo">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between shrink-0">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-code-branch text-brand-900 text-base"></i> ترقية إصدار الوثيقة الأرشيفية
            </span>
            <button onclick="document.getElementById('upgradeVersionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold text-xl cursor-pointer transition">&times;</button>
        </div>
        <form id="form-upgrade-version" method="POST" action="" enctype="multipart/form-data" class="p-6 text-sm space-y-4">
            @csrf
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-slate-800">
                <div class="font-bold text-xs mb-1 text-brand-900" id="upgrade_doc_title"></div>
                <div class="text-[11px] text-slate-600" id="upgrade_doc_meta"></div>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-slate-800 text-xs">الملف الإلكتروني المحدث (PDF حتى 25MB): <span class="text-rose-600">*</span></label>
                <input type="file" name="document_file" required accept="application/pdf,image/*" class="w-full bg-slate-50 border border-slate-200 p-2 text-xs file:py-1.5 file:px-3 file:bg-brand-900 file:text-white file:border-0 file:rounded-lg file:text-xs rounded-xl">
            </div>
            <p class="text-[11px] text-slate-500 leading-relaxed">
                <i class="fa-solid fa-shield-halved text-emerald-600"></i> سيتم حفظ النسخة السابقة تلقائياً وتعيين الملف الجديد كإصدار حالي مع حساب بصمة رقمية SHA-256 جديدة.
            </p>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('upgradeVersionModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold cursor-pointer transition">إلغاء</button>
                <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 cursor-pointer shadow-xs transition">
                    <i class="fa-solid fa-upload text-xs"></i> تأكيد الترقية
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // 1. نظام التبديل بين عرض البطاقات وعرض الجدول مع الحفظ في المتصفح
    function setViewMode(mode) {
        const gridView = document.getElementById('archive-grid-view');
        const tableView = document.getElementById('archive-table-view');
        const btnGrid = document.getElementById('btn-view-grid');
        const btnTable = document.getElementById('btn-view-table');

        if (mode === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.className = "px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer bg-white text-brand-900 shadow-2xs";
            btnGrid.className = "px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer text-slate-600 hover:text-slate-900";
            localStorage.setItem('archive_view_mode', 'table');
        } else {
            tableView.classList.add('hidden');
            gridView.classList.remove('hidden');
            btnGrid.className = "px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer bg-white text-brand-900 shadow-2xs";
            btnTable.className = "px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer text-slate-600 hover:text-slate-900";
            localStorage.setItem('archive_view_mode', 'grid');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedMode = localStorage.getItem('archive_view_mode') || 'grid';
        setViewMode(savedMode);
    });

    // 2. درج المعاينة الجانبي التفاعلي (Inspector Drawer) مع سجل الرقابة
    let currentSelectedDoc = null;

    function openInspectorDrawer(doc) {
        currentSelectedDoc = doc;
        const v = doc.current_version || (doc.versions && doc.versions.length ? doc.versions[0] : null);
        const hasFile = v && v.file_size_bytes > 0;

        document.getElementById('drawer_doc_title').innerText = doc.document_title || '—';
        document.getElementById('drawer_doc_number').innerText = 'رقم القيد الأرشيفي: ' + (doc.document_number || 'غير محدد');
        document.getElementById('drawer_doc_type').innerText = (doc.document_type ? doc.document_type.type_name_ar : 'عام');
        
        // شارة السرية
        const confBadge = document.getElementById('drawer_confidential_badge');
        if (doc.is_confidential) {
            confBadge.classList.remove('hidden');
        } else {
            confBadge.classList.add('hidden');
        }

        // الكادر
        if (doc.employee) {
            document.getElementById('drawer_emp_name').innerText = doc.employee.full_name_ar || '—';
            document.getElementById('drawer_emp_num').innerText = 'الرقم الوظيفي: ' + (doc.employee.employee_number || '—');
            document.getElementById('drawer_emp_link').href = '/employees/' + doc.employee.employee_id;
            document.getElementById('drawer_emp_link').classList.remove('hidden');
        } else {
            document.getElementById('drawer_emp_name').innerText = 'سجل عام للمؤسسة';
            document.getElementById('drawer_emp_num').innerText = 'غير مرتبط بموظف فردي';
            document.getElementById('drawer_emp_link').classList.add('hidden');
        }

        // حالة الملف الرقمي
        const statusTag = document.getElementById('drawer_status_tag');
        if (hasFile) {
            statusTag.innerHTML = '<span class="text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200 font-bold"><i class="fa-solid fa-check text-xs"></i> مؤرشف رقمياً</span>';
            document.getElementById('drawer_file_size').innerText = (v.file_size_bytes / 1024).toFixed(1) + ' KB (' + (v.file_size_bytes / (1024*1024)).toFixed(2) + ' MB)';
            document.getElementById('drawer_checksum').innerText = v.checksum_sha256 || 'غير مسجل';
            
            document.getElementById('drawer_btn_preview').href = '/archive/preview/' + v.document_version_id;
            document.getElementById('drawer_btn_preview').classList.remove('opacity-50', 'pointer-events-none');
            
            document.getElementById('drawer_btn_download').href = '/archive/download/' + v.document_version_id;
            document.getElementById('drawer_btn_download').classList.remove('opacity-50', 'pointer-events-none');
        } else {
            statusTag.innerHTML = '<span class="text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-lg border border-amber-200 font-semibold"><i class="fa-solid fa-hourglass-half text-xs"></i> قيد ورقي (بانتظار الرفع)</span>';
            document.getElementById('drawer_file_size').innerText = 'لا يوجد ملف رقمي مرفق';
            document.getElementById('drawer_checksum').innerText = 'غير متوفر - يتطلب رفع ملف';

            if (v) {
                document.getElementById('drawer_btn_preview').href = '/archive/preview/' + v.document_version_id;
                document.getElementById('drawer_btn_preview').classList.remove('opacity-50', 'pointer-events-none');
            } else {
                document.getElementById('drawer_btn_preview').classList.add('opacity-50', 'pointer-events-none');
            }
            document.getElementById('drawer_btn_download').classList.add('opacity-50', 'pointer-events-none');
        }

        // التواريخ وجهة الإصدار
        document.getElementById('drawer_created_at').innerText = doc.created_at ? doc.created_at.substring(0, 10) : '—';
        document.getElementById('drawer_issuing_authority').innerText = doc.issuing_authority || 'جامعة إب';
        document.getElementById('drawer_description').innerText = doc.description || 'لا توجد ملاحظات توثيقية إضافية.';

        // جلب سجل الرقابة والتدقيق (Audit Trail)
        loadDocAuditTrail(doc.document_id);

        // زر الترقية من داخل الدرج
        document.getElementById('drawer_btn_upgrade').onclick = function() {
            closeInspectorDrawer();
            upgradeDocVersion(doc);
        };

        // فتح الدرج
        document.getElementById('inspectorDrawer').classList.remove('hidden');
    }

    function closeInspectorDrawer() {
        document.getElementById('inspectorDrawer').classList.add('hidden');
        currentSelectedDoc = null;
    }

    // جلب سجل التدقيق
    function loadDocAuditTrail(docId) {
        const list = document.getElementById('drawer_audit_trail_list');
        const loader = document.getElementById('drawer_audit_loading');
        loader.classList.remove('hidden');

        fetch('/archive/' + docId + '/audit-trail')
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');
                if (!data || !data.length) {
                    list.innerHTML = '<div class="text-[11px] text-slate-400 py-2 text-center">لا توجد حركات تدقيق مسجلة.</div>';
                    return;
                }
                let html = '';
                data.forEach(item => {
                    html += `
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 text-[11px]">
                            <div class="flex items-center justify-between font-bold text-slate-800">
                                <span>${item.action}</span>
                                <span class="font-mono text-[10px] text-slate-400">${item.date}</span>
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center justify-between">
                                <span>بواسطة: <strong>${item.user}</strong></span>
                                <span class="font-mono text-[9px] text-slate-400">IP: ${item.ip}</span>
                            </div>
                        </div>
                    `;
                });
                list.innerHTML = html;
            })
            .catch(() => {
                loader.classList.add('hidden');
                list.innerHTML = '<div class="text-[11px] text-slate-400 py-2 text-center">تعذر جلب سجل التدقيق.</div>';
            });
    }

    // إغلاق النوافذ بزر Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeInspectorDrawer();
            document.getElementById('directUploadModal').classList.add('hidden');
            document.getElementById('bulkUploadModal').classList.add('hidden');
            document.getElementById('upgradeVersionModal').classList.add('hidden');
        }
    });

    // 3. ربط وتصفية الكليات والأقسام ديناميكياً
    function onCollegeFilterChange(collegeId) {
        const deptSelect = document.getElementById('archive_dept_select');
        if (!deptSelect) return;
        const options = deptSelect.querySelectorAll('option');

        options.forEach(opt => {
            if (!opt.value) {
                opt.style.display = '';
                return;
            }
            const parentId = opt.getAttribute('data-parent');
            if (!collegeId || parentId === collegeId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
                if (opt.selected) {
                    opt.selected = false;
                }
            }
        });

        if (collegeId && deptSelect.selectedOptions.length && deptSelect.selectedOptions[0].style.display === 'none') {
            deptSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const collegeSelect = document.getElementById('archive_college_select');
        if (collegeSelect && collegeSelect.value) {
            onCollegeFilterChange(collegeSelect.value);
        }
    });

    // 4. ترقية نسخة الوثيقة
    function upgradeDocVersion(doc) {
        document.getElementById('form-upgrade-version').action = '/archive/' + doc.document_id + '/versions';
        document.getElementById('upgrade_doc_title').innerText = doc.document_title || 'وثيقة';
        const empName = doc.employee ? doc.employee.full_name_ar : 'سجل عام';
        document.getElementById('upgrade_doc_meta').innerText = 'الكادر: ' + empName + ' | عدد الإصدارات: ' + (doc.versions ? doc.versions.length : 1);
        document.getElementById('upgradeVersionModal').classList.remove('hidden');
    }

    // 5. إدارة ملفات الأرشفة الجماعية (Bulk Dropzone UI)
    function handleBulkFilesSelected(input) {
        const list = document.getElementById('bulkSelectedFilesList');
        if (!input.files || !input.files.length) {
            list.innerHTML = '';
            return;
        }

        let html = '<div class="font-bold text-[11px] text-slate-700 mb-1">الملفات المحددة (' + input.files.length + '):</div>';
        Array.from(input.files).forEach((f, i) => {
            const kb = (f.size / 1024).toFixed(0);
            html += `
                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg text-[11px]">
                    <span class="truncate max-w-[260px] font-medium text-slate-800"><i class="fa-regular fa-file-pdf text-red-600 mr-1"></i> ${f.name}</span>
                    <span class="font-mono text-[10px] text-slate-400 shrink-0">${kb} KB</span>
                </div>
            `;
        });
        list.innerHTML = html;
    }
</script>
@endsection
