@extends('layouts.app')

@section('title', 'الملف الموحد وعارض الأرشيف المكتبي — ' . $employee->full_name_ar)

@section('content')
<div class="flex-1 flex flex-col gap-2 min-h-0">

    @php 
        $archivedDocs = $employee->documents
            ->where('document_id', '!=', $employee->photo_document_id)
            ->sortByDesc('created_at');
        $firstDoc = $archivedDocs->first(fn($d) => $d->currentVersion && $d->currentVersion->file_size_bytes > 0) ?? $archivedDocs->first();
        $firstVersion = $firstDoc?->currentVersion;
        $curr = $employee->currentEmployment ?? $employee->employmentRecords->first();
    @endphp

    <!-- شريط ترويسة الكادر المريح الواسع -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-5 px-6 flex flex-wrap lg:flex-nowrap items-center justify-between gap-4 shrink-0 select-none">
        
        <!-- هوية الكادر -->
        <div class="flex items-center gap-4 min-w-0">
            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 font-bold text-base shrink-0 shadow-xs">
                @if($employee->photo_document_id && $employee->photoDocument?->currentVersion)
                    <img src="{{ route('archive.preview', $employee->photoDocument->currentVersion->document_version_id) }}" class="w-full h-full object-cover" alt="{{ $employee->full_name_ar }}">
                @elseif($employee->gender == 'female')
                    <i class="fa-solid fa-user text-[#14532d] text-xl"></i>
                @else
                    <i class="fa-solid fa-user-tie text-[#14532d] text-xl"></i>
                @endif
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight truncate">
                        {{ $employee->full_name_ar }}
                    </h1>
                    
                    @if($curr?->academicRank)
                        <span class="text-xs text-slate-700 bg-slate-100 border border-slate-200 px-3 py-1 rounded-xl font-bold">
                            {{ $curr->academicRank->rank_name_ar }}
                        </span>
                    @endif

                    <span class="text-xs text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-xl font-bold">
                        {{ $curr?->employmentStatus?->status_name_ar ?? 'على رأس العمل' }}
                    </span>
                </div>

                <div class="flex items-center gap-3 text-sm text-slate-600 flex-wrap mt-1">
                    <span>الرقم الوظيفي: <strong class="font-mono text-slate-900 font-bold">{{ $employee->employee_number ?? '—' }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>الرقم الوطني: <strong class="font-mono text-slate-900 font-bold">{{ $employee->national_id ?? '—' }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span class="text-slate-800 font-bold truncate max-w-[320px]">
                        {{ $curr?->organizationalUnit?->unit_name_ar ?? 'غير مسكن' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- أزرار التحكم وأوضاع العرض (أيقونات معبرة ومألوفة بتصميم مدمج) -->
        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
            
            <!-- أوضاع العرض: مزدوج، ملف فقط، وثيقة فقط -->
            <div class="inline-flex items-center bg-slate-100 border border-slate-200 rounded-xl p-1 text-sm font-semibold">
                <button type="button" onclick="setViewMode('split')" id="btn-mode-split" 
                        class="w-9 h-9 rounded-lg transition bg-white text-brand-900 shadow-xs cursor-pointer flex items-center justify-center" 
                        title="عرض مزدوج (ملف الكادر وعارض الوثيقة)">
                    <i class="fa-solid fa-table-columns text-sm"></i>
                </button>
                <button type="button" onclick="setViewMode('profile')" id="btn-mode-profile" 
                        class="w-9 h-9 rounded-lg transition text-slate-500 hover:text-slate-900 cursor-pointer flex items-center justify-center" 
                        title="ملف الكادر فقط">
                    <i class="fa-solid fa-id-card text-sm"></i>
                </button>
                <button type="button" onclick="setViewMode('viewer')" id="btn-mode-viewer" 
                        class="w-9 h-9 rounded-lg transition text-slate-500 hover:text-slate-900 cursor-pointer flex items-center justify-center" 
                        title="عارض الوثيقة فقط">
                    <i class="fa-solid fa-file-pdf text-sm"></i>
                </button>
            </div>

            <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

            <!-- أرشفة وثيقة جديدة -->
            <button type="button" onclick="openModal('modal-new-doc')" 
                    class="w-9 h-9 rounded-xl bg-brand-900 hover:bg-brand-800 text-white flex items-center justify-center transition shadow-xs cursor-pointer" 
                    title="أرشفة وثيقة رسمية جديدة">
                <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
            </button>

            <!-- تعديل البيانات الأساسية -->
            <button type="button" onclick="openModal('modal-edit-basic')" 
                    class="w-9 h-9 rounded-xl bg-white hover:bg-slate-100 text-slate-600 hover:text-brand-900 border border-slate-200 flex items-center justify-center transition shadow-2xs cursor-pointer" 
                    title="تعديل البيانات الأساسية">
                <i class="fa-solid fa-pen-to-square text-sm"></i>
            </button>

            <!-- طباعة بيان حالة رسمي -->
            <a href="{{ route('reports.index', ['employee_id' => $employee->employee_id]) }}" 
               class="w-9 h-9 rounded-xl bg-white hover:bg-slate-100 text-slate-600 hover:text-brand-900 border border-slate-200 flex items-center justify-center transition shadow-2xs cursor-pointer" 
               title="طباعة بيان حالة رسمي">
                <i class="fa-solid fa-print text-sm"></i>
            </a>

            <!-- العودة لدليل الكادر -->
            <a href="{{ route('employees.index') }}" 
               class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 border border-slate-200 flex items-center justify-center transition cursor-pointer" 
               title="العودة لقائمة الكادر">
                <i class="fa-solid fa-arrow-right text-sm"></i>
            </a>
        </div>
    </div>

    <!-- مساحة العمل الرئيسية -->
    <div id="main-workspace-grid" class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-3 min-h-0">

        <!-- لوحة ملف الكادر -->
        <div id="col-profile-panel" class="lg:col-span-6 bg-white border border-slate-200 rounded-2xl shadow-xs flex flex-col min-h-0 overflow-hidden">
            
            <!-- تبويبات الملف -->
            <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between shrink-0 select-none">
                <div class="flex items-center gap-2 overflow-x-auto">
                    <button type="button" onclick="switchProfileTab('tab-summary')" id="nav-tab-summary" class="profile-tab-btn active px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 bg-[#14532d] text-white shadow-xs cursor-pointer">
                        <i class="fa-solid fa-id-card"></i>
                        <span>البيانات الأساسية</span>
                    </button>
                    <button type="button" onclick="switchProfileTab('tab-docs')" id="nav-tab-docs" class="profile-tab-btn px-4 py-2 rounded-xl text-sm font-medium transition flex items-center gap-2 bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 cursor-pointer">
                        <i class="fa-solid fa-folder-closed text-slate-400"></i>
                        <span>الوثائق والأرشيف</span>
                        <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded-lg text-xs font-mono font-bold">{{ $archivedDocs->count() }}</span>
                    </button>
                    <button type="button" onclick="switchProfileTab('tab-quals')" id="nav-tab-quals" class="profile-tab-btn px-4 py-2 rounded-xl text-sm font-medium transition flex items-center gap-2 bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 cursor-pointer">
                        <i class="fa-solid fa-graduation-cap text-slate-400"></i>
                        <span>المؤهلات العلمية</span>
                        <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded-lg text-xs font-mono font-bold">{{ $employee->qualifications->count() }}</span>
                    </button>
                    <button type="button" onclick="switchProfileTab('tab-history')" id="nav-tab-history" class="profile-tab-btn px-4 py-2 rounded-xl text-sm font-medium transition flex items-center gap-2 bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 cursor-pointer">
                        <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                        <span>السجل الوظيفي</span>
                        <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded-lg text-xs font-mono font-bold">{{ $employee->employmentRecords->count() }}</span>
                    </button>
                </div>
            </div>

            <!-- محتوى التبويبات -->
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                
                <!-- التبويب 1: البيانات والتسكين -->
                <div id="content-tab-summary" class="profile-tab-content space-y-4">
                    
                    <!-- البيانات الشخصية -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <div class="bg-slate-50/80 border-b border-slate-200 px-5 py-3 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-user text-[#14532d]"></i> البيانات الشخصية
                            </h2>
                            <button type="button" onclick="openModal('modal-edit-basic')" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-500 hover:text-brand-900 flex items-center justify-center transition cursor-pointer shadow-2xs" title="تعديل البيانات الشخصية">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                        </div>
                        
                        <table class="w-full text-right text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الاسم</td>
                                    <td class="py-3 px-4 w-1/4 font-bold text-slate-900">{{ $employee->full_name_ar }}</td>
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الرقم الوظيفي</td>
                                    <td class="py-3 px-4 w-1/4 font-mono font-bold text-brand-900">{{ $employee->employee_number ?? '—' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الرقم الوطني</td>
                                    <td class="py-3 px-4 w-1/4 font-mono text-slate-900 font-bold">{{ $employee->national_id ?? '—' }}</td>
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">النوع</td>
                                    <td class="py-3 px-4 w-1/4 text-slate-900 font-bold">{{ $employee->gender == 'female' ? 'أنثى' : 'ذكر' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الميلاد</td>
                                    <td class="py-3 px-4 w-1/4 text-slate-900 font-medium">
                                        {{ $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '—' }}
                                        @if($employee->place_of_birth) <span class="text-slate-500 font-normal">({{ $employee->place_of_birth }})</span> @endif
                                    </td>
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الهاتف</td>
                                    <td class="py-3 px-4 w-1/4 font-mono text-slate-900 font-medium">{{ $employee->phone_number ?? '—' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">البريد</td>
                                    <td class="py-3 px-4 w-1/4 font-mono text-slate-700" colspan="{{ $employee->address ? 1 : 3 }}">{{ $employee->email ?? '—' }}</td>
                                    @if($employee->address)
                                        <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">العنوان</td>
                                        <td class="py-3 px-4 w-1/4 text-slate-900 font-medium">{{ $employee->address }}</td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- التسكين الإداري -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <div class="bg-slate-50/80 border-b border-slate-200 px-5 py-3 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-sitemap text-[#14532d]"></i> التسكين الإداري والوظيفي
                            </h2>
                            <button type="button" onclick="openModal('modal-new-assignment')" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-500 hover:text-brand-900 flex items-center justify-center transition cursor-pointer shadow-2xs" title="إضافة تسكين أو حركة وظيفية جديدة">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>

                        <table class="w-full text-right text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الجهة</td>
                                    <td class="py-3 px-4 w-1/4 font-bold text-slate-900">
                                        {{ $curr?->organizationalUnit?->parent?->unit_name_ar ?? $curr?->organizationalUnit?->unit_name_ar ?? '—' }}
                                    </td>
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">القسم</td>
                                    <td class="py-3 px-4 w-1/4 font-bold text-slate-900">
                                        {{ $curr?->organizationalUnit?->unit_name_ar ?? '—' }}
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الرتبة</td>
                                    <td class="py-3 px-4 w-1/4 font-bold text-brand-900">
                                        {{ $curr?->academicRank?->rank_name_ar ?? '—' }}
                                    </td>
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الدرجة</td>
                                    <td class="py-3 px-4 w-1/4 font-bold text-slate-800">
                                        {{ $curr?->jobGrade?->grade_name_ar ?? ($curr?->payroll_job_grade ?? '—') }}
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الفئة</td>
                                    <td class="py-3 px-4 w-1/4 font-medium text-slate-800">
                                        {{ $curr?->category?->category_name_ar ?? 'كادر أكاديمي' }}
                                    </td>
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">الحالة</td>
                                    <td class="py-3 px-4 w-1/4 font-bold text-emerald-800">
                                        {{ $curr?->employmentStatus?->status_name_ar ?? 'على رأس العمل' }}
                                        @if($curr?->appointment_basis)
                                            <span class="text-slate-400 font-normal mr-1 text-xs">({{ $curr->appointment_basis }})</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">المباشرة</td>
                                    <td class="py-3 px-4 w-1/4 font-mono font-bold text-slate-900">
                                        {{ $curr?->start_date ? $curr->start_date->format('Y-m-d') : '—' }}
                                    </td>
                                    <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">التعيين الأكاديمي</td>
                                    <td class="py-3 px-4 w-1/4 font-mono font-bold text-slate-900">
                                        {{ $curr?->academic_appointment_date ? $curr->academic_appointment_date->format('Y-m-d') : '—' }}
                                    </td>
                                </tr>
                                @if($curr?->payroll_status || $curr?->payroll_job_title)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">حالة المرتب</td>
                                        <td class="py-3 px-4 w-1/4 font-medium text-slate-900">{{ $curr->payroll_status ?? '—' }}</td>
                                        <td class="py-3 px-4 w-1/4 text-slate-600 font-semibold">مسمى المرتب</td>
                                        <td class="py-3 px-4 w-1/4 font-medium text-slate-900">{{ $curr->payroll_job_title ?? '—' }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- التبويب 2: الوثائق -->
                <div id="content-tab-docs" class="profile-tab-content hidden space-y-4">
                    
                    <!-- شريط تحكم الوثائق -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-3 px-4 shadow-xs flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-slate-900">
                            وثائق الأرشيف الإلكتروني ({{ $archivedDocs->count() }})
                        </span>

                        <div class="flex items-center gap-2">
                            <input type="text" id="docSearchInput" onkeyup="filterDocsTable(this.value)" placeholder="بحث سريع في الوثائق..." class="px-3.5 py-1.5 text-sm border border-slate-200 rounded-xl focus:border-brand-900 focus:outline-hidden w-52 transition bg-slate-50 focus:bg-white">
                            <button type="button" onclick="openModal('modal-new-doc')" class="w-8 h-8 rounded-xl bg-brand-900 hover:bg-brand-800 text-white flex items-center justify-center transition shadow-xs cursor-pointer" title="أرشفة وثيقة جديدة">
                                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- جدول الوثائق -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-right text-sm" id="table-all-documents">
                                <thead class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200 text-sm">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center text-slate-400">#</th>
                                        <th class="py-3 px-4">الوثيقة</th>
                                        <th class="py-3 px-4">النوع</th>
                                        <th class="py-3 px-4">التاريخ</th>
                                        <th class="py-3 px-4">الحجم</th>
                                        <th class="py-3 px-4 text-center">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($archivedDocs as $idx => $doc)
                                        @php
                                            $v = $doc->currentVersion;
                                            $hasFile = $v && $v->file_size_bytes > 0;
                                            $previewUrl = $v ? route('archive.preview', $v->document_version_id ?? $v->version_id) : '#';
                                            $downloadUrl = $v ? route('archive.download', $v->document_version_id ?? $v->version_id) : '#';
                                            $fileSize = $hasFile ? ($v->file_size_bytes >= 1048576 ? number_format($v->file_size_bytes / 1048576, 1) . ' MB' : number_format($v->file_size_bytes / 1024, 0) . ' KB') : null;
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition doc-row" data-search="{{ strtolower($doc->document_title . ' ' . ($doc->documentType?->type_name_ar ?? '') . ' ' . ($doc->document_number ?? '')) }}">
                                            <td class="py-3 px-4 text-center font-mono text-slate-400 text-sm">{{ $loop->iteration }}</td>
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-slate-900 flex items-center gap-2">
                                                    <i class="fa-solid fa-file-pdf text-[#14532d] text-base"></i>
                                                    <span>{{ $doc->document_title }}</span>
                                                    @if($doc->is_confidential)
                                                        <span class="text-xs text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-lg font-bold">سري</span>
                                                    @endif
                                                </div>
                                                @if($doc->issuing_authority)
                                                    <div class="text-xs text-slate-500 mt-0.5">
                                                        {{ $doc->issuing_authority }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-slate-700">
                                                {{ $doc->documentType?->type_name_ar ?? 'عام' }}
                                            </td>
                                            <td class="py-3 px-4 font-mono text-slate-700 text-sm">
                                                {{ $doc->document_date ? $doc->document_date->format('Y-m-d') : ($doc->created_at ? $doc->created_at->format('Y-m-d') : '—') }}
                                            </td>
                                            <td class="py-3 px-4">
                                                @if($hasFile)
                                                    <span class="font-mono text-sm text-slate-700 font-semibold">
                                                        {{ $fileSize }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-slate-400">بانتظار المسح</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="inline-flex items-center gap-1.5 justify-center">
                                                    @if($hasFile)
                                                        <button type="button" 
                                                                onclick="previewDocFromTable('{{ $previewUrl }}', '{{ addslashes($doc->document_title) }}', '{{ $downloadUrl }}', '{{ $doc->document_id }}')" 
                                                                class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 flex items-center justify-center transition cursor-pointer" 
                                                                title="معاينة في العارض">
                                                            <i class="fa-solid fa-eye text-xs"></i>
                                                        </button>
                                                        <a href="{{ $downloadUrl }}" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-brand-900 flex items-center justify-center transition" title="تنزيل الملف">
                                                            <i class="fa-solid fa-download text-xs"></i>
                                                        </a>
                                                    @endif
                                                    <form method="POST" action="{{ route('employees.documents.destroy', [$employee->employee_id, $doc->document_id]) }}" class="inline" onsubmit="return confirm('تأكيد حذف هذه الوثيقة؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer" title="حذف الوثيقة">
                                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="p-8 text-center text-slate-400 text-sm">
                                                لا توجد وثائق مؤرشفة لهذا الكادر.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- التبويب 3: المؤهلات -->
                <div id="content-tab-quals" class="profile-tab-content hidden space-y-4">
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <div class="bg-slate-50/80 border-b border-slate-200 px-5 py-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-graduation-cap text-[#14532d]"></i>
                                المؤهلات العلمية المسجلة ({{ $employee->qualifications->count() }})
                            </span>
                            <button type="button" onclick="openModal('modal-new-qual')" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-500 hover:text-brand-900 flex items-center justify-center transition cursor-pointer shadow-2xs" title="إضافة مؤهل علمي جديد">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                        <div class="p-4">
                            @if($employee->qualifications->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($employee->qualifications as $qual)
                                        <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex justify-between items-center text-sm shadow-2xs hover:border-slate-300 transition">
                                            <div class="space-y-1">
                                                <div class="font-bold text-slate-900 flex items-center gap-2 text-sm">
                                                    <span>{{ $qual->degree_title }}</span>
                                                    @if($qual->is_highest_qualification)
                                                        <span class="text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">أعلى مؤهل</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-600 flex flex-wrap items-center gap-2">
                                                    <span>{{ $qual->level?->level_name_ar ?? 'مؤهل' }}</span>
                                                    @if($qual->university)
                                                        <span>• {{ $qual->university->university_name_ar }}</span>
                                                    @endif
                                                    @if($qual->grade_or_gpa)
                                                        <span>• تقدير: {{ $qual->grade_or_gpa }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono text-sm font-bold text-slate-700 ml-1">
                                                    {{ $qual->graduation_date ? $qual->graduation_date->format('Y') : '—' }}
                                                </span>
                                                <button type="button" onclick="editQual({{ json_encode($qual) }})" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-brand-900 flex items-center justify-center transition cursor-pointer" title="تعديل المؤهل">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </button>
                                                <form method="POST" action="{{ route('employees.qualifications.destroy', [$employee->employee_id, $qual->qualification_id]) }}" onsubmit="return confirm('تأكيد حذف هذا المؤهل؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer" title="حذف المؤهل">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-10 text-center text-slate-400 text-sm">
                                    لا توجد مؤهلات مسجلة.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- التبويب 4: السجل الوظيفي -->
                <div id="content-tab-history" class="profile-tab-content hidden space-y-4">
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <div class="bg-slate-50/80 border-b border-slate-200 px-5 py-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left text-[#14532d]"></i>
                                السجل الوظيفي والتاريخي ({{ $employee->employmentRecords->count() }})
                            </span>
                            <button type="button" onclick="openModal('modal-new-assignment')" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-500 hover:text-brand-900 flex items-center justify-center transition cursor-pointer shadow-2xs" title="إضافة حركة في السجل الوظيفي">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-right text-sm">
                                <thead class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200 text-sm">
                                    <tr>
                                        <th class="py-3 px-4">الجهة</th>
                                        <th class="py-3 px-4">الرتبة / الدرجة</th>
                                        <th class="py-3 px-4">من تاريخ</th>
                                        <th class="py-3 px-4">إلى تاريخ</th>
                                        <th class="py-3 px-4">الحالة</th>
                                        <th class="py-3 px-4 text-center">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($employee->employmentRecords->sortByDesc('start_date') as $hist)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="py-3 px-4 font-bold text-slate-900">{{ $hist->organizationalUnit?->unit_name_ar }}</td>
                                            <td class="py-3 px-4 text-slate-800">{{ $hist->academicRank?->rank_name_ar ?? ($hist->jobGrade?->grade_name_ar ?? 'كادر') }}</td>
                                            <td class="py-3 px-4 font-mono text-slate-700 font-bold">{{ $hist->start_date ? $hist->start_date->format('Y-m-d') : '—' }}</td>
                                            <td class="py-3 px-4 font-mono text-slate-700">{{ $hist->end_date ? $hist->end_date->format('Y-m-d') : 'مستمر' }}</td>
                                            <td class="py-3 px-4">
                                                @if($hist->is_primary && is_null($hist->end_date))
                                                    <span class="text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                                                        حالي
                                                    </span>
                                                @else
                                                    <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                                                        سابق
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <button type="button" onclick="editAssignment({{ json_encode($hist) }})" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-brand-900 inline-flex items-center justify-center transition cursor-pointer" title="تعديل هذا السجل">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="p-8 text-center text-slate-400 text-sm">لا يوجد سجل تاريخي مسجل.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- لوحة عارض الوثائق -->
        <div id="col-viewer-panel" class="lg:col-span-6 bg-white border border-slate-200 rounded-2xl shadow-xs flex flex-col min-h-0 overflow-hidden allow-zoom" data-allow-zoom="true">
            
            <!-- ترويسة عارض الوثائق -->
            <div class="bg-[#14532d] text-white p-2.5 px-4 flex items-center justify-between shrink-0 select-none">
                <span class="font-bold text-xs flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-emerald-200"></i>
                    <span>عارض الوثائق الرسمي</span>
                    <span class="text-[11px] font-mono font-medium text-emerald-100">({{ $archivedDocs->count() }})</span>
                </span>
                <button type="button" onclick="openModal('modal-new-doc')" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer border border-white/20 shadow-xs" title="أرشفة وثيقة جديدة">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                </button>
            </div>

            <!-- شريط أزرار تبويبات الوثائق -->
            <div class="bg-slate-50 border-b border-slate-200 p-1.5 flex gap-1.5 overflow-x-auto shrink-0 select-none">
                @forelse($archivedDocs as $i => $doc)
                    @if($doc->currentVersion)
                        @php 
                            $isActive = ($doc->document_id === $firstDoc?->document_id); 
                            $v = $doc->currentVersion;
                            $hasFile = $v && $v->file_size_bytes > 0;
                        @endphp
                        <button type="button" 
                                data-doc-id="{{ $doc->document_id }}"
                                onclick="loadDesktopPdfViewer('{{ route('archive.preview', $v->document_version_id ?? $v->version_id) }}', '{{ addslashes($doc->document_title) }}', '{{ route('archive.download', $v->document_version_id ?? $v->version_id) }}', this, '{{ $doc->document_id }}')"
                                class="viewer-tab-btn px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5 cursor-pointer max-w-[150px] shrink-0 truncate {{ $isActive ? 'bg-[#14532d] text-white font-bold shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 font-medium' }}" 
                                title="{{ $doc->document_title }}">
                            <span class="truncate">{{ $doc->document_title }}</span>
                        </button>
                    @endif
                @empty
                    <span class="text-xs text-slate-400 p-1">لا توجد وثائق مؤرشفة.</span>
                @endforelse
            </div>

            <!-- شريط تحكم عارض الوثيقة وإطار العرض -->
            @if($firstDoc && $firstVersion)
                <div id="desktop-pdf-bar" class="bg-white border-b border-slate-200 px-3 py-1.5 text-xs text-slate-800 flex justify-between items-center shrink-0">
                    <span id="desktop-pdf-title" class="truncate font-bold max-w-[60%]" title="{{ $firstDoc->document_title }}">
                        {{ $firstDoc->document_title }}
                    </span>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <a id="desktop-pdf-download" href="{{ route('archive.download', $firstVersion->document_version_id ?? $firstVersion->version_id) }}" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-brand-900 flex items-center justify-center transition" title="تنزيل الملف الرقمي">
                            <i class="fa-solid fa-download text-xs"></i>
                        </a>
                        <a id="desktop-pdf-link" href="{{ route('archive.preview', $firstVersion->document_version_id ?? $firstVersion->version_id) }}" target="_blank" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-brand-900 flex items-center justify-center transition" title="فتح في نافذة مستقلة">
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                        </a>
                        <form id="viewer-delete-form" method="POST" action="{{ route('employees.documents.destroy', [$employee->employee_id, $firstDoc->document_id]) }}" class="inline" onsubmit="return confirm('تأكيد حذف هذه الوثيقة؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer" title="حذف الوثيقة">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <iframe id="desktop-pdf-frame" 
                        src="{{ route('archive.preview', $firstVersion->document_version_id ?? $firstVersion->version_id) }}" 
                        class="w-full flex-1 bg-slate-100 border-0 min-h-0" 
                        title="عارض الوثائق">
                </iframe>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-slate-400 p-8 text-center text-xs space-y-3 bg-slate-50/50">
                    <p class="font-bold text-slate-700 text-sm">لا توجد وثائق مؤرشفة</p>
                    <button type="button" onclick="openModal('modal-new-doc')" class="text-xs bg-primary hover:bg-primary-dark text-white font-bold px-3 py-1.5 rounded-md shadow-xs flex items-center gap-1.5 transition cursor-pointer">
                        <i class="fa-solid fa-plus"></i> إضافة وثيقة
                    </button>
                </div>
            @endif

        </div>

    </div>

</div>

<!-- ========================================================================= -->
<!-- النوافذ المنبثقة للعمليات الإدارية المباشرة (Clean Modal Dialogs)           -->
<!-- ========================================================================= -->

    {{-- النوافذ المنبثقة التفاعلية لإدارة ملف الكادر --}}
    @include('employees.modals.edit_basic')
    @include('employees.modals.new_assignment')
    @include('employees.modals.new_qual')
    @include('employees.modals.new_doc')
    @include('employees.modals.edit_qual')
    @include('employees.modals.edit_assignment')

@endsection

@section('scripts')
<script>
    // التبديل بين أوضاع العرض (View Modes: split | profile | viewer)
    function setViewMode(mode) {
        const grid = document.getElementById('main-workspace-grid');
        const profilePanel = document.getElementById('col-profile-panel');
        const viewerPanel = document.getElementById('col-viewer-panel');

        const btnSplit = document.getElementById('btn-mode-split');
        const btnProfile = document.getElementById('btn-mode-profile');
        const btnViewer = document.getElementById('btn-mode-viewer');

        // إعادة ضبط أزرار التبديل
        [btnSplit, btnProfile, btnViewer].forEach(b => {
            if (b) {
                b.className = 'px-2.5 py-1 rounded transition text-slate-600 hover:text-slate-900 cursor-pointer flex items-center gap-1.5';
            }
        });

        if (mode === 'profile') {
            // كادر كامل
            profilePanel.classList.remove('lg:col-span-6', 'hidden');
            profilePanel.classList.add('lg:col-span-12');
            viewerPanel.classList.add('hidden');
            if (btnProfile) btnProfile.className = 'px-2.5 py-1 rounded transition bg-white text-slate-900 shadow-xs font-bold cursor-pointer flex items-center gap-1.5';
        } else if (mode === 'viewer') {
            // وثيقة كاملة
            profilePanel.classList.add('hidden');
            viewerPanel.classList.remove('lg:col-span-6', 'hidden');
            viewerPanel.classList.add('lg:col-span-12');
            if (btnViewer) btnViewer.className = 'px-2.5 py-1 rounded transition bg-white text-slate-900 shadow-xs font-bold cursor-pointer flex items-center gap-1.5';
        } else {
            // عرض مزدوج متوازن 50 / 50
            profilePanel.classList.remove('hidden', 'lg:col-span-12');
            profilePanel.classList.add('lg:col-span-6');
            viewerPanel.classList.remove('hidden', 'lg:col-span-12');
            viewerPanel.classList.add('lg:col-span-6');
            if (btnSplit) btnSplit.className = 'px-2.5 py-1 rounded transition bg-white text-slate-900 shadow-xs font-bold cursor-pointer flex items-center gap-1.5';
        }
    }

    // التبديل بين تبويبات ملف الكادر (Profile Tabs)
    function switchProfileTab(tabId) {
        // إخفاء كل المحتويات
        document.querySelectorAll('.profile-tab-content').forEach(el => el.classList.add('hidden'));

        // إزالة التنشيط من جميع الأزرار
        document.querySelectorAll('.profile-tab-btn').forEach(b => {
            b.classList.remove('bg-primary', 'text-white', 'shadow-xs');
            b.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        });

        // تنشيط الزر والمحتوى المطلوب
        const activeNav = document.getElementById('nav-' + tabId);
        const activeContent = document.getElementById('content-' + tabId);

        if (tabId === 'tab-all') {
            // تفعيل عرض الكل
            const allContent = document.getElementById('content-tab-all');
            const summaryHtml = document.getElementById('content-tab-summary') ? document.getElementById('content-tab-summary').innerHTML : '';
            const docsHtml = document.getElementById('content-tab-docs') ? document.getElementById('content-tab-docs').innerHTML : '';
            const qualsHtml = document.getElementById('content-tab-quals') ? document.getElementById('content-tab-quals').innerHTML : '';
            const historyHtml = document.getElementById('content-tab-history') ? document.getElementById('content-tab-history').innerHTML : '';
            allContent.innerHTML = summaryHtml + docsHtml + qualsHtml + historyHtml;
            allContent.classList.remove('hidden');
        } else if (activeContent) {
            activeContent.classList.remove('hidden');
        }

        if (activeNav) {
            activeNav.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
            activeNav.classList.add('bg-primary', 'text-white', 'shadow-xs');
        }
    }

    // تصفية جدول الوثائق فورياً
    function filterDocsTable(query) {
        query = (query || '').toLowerCase().trim();
        document.querySelectorAll('#table-all-documents .doc-row').forEach(row => {
            const text = row.getAttribute('data-search') || '';
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    // تحميل وتحديث عارض الوثائق
    function loadDesktopPdfViewer(previewUrl, title, downloadUrl, tabEl, docId) {
        const frame = document.getElementById('desktop-pdf-frame');
        const titleEl = document.getElementById('desktop-pdf-title');
        const linkEl = document.getElementById('desktop-pdf-link');
        const downloadEl = document.getElementById('desktop-pdf-download');
        const deleteForm = document.getElementById('viewer-delete-form');

        if (frame) frame.src = previewUrl;
        if (titleEl) {
            titleEl.innerHTML = '<i class="fa-solid fa-file-pdf text-rose-600 shrink-0"></i> <span class="truncate font-bold">' + title + '</span>';
            titleEl.title = title;
        }
        if (linkEl) linkEl.href = previewUrl;
        if (downloadEl) downloadEl.href = downloadUrl;
        if (deleteForm && docId) {
            deleteForm.action = '/employees/{{ $employee->employee_id }}/documents/' + docId;
            deleteForm.classList.remove('hidden');
        }

        // إدارة الحالة النشطة للأزرار (Active Tab styling)
        if (tabEl) {
            document.querySelectorAll('.viewer-tab-btn').forEach(el => {
                el.classList.remove('bg-primary', 'text-white', 'shadow-xs');
                el.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
                const dot = el.querySelector('.w-1\\.5.h-1\\.5');
                if (dot) {
                    dot.classList.remove('bg-white');
                    dot.classList.add('bg-emerald-500');
                }
            });

            tabEl.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
            tabEl.classList.add('bg-primary', 'text-white', 'shadow-xs');
            const activeDot = tabEl.querySelector('.w-1\\.5.h-1\\.5');
            if (activeDot) {
                activeDot.classList.remove('bg-emerald-500');
                activeDot.classList.add('bg-white');
            }
        }
    }

    // معاينة الوثيقة من جدول كافة الوثائق
    function previewDocFromTable(previewUrl, title, downloadUrl, docId) {
        // فتح العارض المزدوج إذا كان مخفياً
        const viewerPanel = document.getElementById('col-viewer-panel');
        if (viewerPanel && viewerPanel.classList.contains('hidden')) {
            setViewMode('split');
        }
        const matchingTab = document.querySelector(`.viewer-tab-btn[data-doc-id="${docId}"]`);
        loadDesktopPdfViewer(previewUrl, title, downloadUrl, matchingTab, docId);
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    // تعبئة وفتح نافذة تعديل المؤهل
    function editQual(qual) {
        document.getElementById('form-edit-qual').action = '/employees/{{ $employee->employee_id }}/qualifications/' + qual.qualification_id;
        document.getElementById('edit_qual_level_id').value = qual.qualification_level_id || '';
        document.getElementById('edit_qual_degree_title').value = qual.degree_title || '';
        document.getElementById('edit_qual_university_name').value = (qual.university ? qual.university.university_name_ar : '') || '';
        document.getElementById('edit_qual_graduation_date').value = qual.graduation_date ? qual.graduation_date.substring(0, 10) : '';
        document.getElementById('edit_qual_grade_or_gpa').value = qual.grade_or_gpa || '';
        document.getElementById('edit_is_highest').checked = !!qual.is_highest_qualification;
        openModal('modal-edit-qual');
    }

    // تعبئة وفتح نافذة تعديل السجل الوظيفي والتسكين
    function editAssignment(hist) {
        document.getElementById('form-edit-assignment').action = '/employees/{{ $employee->employee_id }}/assignments/' + hist.employment_record_id;
        document.getElementById('edit_assign_category_id').value = hist.employee_category_id || '';
        document.getElementById('edit_assign_unit_id').value = hist.organizational_unit_id || '';
        document.getElementById('edit_assign_status_id').value = hist.employment_status_id || '';
        document.getElementById('edit_assign_rank_id').value = hist.academic_rank_id || '';
        document.getElementById('edit_assign_grade_id').value = hist.job_grade_id || '';
        document.getElementById('edit_assign_start_date').value = hist.start_date ? hist.start_date.substring(0, 10) : '';
        document.getElementById('edit_assign_end_date').value = hist.end_date ? hist.end_date.substring(0, 10) : '';
        document.getElementById('edit_assign_basis').value = hist.appointment_basis || '';
        document.getElementById('edit_assign_payroll_status').value = hist.payroll_status || '';
        document.getElementById('edit_assign_payroll_job_title').value = hist.payroll_job_title || '';
        document.getElementById('edit_assign_payroll_job_grade').value = hist.payroll_job_grade || '';
        document.getElementById('edit_assign_qual_appointment').value = hist.qualification_at_appointment || '';
        document.getElementById('edit_assign_first_date').value = hist.first_appointment_date ? hist.first_appointment_date.substring(0, 10) : '';
        document.getElementById('edit_assign_academic_date').value = hist.academic_appointment_date ? hist.academic_appointment_date.substring(0, 10) : '';
        openModal('modal-edit-assignment');
    }

    // إغلاق النوافذ عند الضغط على Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="modal-"]').forEach(el => el.classList.add('hidden'));
        }
    });

    // إغلاق النافذة عند الضغط على الخلفية المعتمة
    document.querySelectorAll('[id^="modal-"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
</script>
@endsection
