@extends('layouts.app')

@section('title', 'منشئ التقارير المخصصة | جامعة إب')
@section('page_title', 'منشئ التقارير المخصصة')

@section('content')
<div class="space-y-6">

    <!-- نموذج التحكم وتحديد المحددات والأعمدة (يختفي أثناء الطباعة) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6 print:hidden">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i data-lucide="sliders" class="w-5 h-5 ml-2 text-brand-900"></i>
                    <span>محددات وأعمدة التقرير المخصص</span>
                </h2>
                <span class="text-xs text-slate-400">حدد نطاق الكادر واختر الأعمدة المراد استخراجها في الكشف الرسمي</span>
            </div>

            <!-- أزرار النماذج الجاهزة السريعة -->
            <div class="flex items-center space-x-1.5 space-x-reverse text-xs">
                <span class="text-slate-400 font-semibold ml-1">قوالب جاهزة:</span>
                <button type="button" onclick="applyPreset('contact')" class="px-2.5 py-1 bg-slate-50 border border-slate-200 hover:border-brand-900 rounded-lg text-slate-600 transition">
                    الاتصال
                </button>
                <button type="button" onclick="applyPreset('academic')" class="px-2.5 py-1 bg-slate-50 border border-slate-200 hover:border-brand-900 rounded-lg text-slate-600 transition">
                    الرتب والمؤهلات
                </button>
                <button type="button" onclick="applyPreset('job')" class="px-2.5 py-1 bg-slate-50 border border-slate-200 hover:border-brand-900 rounded-lg text-slate-600 transition">
                    الوظيفة والدرجة
                </button>
            </div>
        </div>

        <form action="{{ route('reports.builder') }}" method="GET" id="reportBuilderForm" class="space-y-6">

            <!-- 1. محددات النطاق والفلترة -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">الكلية / الجهة</label>
                    <select name="entity_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        <option value="">كافة الكليات والمراكز</option>
                        @foreach($entities as $ent)
                            <option value="{{ $ent->id }}" {{ request('entity_id') == $ent->id ? 'selected' : '' }}>{{ $ent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">القسم العلمي</label>
                    <select name="department_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        <option value="">كافة الأقسام</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">نوع الكادر</label>
                    <select name="staff_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        <option value="">الكل (أكاديمي / إداري / تعاقد)</option>
                        <option value="academic" {{ request('staff_type') == 'academic' ? 'selected' : '' }}>أكاديمي</option>
                        <option value="administrative" {{ request('staff_type') == 'administrative' ? 'selected' : '' }}>إداري</option>
                        <option value="contract" {{ request('staff_type') == 'contract' ? 'selected' : '' }}>متعاقد</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">الموقف الوظيفي</label>
                    <select name="job_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        <option value="">كافة الحالات</option>
                        @foreach($lookups['jobStatuses'] as $st)
                            <option value="{{ $st }}" {{ request('job_status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. شبكة اختيار الأعمدة المطلوبة للتقرير -->
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 flex items-center">
                        <i data-lucide="check-square" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                        <span>اختر الأعمدة المراد تضمينها في الكشف (الأعمدة المختارة: <span id="colCount" class="text-brand-900 font-mono font-bold">{{ count($selectedColumns) }}</span>)</span>
                    </span>
                    <div class="flex items-center space-x-2 space-x-reverse text-xs">
                        <button type="button" onclick="toggleAllColumns(true)" class="text-brand-900 hover:underline font-semibold">تحديد الكل</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="toggleAllColumns(false)" class="text-slate-500 hover:underline">إلغاء التحديد</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    @foreach($allGroups as $groupKey => $group)
                    <div class="bg-slate-50/60 rounded-xl p-3 border border-slate-200 space-y-2.5">
                        <span class="font-bold text-slate-700 block pb-1 border-b border-slate-200 text-[11px]">{{ $group['title'] }}</span>
                        <div class="space-y-1.5">
                            @foreach($group['columns'] as $colKey => $colLabel)
                            <label class="flex items-center space-x-2 space-x-reverse cursor-pointer hover:text-brand-900">
                                <input type="checkbox" name="columns[]" value="{{ $colKey }}" 
                                       {{ in_array($colKey, $selectedColumns) ? 'checked' : '' }}
                                       onchange="updateColCount()"
                                       class="col-checkbox w-3.5 h-3.5 rounded text-brand-900 focus:ring-brand-900 border-slate-300">
                                <span class="text-[11px] text-slate-600 select-none">{{ $colLabel }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- أزرار الإجراءات -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <button type="submit" class="px-5 py-2.5 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold transition flex items-center shadow-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4 ml-1.5"></i>
                    <span>توليد وعرض الكشف</span>
                </button>

                <div class="flex items-center space-x-2 space-x-reverse">
                    <button type="button" onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 hover:border-brand-900 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center">
                        <i data-lucide="printer" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                        <span>طباعة الكشف الرسمي</span>
                    </button>
                    <button type="button" onclick="exportToCsv()" class="px-4 py-2 bg-white border border-slate-200 hover:border-brand-900 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center">
                        <i data-lucide="download" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                        <span>تصدير Excel (CSV)</span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- كشف التقرير المخصص والمعاينة الرسمية (جاهز للطباعة وللعرض) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 print:border-none print:shadow-none print:p-0">

        <!-- ترويسة التقرير الرسمي لجامعة إب -->
        <div class="pb-6 mb-6 border-b border-slate-200 flex items-center justify-between">
            <div class="space-y-1">
                <h3 class="text-xs font-bold text-slate-700">الجمهورية اليمنية</h3>
                <h4 class="text-xs font-bold text-slate-700">وزارة التعليم العالي والبحث العلمي</h4>
                <h5 class="text-sm font-bold text-brand-900">جامعة إب - الإدارة العامة للشؤون الأكاديمية</h5>
                <span class="text-[11px] text-slate-500 font-mono">نظام الأرشفة والتوثيق الإلكتروني</span>
            </div>

            <div class="text-center">
                <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-16 w-auto mx-auto object-contain">
                <span class="text-xs font-bold text-slate-800 block mt-1">{{ $reportTitle }}</span>
            </div>

            <div class="text-left space-y-1 text-xs font-mono text-slate-500">
                <div>التاريخ: {{ now()->format('Y/m/d') }}</div>
                <div>الوقت: {{ now()->format('h:i A') }}</div>
                <div>عدد السجلات: <strong class="text-brand-900">{{ $staffList->count() }}</strong></div>
            </div>
        </div>

        <!-- جدول التقرير الديناميكي المختار -->
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200 text-slate-700 font-bold">
                        <th class="py-2.5 px-3 text-center border-l border-slate-200 w-10">م</th>
                        @foreach($selectedColumns as $colKey)
                        <th class="py-2.5 px-3 border-l border-slate-200 last:border-l-0">
                            {{ $flatAvailable[$colKey] ?? $colKey }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($staffList as $index => $staff)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3 text-center font-mono border-l border-slate-200 text-slate-500">
                            {{ $index + 1 }}
                        </td>
                        @foreach($selectedColumns as $colKey)
                        <td class="py-2.5 px-3 border-l border-slate-200 last:border-l-0 text-slate-800">
                            @switch($colKey)
                                @case('job_number')
                                    <span class="font-mono font-bold text-brand-900">{{ $staff->job_number }}</span>
                                    @break
                                @case('full_name')
                                    <span class="font-bold">{{ $staff->full_name }}</span>
                                    @break
                                @case('national_id')
                                    <span class="font-mono text-slate-600">{{ $staff->national_id }}</span>
                                    @break
                                @case('gender')
                                    <span>{{ $staff->gender }}</span>
                                    @break
                                @case('birth_date')
                                    <span class="font-mono">{{ $staff->birth_date ? $staff->birth_date->format('Y/m/d') : '-' }}</span>
                                    @break
                                @case('phone')
                                    <span class="font-mono">{{ $staff->phone ?? '-' }}</span>
                                    @break
                                @case('email')
                                    <span class="font-mono">{{ $staff->email ?? '-' }}</span>
                                    @break
                                @case('entity_name')
                                    <span>{{ $staff->department && $staff->department->entity ? $staff->department->entity->name : '-' }}</span>
                                    @break
                                @case('department_name')
                                    <span>{{ $staff->department ? $staff->department->name : '-' }}</span>
                                    @break
                                @case('staff_type')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $staff->staff_type == 'academic' ? 'bg-brand-50 text-brand-900' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $staff->staff_type == 'academic' ? 'أكاديمي' : ($staff->staff_type == 'administrative' ? 'إداري' : 'متعاقد') }}
                                    </span>
                                    @break
                                @case('academic_rank')
                                    <span class="font-semibold">{{ $staff->academicDetails && $staff->academicDetails->rank ? $staff->academicDetails->rank->name : '-' }}</span>
                                    @break
                                @case('general_specialization')
                                    <span>{{ $staff->academicDetails ? $staff->academicDetails->general_specialization : '-' }}</span>
                                    @break
                                @case('exact_specialization')
                                    <span>{{ $staff->academicDetails ? $staff->academicDetails->exact_specialization : '-' }}</span>
                                    @break
                                @case('academic_status')
                                    <span>{{ $staff->academicDetails ? $staff->academicDetails->academic_status : '-' }}</span>
                                    @break
                                @case('hire_date')
                                    <span class="font-mono">{{ $staff->hire_date ? $staff->hire_date->format('Y/m/d') : '-' }}</span>
                                    @break
                                @case('job_status')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">{{ $staff->job_status }}</span>
                                    @break
                                @case('job_grade')
                                    <span>{{ $staff->administrativeDetails && $staff->administrativeDetails->jobGrade ? $staff->administrativeDetails->jobGrade->grade_name : '-' }}</span>
                                    @break
                                @case('job_title')
                                    <span>{{ $staff->administrativeDetails ? $staff->administrativeDetails->job_title : '-' }}</span>
                                    @break
                                @case('work_nature')
                                    <span>{{ $staff->administrativeDetails ? $staff->administrativeDetails->work_nature : '-' }}</span>
                                    @break
                                @case('degree_type')
                                    <span>{{ $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->degree_type : '-' }}</span>
                                    @break
                                @case('granting_institution')
                                    <span>{{ $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->granting_institution : '-' }}</span>
                                    @break
                                @case('graduation_year')
                                    <span class="font-mono">{{ $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->graduation_year : '-' }}</span>
                                    @break
                                @case('country')
                                    <span>{{ $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->country : '-' }}</span>
                                    @break
                                @default
                                    <span>-</span>
                            @endswitch
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($selectedColumns) + 1 }}" class="py-10 text-center text-slate-400 text-xs">
                            لا توجد بيانات مطابقة للمحددات التي تم اختيارها.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- تذييل التوقيعات والختم الرسمي للطباعة -->
        <div class="pt-10 mt-8 border-t border-slate-200 hidden print:grid grid-cols-3 gap-6 text-center text-xs">
            <div class="space-y-12">
                <span class="font-bold text-slate-700">المختص / المعد:</span>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
            </div>
            <div class="space-y-12">
                <span class="font-bold text-slate-700">مدير عام الشؤون الأكاديمية:</span>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
            </div>
            <div class="space-y-12">
                <span class="font-bold text-slate-700">رئيس الجامعة / عميد الكلية:</span>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
            </div>
        </div>

    </div>

</div>

<!-- نموذج تصدير CSV المخفي -->
<form id="exportCsvForm" action="{{ route('reports.builder.export') }}" method="GET" class="hidden">
    @foreach(request()->query() as $key => $val)
        @if(is_array($val))
            @foreach($val as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endif
    @endforeach
</form>
@endsection

@push('scripts')
<script>
    function updateColCount() {
        const checked = document.querySelectorAll('.col-checkbox:checked').length;
        document.getElementById('colCount').innerText = checked;
    }

    function toggleAllColumns(state) {
        document.querySelectorAll('.col-checkbox').forEach(cb => cb.checked = state);
        updateColCount();
    }

    function applyPreset(type) {
        toggleAllColumns(false);
        const map = {
            contact: ['job_number', 'full_name', 'entity_name', 'department_name', 'phone', 'email'],
            academic: ['job_number', 'full_name', 'entity_name', 'department_name', 'academic_rank', 'exact_specialization', 'degree_type', 'granting_institution'],
            job: ['job_number', 'full_name', 'entity_name', 'staff_type', 'job_status', 'job_grade', 'hire_date']
        };

        const targetCols = map[type] || [];
        targetCols.forEach(col => {
            const cb = document.querySelector(`.col-checkbox[value="${col}"]`);
            if (cb) cb.checked = true;
        });
        updateColCount();
        document.getElementById('reportBuilderForm').submit();
    }

    function exportToCsv() {
        const form = document.getElementById('reportBuilderForm');
        const exportForm = document.getElementById('exportCsvForm');
        exportForm.innerHTML = '';
        
        // نقل كل قيم الفورم الحالي
        new FormData(form).forEach((value, key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            exportForm.appendChild(input);
        });

        exportForm.submit();
    }
</script>
@endpush
