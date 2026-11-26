@extends('layouts.app')

@section('title', 'الملف الوظيفي والأرشيف الإلكتروني — ' . $employee->full_name_ar)

@section('content')
<div class="flex-1 flex flex-col gap-2 min-h-0">

    @php 
        $archivedDocs = $employee->documents
            ->where('document_id', '!=', $employee->photo_document_id)
            ->sortByDesc('created_at');
        $firstDoc = $archivedDocs->first(fn($d) => $d->currentVersion && $d->currentVersion->file_size_bytes > 0) ?? $archivedDocs->first();
        $firstVersion = $firstDoc?->currentVersion;
        $curr = $employee->active_employment ?? $employee->currentEmployment ?? $employee->employmentRecords->sortByDesc('start_date')->first();
    @endphp

    <!-- شريط ترويسة الكادر القياسي -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-3.5 px-5 flex flex-wrap lg:flex-nowrap items-center justify-between gap-4 shrink-0 select-none">
        
        <!-- هوية الكادر -->
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-12 h-12 rounded-xl overflow-hidden bg-emerald-50/70 border border-emerald-200/80 flex items-center justify-center text-[#14532d] shrink-0 shadow-2xs">
                @if($employee->photo_document_id && $employee->photoDocument?->currentVersion)
                    <img src="{{ route('archive.preview', $employee->photoDocument->currentVersion->document_version_id) }}" class="w-full h-full object-cover" alt="{{ $employee->full_name_ar }}">
                @elseif($employee->gender == 'female')
                    <i class="fa-solid fa-user text-lg"></i>
                @else
                    <i class="fa-solid fa-user-tie text-lg"></i>
                @endif
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg font-black text-slate-900 tracking-tight truncate">
                        {{ $employee->full_name_ar }}
                    </h1>
                    
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $curr?->employmentStatus?->status_name_ar ?? 'على رأس العمل' }}
                    </span>
                </div>

                <div class="flex items-center gap-2.5 text-xs text-slate-600 mt-1">
                    <span class="inline-flex items-center gap-1.5 font-mono font-bold text-slate-800 bg-slate-100/80 px-2 py-0.5 rounded-md border border-slate-200/70" title="الرقم الوظيفي">
                        <i class="fa-solid fa-id-badge text-slate-400 text-[11px]"></i>
                        <span>{{ $employee->employee_number ?? '—' }}</span>
                    </span>

                    @if($curr?->academicRank)
                        <span class="inline-flex items-center gap-1.5 font-bold text-slate-700 bg-slate-100/80 px-2.5 py-0.5 rounded-md border border-slate-200/70" title="الرتبة العلمية">
                            <i class="fa-solid fa-award text-amber-600 text-[11px]"></i>
                            <span>{{ $curr->academicRank->rank_name_ar }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- أزرار التحكم وأوضاع العرض (أيقونات معبرة ومألوفة بتصميم مدمج) -->
        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
            
            <!-- أوضاع العرض: مزدوج، ملف فقط، بطاقة الرفع -->
            <div class="inline-flex items-center bg-slate-100 border border-slate-200 rounded-xl p-1 text-sm font-semibold">
                <button type="button" onclick="setViewMode('split')" id="btn-mode-split" 
                        class="w-9 h-9 rounded-lg transition bg-white text-brand-900 shadow-xs cursor-pointer flex items-center justify-center" 
                        title="عرض مزدوج (ملف الكادر وبطاقة رفع الملف)">
                    <i class="fa-solid fa-table-columns text-sm"></i>
                </button>
                <button type="button" onclick="setViewMode('profile')" id="btn-mode-profile" 
                        class="w-9 h-9 rounded-lg transition text-slate-500 hover:text-slate-900 cursor-pointer flex items-center justify-center" 
                        title="ملف الكادر فقط">
                    <i class="fa-solid fa-id-card text-sm"></i>
                </button>
                <button type="button" onclick="setViewMode('viewer')" id="btn-mode-viewer" 
                        class="w-9 h-9 rounded-lg transition text-slate-500 hover:text-slate-900 cursor-pointer flex items-center justify-center" 
                        title="بطاقة رفع الملف فقط">
                    <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                </button>
            </div>

            <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

            <!-- أرشفة وثيقة جديدة -->
            <button type="button" onclick="focusUploadCard()" 
                    class="w-9 h-9 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white flex items-center justify-center transition shadow-xs cursor-pointer" 
                    title="بطاقة رفع وأرشفة ملف">
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
            <div class="bg-slate-50 border-b border-slate-200 px-3 py-2 flex items-center justify-between shrink-0 select-none">
                <div class="flex items-center gap-1.5 w-full overflow-x-auto no-scrollbar">
                    <button type="button" onclick="switchProfileTab('tab-summary')" id="nav-tab-summary" class="profile-tab-btn active px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-[#14532d] text-white shadow-xs cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-id-card"></i>
                        <span>البيانات</span>
                    </button>
                    <button type="button" onclick="switchProfileTab('tab-docs')" id="nav-tab-docs" class="profile-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-folder-closed text-slate-400"></i>
                        <span>الوثائق</span>
                        <span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold">{{ $archivedDocs->count() }}</span>
                    </button>
                    <button type="button" onclick="switchProfileTab('tab-quals')" id="nav-tab-quals" class="profile-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-graduation-cap text-slate-400"></i>
                        <span>المؤهلات</span>
                        <span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold">{{ $employee->qualifications->count() }}</span>
                    </button>
                    <button type="button" onclick="switchProfileTab('tab-history')" id="nav-tab-history" class="profile-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                        <span>السجل</span>
                        <span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold">{{ $employee->employmentRecords->count() }}</span>
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
                        
                        <table class="w-full text-right text-xs">
                            <tbody class="divide-y divide-slate-100">
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap w-24">الاسم</td>
                                    <td class="py-2.5 px-3.5 font-bold text-slate-900">{{ $employee->full_name_ar }}</td>
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap w-24">الرقم الوظيفي</td>
                                    <td class="py-2.5 px-3.5 font-mono font-bold text-[#14532d]">{{ $employee->employee_number ?? '—' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">الرقم الوطني</td>
                                    <td class="py-2.5 px-3.5 font-mono text-slate-900 font-bold">{{ $employee->national_id ?? '—' }}</td>
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">النوع</td>
                                    <td class="py-2.5 px-3.5 text-slate-900 font-bold">{{ $employee->gender == 'female' ? 'أنثى' : 'ذكر' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">الميلاد</td>
                                    <td class="py-2.5 px-3.5 text-slate-900 font-medium whitespace-nowrap">
                                        {{ $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '—' }}
                                        @if($employee->place_of_birth) <span class="text-slate-400">({{ $employee->place_of_birth }})</span> @endif
                                    </td>
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">الهاتف</td>
                                    <td class="py-2.5 px-3.5 font-mono text-slate-900 font-medium">{{ $employee->phone_number ?? '—' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">البريد</td>
                                    <td class="py-2.5 px-3.5 font-mono text-slate-700" colspan="{{ $employee->address ? 1 : 3 }}">{{ $employee->email ?? '—' }}</td>
                                    @if($employee->address)
                                        <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">العنوان</td>
                                        <td class="py-2.5 px-3.5 text-slate-900 font-medium">{{ $employee->address }}</td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- التسكين الإداري -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <div class="bg-slate-50/80 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between">
                            <h2 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-sitemap text-[#14532d]"></i> التسكين الإداري
                            </h2>
                            <button type="button" onclick="openModal('modal-new-assignment')" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-500 hover:text-[#14532d] flex items-center justify-center transition cursor-pointer shadow-2xs" title="إضافة حركة وظيفية">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>

                        <table class="w-full text-right text-xs">
                            <tbody class="divide-y divide-slate-100">
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap w-24">الجهة</td>
                                    <td class="py-2.5 px-3.5 font-bold text-slate-900">
                                        {{ $curr?->organizationalUnit?->parent?->unit_name_ar ?? $curr?->organizationalUnit?->unit_name_ar ?? '—' }}
                                    </td>
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap w-24">القسم</td>
                                    <td class="py-2.5 px-3.5 font-bold text-slate-900">
                                        {{ $curr?->organizationalUnit?->unit_name_ar ?? '—' }}
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">الرتبة</td>
                                    <td class="py-2.5 px-3.5 font-bold text-[#14532d]">
                                        {{ $curr?->academicRank?->rank_name_ar ?? '—' }}
                                    </td>
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">الدرجة</td>
                                    <td class="py-2.5 px-3.5 font-bold text-slate-800">
                                        {{ $curr?->jobGrade?->grade_name_ar ?? ($curr?->payroll_job_grade ?? '—') }}
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">الفئة</td>
                                    <td class="py-2.5 px-3.5 font-medium text-slate-800">
                                        {{ $curr?->category?->category_name_ar ?? 'كادر أكاديمي' }}
                                    </td>
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">الحالة</td>
                                    <td class="py-2.5 px-3.5 font-bold text-emerald-800">
                                        {{ $curr?->employmentStatus?->status_name_ar ?? 'على رأس العمل' }}
                                        @if($curr?->appointment_basis)
                                            <span class="text-slate-400 font-normal mr-1">({{ $curr->appointment_basis }})</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/40">
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">المباشرة</td>
                                    <td class="py-2.5 px-3.5 font-mono font-bold text-slate-900">
                                        {{ $curr?->start_date ? $curr->start_date->format('Y-m-d') : '—' }}
                                    </td>
                                    <td class="py-2.5 px-3.5 bg-slate-50/70 text-slate-500 font-semibold whitespace-nowrap">التعيين</td>
                                    <td class="py-2.5 px-3.5 font-mono font-bold text-slate-900">
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
                                                    @if($hasFile)
                                                        <a href="{{ $previewUrl }}" target="_blank" class="hover:text-brand-900 hover:underline flex items-center gap-1.5 transition text-slate-900 font-bold" title="انقر لفتح وعرض الوثيقة في نافذة مستقلة">
                                                            <span>{{ $doc->document_title }}</span>
                                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                                                        </a>
                                                    @else
                                                        <span>{{ $doc->document_title }}</span>
                                                    @endif
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
                                                        <a href="{{ $previewUrl }}" target="_blank" 
                                                           class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 flex items-center justify-center transition cursor-pointer shadow-2xs" 
                                                           title="فتح وعرض في صفحة مستقلة">
                                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                                        </a>
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
                                                @php
                                                    $qualOrder = $qual->level?->level_order ?? 0;
                                                    $higherQual = $employee->qualifications->first(function($q) use ($qual, $qualOrder) {
                                                        return $q->qualification_id !== $qual->qualification_id && ($q->level?->level_order ?? 0) > $qualOrder;
                                                    });
                                                @endphp
                                                @if($higherQual)
                                                    <button type="button" 
                                                            onclick="alert('لا يمكن حذف مؤهل ({{ addslashes($qual->degree_title) }}) نظراً لوجود مؤهل أعلى منه ممنوح للكادر ({{ addslashes($higherQual->level?->level_name_ar ?? $higherQual->degree_title) }}). يجب حذف المؤهل الأعلى أولاً.')" 
                                                            class="w-7 h-7 rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed flex items-center justify-center transition" 
                                                            title="لا يمكن حذف هذا المؤهل لوجود مؤهل أعلى منه ممنوح للكادر">
                                                        <i class="fa-solid fa-lock text-xs text-slate-400"></i>
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('employees.qualifications.destroy', [$employee->employee_id, $qual->qualification_id]) }}" onsubmit="return confirm('تأكيد حذف هذا المؤهل؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer" title="حذف المؤهل">
                                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
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

        <!-- بطاقة رفع وثيقة -->
        <div id="col-viewer-panel" class="lg:col-span-6 bg-white border border-slate-200 rounded-2xl shadow-xs flex flex-col min-h-0 overflow-hidden">
            
            <!-- ترويسة بطاقة رفع الملف -->
            <div class="bg-[#14532d] text-white p-3 px-5 flex items-center justify-between shrink-0 select-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-emerald-200 border border-white/15">
                        <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                    </div>
                    <h2 class="font-bold text-sm">رفع وثيقة</h2>
                </div>
                <span class="text-xs font-mono font-bold bg-white/15 px-2.5 py-1 rounded-xl text-emerald-50 border border-white/20 flex items-center gap-1.5">
                    <i class="fa-solid fa-folder-open text-[10px]"></i>
                    <span>{{ $archivedDocs->count() }}</span>
                </span>
            </div>

            <!-- جسم البطاقة -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4">
                
                <!-- استمارة رفع الوثيقة -->
                <form id="uploadCardForm" method="POST" action="{{ route('employees.documents.store', $employee->employee_id) }}" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf

                    <!-- اختيار نوع الوثيقة -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-tag text-[#14532d]"></i>
                                <span>نوع الوثيقة</span>
                                <span class="text-rose-600 font-bold">*</span>
                            </label>
                            <button type="button" onclick="openQuickAdd('doc_type', 'upload_card_doc_type', 'نوع الوثيقة الجديد:', 'إضافة نوع')" class="text-[#14532d] hover:bg-emerald-100 px-2 py-0.5 bg-emerald-50 rounded-lg text-[11px] font-bold flex items-center gap-1 transition cursor-pointer border border-emerald-200" title="إضافة نوع جديد">
                                <i class="fa-solid fa-plus text-[10px]"></i> جديد
                            </button>
                        </div>
                        <select name="document_type_id" id="upload_card_doc_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-[#14532d] focus:outline-hidden transition">
                            <option value="" disabled selected>اختر نوع الوثيقة...</option>
                            @foreach($docTypes as $dtp)
                                <option value="{{ $dtp->document_type_id }}">{{ $dtp->type_name_ar }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- عنوان الوثيقة -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-heading text-[#14532d]"></i>
                            <span>عنوان الوثيقة</span>
                            <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" name="document_title" id="upload_card_doc_title" required placeholder="أدخل العنوان..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-[#14532d] focus:outline-hidden transition">
                    </div>

                    <!-- شبكة رقم الوثيقة وتاريخها -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-hashtag text-slate-400"></i>
                                <span>رقم الوثيقة</span>
                            </label>
                            <input type="text" name="document_number" placeholder="رقم المرجع..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-800 focus:bg-white focus:border-[#14532d] focus:outline-hidden transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-slate-400"></i>
                                <span>تاريخ الإصدار</span>
                            </label>
                            <input type="date" name="document_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-1.5 text-xs font-mono text-slate-800 focus:bg-white focus:border-[#14532d] focus:outline-hidden transition">
                        </div>
                    </div>

                    <!-- منطقة رفع الملف -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-paperclip text-[#14532d]"></i>
                                <span>الملف</span>
                                <span class="text-rose-600 font-bold">*</span>
                            </label>
                            <span class="text-[10px] font-mono font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">25MB</span>
                        </div>

                        <div class="relative border-2 border-dashed border-emerald-300 hover:border-[#14532d] rounded-2xl p-4 text-center bg-emerald-50/20 hover:bg-emerald-50/40 transition cursor-pointer group" onclick="document.getElementById('upload_card_file_input').click()">
                            <input type="file" name="document_file" id="upload_card_file_input" required accept="application/pdf,image/*" 
                                   onchange="handleCardFileSelect(this)" 
                                   class="hidden">
                            <div class="flex flex-col items-center justify-center space-y-1.5 pointer-events-none">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100/70 text-[#14532d] flex items-center justify-center text-lg group-hover:scale-105 transition shadow-2xs">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800" id="upload_card_file_label">اسحب الملف هنا أو انقر للاختيار</p>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">PDF • PNG • JPG</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- زر الحفظ -->
                    <button type="submit" id="btnSubmitCardUpload" class="w-full bg-[#14532d] hover:bg-[#166534] text-white font-bold py-2.5 px-5 rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer text-xs">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>حفظ وأرشفة</span>
                    </button>
                </form>

                <!-- آخر الوثائق -->
                <div class="border-t border-slate-200 pt-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                            آخر الوثائق
                        </span>
                        <button type="button" onclick="switchProfileTab('tab-docs')" class="text-xs font-bold text-[#14532d] hover:underline flex items-center gap-1 cursor-pointer">
                            <span>عرض الكل</span>
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        </button>
                    </div>

                    @if($archivedDocs->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($archivedDocs->take(4) as $doc)
                                @php
                                    $v = $doc->currentVersion;
                                    $hasFile = $v && $v->file_size_bytes > 0;
                                    $previewUrl = $v ? route('archive.preview', $v->document_version_id ?? $v->version_id) : '#';
                                    $fileSize = $hasFile ? ($v->file_size_bytes >= 1048576 ? number_format($v->file_size_bytes / 1048576, 1) . ' MB' : number_format($v->file_size_bytes / 1024, 0) . ' KB') : null;
                                @endphp
                                <div class="bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl p-3 flex items-center justify-between gap-3 transition">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-[#14532d] flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-file-pdf text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $previewUrl }}" target="_blank" class="text-xs font-bold text-slate-900 hover:text-[#14532d] hover:underline truncate block" title="فتح الوثيقة في صفحة مستقلة">
                                                {{ $doc->document_title }}
                                            </a>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                                <span>{{ $doc->documentType?->type_name_ar ?? 'عام' }}</span>
                                                @if($fileSize)
                                                    <span>•</span>
                                                    <span class="font-mono">{{ $fileSize }}</span>
                                                @endif
                                                @if($doc->created_at)
                                                    <span>•</span>
                                                    <span class="font-mono">{{ $doc->created_at->format('Y-m-d') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($hasFile)
                                        <a href="{{ $previewUrl }}" target="_blank" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 text-slate-600 hover:text-emerald-800 flex items-center justify-center shrink-0 transition shadow-2xs cursor-pointer" title="فتح وعرض في صفحة مستقلة">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-400">
                            لا توجد وثائق مؤرشفة لهذا الكادر بعد. ارفع أول وثيقة عبر النموذج أعلاه.
                        </div>
                    @endif
                </div>

            </div>

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
                b.className = 'w-9 h-9 rounded-lg transition text-slate-500 hover:text-slate-900 cursor-pointer flex items-center justify-center';
            }
        });

        if (mode === 'profile') {
            // ملف الكادر فقط
            profilePanel.classList.remove('lg:col-span-6', 'hidden');
            profilePanel.classList.add('lg:col-span-12');
            viewerPanel.classList.add('hidden');
            if (btnProfile) btnProfile.className = 'w-9 h-9 rounded-lg transition bg-white text-slate-900 shadow-xs font-bold cursor-pointer flex items-center justify-center';
        } else if (mode === 'viewer') {
            // بطاقة رفع الملف فقط
            profilePanel.classList.add('hidden');
            viewerPanel.classList.remove('lg:col-span-6', 'hidden');
            viewerPanel.classList.add('lg:col-span-12');
            if (btnViewer) btnViewer.className = 'w-9 h-9 rounded-lg transition bg-white text-slate-900 shadow-xs font-bold cursor-pointer flex items-center justify-center';
        } else {
            // عرض متوازن مزدوج (ملف الكادر وبطاقة الرفع)
            profilePanel.classList.remove('hidden', 'lg:col-span-12');
            profilePanel.classList.add('lg:col-span-6');
            viewerPanel.classList.remove('hidden', 'lg:col-span-12');
            viewerPanel.classList.add('lg:col-span-6');
            if (btnSplit) btnSplit.className = 'w-9 h-9 rounded-lg transition bg-white text-slate-900 shadow-xs font-bold cursor-pointer flex items-center justify-center';
        }
    }

    // إظهار اسم وحجم الملف المختار في بطاقة الرفع
    function handleCardFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            if (file.size > 25 * 1024 * 1024) {
                alert('تنبيه: حجم الملف المختار (' + sizeMb + ' MB) يتجاوز الحد الأقصى المسموح به (25 MB).');
                input.value = '';
                document.getElementById('upload_card_file_label').textContent = 'انقر هنا لاختيار الملف من جهازك أو اسحبه وأفلته هنا';
                return;
            }
            document.getElementById('upload_card_file_label').innerHTML = 
                '<span class="text-emerald-800 font-bold"><i class="fa-solid fa-circle-check text-emerald-600"></i> تم اختيار: ' + file.name + ' (' + sizeMb + ' MB)</span>';
        }
    }

    // الانتقال المباشر لبطاقة الرفع
    function focusUploadCard() {
        setViewMode('split');
        const titleInput = document.getElementById('upload_card_doc_title');
        if (titleInput) {
            titleInput.focus();
            titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

    // فتح الوثيقة في صفحة مستقلة
    function loadDesktopPdfViewer(previewUrl) {
        if (previewUrl && previewUrl !== '#') {
            window.open(previewUrl, '_blank');
        }
    }

    // معاينة الوثيقة من جدول كافة الوثائق
    function previewDocFromTable(previewUrl, title, downloadUrl, docId) {
        window.open(previewUrl, '_blank');
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
    // تفعيل التبويب الممرر عبر الرابط (مثل tab=docs بعد الرفع)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'docs') {
            switchProfileTab('tab-docs');
        } else if (tab === 'quals') {
            switchProfileTab('tab-quals');
        } else if (tab === 'history') {
            switchProfileTab('tab-history');
        }
    });
</script>
@endsection
