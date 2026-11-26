@extends('layouts.app')

@section('title', 'جامعة إب - إدخال كادر جديد')

@section('content')
<div class="flex-1 flex flex-col gap-1.5 min-h-0 pb-0">

    <!-- 1. شريط العمليات والتحكم العلوي الموحد -->
    <div class="bg-white border border-slate-200 p-2.5 px-4 flex items-center justify-between shadow-xs shrink-0 rounded-xl select-none">
        <div class="flex items-center gap-3">
            <span class="font-bold text-slate-800 text-xs flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-[#14532d] text-sm"></i> إدخال كادر جديد في المنظومة
            </span>
            <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                <i class="fa-solid fa-keyboard text-slate-400 text-[10px]"></i> التنقل بين الصناديق بمفتاح <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-700 font-mono font-bold shadow-2xs">Enter</kbd>
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('employees.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3.5 py-1.5 rounded-xl text-xs transition border border-slate-200">
                إلغاء
            </a>
            <button type="button" onclick="submitStaffForm()" id="btn-save-staff" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-4 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <i class="fa-solid fa-check text-white"></i> حفظ السجل
            </button>
        </div>
    </div>

    <!-- 2. حاوية النموذج المستقلة مع إخفاء شريط التمرير (Ghost Scroll) -->
    <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden relative">
        
        <form id="main-staff-form" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" novalidate onsubmit="return handleMainStaffFormSubmit(event)"
              class="flex-1 overflow-y-auto no-scrollbar p-3.5 space-y-3">
            @csrf

            @if(session('error') || (isset($errors) && $errors->any()))
            <div class="p-3 px-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-xl text-xs font-bold space-y-1 shadow-2xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                        <span>{{ session('error') ?? 'تعذر حفظ السجل، يرجى مراجعة الأخطاء التالية:' }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                @if(isset($errors) && $errors->any())
                    <ul class="list-disc list-inside text-[11px] font-medium pr-6 space-y-0.5 text-rose-800">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @endif

            <!-- 0. اختيار نوع الكادر (تصميم رسمي نظيف) -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex flex-wrap items-center justify-between gap-3 select-none">
                <div class="flex items-center gap-2 text-xs font-black text-slate-800">
                    <i class="fa-solid fa-user-tag text-[#14532d]"></i>
                    <span>نوع الكادر:</span>
                </div>

                <input type="hidden" name="cadre_type" id="hidden_cadre_type" value="{{ old('cadre_type', 'academic') }}">

                <div class="grid grid-cols-3 gap-2 flex-1 max-w-2xl text-xs">
                    <button type="button" onclick="switchCadreType('academic')" id="card-cadre-academic"
                            class="cadre-select-card cursor-pointer flex items-center justify-center gap-2 px-3 py-2 rounded-xl border font-bold transition-all bg-[#14532d] border-[#14532d] text-white shadow-2xs">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>كادر أكاديمي</span>
                    </button>

                    <button type="button" onclick="switchCadreType('admin')" id="card-cadre-admin"
                            class="cadre-select-card cursor-pointer flex items-center justify-center gap-2 px-3 py-2 rounded-xl border font-bold transition-all bg-white border-slate-200 text-slate-700 hover:bg-slate-100">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>كادر إداري</span>
                    </button>

                    <button type="button" onclick="switchCadreType('hourly')" id="card-cadre-hourly"
                            class="cadre-select-card cursor-pointer flex items-center justify-center gap-2 px-3 py-2 rounded-xl border font-bold transition-all bg-white border-slate-200 text-slate-700 hover:bg-slate-100">
                        <i class="fa-solid fa-clock"></i>
                        <span>متعاقد بالساعات</span>
                    </button>
                </div>
            </div>

            <!-- شريط المراحل الأفقي (Horizontal 5-Step Stepper) -->
            <div class="bg-white border border-slate-200 rounded-xl p-2 px-3 grid grid-cols-5 gap-1.5 select-none">
                @php
                    $wizardSteps = [
                        1 => ['label' => 'البيانات الأساسية', 'icon' => 'fa-id-card'],
                        2 => ['label' => 'التسكين والتكليف', 'icon' => 'fa-sitemap'],
                        3 => ['label' => 'الراتب والتعيين', 'icon' => 'fa-money-check-dollar'],
                        4 => ['label' => 'القرار والمؤهل', 'icon' => 'fa-graduation-cap'],
                        5 => ['label' => 'الأرشيف الرقمي', 'icon' => 'fa-file-pdf'],
                    ];
                @endphp
                @foreach($wizardSteps as $sNum => $sInfo)
                    <button type="button" onclick="goToWizardStep({{ $sNum }})" id="wizard-tab-{{ $sNum }}"
                            class="wizard-tab-btn flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-bold transition cursor-pointer border {{ $sNum === 1 ? 'bg-[#14532d] text-white border-[#14532d] shadow-2xs' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100' }}">
                        <span class="w-5 h-5 rounded-full text-[10px] font-mono flex items-center justify-center shrink-0 wizard-step-num {{ $sNum === 1 ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $sNum }}</span>
                        <i class="fa-solid {{ $sInfo['icon'] }} text-[11px] hidden sm:inline"></i>
                        <span class="truncate">{{ $sInfo['label'] }}</span>
                    </button>
                @endforeach
            </div>

            <!-- القسم 1: البيانات الشخصية والأساسية -->
            <div id="wizard-step-1" class="wizard-step-panel bg-slate-50/50 border border-slate-200/90 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-id-card text-primary text-xs"></i>
                    <span>1. البيانات الشخصية والأساسية</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الاسم الرباعي الكامل <span class="text-rose-600">*</span></label>
                        <input type="text" name="full_name_ar" required value="{{ old('full_name_ar') }}" placeholder="الاسم الرباعي الكامل مع اللقب" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الجنس <span class="text-rose-600">*</span></label>
                        <select name="gender" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-slate-700">الرقم الوظيفي <span class="text-rose-600">*</span></label>
                            <span class="text-[9px] font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">ترقيم تلقائي</span>
                        </div>
                        <div class="relative">
                            <input type="text" name="employee_number" id="employee_number_input" required 
                                   value="{{ old('employee_number', $nextEmployeeNumber ?? '') }}" 
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkDuplicateLive('employee_number', this.value, 'emp_num_feedback');"
                                   placeholder="الرقم الوظيفي (تلقائي)" 
                                   class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono font-bold text-primary focus:border-primary focus:outline-none transition shadow-2xs">
                            <div id="emp_num_feedback" class="hidden text-[10px] font-bold mt-1 text-rose-600 flex items-center gap-1"></div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الرقم الوطني / البطاقة</label>
                        <div class="relative">
                            <input type="text" name="national_id" id="national_id_input"
                                   value="{{ old('national_id') }}" 
                                   inputmode="numeric"
                                   maxlength="20"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkDuplicateLive('national_id', this.value, 'nat_id_feedback');"
                                   placeholder="أرقام فقط (مثال: 0101002345)" 
                                   class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                            <div id="nat_id_feedback" class="hidden text-[10px] font-bold mt-1 text-rose-600 flex items-center gap-1"></div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم الهاتف</label>
                        <div class="relative">
                            <input type="tel" name="phone_number" id="phone_number_input"
                                   value="{{ old('phone_number') }}" 
                                   inputmode="tel"
                                   maxlength="16"
                                   oninput="this.value = this.value.replace(/[^0-9+]/g, '');"
                                   placeholder="أرقام فقط (مثال: 771234567)" 
                                   class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ الميلاد</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">محل الميلاد</label>
                        <input type="text" name="place_of_birth" value="{{ old('place_of_birth') }}" placeholder="إب، صنعاء، تعز..." 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">العنوان السكني</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="المحافظة - المديرية - الحي" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div class="lg:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-slate-700">الصورة الشخصية الرسمية (JPG/PNG)</label>
                            <span class="text-[10px] text-slate-400">الحد الأقصى: 10MB</span>
                        </div>
                        <input type="file" name="photo_file" accept="image/*" 
                               onchange="validateFileSize(this, 10)"
                               class="w-full bg-white border border-slate-300 rounded-md px-2 py-1 text-xs text-slate-500 file:ml-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-bold file:bg-primary file:text-white file:cursor-pointer shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- القسم 2: التسكين والتكليف بالهيكل الجامعي -->
            <div id="wizard-step-2" class="wizard-step-panel hidden bg-slate-50/50 border border-slate-200/90 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-sitemap text-primary text-xs"></i>
                    <span id="sec2-title-text">2. التسكين الأكاديمي والتكليف بالقسم العلمي</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الفئة الوظيفية <span class="text-rose-600">*</span></label>
                        <select name="employee_category_id" id="desktop_category_select" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->employee_category_id }}" {{ old('employee_category_id', 1) == $cat->employee_category_id ? 'selected' : '' }}>
                                    {{ $cat->category_name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- الكلية / المركز -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1" id="label-faculty">الكلية / المركز <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select id="desktop_faculty_select" required data-quick-type="faculty" data-quick-title="إضافة كلية / مركز" data-quick-label="اسم الكلية أو المركز الجديد:" onchange="handleQuickAddChange(this); filterFacultyDepartments();" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر الكلية أو المركز --</option>
                                @foreach($colleges as $col)
                                    <option value="{{ $col->organizational_unit_id }}" {{ (request('faculty_id') == $col->organizational_unit_id || old('faculty_id') == $col->organizational_unit_id) ? 'selected' : '' }}>{{ $col->unit_name_ar }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة كلية / مركز جديد...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('faculty', 'desktop_faculty_select', 'اسم الكلية أو المركز الجديد:', 'إضافة كلية / مركز')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة كلية جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- القسم / الوحدة -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1" id="label-dept">القسم / الوحدة <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="organizational_unit_id" id="desktop_dept_select" data-quick-type="department" data-quick-title="إضافة قسم علمي" data-quick-label="اسم القسم العلمي الجديد:" onchange="handleQuickAddChange(this)" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر القسم أولاً --</option>
                                @foreach($colleges as $col)
                                    @foreach($col->children as $dept)
                                        <option value="{{ $dept->organizational_unit_id }}" data-parent="{{ $col->organizational_unit_id }}" {{ (request('dept_id') == $dept->organizational_unit_id || old('organizational_unit_id') == $dept->organizational_unit_id) ? 'selected' : '' }}>
                                            {{ $dept->unit_name_ar }} ({{ $col->unit_code }})
                                        </option>
                                    @endforeach
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة قسم علمي جديد...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('department', 'desktop_dept_select', 'اسم القسم العلمي الجديد:', 'إضافة قسم علمي')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة قسم جديد">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- الموقف الوظيفي -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الموقف الوظيفي <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="employment_status_id" id="desktop_status_select" data-quick-type="status" data-quick-title="إضافة موقف وظيفي" data-quick-label="اسم الموقف الوظيفي الجديد:" onchange="handleQuickAddChange(this)" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                @foreach($statuses as $st)
                                    <option value="{{ $st->employment_status_id }}" {{ old('employment_status_id') == $st->employment_status_id ? 'selected' : '' }}>
                                        {{ $st->status_name_ar }}
                                    </option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة موقف وظيفي جديد...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('status', 'desktop_status_select', 'اسم الموقف الوظيفي الجديد:', 'إضافة موقف وظيفي')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة موقف جديد">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- الرتبة الأكاديمية (أكاديمي ومتقاعدين) -->
                    <div class="cadre-field-academic cadre-field-hourly">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الرتبة الأكاديمية</label>
                        <div class="flex items-center gap-1.5">
                            <select name="academic_rank_id" id="desktop_rank_select" data-quick-type="rank" data-quick-title="إضافة رتبة أكاديمية" data-quick-label="اسم الرتبة العلمية الجديدة:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- بدون رتبة --</option>
                                @foreach($ranks as $rnk)
                                    <option value="{{ $rnk->academic_rank_id }}" {{ old('academic_rank_id') == $rnk->academic_rank_id ? 'selected' : '' }}>
                                        {{ $rnk->rank_name_ar }}
                                    </option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة رتبة أكاديمية جديدة...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('rank', 'desktop_rank_select', 'اسم الرتبة العلمية الجديدة:', 'إضافة رتبة أكاديمية')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة رتبة جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- المسمى الوظيفي الإداري (كادر إداري) -->
                    <div class="cadre-field-admin hidden">
                        <label class="block text-[11px] font-bold text-blue-900 mb-1">المسمى الوظيفي الإداري <span class="text-rose-600">*</span></label>
                        <input type="text" name="admin_job_title" id="admin_job_title" value="{{ old('admin_job_title') }}" placeholder="مدير إدارة، رئيس قسم، أخصائي شؤون موظفين..." 
                               class="w-full bg-white border border-blue-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- نصاب الساعات (متقاعدين بالساعات) -->
                    <div class="cadre-field-hourly hidden">
                        <label class="block text-[11px] font-bold text-amber-900 mb-1">نصاب الساعات المعتمدة <span class="text-rose-600">*</span></label>
                        <input type="number" step="0.5" min="1" max="50" name="hourly_hours" value="{{ old('hourly_hours') }}" 
                               inputmode="decimal"
                               oninput="this.value = this.value.replace(/[^0-9.]/g, '');"
                               placeholder="أرقام فقط (مثال: 12)" 
                               class="w-full bg-white border border-amber-300 rounded-md px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- أجر الساعة (متقاعدين بالساعات) -->
                    <div class="cadre-field-hourly hidden">
                        <label class="block text-[11px] font-bold text-amber-900 mb-1">أجر الساعة المعتمد (ريال)</label>
                        <input type="text" name="hourly_rate" value="{{ old('hourly_rate') }}" 
                               inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                               placeholder="أرقام فقط (مثال: 5000)" 
                               class="w-full bg-white border border-amber-300 rounded-md px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- العام الجامعي والفصل (متقاعدين بالساعات) -->
                    <div class="cadre-field-hourly hidden">
                        <label class="block text-[11px] font-bold text-amber-900 mb-1">العام الجامعي والفصل</label>
                        <input type="text" name="academic_year" value="{{ old('academic_year', '2024-2025 / الفصل الأول') }}" placeholder="2024-2025 / الفصل الأول" 
                               class="w-full bg-white border border-amber-300 rounded-md px-2.5 py-1.5 text-xs font-bold text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- جهة التقاعد الأصلية (متقاعدين بالساعات) -->
                    <div class="cadre-field-hourly hidden">
                        <label class="block text-[11px] font-bold text-amber-900 mb-1">جهة التقاعد الأصلية</label>
                        <input type="text" name="retirement_source" value="{{ old('retirement_source') }}" placeholder="مثال: جامعة إب (متقاعد)، جامعة صنعاء..." 
                               class="w-full bg-white border border-amber-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- الدرجة الوظيفية -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الدرجة الوظيفية</label>
                        <div class="flex items-center gap-1.5">
                            <select name="job_grade_id" id="desktop_grade_select" data-quick-type="grade" data-quick-title="إضافة درجة مالية" data-quick-label="اسم الدرجة الوظيفية الجديدة:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر الدرجة --</option>
                                @foreach($grades as $grd)
                                    <option value="{{ $grd->job_grade_id }}" {{ old('job_grade_id') == $grd->job_grade_id ? 'selected' : '' }}>
                                        {{ $grd->grade_name_ar }}
                                    </option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة درجة وظيفية جديدة...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('grade', 'desktop_grade_select', 'اسم الدرجة الوظيفية الجديدة:', 'إضافة درجة مالية')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة درجة جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ المباشرة <span class="text-rose-600">*</span></label>
                        <input type="date" name="start_date" required value="{{ old('start_date', date('Y-m-d')) }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">أساس التعيين</label>
                        <input type="text" name="appointment_basis" id="appointment_basis_input" value="{{ old('appointment_basis', 'تثبيت رسمي') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- القسم 3: بيانات كشف الراتب وتاريخ التعيين -->
            <div id="wizard-step-3" class="wizard-step-panel hidden bg-slate-50/50 border border-slate-200/90 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-file-invoice-dollar text-primary text-xs"></i>
                    <span>3. بيانات كشف الراتب وتاريخ التعيين</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                    <!-- الحالة بكشف الراتب -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الحالة بكشف الراتب <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="payroll_status" id="payroll_status_select" required data-use-name-as-value="true" data-quick-type="payroll_status" data-quick-title="إضافة حالة براتب" data-quick-label="اسم الحالة بكشف الراتب:" onchange="handleQuickAddChange(this)"
                                    class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="على رأس العمل" {{ old('payroll_status', 'على رأس العمل') == 'على رأس العمل' ? 'selected' : '' }}>على رأس العمل</option>
                                <option value="مفرغ للدراسة (داخلية)" {{ old('payroll_status') == 'مفرغ للدراسة (داخلية)' ? 'selected' : '' }}>مفرغ للدراسة (داخلية)</option>
                                <option value="مفرغ للدراسة (خارجية)" {{ old('payroll_status') == 'مفرغ للدراسة (خارجية)' ? 'selected' : '' }}>مفرغ للدراسة (خارجية)</option>
                                <option value="إجازة بدون راتب" {{ old('payroll_status') == 'إجازة بدون راتب' ? 'selected' : '' }}>إجازة بدون راتب</option>
                                <option value="إعارة" {{ old('payroll_status') == 'إعارة' ? 'selected' : '' }}>إعارة</option>
                                <option value="منقطع عن العمل" {{ old('payroll_status') == 'منقطع عن العمل' ? 'selected' : '' }}>منقطع عن العمل</option>
                                <option value="متقاعد" {{ old('payroll_status') == 'متقاعد' ? 'selected' : '' }}>متقاعد</option>
                                <option value="متقاعد بالساعات" {{ old('payroll_status') == 'متقاعد بالساعات' ? 'selected' : '' }}>متقاعد بالساعات</option>
                                <option value="متوفى" {{ old('payroll_status') == 'متوفى' ? 'selected' : '' }}>متوفى</option>
                                @foreach($statuses as $st)
                                    @if(!in_array($st->status_name_ar, ['على رأس العمل','مفرغ للدراسة (داخلية)','مفرغ للدراسة (خارجية)','إجازة بدون راتب','إعارة','منقطع عن العمل','متقاعد','متقاعد بالساعات','متوفى']))
                                        <option value="{{ $st->status_name_ar }}" {{ old('payroll_status') == $st->status_name_ar ? 'selected' : '' }}>{{ $st->status_name_ar }}</option>
                                    @endif
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة حالة جديدة...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('payroll_status', 'payroll_status_select', 'اسم الحالة بكشف الراتب:', 'إضافة حالة براتب')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة حالة جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- الوظيفة بكشف الراتب -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الوظيفة بكشف الراتب <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="payroll_job_title" id="payroll_job_title_select" required data-use-name-as-value="true" data-quick-type="job_title" data-quick-title="إضافة وظيفة براتب" data-quick-label="اسم الوظيفة بكشف الراتب:" onchange="handleQuickAddChange(this)"
                                    class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر الوظيفة --</option>
                                @php
                                    $defaultTitles = [
                                        'عضو هيئة تدريس', 'أستاذ', 'أستاذ مشارك', 'أستاذ مساعد', 'مدرس', 'معيد',
                                        'متقاعد بنظام الساعات', 'مدير عام', 'أمين كلية', 'مدير إدارة', 'رئيس قسم',
                                        'أخصائي شؤون موظفين', 'محاسب مالي', 'مهندس حاسوب وشبكات', 'فني مختبرات علمية',
                                        'سكرتير إداري', 'خدمات معاونة'
                                    ];
                                    if(isset($jobTitles)) {
                                        foreach($jobTitles as $jt) {
                                            if(!in_array($jt->title_name_ar, $defaultTitles)) {
                                                $defaultTitles[] = $jt->title_name_ar;
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($defaultTitles as $tName)
                                    <option value="{{ $tName }}" {{ old('payroll_job_title') == $tName ? 'selected' : '' }}>{{ $tName }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة وظيفة جديدة...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('job_title', 'payroll_job_title_select', 'اسم الوظيفة بكشف الراتب:', 'إضافة وظيفة براتب')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة وظيفة جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- الدرجة بكشف الراتب -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الدرجة بكشف الراتب <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="payroll_job_grade" id="payroll_job_grade_select" required data-use-name-as-value="true" data-quick-type="grade" data-quick-title="إضافة درجة مالية" data-quick-label="اسم الدرجة بكشف الراتب:" onchange="handleQuickAddChange(this)"
                                    class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر الدرجة --</option>
                                @php
                                    $defaultGrades = ['أستاذ', 'أستاذ مشارك', 'أستاذ مساعد', 'مدرس', 'معيد', 'الدرجة الأولى', 'الدرجة الثانية', 'الدرجة الثالثة', 'الدرجة الرابعة', 'الدرجة الخامسة', 'الدرجة السادسة', 'الدرجة السابعة'];
                                    if(isset($grades)) {
                                        foreach($grades as $grd) {
                                            if(!in_array($grd->grade_name_ar, $defaultGrades)) {
                                                $defaultGrades[] = $grd->grade_name_ar;
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($defaultGrades as $gName)
                                    <option value="{{ $gName }}" {{ old('payroll_job_grade') == $gName ? 'selected' : '' }}>{{ $gName }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة درجة جديدة...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('grade', 'payroll_job_grade_select', 'اسم الدرجة بكشف الراتب:', 'إضافة درجة مالية')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة درجة جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- المؤهل عند التعيين بالجامعة -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">المؤهل عند التعيين بالجامعة <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="qualification_at_appointment" id="qualification_at_appointment_select" required data-use-name-as-value="true" data-quick-type="qual_level" data-quick-title="إضافة مؤهل تعيين" data-quick-label="اسم المؤهل عند التعيين:" onchange="handleQuickAddChange(this)"
                                    class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر المؤهل --</option>
                                @php
                                    $defaultQuals = ['دكتوراه', 'ماجستير', 'دبلوم عالي', 'بكالوريوس', 'دبلوم تقني/مهني', 'دبلوم متوسط', 'ثانوية عامة'];
                                    if(isset($qualLevels)) {
                                        foreach($qualLevels as $ql) {
                                            if(!in_array($ql->level_name_ar, $defaultQuals)) {
                                                $defaultQuals[] = $ql->level_name_ar;
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($defaultQuals as $qName)
                                    <option value="{{ $qName }}" {{ old('qualification_at_appointment') == $qName ? 'selected' : '' }}>{{ $qName }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة مؤهل جديد...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('qual_level', 'qualification_at_appointment_select', 'اسم المؤهل عند التعيين:', 'إضافة مؤهل تعيين')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة مؤهل تعيين جديد">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ التعيين بالوظيفة أول مرة</label>
                        <input type="date" name="first_appointment_date" value="{{ old('first_appointment_date') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div class="cadre-field-academic">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ التعيين بالجامعة أكاديمي</label>
                        <input type="date" name="academic_appointment_date" value="{{ old('academic_appointment_date') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- الدرجة المعين فيها أول مرة -->
                    <div class="cadre-field-academic">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الدرجة المعين فيها أول مرة</label>
                        <div class="flex items-center gap-1.5">
                            <select name="initial_rank" id="initial_rank_select" data-use-name-as-value="true" data-quick-type="rank" data-quick-title="إضافة رتبة/درجة تعيين" data-quick-label="اسم الرتبة أو الدرجة عند أول تعيين:" onchange="handleQuickAddChange(this)"
                                    class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر الرتبة / الدرجة --</option>
                                @php
                                    $defaultInitRanks = ['معيد', 'مدرس', 'أستاذ مساعد', 'أستاذ مشارك', 'أستاذ', 'الدرجة الرابعة', 'الدرجة الخامسة', 'الدرجة السادسة'];
                                    if(isset($ranks)) {
                                        foreach($ranks as $rnk) {
                                            if(!in_array($rnk->rank_name_ar, $defaultInitRanks)) {
                                                $defaultInitRanks[] = $rnk->rank_name_ar;
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($defaultInitRanks as $irName)
                                    <option value="{{ $irName }}" {{ old('initial_rank') == $irName ? 'selected' : '' }}>{{ $irName }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة رتبة/درجة تعيين جديدة...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('rank', 'initial_rank_select', 'اسم الرتبة أو الدرجة عند أول تعيين:', 'إضافة رتبة/درجة تعيين')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة رتبة أو درجة جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- القسم 4: القرار والمؤهل الأكاديمي -->
            <div id="wizard-step-4" class="wizard-step-panel hidden bg-slate-50/50 border border-slate-200/90 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-stamp text-primary text-xs"></i>
                    <span>4. القرار والمؤهل الأكاديمي</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">نوع القرار <span class="text-rose-600">*</span></label>
                        <select name="decision_type_id" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            @foreach($decisionTypes as $dt)
                                <option value="{{ $dt->decision_type_id }}">{{ $dt->type_name_ar }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-slate-700">رقم القرار <span class="text-rose-600">*</span></label>
                            <span class="text-[9px] font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">ترقيم تلقائي</span>
                        </div>
                        <input type="text" name="decision_number" required value="{{ old('decision_number', $nextDecisionNumber) }}" placeholder="رقم القرار الإداري (تلقائي)" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- مستوى المؤهل -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">مستوى المؤهل <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="qualification_level_id" id="desktop_qual_level_select" required data-quick-type="qual_level" data-quick-title="إضافة مستوى مؤهل" data-quick-label="اسم المستوى العلمي الجديد:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر المستوى --</option>
                                @foreach($qualLevels as $ql)
                                    <option value="{{ $ql->qualification_level_id }}" {{ old('qualification_level_id') == $ql->qualification_level_id ? 'selected' : '' }}>{{ $ql->level_name_ar }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة مستوى مؤهل جديد...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('qual_level', 'desktop_qual_level_select', 'اسم المستوى العلمي الجديد:', 'إضافة مستوى مؤهل')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة مستوى مؤهل جديد">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">التخصص وعنوان الشهادة</label>
                        <input type="text" name="degree_title" value="{{ old('degree_title') }}" placeholder="دكتوراه في علوم الحاسوب" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- الجامعة المانحة للمؤهل -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الجامعة المانحة للمؤهل <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-1.5">
                            <select name="university_name" id="university_name_select" required data-use-name-as-value="true" data-quick-type="university" data-quick-title="إضافة جامعة مانحة" data-quick-label="اسم الجامعة المانحة للمؤهل:" onchange="handleQuickAddChange(this)"
                                    class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                <option value="">-- اختر الجامعة المانحة للمؤهل --</option>
                                @php
                                    $defaultUnivs = [
                                        'جامعة إب', 'جامعة صنعاء', 'جامعة تعز', 'جامعة ذمار', 'جامعة عدن', 'جامعة الحديدة',
                                        'جامعة البيضاء', 'جامعة حجة', 'جامعة جبلة للعلوم الطبية', 'جامعة العلوم والتكنولوجيا',
                                        'جامعة القاهرة', 'جامعة عين شمس', 'جامعة أسيوط', 'جامعة المنصورة', 'جامعة دمشق',
                                        'جامعة الأردن', 'جامعة اليرموك', 'جامعة بغداد', 'جامعة الملك سعود', 'جامعة مالايا',
                                        'جامعة الخرطوم', 'جامعة الملك عبد العزيز'
                                    ];
                                    if(isset($universities)) {
                                        foreach($universities as $unv) {
                                            if(!in_array($unv->university_name_ar, $defaultUnivs)) {
                                                $defaultUnivs[] = $unv->university_name_ar;
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($defaultUnivs as $uName)
                                    <option value="{{ $uName }}" {{ old('university_name', 'جامعة إب') == $uName ? 'selected' : '' }}>{{ $uName }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة جامعة جديدة...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('university', 'university_name_select', 'اسم الجامعة المانحة للمؤهل:', 'إضافة جامعة مانحة')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة جامعة جديدة">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- القسم 5: وثيقة الأرشيف الرقمي (PDF) -->
            <div id="wizard-step-5" class="wizard-step-panel hidden bg-slate-50/50 border border-slate-200/90 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-file-pdf text-primary text-xs"></i>
                    <span>5. وثيقة الأرشيف الرقمي (PDF)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                    <!-- نوع الوثيقة -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">نوع الوثيقة</label>
                        <div class="flex items-center gap-1.5">
                            <select name="document_type_id" id="desktop_doc_type_select" data-quick-type="doc_type" data-quick-title="إضافة نوع وثيقة" data-quick-label="اسم تصنيف الوثيقة الجديد:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                                @foreach($docTypes as $docT)
                                    <option value="{{ $docT->document_type_id }}">{{ $docT->type_name_ar }}</option>
                                @endforeach
                                <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة نوع وثيقة جديد...</option>
                            </select>
                            <button type="button" onclick="openQuickAdd('doc_type', 'desktop_doc_type_select', 'اسم تصنيف الوثيقة الجديد:', 'إضافة نوع وثيقة')"
                                    class="shrink-0 w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-300 flex items-center justify-center text-xs font-bold transition shadow-2xs hover:scale-105 cursor-pointer"
                                    title="إضافة نوع وثيقة جديد">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">عنوان الوثيقة</label>
                        <input type="text" name="document_title" value="{{ old('document_title', 'قرار التعيين والملف الموحد') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-slate-700">ملف الوثيقة (PDF أو صورة)</label>
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">الحد الأقصى: 25MB</span>
                        </div>
                        <input type="file" name="document_file" accept="application/pdf,image/*" 
                               onchange="validateFileSize(this, 25)"
                               class="w-full bg-white border border-slate-300 rounded-md px-2 py-1 text-xs text-slate-500 file:ml-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-bold file:bg-primary file:text-white file:cursor-pointer shadow-2xs">
                    </div>
                </div>
            </div>

        </form>

        <!-- شريط التقدم وأزرار التنقل بالأسهم (Sticky Wizard Footer) -->
        <div class="bg-slate-50 border-t border-slate-200 px-5 py-3 flex items-center justify-between gap-4 shrink-0 select-none">
            <button type="button" id="btnWizardPrev" onclick="prevWizardStep()"
                    class="hidden px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold text-xs transition flex items-center gap-2 cursor-pointer shadow-2xs">
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                <span>السابق</span>
            </button>
            <div id="btnWizardPrevSpacer" class="w-20"></div>

            <!-- شريط نسبة الإنجاز -->
            <div class="flex-1 max-w-md flex flex-col gap-1">
                <div class="flex items-center justify-between text-[11px] font-bold">
                    <span class="text-slate-600" id="wizardStepText">الخطوة 1 من 5</span>
                    <span class="font-mono text-[#14532d]" id="wizardPercentText">20%</span>
                </div>
                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div id="wizardProgressBar" class="h-full bg-[#14532d] rounded-full transition-all duration-300" style="width: 20%"></div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="btnWizardNext" onclick="nextWizardStep()"
                        class="px-5 py-2 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white font-bold text-xs transition flex items-center gap-2 cursor-pointer shadow-xs">
                    <span>التالي</span>
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                </button>
                <button type="button" id="btnWizardSubmit" onclick="submitStaffForm()"
                        class="hidden px-5 py-2 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white font-bold text-xs transition flex items-center gap-2 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-check text-[11px]"></i>
                    <span>حفظ السجل</span>
                </button>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    // التبديل التفاعلي بين أنواع ومسارات الكادر (أكاديمي / إداري / متقاعدين بالساعات)
    function switchCadreType(type) {
        const hiddenInput = document.getElementById('hidden_cadre_type');
        if (hiddenInput) hiddenInput.value = type;

        // بطاقات الاختيار
        const cards = {
            academic: document.getElementById('card-cadre-academic'),
            admin: document.getElementById('card-cadre-admin'),
            hourly: document.getElementById('card-cadre-hourly')
        };

        Object.keys(cards).forEach(key => {
            const card = cards[key];
            if (!card) return;
            if (key === type) {
                card.classList.remove('bg-white', 'border-slate-200', 'text-slate-700', 'hover:bg-slate-100');
                card.classList.add('bg-[#14532d]', 'border-[#14532d]', 'text-white', 'shadow-2xs');
            } else {
                card.classList.remove('bg-[#14532d]', 'border-[#14532d]', 'text-white', 'shadow-2xs');
                card.classList.add('bg-white', 'border-slate-200', 'text-slate-700', 'hover:bg-slate-100');
            }
        });

        // إظهار وإخفاء الحقول المخصصة لكل هيكل
        document.querySelectorAll('.cadre-field-academic').forEach(el => {
            if (type === 'academic' || type === 'hourly') {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });

        document.querySelectorAll('.cadre-field-admin').forEach(el => {
            if (type === 'admin') {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });

        document.querySelectorAll('.cadre-field-hourly').forEach(el => {
            if (type === 'hourly') {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });

        // تحديث عناوين الأقسام والتسميات
        const sec2Title = document.getElementById('sec2-title-text');
        const facLabel = document.getElementById('label-faculty');
        const deptLabel = document.getElementById('label-dept');
        const catSelect = document.getElementById('desktop_category_select');
        const basisInput = document.getElementById('appointment_basis_input');

        if (type === 'academic') {
            if (sec2Title) sec2Title.textContent = '2. التسكين الأكاديمي والتكليف بالقسم العلمي';
            if (facLabel) facLabel.innerHTML = 'الكلية / المركز الأكاديمي <span class="text-rose-600">*</span>';
            if (deptLabel) deptLabel.innerHTML = 'القسم العلمي <span class="text-rose-600">*</span>';
            if (catSelect) catSelect.value = '1';
            if (basisInput && (!basisInput.value || basisInput.value.includes('ساعات') || basisInput.value.includes('إداري'))) {
                basisInput.value = 'تثبيت رسمي';
            }
        } else if (type === 'admin') {
            if (sec2Title) sec2Title.textContent = '2. التسكين والتعيين بالهيكل الإداري';
            if (facLabel) facLabel.innerHTML = 'الجهة / الكلية / الإدارة العامة <span class="text-rose-600">*</span>';
            if (deptLabel) deptLabel.innerHTML = 'الإدارة / القسم التابع <span class="text-rose-600">*</span>';
            if (catSelect) catSelect.value = '2';
            if (basisInput && (!basisInput.value || basisInput.value.includes('ساعات') || basisInput.value.includes('أكاديمي'))) {
                basisInput.value = 'تثبيت إداري رسمي';
            }
        } else if (type === 'hourly') {
            if (sec2Title) sec2Title.textContent = '2. بيانات الاستعانة والتدريس بالساعات (متقاعد)';
            if (facLabel) facLabel.innerHTML = 'الكلية المستعينة <span class="text-rose-600">*</span>';
            if (deptLabel) deptLabel.innerHTML = 'القسم العلمي المستعين <span class="text-rose-600">*</span>';
            if (catSelect) catSelect.value = '7';
            if (basisInput) basisInput.value = 'تدريس بالساعات الإضافية';
        }

        if (typeof updateFormScrollIndicator === 'function') {
            updateFormScrollIndicator();
        }
    }

    // معالجة اختيار "+ إضافة جديد..." من القائمة المنسدلة مباشرة
    function handleQuickAddChange(selectEl) {
        if (selectEl.value === '__add_new__') {
            const type = selectEl.getAttribute('data-quick-type');
            const title = selectEl.getAttribute('data-quick-title');
            const label = selectEl.getAttribute('data-quick-label');
            let parentId = null;

            if (type === 'department') {
                const facultySelect = document.getElementById('desktop_faculty_select');
                if (!facultySelect || !facultySelect.value || facultySelect.value === '__add_new__') {
                    alert('يرجى اختيار الكلية أو المركز أولاً قبل إضافة قسم علمي جديد.');
                    selectEl.value = selectEl.dataset.previousValue || '';
                    return;
                }
                parentId = facultySelect.value;
            }

            if (typeof window.openQuickAdd === 'function') {
                window.openQuickAdd(type, selectEl.id, label, title, parentId);
            }
        } else {
            selectEl.dataset.previousValue = selectEl.value;
        }
    }

    function filterFacultyDepartments(selectedDeptId = null) {
        let facultySelect = document.getElementById('desktop_faculty_select');
        let facultyId = facultySelect ? facultySelect.value : '';
        if (facultyId === '__add_new__') return;

        let deptSelect = document.getElementById('desktop_dept_select');
        let options = deptSelect.querySelectorAll('option');

        let matched = false;
        options.forEach(opt => {
            if (!opt.value) return;
            if (opt.value === '__add_new__') {
                opt.style.display = 'block';
                return;
            }
            if (!facultyId || opt.getAttribute('data-parent') === facultyId) {
                opt.style.display = 'block';
                if (selectedDeptId && opt.value === selectedDeptId) {
                    opt.selected = true;
                    matched = true;
                } else if (!selectedDeptId && !matched && !deptSelect.value) {
                    opt.selected = true;
                    matched = true;
                }
            } else {
                opt.style.display = 'none';
            }
        });

        if (selectedDeptId) {
            deptSelect.value = selectedDeptId;
        }
    }

    // التهيئة التلقائية عند الدخول بروابط محددة الكلية والقسم مسبقاً ونوع الكادر
    document.addEventListener('DOMContentLoaded', function() {
        const currentCadre = document.getElementById('hidden_cadre_type')?.value || 'academic';
        switchCadreType(currentCadre);

        const urlParams = new URLSearchParams(window.location.search);
        const reqFacultyId = urlParams.get('faculty_id');
        const reqDeptId = urlParams.get('dept_id');

        if (reqDeptId) {
            const deptSelect = document.getElementById('desktop_dept_select');
            const deptOpt = deptSelect ? deptSelect.querySelector(`option[value="${reqDeptId}"]`) : null;
            const parentFacId = deptOpt ? deptOpt.getAttribute('data-parent') : reqFacultyId;
            const facSelect = document.getElementById('desktop_faculty_select');

            if (facSelect && parentFacId) {
                facSelect.value = parentFacId;
                filterFacultyDepartments(reqDeptId);
            }
            if (deptSelect) {
                deptSelect.value = reqDeptId;
            }
        } else if (reqFacultyId) {
            const facSelect = document.getElementById('desktop_faculty_select');
            if (facSelect) {
                facSelect.value = reqFacultyId;
                filterFacultyDepartments();
            }
        }
    });

    // التمرير السلس للأسفل عبر المؤشر العائم
    function scrollFormDown() {
        const formEl = document.getElementById('main-staff-form');
        if (formEl) {
            formEl.scrollBy({ top: 320, behavior: 'smooth' });
        }
    }

    function updateFormScrollIndicator() {
        const formEl = document.getElementById('main-staff-form');
        const indicator = document.getElementById('scroll-form-indicator');
        if (!formEl || !indicator) return;

        const hasMoreToScroll = formEl.scrollHeight - formEl.scrollTop - formEl.clientHeight > 35;
        if (hasMoreToScroll) {
            indicator.classList.remove('hidden');
        } else {
            indicator.classList.add('hidden');
        }
    }

    // التنقل السلس بين صناديق الإدخال باستخدام مفتاح Enter
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('main-staff-form');
        if (!form) return;

        // تهيئة القيمة السابقة لكافة القوائم المنسدلة لدعم التراجع عند الإلغاء
        form.querySelectorAll('select[data-quick-type]').forEach(sel => {
            sel.dataset.previousValue = sel.value;
        });

        // متابعة حركة التمرير لتحديث المؤشر السفلي
        form.addEventListener('scroll', updateFormScrollIndicator);
        window.addEventListener('resize', updateFormScrollIndicator);
        setTimeout(updateFormScrollIndicator, 150);

        // دالة حصر جميع حقول وصناديق الإدخال النشطة والمرئية بالترتيب
        function getFormInputs() {
            const selector = 'input:not([type="hidden"]):not([type="button"]):not([type="submit"]):not([type="reset"]):not([disabled]), select:not([disabled]), textarea:not([disabled])';
            const elements = Array.from(form.querySelectorAll(selector));
            return elements.filter(el => {
                // التأكد من أن الحقل مرئي وليس مخفياً أو للقراءة فقط
                if (el.readOnly) return false;
                if (typeof el.checkVisibility === 'function') {
                    return el.checkVisibility();
                }
                return el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0;
            });
        }

        // التركيز التلقائي على أول صندوق إدخال (الاسم الرباعي) فور فتح الصفحة
        const initialInputs = getFormInputs();
        if (initialInputs.length > 0) {
            initialInputs[0].focus();
            if (typeof initialInputs[0].select === 'function') {
                initialInputs[0].select();
            }
        }

        // الاستماع لضغطات المفاتيح داخل النموذج
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const target = e.target;

                // استثناء مربعات النص المتعددة (textarea) إذا لم يتم الضغط على Ctrl
                if (target.tagName.toLowerCase() === 'textarea' && !e.ctrlKey) {
                    return;
                }

                // استثناء الأزرار العادية وزر الإرسال
                if (target.tagName.toLowerCase() === 'button' || target.type === 'submit') {
                    return;
                }

                // إذا كان خيار الإضافة السريعة محدداً، لا نتدخل لأن النافذة ستفتح
                if (target.tagName.toLowerCase() === 'select' && target.value === '__add_new__') {
                    return;
                }

                const inputs = getFormInputs();
                const currentIndex = inputs.indexOf(target);

                if (currentIndex !== -1) {
                    e.preventDefault(); // منع الإرسال التلقائي للفورم

                    // عند الضغط على Shift + Enter: الرجوع إلى الصندوق السابق
                    if (e.shiftKey) {
                        if (currentIndex > 0) {
                            const prevInput = inputs[currentIndex - 1];
                            prevInput.focus();
                            if (typeof prevInput.select === 'function' && prevInput.type !== 'date' && prevInput.type !== 'file' && prevInput.tagName.toLowerCase() !== 'select') {
                                prevInput.select();
                            }
                            prevInput.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                        return;
                    }

                    // عند الضغط على Enter: الانتقال إلى صندوق الإدخال التالي
                    if (currentIndex < inputs.length - 1) {
                        const nextInput = inputs[currentIndex + 1];
                        nextInput.focus();
                        if (typeof nextInput.select === 'function' && nextInput.type !== 'date' && nextInput.type !== 'file' && nextInput.tagName.toLowerCase() !== 'select') {
                            nextInput.select();
                        }
                        nextInput.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        // عند الوصول لآخر صندوق إدخال: التركيز على زر "حفظ السجل"
                        const saveBtn = document.getElementById('btn-save-staff') || document.querySelector('button[type="submit"][form="main-staff-form"]');
                        if (saveBtn) {
                            saveBtn.focus();
                            saveBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }
                }
            }
        });
    });

    // منطق المعالج الأفقي ذي الخطوات الخمس (5-Step Horizontal Wizard)
    let currentWizardStep = 1;
    const totalWizardSteps = 5;
    window.isEmployeeNumberDuplicate = false;
    window.isNationalIdDuplicate = false;
    let checkDuplicateTimeout = null;

    function checkDuplicateLive(field, value, feedbackId) {
        clearTimeout(checkDuplicateTimeout);
        const fbEl = document.getElementById(feedbackId);
        if (!fbEl) return;

        const val = (value || '').trim();
        if (!val) {
            fbEl.classList.add('hidden');
            fbEl.innerHTML = '';
            if (field === 'employee_number') window.isEmployeeNumberDuplicate = false;
            if (field === 'national_id') window.isNationalIdDuplicate = false;
            return;
        }

        checkDuplicateTimeout = setTimeout(() => {
            fetch(`{{ route('employees.check_duplicate') }}?field=${encodeURIComponent(field)}&value=${encodeURIComponent(val)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    if (field === 'employee_number') window.isEmployeeNumberDuplicate = true;
                    if (field === 'national_id') window.isNationalIdDuplicate = true;
                    fbEl.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500"></i> <span>${data.message}</span>`;
                    fbEl.classList.remove('hidden');
                } else {
                    if (field === 'employee_number') window.isEmployeeNumberDuplicate = false;
                    if (field === 'national_id') window.isNationalIdDuplicate = false;
                    fbEl.classList.add('hidden');
                    fbEl.innerHTML = '';
                }
            })
            .catch(() => {});
        }, 300);
    }

    function showWizardAlert(msg, fieldToFocus = null) {
        let alertBox = document.getElementById('wizard-validation-toast');
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.id = 'wizard-validation-toast';
            alertBox.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[999999] max-w-lg w-[92%] bg-rose-700 text-white text-xs font-bold px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 transition-all duration-300 transform -translate-y-4 opacity-0 pointer-events-auto border border-rose-500';
            document.body.appendChild(alertBox);
        }
        alertBox.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-exclamation text-amber-200 text-base"></i>
            </div>
            <div class="flex-1 leading-relaxed">${msg}</div>
            <button type="button" onclick="this.parentElement.classList.add('opacity-0', '-translate-y-4');" class="text-white/80 hover:text-white text-lg font-bold p-1 cursor-pointer">&times;</button>
        `;
        alertBox.classList.remove('opacity-0', '-translate-y-4', 'hidden');
        alertBox.classList.add('opacity-100', 'translate-y-0');

        if (fieldToFocus) {
            fieldToFocus.classList.add('ring-2', 'ring-rose-500', 'border-rose-500', 'bg-rose-50/40');
            try {
                fieldToFocus.focus();
                fieldToFocus.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch(e) {}
            
            const clearHighlight = () => {
                fieldToFocus.classList.remove('ring-2', 'ring-rose-500', 'border-rose-500', 'bg-rose-50/40');
                fieldToFocus.removeEventListener('input', clearHighlight);
                fieldToFocus.removeEventListener('change', clearHighlight);
            };
            fieldToFocus.addEventListener('input', clearHighlight);
            fieldToFocus.addEventListener('change', clearHighlight);
        }

        clearTimeout(window.wizardAlertTimeout);
        window.wizardAlertTimeout = setTimeout(() => {
            alertBox.classList.remove('opacity-100', 'translate-y-0');
            alertBox.classList.add('opacity-0', '-translate-y-4');
        }, 5000);
    }

    function validateWizardStep(stepNum) {
        const panel = document.getElementById('wizard-step-' + stepNum);
        if (!panel) return true;

        if (stepNum === 1) {
            const nameInput = panel.querySelector('input[name="full_name_ar"]');
            if (!nameInput || !nameInput.value.trim()) {
                showWizardAlert('يرجى تعبئة الاسم الرباعي الكامل للكادر للانتقال للخطوة التالية.', nameInput);
                return false;
            }
            const genderSelect = panel.querySelector('select[name="gender"]');
            if (!genderSelect || !genderSelect.value) {
                showWizardAlert('يرجى تحديد الجنس للكادر.', genderSelect);
                return false;
            }
            const empNumInput = document.getElementById('employee_number_input');
            if (!empNumInput || !empNumInput.value.trim()) {
                showWizardAlert('يرجى تعبئة الرقم الوظيفي للكادر (أو اعتماد الرقم التلقائي) للمتابعة.', empNumInput);
                return false;
            }
            if (window.isEmployeeNumberDuplicate) {
                showWizardAlert('الرقم الوظيفي مسجل مسبقاً لموظف آخر، يرجى كتابة رقم مختلف.', empNumInput);
                return false;
            }
            if (window.isNationalIdDuplicate) {
                const natIdInput = document.getElementById('national_id_input');
                showWizardAlert('الرقم الوطني / البطاقة مسجل مسبقاً لموظف آخر في النظام!', natIdInput);
                return false;
            }
        } else if (stepNum === 2) {
            const facSelect = document.getElementById('desktop_faculty_select');
            if (facSelect && (!facSelect.value || facSelect.value === '__add_new__')) {
                showWizardAlert('يرجى اختيار الكلية أو المركز أولاً للمتابعة.', facSelect);
                return false;
            }
            const deptSelect = document.getElementById('desktop_dept_select');
            if (deptSelect && (!deptSelect.value || deptSelect.value === '__add_new__')) {
                showWizardAlert('يرجى اختيار القسم العلمي أو الوحدة التابع لها الكادر.', deptSelect);
                return false;
            }
            const statusSelect = document.getElementById('desktop_status_select');
            if (statusSelect && (!statusSelect.value || statusSelect.value === '__add_new__')) {
                showWizardAlert('يرجى تحديد الموقف الوظيفي للكادر.', statusSelect);
                return false;
            }
            const cadreType = document.getElementById('hidden_cadre_type')?.value || 'academic';
            if (cadreType === 'admin') {
                const adminTitle = document.getElementById('admin_job_title');
                if (adminTitle && !adminTitle.value.trim()) {
                    showWizardAlert('يرجى كتابة المسمى الوظيفي الإداري للكادر.', adminTitle);
                    return false;
                }
            } else if (cadreType === 'hourly') {
                const hourlyHours = panel.querySelector('input[name="hourly_hours"]');
                if (hourlyHours && !hourlyHours.value.trim()) {
                    showWizardAlert('يرجى إدخال نصاب الساعات المعتمدة للمتعاقد بالساعات.', hourlyHours);
                    return false;
                }
            }
            const startDate = panel.querySelector('input[name="start_date"]');
            if (startDate && !startDate.value) {
                showWizardAlert('يرجى إدخال تاريخ مباشرة العمل.', startDate);
                return false;
            }
        } else if (stepNum === 3) {
            const pStatus = document.getElementById('payroll_status_select');
            if (pStatus && (!pStatus.value || pStatus.value === '__add_new__')) {
                showWizardAlert('يرجى تحديد الحالة بكشف الراتب (مثال: على رأس العمل).', pStatus);
                return false;
            }
            const pTitle = document.getElementById('payroll_job_title_select');
            if (pTitle && (!pTitle.value || pTitle.value === '__add_new__')) {
                showWizardAlert('يرجى اختيار أو إدخال الوظيفة بكشف الراتب.', pTitle);
                return false;
            }
            const pGrade = document.getElementById('payroll_job_grade_select');
            if (pGrade && (!pGrade.value || pGrade.value === '__add_new__')) {
                showWizardAlert('يرجى اختيار أو إدخال الدرجة بكشف الراتب.', pGrade);
                return false;
            }
            const qualApp = document.getElementById('qualification_at_appointment_select');
            if (qualApp && (!qualApp.value || qualApp.value === '__add_new__')) {
                showWizardAlert('يرجى تحديد المؤهل عند التعيين بالجامعة.', qualApp);
                return false;
            }
        } else if (stepNum === 4) {
            const decNum = panel.querySelector('input[name="decision_number"]');
            if (decNum && !decNum.value.trim()) {
                showWizardAlert('يرجى إدخال رقم القرار الإداري التأسيسي.', decNum);
                return false;
            }
            const qualLevel = document.getElementById('desktop_qual_level_select');
            if (qualLevel && (!qualLevel.value || qualLevel.value === '__add_new__')) {
                showWizardAlert('يرجى تحديد مستوى المؤهل العلمي.', qualLevel);
                return false;
            }
            const univSelect = document.getElementById('university_name_select');
            if (univSelect && (!univSelect.value || univSelect.value === '__add_new__')) {
                showWizardAlert('يرجى اختيار الجامعة المانحة للمؤهل أو إضافتها.', univSelect);
                return false;
            }
        }

        // تحقق عام من أي حقل required آخر داخل اللوحة غير مخفي
        const requiredFields = panel.querySelectorAll('input[required], select[required], textarea[required]');
        for (const field of requiredFields) {
            if (field.closest('.hidden')) continue;
            if (!field.checkValidity()) {
                field.reportValidity();
                showWizardAlert('يرجى استكمال الحقل المطلوب للمتابعة.', field);
                return false;
            }
        }
        return true;
    }

    function goToWizardStep(stepNum, skipValidation = false) {
        if (stepNum < 1 || stepNum > totalWizardSteps) return;
        if (!skipValidation && stepNum > currentWizardStep) {
            for (let s = currentWizardStep; s < stepNum; s++) {
                if (!validateWizardStep(s)) {
                    return;
                }
            }
        }

        currentWizardStep = stepNum;

        // إظهار المرحلة المطلوبة وإخفاء البقية تماماً بدون سكرول
        document.querySelectorAll('.wizard-step-panel').forEach((panel, idx) => {
            if (idx + 1 === stepNum) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });

        // تحديث شريط التبويبات الأفقي في الأعلى
        for (let i = 1; i <= totalWizardSteps; i++) {
            const tab = document.getElementById('wizard-tab-' + i);
            if (!tab) continue;
            const numSpan = tab.querySelector('.wizard-step-num');
            tab.classList.remove('bg-[#14532d]', 'text-white', 'border-[#14532d]', 'shadow-2xs', 'bg-emerald-50', 'text-[#14532d]', 'border-emerald-300', 'bg-slate-50', 'text-slate-500', 'border-slate-200');
            if (numSpan) {
                numSpan.classList.remove('bg-white/20', 'text-white', 'bg-[#14532d]', 'bg-slate-200', 'text-slate-700');
            }

            if (i === stepNum) {
                tab.classList.add('bg-[#14532d]', 'text-white', 'border-[#14532d]', 'shadow-2xs');
                if (numSpan) {
                    numSpan.innerText = i;
                    numSpan.classList.add('bg-white/20', 'text-white');
                }
            } else if (i < stepNum) {
                tab.classList.add('bg-emerald-50', 'text-[#14532d]', 'border-emerald-300');
                if (numSpan) {
                    numSpan.innerHTML = '<i class="fa-solid fa-check text-[9px]"></i>';
                    numSpan.classList.add('bg-[#14532d]', 'text-white');
                }
            } else {
                tab.classList.add('bg-slate-50', 'text-slate-500', 'border-slate-200');
                if (numSpan) {
                    numSpan.innerText = i;
                    numSpan.classList.add('bg-slate-200', 'text-slate-700');
                }
            }
        }

        // تحديث شريط التقدم والأزرار السفلية
        const pct = Math.round((stepNum / totalWizardSteps) * 100);
        const bar = document.getElementById('wizardProgressBar');
        const stepTxt = document.getElementById('wizardStepText');
        const pctTxt = document.getElementById('wizardPercentText');
        if (bar) bar.style.width = pct + '%';
        if (stepTxt) stepTxt.innerText = `الخطوة ${stepNum} من ${totalWizardSteps}`;
        if (pctTxt) pctTxt.innerText = pct + '%';

        const btnPrev = document.getElementById('btnWizardPrev');
        const prevSpacer = document.getElementById('btnWizardPrevSpacer');
        const btnNext = document.getElementById('btnWizardNext');
        const btnSubmit = document.getElementById('btnWizardSubmit');

        if (btnPrev && prevSpacer) {
            if (stepNum === 1) {
                btnPrev.classList.add('hidden');
                prevSpacer.classList.remove('hidden');
            } else {
                btnPrev.classList.remove('hidden');
                prevSpacer.classList.add('hidden');
            }
        }

        if (btnNext && btnSubmit) {
            if (stepNum === totalWizardSteps) {
                btnNext.classList.add('hidden');
                btnSubmit.classList.remove('hidden');
            } else {
                btnNext.classList.remove('hidden');
                btnSubmit.classList.add('hidden');
            }
        }
    }

    function nextWizardStep() {
        goToWizardStep(currentWizardStep + 1);
    }

    function prevWizardStep() {
        goToWizardStep(currentWizardStep - 1, true);
    }

    // إرسال وحفظ النموذج مع التحقق الشامل من كافة المراحل
    function submitStaffForm(e) {
        if (e && e.preventDefault) e.preventDefault();

        // فحص كافة خطوات النموذج من 1 إلى 4 للتأكد من خلوها من النواقص
        for (let s = 1; s <= 4; s++) {
            if (!validateWizardStep(s)) {
                goToWizardStep(s, true);
                return false;
            }
        }

        if (window.isEmployeeNumberDuplicate) {
            goToWizardStep(1, true);
            const empNumInput = document.getElementById('employee_number_input');
            showWizardAlert('الرقم الوظيفي مسجل مسبقاً لموظف آخر، يرجى كتابة رقم مختلف.', empNumInput);
            return false;
        }

        if (window.isNationalIdDuplicate) {
            goToWizardStep(1, true);
            const natIdInput = document.getElementById('national_id_input');
            showWizardAlert('الرقم الوطني / البطاقة مسجل مسبقاً لموظف آخر في النظام!', natIdInput);
            return false;
        }

        const btnTop = document.getElementById('btn-save-staff');
        const btnBottom = document.getElementById('btnWizardSubmit');
        if (btnTop) {
            btnTop.disabled = true;
            btnTop.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري حفظ السجل...';
        }
        if (btnBottom) {
            btnBottom.disabled = true;
            btnBottom.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري حفظ السجل...';
        }

        document.getElementById('main-staff-form').submit();
        return true;
    }

    function handleMainStaffFormSubmit(e) {
        return submitStaffForm(e);
    }

    // في حال وجود حقل مطلوب غير مكتمل عند محاولة الحفظ المباشر من الأعلى، الانتقال تلقائياً لخطوته
    document.getElementById('main-staff-form')?.addEventListener('invalid', function(e) {
        const panel = e.target.closest('.wizard-step-panel');
        if (panel && panel.id) {
            const sNum = parseInt(panel.id.replace('wizard-step-', ''), 10);
            if (!isNaN(sNum) && sNum !== currentWizardStep) {
                goToWizardStep(sNum, true);
            }
        }
    }, true);
</script>
@endsection
