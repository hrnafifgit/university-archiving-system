@extends('layouts.app')

@section('title', 'الملف والأرشيف الرقمي | ' . $staff->full_name)
@section('page_title', 'الملف الرقمي وعارض الأرشيف')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

    <!-- الجانب الأيمن: بطاقة الموظف + شجرة الأرشيف (5 أعمدة) -->
    <div class="lg:col-span-5 space-y-4">

        <!-- بطاقة هوية الكادر - أبيض وأخضر داكن -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-brand-900 text-white flex items-center justify-center font-bold text-lg ml-3">
                        {{ mb_substr($staff->full_name, 0, 2) }}
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $staff->full_name }}</h2>
                        <span class="text-xs font-semibold text-brand-900">
                            @if($staff->academicDetails)
                                {{ $staff->academicDetails->rank->name ?? 'عضو هيئة تدريس' }}
                            @elseif($staff->administrativeDetails)
                                {{ $staff->administrativeDetails->job_title }}
                            @else
                                متعاقد
                            @endif
                        </span>
                    </div>
                </div>
                <span class="text-xs font-mono font-bold text-slate-500 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200">
                    {{ $staff->job_number }}
                </span>
            </div>

            <!-- معلومات الكلية والقسم -->
            <div class="grid grid-cols-2 gap-3 pt-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">الكلية / المركز</span>
                    <span class="font-bold text-slate-800">{{ $staff->department->entity->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">القسم</span>
                    <span class="font-bold text-slate-800">{{ $staff->department->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">الرقم الوطني</span>
                    <span class="font-mono text-slate-700">{{ $staff->national_id }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">الحالة</span>
                    <span class="font-bold text-brand-900">{{ $staff->job_status }}</span>
                </div>
            </div>

            <!-- رابط سريع لبيان الحالة -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('reports.index', ['staff_id' => $staff->id]) }}" class="text-brand-900 hover:underline font-bold flex items-center">
                    <i data-lucide="printer" class="w-3.5 h-3.5 ml-1"></i>
                    <span>بيان الحالة الرسمي</span>
                </a>
                <span class="text-slate-400 font-mono text-[11px]">{{ $staff->phone ?? '' }}</span>
            </div>
        </div>

        <!-- شجرة الوثائق الرقمية (DMS Tree) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <span class="font-bold text-xs text-slate-800 flex items-center">
                    <i data-lucide="folder-tree" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                    <span>وثائق الأرشيف</span>
                </span>
                <span class="text-[11px] font-mono text-brand-900 font-bold bg-brand-50 px-2 py-0.5 rounded">
                    {{ $staff->archives->count() }} ملف
                </span>
            </div>

            <div class="p-3 space-y-3 text-xs">
                
                <!-- 1. القرارات -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">القرارات الإدارية</span>
                    <div class="space-y-1">
                        @forelse($staff->decisions as $dec)
                        <button onclick="previewDecision('{{ $dec->decision_type }} رقم {{ $dec->decision_number }}', '{{ $dec->decision_number }}', '{{ $dec->issued_date }}', '{{ addslashes($dec->notes) }}')" 
                                class="w-full text-right flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 text-slate-700 transition">
                            <span class="font-semibold">{{ $dec->decision_type }} ({{ $dec->decision_number }})</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $dec->issued_date }}</span>
                        </button>
                        @empty
                            <span class="text-slate-400 text-[11px] block pr-1">لا توجد قرارات</span>
                        @endforelse
                    </div>
                </div>

                <!-- 2. المؤهلات -->
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 font-bold block mb-1">المؤهلات العلمية</span>
                    <div class="space-y-1">
                        @forelse($staff->qualifications as $qual)
                        <button onclick="previewQualification('شهادة {{ $qual->degree_type }}', '{{ $qual->degree_type }}', '{{ $qual->graduation_year }}', '{{ $qual->granting_institution }} ({{ $qual->country }}) - تخصص: {{ $qual->specialization }} {{ $qual->thesis_title ? " - أطروحة: " . addslashes($qual->thesis_title) : "" }}')"
                                class="w-full text-right flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 text-slate-700 transition">
                            <span class="font-semibold">{{ $qual->degree_type }} ({{ $qual->specialization }})</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $qual->graduation_year }}</span>
                        </button>
                        @empty
                            <span class="text-slate-400 text-[11px] block pr-1">لا توجد مؤهلات</span>
                        @endforelse
                    </div>
                </div>

                <!-- 3. ملفات PDF المخزنة بالسيرفر -->
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 font-bold block mb-1">ملفات PDF المؤرشفة</span>
                    <div class="space-y-1">
                        @forelse($staff->archives as $arch)
                        <button onclick="previewActualPdf('{{ $arch->document_title }}', '{{ $arch->uuid }}', '{{ asset('storage/' . $arch->file_path) }}')"
                                class="w-full text-right flex items-center justify-between p-2 rounded-lg hover:bg-brand-50 hover:text-brand-900 transition">
                            <span class="font-semibold truncate max-w-[180px]">{{ $arch->document_title }}</span>
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-brand-900"></i>
                        </button>
                        @empty
                            <span class="text-slate-400 text-[11px] block pr-1">لا توجد ملفات مرفوعة</span>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- الجانب الأيسر: عارض ملفات PDF (7 أعمدة) -->
    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[700px]">
        
        <!-- شريط أدوات العارض -->
        <div class="p-3.5 bg-white border-b border-slate-200 flex items-center justify-between flex-shrink-0">
            <div>
                <h3 id="pdfTitle" class="font-bold text-xs text-slate-800">معاينة الوثيقة المحددة</h3>
                <span id="pdfSub" class="text-[10px] text-slate-400 font-mono">جامعة إب - الأرشيف الإلكتروني</span>
            </div>

            <div class="flex items-center space-x-1.5 space-x-reverse">
                <button onclick="window.print()" class="p-1.5 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition" title="طباعة">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                </button>
                <a id="btnDownloadPdf" href="{{ asset('storage/archives/1.PDF') }}" target="_blank" class="p-1.5 bg-brand-900 hover:bg-brand-800 text-white rounded-lg transition" title="تحميل">
                    <i data-lucide="download" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        <!-- مساحة العرض -->
        <div class="flex-1 bg-slate-50 p-4 overflow-y-auto flex items-center justify-center relative">
            <iframe id="pdfIframe" src="{{ asset('storage/archives/1.PDF') }}" class="w-full h-full rounded-xl border border-slate-200 bg-white"></iframe>

            <!-- معاينة نص القرار كبديل -->
            <div id="styledDecisionView" class="hidden w-full max-w-lg bg-white rounded-xl p-8 border border-slate-200 text-slate-800 space-y-4">
                <div class="border-b border-slate-200 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block">جامعة إب</span>
                        <h4 id="docHeading" class="font-bold text-sm text-brand-900">قرار إداري رسمي</h4>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-500" id="docNum"></span>
                </div>
                <p id="docContentText" class="text-xs leading-relaxed text-slate-700"></p>
                <div class="pt-3 border-t border-slate-100 text-[10px] text-slate-400 flex items-center justify-between">
                    <span>التاريخ: <span id="docDate" class="font-mono"></span></span>
                    <span class="text-brand-900 font-bold">معتمد إلكترونياً</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function previewActualPdf(title, uuid, fileUrl) {
        document.getElementById('pdfTitle').innerText = title;
        document.getElementById('pdfSub').innerText = 'UUID: ' + uuid;
        document.getElementById('btnDownloadPdf').href = fileUrl;
        
        document.getElementById('pdfIframe').classList.remove('hidden');
        document.getElementById('styledDecisionView').classList.add('hidden');
        document.getElementById('pdfIframe').src = fileUrl;
    }

    function previewDecision(title, number, date, notes) {
        document.getElementById('pdfTitle').innerText = title;
        document.getElementById('pdfSub').innerText = 'رقم القرار: ' + number + ' | تاريخ الصدور: ' + date;
        
        document.getElementById('docNum').innerText = number;
        document.getElementById('docDate').innerText = date;
        document.getElementById('docHeading').innerText = title;
        document.getElementById('docContentText').innerText = notes || 'قرار معتمد من رئاسة جامعة إب للشؤون الأكاديمية.';
        
        document.getElementById('pdfIframe').classList.add('hidden');
        document.getElementById('styledDecisionView').classList.remove('hidden');
    }

    function previewQualification(title, degree, year, details) {
        document.getElementById('pdfTitle').innerText = title;
        document.getElementById('pdfSub').innerText = 'الدرجة: ' + degree + ' | سنة: ' + year;
        
        document.getElementById('docNum').innerText = 'مؤهل ' + year;
        document.getElementById('docDate').innerText = year;
        document.getElementById('docHeading').innerText = title;
        document.getElementById('docContentText').innerText = details;
        
        document.getElementById('pdfIframe').classList.add('hidden');
        document.getElementById('styledDecisionView').classList.remove('hidden');
    }
</script>
@endpush
