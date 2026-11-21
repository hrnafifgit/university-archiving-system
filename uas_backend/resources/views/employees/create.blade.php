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
            <button type="submit" form="main-staff-form" id="btn-save-staff" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-4 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <i class="fa-solid fa-check text-white"></i> حفظ السجل
            </button>
        </div>
    </div>

    <!-- 2. حاوية النموذج المستقلة مع إخفاء شريط التمرير (Ghost Scroll) -->
    <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden relative">
        
        <form id="main-staff-form" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" 
              class="flex-1 overflow-y-auto no-scrollbar p-3.5 space-y-3">
            @csrf

            <!-- القسم 1: البيانات الشخصية والأساسية -->
            <div class="bg-slate-50/50 border border-slate-200/90 rounded-lg p-3">
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
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الرقم الوظيفي</label>
                        <input type="text" name="employee_number" value="{{ old('employee_number') }}" placeholder="مثال: 10045" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono font-bold text-primary focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الرقم الوطني / البطاقة</label>
                        <input type="text" name="national_id" value="{{ old('national_id') }}" placeholder="رقم البطاقة الشخصية" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم الهاتف</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="77xxxxxxx" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
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
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الصورة الشخصية الرسمية (JPG/PNG)</label>
                        <input type="file" name="photo_file" accept="image/*" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2 py-1 text-xs text-slate-500 file:ml-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-bold file:bg-primary file:text-white file:cursor-pointer shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- القسم 2: التسكين والتكليف بالهيكل الجامعي -->
            <div class="bg-slate-50/50 border border-slate-200/90 rounded-lg p-3">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-sitemap text-primary text-xs"></i>
                    <span>2. التسكين والتكليف بالهيكل الجامعي</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الفئة الوظيفية <span class="text-rose-600">*</span></label>
                        <select name="employee_category_id" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->employee_category_id }}" {{ old('employee_category_id') == $cat->employee_category_id ? 'selected' : '' }}>
                                    {{ $cat->category_name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- الكلية / المركز -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الكلية / المركز <span class="text-rose-600">*</span></label>
                        <select id="desktop_faculty_select" data-quick-type="faculty" data-quick-title="إضافة كلية / مركز" data-quick-label="اسم الكلية أو المركز الجديد:" onchange="handleQuickAddChange(this); filterFacultyDepartments();" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            <option value="">-- اختر الكلية أو المركز --</option>
                            @foreach($colleges as $col)
                                <option value="{{ $col->organizational_unit_id }}">{{ $col->unit_name_ar }}</option>
                            @endforeach
                            <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة كلية / مركز جديد...</option>
                        </select>
                    </div>

                    <!-- القسم / الوحدة -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">القسم / الوحدة <span class="text-rose-600">*</span></label>
                        <select name="organizational_unit_id" id="desktop_dept_select" data-quick-type="department" data-quick-title="إضافة قسم علمي" data-quick-label="اسم القسم العلمي الجديد:" onchange="handleQuickAddChange(this)" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            <option value="">-- اختر القسم أولاً --</option>
                            @foreach($colleges as $col)
                                @foreach($col->children as $dept)
                                    <option value="{{ $dept->organizational_unit_id }}" data-parent="{{ $col->organizational_unit_id }}">
                                        {{ $dept->unit_name_ar }} ({{ $col->unit_code }})
                                    </option>
                                @endforeach
                            @endforeach
                            <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة قسم علمي جديد...</option>
                        </select>
                    </div>

                    <!-- الموقف الوظيفي -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الموقف الوظيفي <span class="text-rose-600">*</span></label>
                        <select name="employment_status_id" id="desktop_status_select" data-quick-type="status" data-quick-title="إضافة موقف وظيفي" data-quick-label="اسم الموقف الوظيفي الجديد:" onchange="handleQuickAddChange(this)" required class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            @foreach($statuses as $st)
                                <option value="{{ $st->employment_status_id }}" {{ old('employment_status_id') == $st->employment_status_id ? 'selected' : '' }}>
                                    {{ $st->status_name_ar }}
                                </option>
                            @endforeach
                            <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة موقف وظيفي جديد...</option>
                        </select>
                    </div>

                    <!-- الرتبة الأكاديمية -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الرتبة الأكاديمية</label>
                        <select name="academic_rank_id" id="desktop_rank_select" data-quick-type="rank" data-quick-title="إضافة رتبة أكاديمية" data-quick-label="اسم الرتبة العلمية الجديدة:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-bold focus:border-primary focus:outline-none transition shadow-2xs">
                            <option value="">-- بدون رتبة --</option>
                            @foreach($ranks as $rnk)
                                <option value="{{ $rnk->academic_rank_id }}" {{ old('academic_rank_id') == $rnk->academic_rank_id ? 'selected' : '' }}>
                                    {{ $rnk->rank_name_ar }}
                                </option>
                            @endforeach
                            <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة رتبة أكاديمية جديدة...</option>
                        </select>
                    </div>

                    <!-- الدرجة الوظيفية -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الدرجة الوظيفية</label>
                        <select name="job_grade_id" id="desktop_grade_select" data-quick-type="grade" data-quick-title="إضافة درجة مالية" data-quick-label="اسم الدرجة الوظيفية الجديدة:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                            <option value="">-- اختر الدرجة --</option>
                            @foreach($grades as $grd)
                                <option value="{{ $grd->job_grade_id }}" {{ old('job_grade_id') == $grd->job_grade_id ? 'selected' : '' }}>
                                    {{ $grd->grade_name_ar }}
                                </option>
                            @endforeach
                            <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة درجة وظيفية جديدة...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ المباشرة <span class="text-rose-600">*</span></label>
                        <input type="date" name="start_date" required value="{{ old('start_date', date('Y-m-d')) }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">أساس التعيين</label>
                        <input type="text" name="appointment_basis" value="{{ old('appointment_basis', 'تثبيت رسمي') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- القسم 3: بيانات كشف الراتب وتاريخ التعيين -->
            <div class="bg-slate-50/50 border border-slate-200/90 rounded-lg p-3">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-file-invoice-dollar text-primary text-xs"></i>
                    <span>3. بيانات كشف الراتب وتاريخ التعيين</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الحالة بكشف الراتب</label>
                        <input type="text" name="payroll_status" value="{{ old('payroll_status', 'على رأس العمل') }}" placeholder="على رأس العمل، إجازة..." 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الوظيفة بكشف الراتب</label>
                        <input type="text" name="payroll_job_title" value="{{ old('payroll_job_title') }}" placeholder="هيئة تدريس، مدرس، معيد..." 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الدرجة بكشف الراتب</label>
                        <input type="text" name="payroll_job_grade" value="{{ old('payroll_job_grade') }}" placeholder="أستاذ، أستاذ مشارك..." 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">المؤهل عند التعيين بالجامعة</label>
                        <input type="text" name="qualification_at_appointment" value="{{ old('qualification_at_appointment') }}" placeholder="دكتوراه، ماجستير، بكالوريوس" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ التعيين بالوظيفة أول مرة</label>
                        <input type="date" name="first_appointment_date" value="{{ old('first_appointment_date') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ التعيين بالجامعة أكاديمي</label>
                        <input type="date" name="academic_appointment_date" value="{{ old('academic_appointment_date') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الدرجة المعين فيها أول مرة</label>
                        <input type="text" name="initial_rank" value="{{ old('initial_rank') }}" placeholder="معيد، أستاذ مساعد..." 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- القسم 4: القرار والمؤهل الأكاديمي -->
            <div class="bg-slate-50/50 border border-slate-200/90 rounded-lg p-3">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-stamp text-primary text-xs"></i>
                    <span>4. القرار والمؤهل الأكاديمي</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">نوع القرار</label>
                        <select name="decision_type_id" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                            @foreach($decisionTypes as $dt)
                                <option value="{{ $dt->decision_type_id }}">{{ $dt->type_name_ar }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم القرار</label>
                        <input type="text" name="decision_number" value="{{ old('decision_number') }}" placeholder="مثال: ق/2024/142" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-mono text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <!-- مستوى المؤهل -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">مستوى المؤهل</label>
                        <select name="qualification_level_id" id="desktop_qual_level_select" data-quick-type="qual_level" data-quick-title="إضافة مستوى مؤهل" data-quick-label="اسم المستوى العلمي الجديد:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                            <option value="">-- اختر المستوى --</option>
                            @foreach($qualLevels as $ql)
                                <option value="{{ $ql->qualification_level_id }}">{{ $ql->level_name_ar }}</option>
                            @endforeach
                            <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة مستوى مؤهل جديد...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">التخصص وعنوان الشهادة</label>
                        <input type="text" name="degree_title" value="{{ old('degree_title') }}" placeholder="دكتوراه في علوم الحاسوب" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">الجامعة المانحة للمؤهل</label>
                        <input type="text" name="university_name" value="{{ old('university_name') }}" placeholder="جامعة إب، جامعة القاهرة، جامعة دمشق..." 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- القسم 5: وثيقة الأرشيف الرقمي (PDF) -->
            <div class="bg-slate-50/50 border border-slate-200/90 rounded-lg p-3">
                <div class="flex items-center gap-2 pb-1.5 mb-2.5 border-b border-slate-200 text-xs font-bold text-slate-800">
                    <i class="fa-solid fa-file-pdf text-primary text-xs"></i>
                    <span>5. وثيقة الأرشيف الرقمي (PDF)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                    <!-- نوع الوثيقة -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">نوع الوثيقة</label>
                        <select name="document_type_id" id="desktop_doc_type_select" data-quick-type="doc_type" data-quick-title="إضافة نوع وثيقة" data-quick-label="اسم تصنيف الوثيقة الجديد:" onchange="handleQuickAddChange(this)" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                            @foreach($docTypes as $docT)
                                <option value="{{ $docT->document_type_id }}">{{ $docT->type_name_ar }}</option>
                            @endforeach
                            <option value="__add_new__" class="font-bold text-primary bg-amber-50">+ إضافة نوع وثيقة جديد...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">عنوان الوثيقة</label>
                        <input type="text" name="document_title" value="{{ old('document_title', 'قرار التعيين والملف الموحد') }}" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:outline-none transition shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">ملف PDF (حتى 20MB)</label>
                        <input type="file" name="document_file" accept="application/pdf" 
                               class="w-full bg-white border border-slate-300 rounded-md px-2 py-1 text-xs text-slate-500 file:ml-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-bold file:bg-primary file:text-white file:cursor-pointer shadow-2xs">
                    </div>
                </div>
            </div>

        </form>

        <!-- مؤشر التمرير الهادئ (رمز ^ معكوس / chevron-down) للدلالة على توفر حقول إضافية -->
        <button type="button" id="scroll-form-indicator" onclick="scrollFormDown()" 
                class="hidden absolute bottom-3 left-1/2 -translate-x-1/2 z-30 transition-all duration-300 bg-white/95 hover:bg-white text-slate-600 hover:text-primary border border-slate-200/90 rounded-full w-8 h-8 shadow-sm flex items-center justify-center cursor-pointer select-none"
                title="توجد حقول إضافية بالأسفل (انقر أو مرر للتمرير لأسفل)">
            <i class="fa-solid fa-chevron-down text-primary text-[11px] animate-subtle-bounce"></i>
        </button>

    </div>

</div>
@endsection

@section('scripts')
<script>
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

    function filterFacultyDepartments() {
        let facultySelect = document.getElementById('desktop_faculty_select');
        let facultyId = facultySelect ? facultySelect.value : '';
        if (facultyId === '__add_new__') return;

        let deptSelect = document.getElementById('desktop_dept_select');
        let options = deptSelect.querySelectorAll('option');

        let firstMatch = false;
        options.forEach(opt => {
            if (!opt.value) return;
            if (opt.value === '__add_new__') {
                opt.style.display = 'block';
                return;
            }
            if (!facultyId || opt.getAttribute('data-parent') === facultyId) {
                opt.style.display = 'block';
                if (!firstMatch) {
                    opt.selected = true;
                    firstMatch = true;
                }
            } else {
                opt.style.display = 'none';
            }
        });
    }

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
</script>
@endsection
