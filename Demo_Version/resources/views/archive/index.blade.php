@extends('layouts.app')

@section('title', 'جامعة إب - المستودع الرقمي والأرشيف')

@section('content')
<div class="flex-1 flex flex-col min-h-0 gap-3.5 pb-0">

    <!-- 1. شريط العمليات والفلترة الموحد -->
    <div class="bg-white border border-slate-200 p-3 px-4 flex flex-wrap items-center justify-between gap-3 shadow-xs shrink-0 rounded-2xl select-none">
        
        <!-- نموذج البحث والتصفية المباشر -->
        <form method="GET" action="{{ route('archive.index') }}" class="flex flex-wrap items-center gap-2 flex-1 min-w-0 text-xs">
            
            <!-- حقل البحث -->
            <div class="relative w-44 sm:w-52 shrink-0">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="بحث..." 
                       class="w-full bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-900 focus:bg-white pr-8 font-medium rounded-xl shadow-2xs transition">
                <i class="fa-solid fa-search absolute right-2.5 top-2.5 text-slate-400 text-xs"></i>
            </div>

            <!-- تصنيف الوثيقة -->
            <div class="w-40 shrink-0">
                <select name="type_id" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-brand-900 font-bold rounded-xl shadow-2xs transition">
                    <option value="">كل التصنيفات</option>
                    @foreach($documentTypes as $dt)
                        <option value="{{ $dt->document_type_id }}" {{ request('type_id') == $dt->document_type_id ? 'selected' : '' }}>
                            {{ $dt->type_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- الكلية / الجهة -->
            @php
                $selectedCollege = request('college_id') ?? request('faculty_id');
                $selectedDept = request('unit_id') ?? request('dept_id');
            @endphp
            <div class="w-44 shrink-0">
                <select name="college_id" id="archive_filter_college" onchange="filterArchiveDepts()" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-brand-900 font-bold rounded-xl shadow-2xs transition">
                    <option value="">كل الكليات</option>
                    @foreach($colleges as $col)
                        <option value="{{ $col->organizational_unit_id }}" {{ $selectedCollege == $col->organizational_unit_id ? 'selected' : '' }}>
                            {{ $col->unit_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- القسم -->
            <div class="w-44 shrink-0">
                <select name="unit_id" id="archive_filter_dept" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-brand-900 font-bold rounded-xl shadow-2xs transition">
                    <option value="">كل الأقسام</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->organizational_unit_id }}" 
                                data-parent="{{ $dept->parent_unit_id }}"
                                {{ $selectedDept == $dept->organizational_unit_id ? 'selected' : '' }}>
                            {{ $dept->unit_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- أزرار التصفية -->
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold py-2 px-3.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer shadow-xs whitespace-nowrap">
                    <i class="fa-solid fa-filter text-[11px]"></i> تصفية
                </button>
                @if(request()->anyFilled(['search', 'type_id', 'dept_id', 'unit_id', 'college_id', 'faculty_id']))
                    <a href="{{ route('archive.index') }}" class="w-8 h-8 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition border border-slate-200 flex items-center justify-center shrink-0" title="إلغاء التصفية">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>

        </form>

        <!-- عدادات الأرشيف وزر الأرشفة -->
        <div class="flex items-center gap-2 shrink-0 text-xs whitespace-nowrap">
            <span class="bg-slate-50 text-slate-700 font-semibold px-3 py-2 rounded-xl border border-slate-200 font-mono text-[11px]">
                <i class="fa-solid fa-file-lines text-[#14532d] ml-1"></i> {{ $totalDocsCount }}
                <span class="text-slate-300 mx-1.5">|</span>
                <i class="fa-solid fa-layer-group text-slate-400 ml-1"></i> {{ $totalVersionsCount }}
            </span>
            <button type="button" onclick="document.getElementById('directUploadModal').classList.remove('hidden')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer shrink-0">
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i> أرشفة وثيقة
            </button>
        </div>

    </div>

    @if(isset($activeUnit) && $activeUnit)
    <div class="mb-1 px-4 py-2 bg-purple-50/90 border border-purple-200 text-purple-900 rounded-xl flex items-center justify-between text-xs font-semibold shadow-2xs">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-box-archive text-purple-600 text-sm"></i>
            <span>أرشيف: <strong class="text-purple-950 font-bold">{{ $activeUnit->unit_name_ar }}</strong></span>
            <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-[11px] font-mono font-bold">{{ $documents->total() }}</span>
        </div>
        <a href="{{ route('archive.index') }}" class="text-[11px] bg-white border border-purple-300 text-purple-700 hover:bg-purple-100 px-2.5 py-1 rounded-lg transition inline-flex items-center gap-1 font-bold">
            <i class="fa-solid fa-xmark"></i> الكل
        </a>
    </div>
    @endif

    <!-- 2. جدول وثائق الأرشيف الإلكتروني -->
    <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden relative">
        <div id="archive-scroll-container" class="flex-1 overflow-y-auto min-h-0 relative no-scrollbar">
            <table class="w-full text-right text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-800 font-bold sticky top-0 z-20 select-none text-xs border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-2.5 w-10 text-center font-mono text-slate-400 font-bold">#</th>
                        <th class="py-3 px-3 text-slate-900">عنوان الوثيقة</th>
                        <th class="py-3 px-3 text-slate-700">التصنيف</th>
                        <th class="py-3 px-3 text-slate-700">الكادر المعني</th>
                        <th class="py-3 px-2.5 w-20 text-center font-mono text-slate-700">القيد</th>
                        <th class="py-3 px-3 w-32 text-center text-slate-700">الحالة</th>
                        <th class="py-3 px-3 w-28 text-center text-slate-700">التاريخ</th>
                        <th class="py-3 px-3 w-28 text-center text-slate-700">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800 text-xs">
                    @forelse($documents as $index => $doc)
                        @php 
                            $v = $doc->currentVersion ?? $doc->versions->first(); 
                            $hasFile = $v && $v->file_size_bytes > 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/40' }}">
                            <td class="py-2.5 px-2.5 text-center text-slate-400 font-mono text-[11px]">{{ $documents->firstItem() + $index }}</td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fa-regular fa-file-pdf text-[#14532d] text-base shrink-0"></i>
                                    <span class="truncate max-w-[200px]" title="{{ $doc->document_title }}">{{ $doc->document_title }}</span>
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 font-medium whitespace-nowrap">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold border border-slate-200">
                                    {{ $doc->documentType?->type_name_ar ?? 'عام' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($doc->employee)
                                    <a href="{{ route('employees.show', $doc->employee->employee_id) }}" class="font-bold text-[#14532d] hover:underline flex items-center gap-1">
                                        <span class="truncate max-w-[140px]">{{ $doc->employee->full_name_ar }}</span>
                                        <span class="text-[11px] text-slate-400 font-mono font-normal">({{ $doc->employee->employee_number }})</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 font-normal">عام</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-2.5 text-center font-mono text-slate-600 whitespace-nowrap">
                                {{ $doc->document_number ?? '—' }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                @if($hasFile)
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-[#14532d] border border-emerald-200 px-2.5 py-0.5 rounded-lg text-[11px] font-bold" title="SHA-256: {{ $v?->checksum_sha256 }}">
                                        <i class="fa-solid fa-check text-[10px]"></i>
                                        <span>{{ number_format($v->file_size_bytes / 1024, 0) }} KB</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-slate-50 text-slate-400 border border-slate-200 px-2 py-0.5 rounded-lg text-[11px]">
                                        <span>ورقي</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-600 text-sm whitespace-nowrap">
                                {{ $doc->created_at ? $doc->created_at->format('Y-m-d') : '—' }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($v)
                                        <a href="{{ route('archive.preview', $v->document_version_id) }}" 
                                           target="_blank"
                                           class="w-8 h-8 bg-white hover:bg-slate-50 text-slate-700 hover:text-brand-900 rounded-lg border border-slate-200 transition text-xs font-bold inline-flex items-center justify-center shadow-2xs cursor-pointer" 
                                           title="معاينة الوثيقة">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        @if($hasFile)
                                            <a href="{{ route('archive.download', $v->document_version_id) }}" 
                                               class="w-8 h-8 bg-white hover:bg-slate-50 text-slate-700 hover:text-brand-900 rounded-lg border border-slate-200 transition text-xs font-bold inline-flex items-center justify-center shadow-2xs cursor-pointer" 
                                               title="تنزيل الملف">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        @endif
                                    @endif
                                    <button type="button" onclick="upgradeDocVersion({{ json_encode($doc) }})" 
                                            class="w-8 h-8 bg-brand-50 hover:bg-brand-100 text-brand-900 rounded-lg border border-brand-200 transition text-xs font-bold inline-flex items-center justify-center cursor-pointer shadow-2xs" 
                                            title="تحديث أو ترقية إصدار الوثيقة">
                                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-400 font-medium bg-white">
                                <i class="fa-solid fa-inbox text-4xl text-slate-300 mb-3 block"></i>
                                لا توجد وثائق مؤرشفة مطابقة للبحث أو التصفية.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- مؤشر التمرير السفلي -->
        <button type="button" id="scroll-down-indicator" onclick="scrollArchiveDown()" 
                class="hidden absolute bottom-3 left-1/2 -translate-x-1/2 z-30 transition-all duration-300 bg-white hover:bg-slate-50 text-slate-600 hover:text-brand-900 border border-slate-200 rounded-full w-9 h-9 shadow-sm flex items-center justify-center cursor-pointer select-none"
                title="توجد وثائق إضافية">
            <i class="fa-solid fa-chevron-down text-brand-900 text-sm animate-subtle-bounce"></i>
        </button>
    </div>

</div>

<!-- Modal أرشفة وثيقة مباشرة من المستودع -->
<div id="directUploadModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-lg flex flex-col shadow-xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between shrink-0">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up text-brand-900 text-base"></i> أرشفة وثيقة جديدة في المستودع
            </span>
            <button onclick="document.getElementById('directUploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold text-xl cursor-pointer transition">&times;</button>
        </div>
        <form method="POST" action="{{ route('archive.store') }}" enctype="multipart/form-data" class="p-6 text-sm space-y-4">
            @csrf
            <div>
                <label class="block font-semibold mb-1.5 text-slate-800">الكادر المعني بالوثيقة: <span class="text-rose-600">*</span></label>
                <select name="employee_id" required class="w-full bg-slate-50 border border-slate-200 p-2.5 text-sm focus:outline-none focus:border-brand-900 font-medium rounded-xl">
                    <option value="">-- حدد الكادر --</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->employee_id }}">{{ $e->full_name_ar }} ({{ $e->employee_number }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800">تصنيف الوثيقة: <span class="text-rose-600">*</span></label>
                    <select name="document_type_id" required class="w-full bg-slate-50 border border-slate-200 p-2.5 text-sm focus:outline-none focus:border-brand-900 font-medium rounded-xl">
                        @foreach($documentTypes as $dt)
                            <option value="{{ $dt->document_type_id }}">{{ $dt->type_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800">رقم الوثيقة / القيد:</label>
                    <input type="text" name="document_number" placeholder="رقم القيد أو القرار..." class="w-full bg-slate-50 border border-slate-200 p-2.5 text-sm focus:outline-none focus:border-brand-900 font-mono rounded-xl">
                </div>
            </div>
            <div>
                <label class="block font-semibold mb-1.5 text-slate-800">عنوان وموضوع الوثيقة: <span class="text-rose-600">*</span></label>
                <input type="text" name="document_title" required placeholder="مثال: شهادة دكتوراه، قرار ترقية، وثيقة هوية..." class="w-full bg-slate-50 border border-slate-200 p-2.5 text-sm focus:outline-none focus:border-brand-900 font-medium rounded-xl">
            </div>
            <div>
                <label class="block font-semibold mb-1.5 text-slate-800">ملف الوثيقة (PDF أو صورة حتى 50MB): <span class="text-rose-600">*</span></label>
                <input type="file" name="document_file" required accept="application/pdf,image/*" class="w-full bg-slate-50 border border-slate-200 p-2 text-sm file:py-2 file:px-4 file:bg-brand-900 file:text-white file:border-0 file:rounded-lg file:text-xs text-sm rounded-xl">
            </div>
            <div>
                <label class="block font-semibold mb-1.5 text-slate-800">وصف أو ملاحظات أرشيفية:</label>
                <textarea name="description" rows="2" placeholder="أي بيانات توثيقية إضافية..." class="w-full bg-slate-50 border border-slate-200 p-2.5 text-sm focus:outline-none focus:border-brand-900 rounded-xl"></textarea>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="conf_check" name="is_confidential" value="1" checked class="rounded text-brand-900 border-slate-300 w-4 h-4">
                <label for="conf_check" class="text-slate-800 font-medium cursor-pointer text-sm">وثيقة سرية / محمية</label>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('directUploadModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition">إلغاء</button>
                <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 cursor-pointer shadow-xs transition">
                    <i class="fa-solid fa-cloud-arrow-up text-sm"></i> رفع وأرشفة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal رفع إصدار أحدث v+ لوثيقة موجودة -->
<div id="upgradeVersionModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-md flex flex-col shadow-xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between shrink-0">
            <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-code-branch text-brand-900 text-base"></i> ترقية إصدار الوثيقة الأرشيفية
            </span>
            <button onclick="document.getElementById('upgradeVersionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold text-xl cursor-pointer transition">&times;</button>
        </div>
        <form id="form-upgrade-version" method="POST" action="" enctype="multipart/form-data" class="p-6 text-sm space-y-4">
            @csrf
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-slate-800">
                <div class="font-bold text-sm mb-1 text-brand-900" id="upgrade_doc_title"></div>
                <div class="text-xs text-slate-600" id="upgrade_doc_meta"></div>
            </div>
            <div>
                <label class="block font-semibold mb-1.5 text-slate-800">الملف الإلكتروني المحدث (PDF أو صورة حتى 50MB): <span class="text-rose-600">*</span></label>
                <input type="file" name="document_file" required accept="application/pdf,image/*" class="w-full bg-slate-50 border border-slate-200 p-2 text-sm file:py-2 file:px-4 file:bg-brand-900 file:text-white file:border-0 file:rounded-lg file:text-xs text-sm rounded-xl">
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                <i class="fa-solid fa-shield-halved text-emerald-600"></i> سيتم أرشفة النسخة السابقة تلقائياً وتعيين الملف الجديد كإصدار حالي مع حساب بصمة رقمية SHA-256 جديدة.
            </p>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('upgradeVersionModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition">إلغاء</button>
                <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 cursor-pointer shadow-xs transition">
                    <i class="fa-solid fa-upload text-sm"></i> تأكيد الترقية
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const scrollContainer = document.getElementById('archive-scroll-container');
    const scrollIndicator = document.getElementById('scroll-down-indicator');

    function updateScrollIndicator() {
        if (!scrollIndicator || !scrollContainer) return;
        const canScroll = scrollContainer.scrollHeight > scrollContainer.clientHeight + 10;
        const isNearBottom = (scrollContainer.scrollTop + scrollContainer.clientHeight) >= (scrollContainer.scrollHeight - 25);
        if (canScroll && !isNearBottom) {
            scrollIndicator.classList.remove('hidden');
        } else {
            scrollIndicator.classList.add('hidden');
        }
    }

    function scrollArchiveDown() {
        if (!scrollContainer) return;
        scrollContainer.scrollBy({ top: 220, behavior: 'smooth' });
    }

    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', updateScrollIndicator);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(updateScrollIndicator, 200);
        filterArchiveDepts(true);
    });

    function filterArchiveDepts(keepSelected = false) {
        const collegeSel = document.getElementById('archive_filter_college');
        const deptSel = document.getElementById('archive_filter_dept');
        if (!collegeSel || !deptSel) return;

        const collegeId = collegeSel.value;
        const currentDept = deptSel.value;
        let firstMatch = false;

        Array.from(deptSel.options).forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }
            const parent = opt.getAttribute('data-parent');
            const matches = !collegeId || parent === collegeId;
            opt.hidden = !matches;
            opt.disabled = !matches;
            if (matches && opt.value === currentDept) {
                firstMatch = true;
            }
        });

        if (!keepSelected || !firstMatch) {
            if (collegeId && !firstMatch) {
                deptSel.value = '';
            }
        }
    }

    window.addEventListener('resize', updateScrollIndicator);

    function upgradeDocVersion(doc) {
        document.getElementById('form-upgrade-version').action = '/archive/' + doc.document_id + '/versions';
        document.getElementById('upgrade_doc_title').innerText = doc.document_title || 'وثيقة';
        const empName = doc.employee ? doc.employee.full_name_ar : 'سجل عام';
        document.getElementById('upgrade_doc_meta').innerText = 'الكادر: ' + empName + ' | عدد الإصدارات: ' + (doc.versions ? doc.versions.length : 1);
        document.getElementById('upgradeVersionModal').classList.remove('hidden');
    }
</script>
@endsection
