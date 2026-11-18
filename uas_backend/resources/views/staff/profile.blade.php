@extends('layouts.app')

@section('title', 'الملف الموحد وعارض الأرشيف | نظام الأرشفة الجامعي')
@section('page_title', 'الملف الموحد وعارض الأرشيف الرقمي الشجري')
@section('page_subtitle', 'استعراض البيانات الشخصية والأكاديمية وشجرة وثائق الـ PDF المدمجة الحقيقية')

@section('content')

@if(session('success'))
<div class="mb-6 p-4 bg-emerald-50 border-r-4 border-univ-600 rounded-xl text-univ-800 text-sm flex items-center justify-between shadow-sm">
    <div class="flex items-center">
        <i data-lucide="check-circle-2" class="w-5 h-5 ml-2 text-univ-600"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
</div>
@endif

<!-- مساحة العمل المزدوجة (Split View) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- الجانب الأيمن: بطاقة الموظف + شجرة الأرشيف الرقمي (5 أعمدة) -->
    <div class="lg:col-span-5 space-y-6">

        <!-- بطاقة هوية الكادر -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 rounded-2xl bg-univ-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-univ-600/30 ml-4">
                        {{ mb_substr($staff->full_name, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $staff->full_name }}</h3>
                        <p class="text-xs font-semibold text-univ-700 mt-0.5">
                            @if($staff->academicDetails)
                                {{ $staff->academicDetails->rank->name ?? 'عضو هيئة تدريس' }} ({{ $staff->academicDetails->exact_specialization ?? '' }})
                            @elseif($staff->administrativeDetails)
                                {{ $staff->administrativeDetails->job_title }}
                            @else
                                متعاقد بالساعات
                            @endif
                        </p>
                        <p class="text-xs text-slate-400 mt-1">الرقم الوظيفي: <span class="font-mono font-bold text-slate-700">{{ $staff->job_number }}</span></p>
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <span class="bg-emerald-50 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-bold border border-emerald-200 shadow-sm">
                        {{ $staff->job_status }}
                    </span>
                    <span class="text-[10px] text-slate-400 mt-1.5 flex items-center font-medium">
                        <img src="{{ asset('images/ibb_logo.png') }}" class="w-4 h-4 ml-1 object-contain">
                        جامعة إب
                    </span>
                </div>
            </div>

            <!-- تفاصيل إضافية -->
            <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block mb-0.5">الجهة العليا</span>
                    <span class="font-bold text-slate-700">{{ $staff->department->entity->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">القسم / الوحدة</span>
                    <span class="font-bold text-slate-700">{{ $staff->department->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">الرقم الوطني</span>
                    <span class="font-bold text-slate-700 font-mono">{{ $staff->national_id }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">تاريخ التعيين</span>
                    <span class="font-bold text-slate-700">{{ $staff->hire_date ? $staff->hire_date->format('Y/m/d') : '2012/09/01' }}</span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                <a href="{{ route('reports.index', ['staff_id' => $staff->id]) }}" class="text-univ-700 hover:text-univ-900 flex items-center">
                    <i data-lucide="printer" class="w-4 h-4 ml-1"></i>
                    <span>طباعة بيان الحالة المعتمد</span>
                </a>
                <span class="text-slate-400 font-mono">هاتف: {{ $staff->phone ?? 'لا يوجد' }}</span>
            </div>
        </div>

        <!-- شجرة الأرشيف الرقمي التفاعلية (The Interactive Archiving Tree) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center">
                    <i data-lucide="folder-tree" class="w-4 h-4 text-univ-400 ml-2"></i>
                    <h4 class="font-bold text-sm">شجرة الوثائق الرقمية (DMS)</h4>
                </div>
                <span class="text-xs bg-slate-800 text-univ-400 px-2 py-0.5 rounded font-mono">{{ $staff->archives->count() }} وثيقة</span>
            </div>

            <div class="p-4 space-y-3 text-sm">
                <!-- فرع 1: القرارات الإدارية -->
                <div>
                    <div class="flex items-center text-slate-700 font-bold text-xs py-1 cursor-pointer">
                        <i data-lucide="folder" class="w-4 h-4 text-amber-500 ml-1.5"></i>
                        <span>📁 القرارات الإدارية الرسمية</span>
                    </div>
                    <div class="pr-5 mt-1 space-y-1">
                        @forelse($staff->decisions as $dec)
                        <button onclick="previewDecision('{{ $dec->decision_type }} رقم {{ $dec->decision_number }}', '{{ $dec->decision_number }}', '{{ $dec->issued_date }}', '{{ addslashes($dec->notes) }}')" 
                                class="w-full text-right flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-200 text-xs">
                            <div class="flex items-center">
                                <i data-lucide="file-text" class="w-4 h-4 text-blue-600 ml-2"></i>
                                <div>
                                    <span class="font-semibold text-slate-800 block">{{ $dec->decision_type }} ({{ $dec->decision_number }})</span>
                                    <span class="text-slate-400 text-[10px]">تاريخ: {{ $dec->issued_date }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full font-bold">عرض الوثيقة</span>
                        </button>
                        @empty
                            <p class="text-[11px] text-slate-400 pr-2">لا توجد قرارات مسجلة.</p>
                        @endforelse
                    </div>
                </div>

                <!-- فرع 2: المؤهلات والشهادات -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center text-slate-700 font-bold text-xs py-1 cursor-pointer">
                        <i data-lucide="folder" class="w-4 h-4 text-emerald-500 ml-1.5"></i>
                        <span>📁 المؤهلات والشهادات العلمية</span>
                    </div>
                    <div class="pr-5 mt-1 space-y-1">
                        @forelse($staff->qualifications as $qual)
                        <button onclick="previewQualification('شهادة {{ $qual->degree_type }}', '{{ $qual->degree_type }}', '{{ $qual->graduation_year }}', '{{ $qual->granting_institution }} ({{ $qual->country }}) - تخصص: {{ $qual->specialization }} {{ $qual->thesis_title ? " - أطروحة: " . addslashes($qual->thesis_title) : "" }}')"
                                class="w-full text-right flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-200 text-xs">
                            <div class="flex items-center">
                                <i data-lucide="file-check" class="w-4 h-4 text-emerald-600 ml-2"></i>
                                <div>
                                    <span class="font-semibold text-slate-800 block">شهادة {{ $qual->degree_type }} ({{ $qual->specialization }})</span>
                                    <span class="text-slate-400 text-[10px]">{{ $qual->granting_institution }} ({{ $qual->graduation_year }})</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-bold">عرض الشهادة</span>
                        </button>
                        @empty
                            <p class="text-[11px] text-slate-400 pr-2">لا توجد مؤهلات مسجلة.</p>
                        @endforelse
                    </div>
                </div>

                <!-- فرع 3: ملفات الـ PDF الفعلية المخزنة بالسيرفر (digital_archives) -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center text-slate-700 font-bold text-xs py-1 cursor-pointer">
                        <i data-lucide="folder-archive" class="w-4 h-4 text-purple-500 ml-1.5"></i>
                        <span>📁 ملفات الـ PDF المؤرشفة بالسيرفر</span>
                    </div>
                    <div class="pr-5 mt-1 space-y-1">
                        @forelse($staff->archives as $arch)
                        <button onclick="previewActualPdf('{{ $arch->document_title }}', '{{ $arch->uuid }}', '{{ asset('storage/' . $arch->file_path) }}')"
                                class="w-full text-right flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-200 text-xs">
                            <div class="flex items-center">
                                <i data-lucide="file-text" class="w-4 h-4 text-red-600 ml-2"></i>
                                <div>
                                    <span class="font-semibold text-slate-800 block truncate max-w-[170px]">{{ $arch->document_title }}</span>
                                    <span class="text-slate-400 text-[10px] font-mono">UUID: {{ substr($arch->uuid, 0, 8) }}...</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-red-700 bg-red-50 px-2 py-0.5 rounded-full font-bold">فتح PDF</span>
                        </button>
                        @empty
                            <p class="text-[11px] text-slate-400 pr-2">لا توجد ملفات مرفوعة.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- الجانب الأيسر: عارض ملفات الـ PDF المدمج التفاعلي (7 أعمدة) -->
    <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-[750px]">
        
        <!-- شريط أدوات عارض الـ PDF -->
        <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center">
                <i data-lucide="file-text" class="w-5 h-5 text-univ-400 ml-2"></i>
                <div>
                    <h4 id="pdfTitle" class="font-bold text-sm">وثيقة قرار التعيين والترقية المعتمدة</h4>
                    <span id="pdfSub" class="text-xs text-slate-400 font-mono">قرار رسمي صادر عن جامعة إب</span>
                </div>
            </div>

            <div class="flex items-center space-x-2 space-x-reverse text-xs font-bold">
                <button onclick="window.print()" class="flex items-center bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg transition">
                    <i data-lucide="printer" class="w-4 h-4 ml-1"></i>
                    <span>طباعة</span>
                </button>
                <a id="btnDownloadPdf" href="{{ asset('storage/archives/1.PDF') }}" target="_blank" class="flex items-center bg-univ-600 hover:bg-univ-700 text-white px-3 py-1.5 rounded-lg transition">
                    <i data-lucide="download" class="w-4 h-4 ml-1"></i>
                    <span>تحميل الملف الأصلي</span>
                </a>
            </div>
        </div>

        <!-- مساحة العرض الفعلية المزدوجة (PDF Object / Decision Document View) -->
        <div class="flex-1 bg-slate-200/70 p-6 overflow-y-auto flex items-center justify-center relative">
            
            <!-- عارض ملفات الـ PDF الفعلية (Iframe / Object Viewer) -->
            <iframe id="pdfIframe" src="{{ asset('storage/archives/1.PDF') }}" class="w-full h-full rounded-xl border border-slate-300 shadow-md bg-white"></iframe>

            <!-- عارض نص القرار المنمق (بديل في حال الرغبة في معاينة نصية) -->
            <div id="styledDecisionView" class="hidden w-full max-w-[550px] bg-white shadow-2xl rounded-lg p-10 text-slate-800 border border-slate-300 min-h-[600px] flex flex-col justify-between">
                <div>
                    <div class="text-center border-b-2 border-slate-800 pb-4 mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-600">الجمهورية اليمنية<br>وزارة التعليم العالي<br>جامعة إب</span>
                            <div class="w-14 h-14 rounded-full border-2 border-univ-800 flex items-center justify-center font-black text-univ-800 text-xs">
                                شعار الجامعة
                            </div>
                            <span class="text-xs font-bold text-slate-600 font-mono">الرقم: <span id="docNum">ق/142</span><br>التاريخ: <span id="docDate">2024/09/15</span></span>
                        </div>
                        <h2 id="docHeading" class="text-base font-black text-slate-900 mt-3 underline decoration-univ-600 decoration-2 underline-offset-4">قرار إداري رسمي</h2>
                    </div>

                    <div class="text-xs leading-relaxed space-y-3 text-justify">
                        <p><strong>رئيس جامعة إب:</strong></p>
                        <p>- بعد الاطلاع على قانون الجامعات اليمنية ولائحته التنفيذية ولائحة جامعة إب.</p>
                        
                        <div class="p-3 bg-slate-50 border-r-4 border-univ-600 rounded-md my-4">
                            <h4 class="font-bold text-slate-900 mb-1">《 قـــــــــرر 》</h4>
                            <p id="docContentText" class="text-slate-700 leading-relaxed"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-slate-200 flex items-end justify-between">
                    <div class="text-center">
                        <div class="w-16 h-16 border-2 border-dashed border-red-500 rounded-full flex items-center justify-center text-[9px] text-red-600 font-bold rotate-[-12deg]">
                            خاتم الأرشيف<br>جامعة إب
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-bold text-slate-800">أ.د / رئيس جامعة إب</p>
                        <p class="font-cursive text-slate-600 text-xs mt-2 underline">الاعتماد والتوقيع</p>
                    </div>
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
        
        // إظهار الـ Iframe وعرض ملف الـ PDF الحقيقي
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
        document.getElementById('pdfSub').innerText = 'الدرجة: ' + degree + ' | سنة المنح: ' + year;
        
        document.getElementById('docNum').innerText = 'مؤهل ' + year;
        document.getElementById('docDate').innerText = year;
        document.getElementById('docHeading').innerText = title;
        document.getElementById('docContentText').innerText = details;
        
        document.getElementById('pdfIframe').classList.add('hidden');
        document.getElementById('styledDecisionView').classList.remove('hidden');
    }
</script>
@endpush
