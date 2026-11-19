@extends('layouts.app')

@section('title', 'تسجيل كادر جديد | جامعة إب')
@section('page_title', 'شاشة تسجيل وإضافة كادر جديد')
@section('no_sidebar', true)
@section('no_header', true)

@section('content')
<div class="min-h-screen bg-slate-100 p-2 sm:p-6 flex flex-col justify-between">

    <!-- 1. شريط الترويسة العلوي المخصص لشاشة الإضافة الكاملة (Full Header) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3 space-x-reverse">
            <div class="w-12 h-12 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center p-1.5 shadow-sm">
                <img src="{{ asset('images/ibb_logo.png') }}" alt="جامعة إب" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-black text-slate-900 flex items-center">
                    <span>استمارة تسجيل وإضافة كادر جديد</span>
                    <span class="mr-2 text-xs bg-brand-50 text-brand-900 font-bold px-2 py-0.5 rounded-md border border-brand-200">
                        جامعة إب 🇾🇪
                    </span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5 font-semibold">
                    إدخال البيانات الأساسية، التخصيص الوظيفي (أكاديمي/إداري/متعاقد)، والأرشفة الرقمية
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-2 space-x-reverse">
            <a href="{{ route('dashboard') }}" 
               class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2.5 rounded-xl text-xs flex items-center transition">
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5 text-slate-500"></i>
                <span>العودة للوحة التحكم</span>
            </a>
            <button type="submit" form="staffCreateForm" 
                    class="bg-brand-900 hover:bg-brand-800 active:scale-95 text-white font-black px-6 py-2.5 rounded-xl text-xs sm:text-sm flex items-center shadow-lg shadow-brand-900/20 transition">
                <i data-lucide="save" class="w-4 h-4 ml-1.5"></i>
                <span>حفظ وتوثيق الكادر</span>
            </button>
        </div>
    </div>

    <!-- رسائل التنبيه والأخطاء إن وجدت -->
    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border-r-4 border-rose-600 rounded-2xl text-rose-800 text-xs shadow-sm">
        <div class="flex items-center font-bold mb-1">
            <i data-lucide="alert-triangle" class="w-4 h-4 ml-1.5 text-rose-600"></i>
            <span>يرجى مراجعة وتصحيح الحقول التالية:</span>
        </div>
        <ul class="list-disc pr-5 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- 2. النموذج الرئيسي المتكامل (The Main Comprehensive Form) -->
    <form id="staffCreateForm" action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- ========================================== -->
        <!-- القسم الأول: البيانات الأساسية المشتركة لكافة الكوادر -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center space-x-2.5 space-x-reverse">
                    <span class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center font-bold text-xs ml-2">1</span>
                    <div>
                        <h2 class="text-base font-black text-slate-900">أولاً: البيانات الأساسية المشتركة لكافة الكوادر</h2>
                        <p class="text-xs text-slate-400 font-semibold">المعلومات الشخصية والوظيفية التي يشترك بها كل عضو في جامعة إب</p>
                    </div>
                </div>
                <span class="text-[11px] font-bold text-brand-900 bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
                    بيانات إلزامية
                </span>
            </div>

            <!-- شبكة الحقول الأساسية -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-bold text-slate-700">
                
                <!-- الاسم الكامل الرباعي -->
                <div class="sm:col-span-2">
                    <label class="block mb-1.5">الاسم الرباعي الكامل <span class="text-rose-600">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required 
                           placeholder="مثال: د. محمد أحمد عبدالله الإرياني"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <!-- الرقم الوظيفي -->
                <div>
                    <label class="block mb-1.5">الرقم الوظيفي <span class="text-rose-600">*</span></label>
                    <input type="text" name="job_number" value="{{ old('job_number') }}" required 
                           placeholder="مثال: 10452"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <!-- الرقم الوطني / الهوية -->
                <div>
                    <label class="block mb-1.5">الرقم الوطني (البطاقة الشخصية) <span class="text-rose-600">*</span></label>
                    <input type="text" name="national_id" value="{{ old('national_id') }}" required 
                           placeholder="مثال: 03010045812"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <!-- الجنس -->
                <div>
                    <label class="block mb-1.5">الجنس <span class="text-rose-600">*</span></label>
                    <select name="gender" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        <option value="ذكر" {{ old('gender') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                        <option value="أنثى" {{ old('gender') == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                    </select>
                </div>

                <!-- تاريخ الميلاد -->
                <div>
                    <label class="block mb-1.5">تاريخ الميلاد <span class="text-rose-600">*</span></label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', '1985-01-01') }}" required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <!-- تاريخ التعيين بالجامعة -->
                <div>
                    <label class="block mb-1.5">تاريخ التعيين بالجامعة <span class="text-rose-600">*</span></label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', date('Y-m-d')) }}" required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <!-- الموقف الوظيفي (مستخرج من إكسل) -->
                <div>
                    <label class="block mb-1.5">الموقف الوظيفي <span class="text-rose-600">*</span></label>
                    <select name="job_status" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        @foreach($lookups['jobStatuses'] as $status)
                            <option value="{{ $status }}" {{ old('job_status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- رقم الهاتف -->
                <div>
                    <label class="block mb-1.5">رقم الهاتف / الواتساب</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" 
                           placeholder="مثال: 771234567"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <!-- البريد الإلكتروني -->
                <div>
                    <label class="block mb-1.5">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           placeholder="name@ibbuniv.edu.ye"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <!-- الكلية والجهة العليا + زر إضافة كلية جديدة -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label>الكلية / المركز <span class="text-rose-600">*</span></label>
                        <button type="button" onclick="openEntityModal()" class="text-[10px] text-brand-900 hover:underline font-bold bg-brand-50 px-1.5 py-0.5 rounded">
                            + إضافة كلية جديدة
                        </button>
                    </div>
                    <select id="entitySelect" onchange="filterDepartments()" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        <option value="">-- اختر الكلية أو المركز --</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ old('entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- القسم التابع + زر إضافة قسم جديد -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label>القسم الأكاديمي / الوحدة <span class="text-rose-600">*</span></label>
                        <button type="button" onclick="openDepartmentModal()" class="text-[10px] text-brand-900 hover:underline font-bold bg-brand-50 px-1.5 py-0.5 rounded">
                            + إضافة قسم جديد
                        </button>
                    </div>
                    <select name="department_id" id="departmentSelect" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        <option value="">-- اختر القسم التابع --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" data-entity-id="{{ $dept->entity_id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- القسم الثاني: تحديد تصنيف ونوع الكادر (Selection Cards) -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2.5 space-x-reverse">
                    <span class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center font-bold text-xs ml-2">2</span>
                    <div>
                        <h2 class="text-base font-black text-slate-900">ثانياً: تحديد نوع وتصنيف الكادر</h2>
                        <p class="text-xs text-slate-400 font-semibold">بناءً على اختيارك، سيتم فتح حقول الإدخال المتخصصة للكادر أدناه تلقائياً</p>
                    </div>
                </div>
            </div>

            <!-- بطاقات اختيار نوع الكادر الثلاثة -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <!-- 1. كادر أكاديمي -->
                <label id="typeCardAcademic" onclick="selectStaffType('academic')" 
                       class="cursor-pointer p-5 rounded-2xl border-2 border-brand-900 bg-brand-50/50 shadow-sm transition-all flex flex-col justify-between select-none">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <input type="radio" name="staff_type" value="academic" id="radioAcademic" checked class="w-4 h-4 text-brand-900 focus:ring-brand-900 ml-2">
                            <span class="text-base font-black text-slate-900">كادر أكاديمي</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-brand-900 text-white flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed font-semibold">
                        أعضاء هيئة التدريس ومساعدوهم (أستاذ دكتور، مشارك، مساعد، مدرس، معيد) مع الرتب والمؤهلات.
                    </p>
                    <span class="mt-4 text-[11px] font-bold text-brand-900 bg-white px-2.5 py-1 rounded-lg border border-brand-200 inline-block text-center">
                        ✓ مفعّل حالياً
                    </span>
                </label>

                <!-- 2. كادر إداري -->
                <label id="typeCardAdmin" onclick="selectStaffType('administrative')" 
                       class="cursor-pointer p-5 rounded-2xl border-2 border-slate-200 bg-white hover:border-slate-300 transition-all flex flex-col justify-between select-none">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <input type="radio" name="staff_type" value="administrative" id="radioAdmin" class="w-4 h-4 text-brand-900 focus:ring-brand-900 ml-2">
                            <span class="text-base font-black text-slate-900">كادر إداري وفني</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center">
                            <i data-lucide="briefcase" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed font-semibold">
                        الموظفون، مدراء العموم، الفنيون، والأخصائيون بالدرجات الوظيفية المعتمدة.
                    </p>
                    <span class="mt-4 text-[11px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200 inline-block text-center">
                        انقر للاختيار
                    </span>
                </label>

                <!-- 3. متعاقد بالساعات -->
                <label id="typeCardContract" onclick="selectStaffType('contract')" 
                       class="cursor-pointer p-5 rounded-2xl border-2 border-slate-200 bg-white hover:border-slate-300 transition-all flex flex-col justify-between select-none">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <input type="radio" name="staff_type" value="contract" id="radioContract" class="w-4 h-4 text-brand-900 focus:ring-brand-900 ml-2">
                            <span class="text-base font-black text-slate-900">متعاقد بالساعات</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed font-semibold">
                        المنتدبون والمتعاقدون فصلياً أو سنوياً بنظام الساعات المعتمدة في الكليات.
                    </p>
                    <span class="mt-4 text-[11px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200 inline-block text-center">
                        انقر للاختيار
                    </span>
                </label>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- القسم الثالث: حقول الإدخال الديناميكية المتخصصة بحسب نوع الكادر -->
        <!-- ========================================== -->
        
        <!-- 3.أ: حقول الكادر الأكاديمي (تظهر عند اختيار أكاديمي) -->
        <div id="fieldsAcademic" class="subtype-section bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center font-bold text-xs ml-2">3</span>
                    <div>
                        <h3 class="text-base font-black text-slate-900">ثالثاً: البيانات المتخصصة للكادر الأكاديمي</h3>
                        <p class="text-xs text-slate-400 font-semibold">الرتبة العلمية، التخصصات، النصاب، والمؤهل الدراسي الموثق</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full">
                    بيانات الهيئة الأكاديمية
                </span>
            </div>

            <!-- الحقول الأكاديمية -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs font-bold text-slate-700">
                <div>
                    <label class="block mb-1.5">الرتبة الأكاديمية الرسمية <span class="text-rose-600">*</span></label>
                    <select name="rank_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        @foreach($ranks as $rank)
                            <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5">التخصص العام <span class="text-rose-600">*</span></label>
                    <input type="text" name="general_specialization" value="{{ old('general_specialization', 'علوم الحاسوب وتكنولوجيا المعلومات') }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1.5">التخصص الدقيق <span class="text-rose-600">*</span></label>
                    <input type="text" name="exact_specialization" value="{{ old('exact_specialization', 'هندسة البرمجيات والذكاء الاصطناعي') }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1.5">الوضع الأكاديمي بالشؤون الأكاديمية</label>
                    <select name="academic_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        <option value="مستمر بالعمل">مستمر بالعمل وعلى رأس عمله</option>
                        <option value="موفد بالخارج">موفد بالخارج (دراسات عليا)</option>
                        <option value="تفرغ علمي">إجازة تفرغ علمي</option>
                        <option value="إجازة بدون راتب">إجازة بدون راتب</option>
                        <option value="منتدب">منتدب لجهة أخرى</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5">النصاب التدريسي (ساعات أسبوعية)</label>
                    <input type="number" name="teaching_load" value="12" min="0" max="30" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1.5">تاريخ آخر ترقية علمية</label>
                    <input type="date" name="last_promotion_date" value="{{ date('Y-m-d') }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>
            </div>

            <!-- المؤهل العلمي الموثق للشهادة -->
            <div class="pt-4 border-t border-slate-100">
                <span class="text-xs font-black text-slate-800 block mb-3">🎓 أعلى مؤهل علمي محقق:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs font-bold text-slate-700">
                    <div>
                        <label class="block mb-1.5">الدرجة العلمية</label>
                        <select name="degree_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                            @foreach($lookups['degrees'] as $deg)
                                <option value="{{ $deg }}">{{ $deg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1.5">الجامعة أو المؤسسة المانحة</label>
                        <input type="text" name="granting_institution" value="جامعة إب" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block mb-1.5">سنة التخرج</label>
                        <input type="number" name="graduation_year" value="{{ date('Y') - 3 }}" min="1970" max="{{ date('Y') }}" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block mb-1.5">دولة التخرج</label>
                        <select name="country" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                            @foreach($lookups['countries'] as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block mb-1.5">عنوان الأطروحة أو الرسالة العلمية</label>
                        <input type="text" name="thesis_title" placeholder="أدخل عنوان رسالة الدكتوراه أو الماجستير..." 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3.ب: حقول الكادر الإداري والفني (تظهر عند اختيار إداري) -->
        <div id="fieldsAdmin" class="subtype-section hidden bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs ml-2">3</span>
                    <div>
                        <h3 class="text-base font-black text-slate-900">ثالثاً: البيانات المتخصصة للكادر الإداري والفني</h3>
                        <p class="text-xs text-slate-400 font-semibold">المسمى الوظيفي، الدرجة المالية في جدول الرواتب، وتاريخ التثبيت</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-blue-800 bg-blue-50 px-3 py-1 rounded-full">
                    بيانات الكادر الإداري
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs font-bold text-slate-700">
                <div>
                    <label class="block mb-1.5">المسمى الوظيفي <span class="text-rose-600">*</span></label>
                    <select name="job_title" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        @foreach($lookups['positions'] as $pos)
                            <option value="{{ $pos }}">{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5">الدرجة الوظيفية بكشف الراتب <span class="text-rose-600">*</span></label>
                    <select name="job_grade_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5">طبيعة العمل الميداني</label>
                    <input type="text" name="work_nature" value="إداري ومكتبي وإشرافي" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1.5">تاريخ التثبيت الإداري الرسمي</label>
                    <input type="date" name="admin_confirmation_date" value="{{ date('Y-m-d') }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1.5">تقييم الأداء السنوي المعتمد</label>
                    <select name="performance_rating" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        <option value="ممتاز">ممتاز (90% - 100%)</option>
                        <option value="جيد جداً">جيد جداً (80% - 89%)</option>
                        <option value="جيد">جيد (70% - 79%)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3.ج: حقول المتعاقد بالساعات (تظهر عند اختيار متعاقد) -->
        <div id="fieldsContract" class="subtype-section hidden bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-xs ml-2">3</span>
                    <div>
                        <h3 class="text-base font-black text-slate-900">ثالثاً: البيانات المتخصصة للمتعاقد بالساعات</h3>
                        <p class="text-xs text-slate-400 font-semibold">تفاصيل العقد الفصلي، الساعات المعتمدة أسبوعياً، والاستحقاق المالي</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-amber-800 bg-amber-50 px-3 py-1 rounded-full">
                    بيانات عقود الساعات
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-bold text-slate-700">
                <div>
                    <label class="block mb-1.5">نوع العقد التدريسي <span class="text-rose-600">*</span></label>
                    <select name="contract_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                        @foreach($lookups['contractTypes'] as $ct)
                            <option value="{{ $ct }}">{{ $ct }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5">عدد الساعات المعتمدة أسبوعياً <span class="text-rose-600">*</span></label>
                    <input type="number" name="approved_hours" value="12" min="1" max="40" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1.5">تاريخ بدء سريان العقد</label>
                    <input type="date" name="contract_start_date" value="{{ date('Y-m-d') }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1.5">تاريخ انتهاء سريان العقد</label>
                    <input type="date" name="contract_end_date" value="{{ date('Y-m-d', strtotime('+6 months')) }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- القسم الرابع: الأرشفة الرقمية والوثائق مع عارض PDF مباشر -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="w-8 h-8 rounded-xl bg-purple-700 text-white flex items-center justify-center font-bold text-xs ml-2">4</span>
                    <div>
                        <h2 class="text-base font-black text-slate-900">رابعاً: الأرشفة الرقمية ورفع ومعاينة ملف الـ PDF</h2>
                        <p class="text-xs text-slate-400 font-semibold">أرشفة قرارات التعيين والمؤهلات مع ميزة المعاينة المباشرة</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- إدخال بيانات القرار ورفع الملف -->
                <div class="space-y-4 text-xs font-bold text-slate-700">
                    <div>
                        <label class="block mb-1.5">رقم القرار الإداري / قرار التعيين</label>
                        <input type="text" name="decision_number" placeholder="مثال: ق/2026/108" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-brand-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block mb-1.5">عنوان وتصنيف الوثيقة</label>
                        <input type="text" name="document_title" value="قرار التعيين والاعتماد الأكاديمي الرسمي" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-900 focus:bg-white transition">
                    </div>

                    <!-- صندوق رفع الملف -->
                    <div>
                        <label class="block mb-1.5">اختيار ملف الوثيقة (PDF فقط)</label>
                        <div class="border-2 border-dashed border-slate-300 hover:border-brand-900 rounded-2xl p-6 text-center bg-slate-50 hover:bg-white transition-all cursor-pointer relative">
                            <input type="file" name="document_file" id="pdfFileInput" accept="application/pdf" onchange="previewPdfFile(this)" 
                                   class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                            <i data-lucide="file-up" class="w-10 h-10 text-brand-900 mx-auto mb-2"></i>
                            <span class="text-xs font-bold text-slate-800 block" id="pdfFileName">انقر هنا لاختيار ملف الـ PDF أو اسحبه إلى هنا</span>
                            <span class="text-[10px] text-slate-400 block mt-1">يقبل النظام ملفات PDF الرسمية بحجم حتى 20 ميجابايت</span>
                        </div>
                    </div>
                </div>

                <!-- شاشة المعاينة الحية لملف الـ PDF المرفوع -->
                <div class="bg-slate-900 rounded-2xl p-3 border-2 border-slate-800 flex flex-col justify-between shadow-inner min-h-[300px]">
                    <div class="flex items-center justify-between pb-2 border-b border-white/10 px-2 text-white text-xs font-bold">
                        <span class="flex items-center">
                            <i data-lucide="eye" class="w-4 h-4 ml-1.5 text-emerald-400"></i>
                            <span>معاينة ملف الـ PDF المرفوع:</span>
                        </span>
                        <span id="previewStatus" class="text-[10px] text-emerald-300 font-mono">في انتظار اختيار الملف...</span>
                    </div>

                    <div class="flex-1 flex items-center justify-center my-2 bg-slate-950/80 rounded-xl overflow-hidden relative min-h-[240px]">
                        <iframe id="pdfPreviewFrame" class="hidden w-full h-full min-h-[260px] rounded-lg border-0"></iframe>
                        <div id="pdfEmptyState" class="text-center p-6 text-slate-500">
                            <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-2 text-slate-600"></i>
                            <p class="text-xs font-bold">لم يتم اختيار ملف PDF بعد للمعاينة</p>
                            <span class="text-[10px] text-slate-600">عند اختيار الملف سيتم عرضه هنا مباشرة للتأكد منه قبل الحفظ</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- أزرار الحفظ السفلية -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                إلغاء والعودة للرئيسية
            </a>
            <button type="submit" 
                    class="bg-brand-900 hover:bg-brand-800 active:scale-95 text-white font-black px-8 py-3 rounded-2xl text-sm flex items-center shadow-xl shadow-brand-900/20 transition">
                <i data-lucide="check-circle" class="w-5 h-5 ml-1.5"></i>
                <span>حفظ وتوثيق الكادر بشكل نهائي</span>
            </button>
        </div>

    </form>

</div>

<!-- ========================================== -->
<!-- نافذة Modal لإضافة كلية أو مركز جديد فورياً عبر AJAX -->
<!-- ========================================== -->
<div id="entityModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 text-right">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">إضافة كلية أو مركز جديد فورياً</h3>
            <button type="button" onclick="closeEntityModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="newEntityForm" onsubmit="submitNewEntity(event)" class="space-y-4 text-xs font-bold text-slate-700">
            <div>
                <label class="block mb-1.5">اسم الكلية أو المركز الجديد <span class="text-rose-600">*</span></label>
                <input type="text" id="newEntityName" required placeholder="مثال: كلية الذكاء الاصطناعي والروبوت" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-900">
            </div>

            <div>
                <label class="block mb-1.5">النوع</label>
                <select id="newEntityType" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-900">
                    <option value="كلية">كلية</option>
                    <option value="مركز">مركز</option>
                    <option value="معهد">معهد</option>
                </select>
            </div>

            <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                <button type="button" onclick="closeEntityModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-900 hover:bg-brand-800 text-white text-xs font-black shadow-md">
                    حفظ وإدراج بالقائمة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- نافذة Modal لإضافة قسم أكاديمي جديد فورياً عبر AJAX -->
<!-- ========================================== -->
<div id="departmentModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 text-right">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">إضافة قسم أكاديمي جديد فورياً</h3>
            <button type="button" onclick="closeDepartmentModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="newDeptForm" onsubmit="submitNewDepartment(event)" class="space-y-4 text-xs font-bold text-slate-700">
            <div>
                <label class="block mb-1.5">اختر الكلية التابع لها <span class="text-rose-600">*</span></label>
                <select id="newDeptEntityId" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-900">
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1.5">اسم القسم الأكاديمي الجديد <span class="text-rose-600">*</span></label>
                <input type="text" id="newDeptName" required placeholder="مثال: قسم الأمن السيبراني" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-900">
            </div>

            <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                <button type="button" onclick="closeDepartmentModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-900 hover:bg-brand-800 text-white text-xs font-black shadow-md">
                    حفظ وإدراج بالقسم
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. التبديل الديناميكي بين أنواع الكادر (أكاديمي / إداري / متعاقد)
    function selectStaffType(type) {
        document.querySelectorAll('.subtype-section').forEach(el => el.classList.add('hidden'));

        // إعادة ضبط حدود البطاقات الثلاث
        ['Academic', 'Admin', 'Contract'].forEach(t => {
            const card = document.getElementById('typeCard' + t);
            if (card) {
                card.classList.remove('border-brand-900', 'bg-brand-50/50');
                card.classList.add('border-slate-200', 'bg-white');
            }
        });

        if (type === 'academic') {
            document.getElementById('radioAcademic').checked = true;
            document.getElementById('fieldsAcademic').classList.remove('hidden');
            const card = document.getElementById('typeCardAcademic');
            card.classList.remove('border-slate-200', 'bg-white');
            card.classList.add('border-brand-900', 'bg-brand-50/50');
        } else if (type === 'administrative') {
            document.getElementById('radioAdmin').checked = true;
            document.getElementById('fieldsAdmin').classList.remove('hidden');
            const card = document.getElementById('typeCardAdmin');
            card.classList.remove('border-slate-200', 'bg-white');
            card.classList.add('border-brand-900', 'bg-brand-50/50');
        } else if (type === 'contract') {
            document.getElementById('radioContract').checked = true;
            document.getElementById('fieldsContract').classList.remove('hidden');
            const card = document.getElementById('typeCardContract');
            card.classList.remove('border-slate-200', 'bg-white');
            card.classList.add('border-brand-900', 'bg-brand-50/50');
        }
    }

    // 2. تصفية الأقسام بناءً على الكلية المختارة
    function filterDepartments() {
        const entityId = document.getElementById('entitySelect').value;
        const deptSelect = document.getElementById('departmentSelect');
        const options = deptSelect.querySelectorAll('option');

        let hasVisible = false;
        options.forEach(opt => {
            if (!opt.value) return; // ignore placeholder
            if (!entityId || opt.getAttribute('data-entity-id') === entityId) {
                opt.style.display = 'block';
                if (!hasVisible && opt.getAttribute('data-entity-id') === entityId) {
                    opt.selected = true;
                    hasVisible = true;
                }
            } else {
                opt.style.display = 'none';
            }
        });
    }

    // 3. معاينة ملف الـ PDF المرفوع مباشرة قبل الحفظ
    function previewPdfFile(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('pdfFileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            
            const fileUrl = URL.createObjectURL(file);
            const frame = document.getElementById('pdfPreviewFrame');
            const emptyState = document.getElementById('pdfEmptyState');
            const status = document.getElementById('previewStatus');

            frame.src = fileUrl;
            frame.classList.remove('hidden');
            emptyState.classList.add('hidden');
            status.textContent = 'تم تحميل المعاينة بنجاح ✓';
        }
    }

    // 4. Modal الكليات والأقسام
    function openEntityModal() {
        document.getElementById('entityModal').classList.remove('hidden');
    }
    function closeEntityModal() {
        document.getElementById('entityModal').classList.add('hidden');
    }

    function openDepartmentModal() {
        // نحدد نفس الكلية المختارة حالياً في القائمة
        const currentEntity = document.getElementById('entitySelect').value;
        if (currentEntity) {
            document.getElementById('newDeptEntityId').value = currentEntity;
        }
        document.getElementById('departmentModal').classList.remove('hidden');
    }
    function closeDepartmentModal() {
        document.getElementById('departmentModal').classList.add('hidden');
    }

    // 5. حفظ كلية جديدة عبر AJAX وإضافتها للقائمة فوراً
    function submitNewEntity(e) {
        e.preventDefault();
        const name = document.getElementById('newEntityName').value;
        const type = document.getElementById('newEntityType').value;

        fetch('{{ route("api.entities.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name, entity_type: type })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // إضافة الكلية الجديدة إلى القائمة وتحديدها
                const select = document.getElementById('entitySelect');
                const option = document.createElement('option');
                option.value = data.entity.id;
                option.textContent = data.entity.name;
                option.selected = true;
                select.appendChild(option);

                // إضافتها لقائمة الـ modal أيضاً
                const modalSelect = document.getElementById('newDeptEntityId');
                const mOption = document.createElement('option');
                mOption.value = data.entity.id;
                mOption.textContent = data.entity.name;
                modalSelect.appendChild(mOption);

                closeEntityModal();
                alert('✓ تمت إضافة ' + data.entity.name + ' بنجاح وأُدرجت في القائمة!');
                filterDepartments();
            } else {
                alert('حدث خطأ أثناء الإضافة: ' + (data.message || 'يرجى التأكد من البيانات'));
            }
        })
        .catch(err => {
            alert('تعذر الحفظ، يرجى المحاولة مرة أخرى.');
        });
    }

    // 6. حفظ قسم جديد عبر AJAX وإضافته للقائمة فوراً
    function submitNewDepartment(e) {
        e.preventDefault();
        const entityId = document.getElementById('newDeptEntityId').value;
        const name = document.getElementById('newDeptName').value;

        fetch('{{ route("api.departments.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ entity_id: entityId, name: name })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // إضافة القسم للقائمة وتحديده
                const select = document.getElementById('departmentSelect');
                const option = document.createElement('option');
                option.value = data.department.id;
                option.textContent = data.department.name;
                option.setAttribute('data-entity-id', data.department.entity_id);
                option.selected = true;
                select.appendChild(option);

                closeDepartmentModal();
                alert('✓ تمت إضافة ' + data.department.name + ' بنجاح وأُدرج في قائمة الأقسام!');
            } else {
                alert('حدث خطأ أثناء الإضافة.');
            }
        })
        .catch(err => {
            alert('تعذر الحفظ، يرجى المحاولة مرة أخرى.');
        });
    }
</script>

@endsection
