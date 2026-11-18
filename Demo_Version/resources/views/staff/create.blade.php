@extends('layouts.app')

@section('title', 'إضافة كادر جديد | نظام الأرشفة الجامعي')
@section('page_title', 'معالج تسجيل كادر جديد (Smart Staff Wizard)')
@section('page_subtitle', 'إدخال منظم عبر 4 خطوات تفاعلية بدون تمرير، بالاعتماد على بيانات وقوائم نظام الإكسل الرسمي')

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- شريط المراحل التفاعلي الأفقي (Interactive Wizard Tabs) -->
    <div class="p-4 sm:p-5 bg-slate-900 text-white border-b border-slate-800">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
            <!-- الخطوة 1 -->
            <button type="button" onclick="goToStep(1)" id="stepTab1" class="step-tab flex items-center p-3 rounded-xl transition-all duration-200 text-right bg-univ-600/20 border border-univ-500 text-white shadow-sm">
                <span id="stepBadge1" class="w-8 h-8 rounded-lg bg-univ-600 text-white font-bold flex items-center justify-center text-xs ml-3 shrink-0 shadow">
                    1
                </span>
                <div class="truncate">
                    <span class="block text-xs font-bold leading-tight truncate">البيانات الأساسية</span>
                    <span class="block text-[10px] text-slate-400 font-normal truncate">staff_base</span>
                </div>
            </button>

            <!-- الخطوة 2 -->
            <button type="button" onclick="goToStep(2)" id="stepTab2" class="step-tab flex items-center p-3 rounded-xl transition-all duration-200 text-right bg-slate-800/60 border border-transparent text-slate-400 hover:text-white hover:bg-slate-800">
                <span id="stepBadge2" class="w-8 h-8 rounded-lg bg-slate-700 text-slate-300 font-bold flex items-center justify-center text-xs ml-3 shrink-0">
                    2
                </span>
                <div class="truncate">
                    <span class="block text-xs font-bold leading-tight truncate">التخصيص الوظيفي</span>
                    <span class="block text-[10px] text-slate-400 font-normal truncate">Subtype Details</span>
                </div>
            </button>

            <!-- الخطوة 3 -->
            <button type="button" onclick="goToStep(3)" id="stepTab3" class="step-tab flex items-center p-3 rounded-xl transition-all duration-200 text-right bg-slate-800/60 border border-transparent text-slate-400 hover:text-white hover:bg-slate-800">
                <span id="stepBadge3" class="w-8 h-8 rounded-lg bg-slate-700 text-slate-300 font-bold flex items-center justify-center text-xs ml-3 shrink-0">
                    3
                </span>
                <div class="truncate">
                    <span class="block text-xs font-bold leading-tight truncate">المؤهلات والقرارات</span>
                    <span class="block text-[10px] text-slate-400 font-normal truncate">Decisions & Degrees</span>
                </div>
            </button>

            <!-- الخطوة 4 -->
            <button type="button" onclick="goToStep(4)" id="stepTab4" class="step-tab flex items-center p-3 rounded-xl transition-all duration-200 text-right bg-slate-800/60 border border-transparent text-slate-400 hover:text-white hover:bg-slate-800">
                <span id="stepBadge4" class="w-8 h-8 rounded-lg bg-slate-700 text-slate-300 font-bold flex items-center justify-center text-xs ml-3 shrink-0">
                    4
                </span>
                <div class="truncate">
                    <span class="block text-xs font-bold leading-tight truncate">الأرشيف الرقمي</span>
                    <span class="block text-[10px] text-slate-400 font-normal truncate">رفع ملف الـ PDF</span>
                </div>
            </button>
        </div>
    </div>

    <!-- رسائل الأخطاء العامة إن وجدت -->
    @if($errors->any())
    <div class="m-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-red-700 text-sm">
        <div class="flex items-center font-bold mb-1">
            <i data-lucide="alert-triangle" class="w-4 h-4 ml-1.5 text-red-600"></i>
            <span>يرجى مراجعة وتصحيح الحقول التالية:</span>
        </div>
        <ul class="list-disc pr-5 text-xs space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- النموذج الرئيسي المتكامل -->
    <form id="staffWizardForm" action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
        @csrf

        <!-- ========================================== -->
        <!-- المرحلة 1: البيانات الأساسية المشتركة -->
        <!-- ========================================== -->
        <div id="stepSection1" class="wizard-step">
            <!-- رأس المرحلة -->
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-univ-50 text-univ-700 flex items-center justify-center ml-3 font-bold border border-univ-200">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-base">الخطوة الأولى: البيانات الشخصية والأساسية المشتركة</h4>
                        <p class="text-xs text-slate-500">حفظ القيود الرئيسية الموحدة في جدول الأساس (<code class="text-univ-600 font-mono">staff_base</code>)</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">1 من 4</span>
            </div>

            <!-- اختيار تصنيف الكادر أولاً -->
            <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                <label class="block text-xs font-bold text-slate-700 mb-2.5">
                    اختر تصنيف الكادر (يحدد تفاصيل المرحلة الثانية تلقائياً):
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label id="lblAcademic" class="cursor-pointer border-2 border-univ-600 bg-white p-3.5 rounded-xl flex items-center justify-between shadow-sm transition">
                        <div class="flex items-center">
                            <input type="radio" name="staff_type" value="academic" checked onchange="toggleStaffType('academic')" class="text-univ-600 focus:ring-univ-500 ml-2">
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">كادر أكاديمي</span>
                                <span class="text-[10px] text-slate-500">هيئة التدريس ومساعدوهم</span>
                            </div>
                        </div>
                        <i data-lucide="graduation-cap" class="w-5 h-5 text-univ-600"></i>
                    </label>

                    <label id="lblAdmin" class="cursor-pointer border-2 border-slate-200 bg-white p-3.5 rounded-xl flex items-center justify-between transition hover:border-indigo-400">
                        <div class="flex items-center">
                            <input type="radio" name="staff_type" value="administrative" onchange="toggleStaffType('administrative')" class="text-indigo-600 focus:ring-indigo-500 ml-2">
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">كادر إداري وفني</span>
                                <span class="text-[10px] text-slate-500">موظفو الإدارات والخدمة المدنية</span>
                            </div>
                        </div>
                        <i data-lucide="briefcase" class="w-5 h-5 text-indigo-600"></i>
                    </label>

                    <label id="lblContract" class="cursor-pointer border-2 border-slate-200 bg-white p-3.5 rounded-xl flex items-center justify-between transition hover:border-amber-400">
                        <div class="flex items-center">
                            <input type="radio" name="staff_type" value="contract" onchange="toggleStaffType('contract')" class="text-amber-600 focus:ring-amber-500 ml-2">
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">متعاقد بالساعات</span>
                                <span class="text-[10px] text-slate-500">كادر التدريس والتعاقد المؤقت</span>
                            </div>
                        </div>
                        <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
                    </label>
                </div>
            </div>

            <!-- شبكة الحقول الأساسية مع القوائم المنسدلة الحقيقية من الإكسل -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الرقم الوظيفي <span class="text-red-500">*</span></label>
                    <input name="job_number" value="{{ old('job_number', '4' . rand(1000, 9999)) }}" type="text" required placeholder="مثال: 40820" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الرقم الوطني للبطاقة <span class="text-red-500">*</span></label>
                    <input name="national_id" value="{{ old('national_id', '010' . rand(1000000, 9999999)) }}" type="text" required placeholder="مثال: 0102009988" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الاسم الرباعي الكامل <span class="text-red-500">*</span></label>
                    <input name="full_name" value="{{ old('full_name') }}" type="text" required placeholder="الاسم الرباعي كما في الوثائق الرسمية..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                </div>

                <!-- 1. الكلية أو المركز (من الإكسل) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الكلية / المركز (الجهة العليا) <span class="text-univ-600 font-normal">(بيانات الإكسل)</span></label>
                    <select id="entitySelector" onchange="filterDepartments(this.value)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                        <option value="">-- كل الكليات والمراكز --</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}">{{ $entity->name }} ({{ $entity->entity_type }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. القسم العلمي أو الوحدة التابعة (مرتبط بالكلية) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">القسم العلمي / الإدارة التابعة <span class="text-red-500">*</span></label>
                    <select name="department_id" id="departmentSelector" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" data-entity="{{ $dept->entity_id }}">
                                {{ $dept->entity->name }} ⬅ {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. الموقف والحالة الوظيفية (من الإكسل) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الحالة / الموقف الوظيفي <span class="text-red-500">*</span> <span class="text-univ-600 font-normal">(من الإكسل)</span></label>
                    <select name="job_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                        @foreach($lookups['jobStatuses'] as $status)
                            <option value="{{ $status }}" {{ $status === 'مثبت' ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">النوع الاجتماعي (الجنس) <span class="text-red-500">*</span></label>
                    <select name="gender" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                        <option value="ذكر">ذكر</option>
                        <option value="أنثى">أنثى</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">رقم الهاتف الجوال</label>
                    <input name="phone" value="{{ old('phone') }}" type="text" placeholder="77xxxxxxx" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                    <input name="email" value="{{ old('email') }}" type="email" placeholder="staff@ibbuniv.edu.ye" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ التعيين بالجامعة</label>
                    <input name="hire_date" value="{{ old('hire_date', date('Y-m-d')) }}" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                </div>
            </div>

            <!-- أزرار الخطوة 1 -->
            <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('staff.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                    إلغاء والعودة
                </a>
                <button type="button" onclick="nextStep(2)" class="flex items-center bg-univ-600 hover:bg-univ-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-univ-600/20 transition">
                    <span>التالي: تفاصيل التخصيص الوظيفي</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- المرحلة 2: التخصيص الوظيفي (Subtype) -->
        <!-- ========================================== -->
        <div id="stepSection2" class="wizard-step hidden">
            <!-- رأس المرحلة -->
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center ml-3 font-bold border border-emerald-200">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-base">الخطوة الثانية: تفاصيل التخصيص الوظيفي (Subtype)</h4>
                        <p class="text-xs text-slate-500">تخزين الحقول التخصصية بدون فراغات (Zero Nulls) حسب نوع الكادر المختار</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">2 من 4</span>
            </div>

            <!-- 1. تفاصيل الأكاديمي -->
            <div id="academicSubtypePanel" class="space-y-4">
                <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-200 flex items-center text-emerald-800 text-xs font-bold">
                    <i data-lucide="graduation-cap" class="w-4 h-4 ml-2 text-emerald-600"></i>
                    <span>تخصيص الكادر الأكاديمي (جدول <code class="font-mono">academic_details</code>)</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الرتبة الأكاديمية العلمية <span class="text-red-500">*</span> <span class="text-emerald-700 font-normal">(الإكسل)</span></label>
                        <select name="rank_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                            @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">التخصص العام</label>
                        <input name="general_specialization" list="specializationsList" value="{{ old('general_specialization', 'علوم الحاسوب') }}" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">التخصص الدقيق <span class="text-emerald-700 font-normal">(مقترحات الإكسل)</span></label>
                        <input name="exact_specialization" list="specializationsList" value="{{ old('exact_specialization', 'هندسة برمجيات') }}" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">العبء التدريسي (ساعات أسبوعياً)</label>
                        <input name="teaching_load" value="{{ old('teaching_load', 12) }}" type="number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ آخر ترقية علمية</label>
                        <input name="last_promotion_date" value="{{ old('last_promotion_date') }}" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الحالة الأكاديمية</label>
                        <input name="academic_status" value="مستمر" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                </div>
            </div>

            <!-- 2. تفاصيل الإداري -->
            <div id="adminSubtypePanel" class="space-y-4 hidden">
                <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-200 flex items-center text-indigo-800 text-xs font-bold">
                    <i data-lucide="briefcase" class="w-4 h-4 ml-2 text-indigo-600"></i>
                    <span>تخصيص الكادر الإداري والفني (جدول <code class="font-mono">administrative_details</code>)</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">المسمى الوظيفي الإداري <span class="text-red-500">*</span> <span class="text-indigo-600 font-normal">(من الإكسل)</span></label>
                        <input name="job_title" list="adminJobsList" value="{{ old('job_title', 'مسجل كلية') }}" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الدرجة الوظيفية للخدمة المدنية <span class="text-red-500">*</span></label>
                        <select name="job_grade_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->grade_name }} ({{ $grade->executive_level }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">طبيعة العمل الوظيفي</label>
                        <select name="work_nature" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                            <option value="مكتبي">مكتبي</option>
                            <option value="ميداني">ميداني</option>
                            <option value="فني وتقني">فني وتقني</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. تفاصيل المتعاقد -->
            <div id="contractSubtypePanel" class="space-y-4 hidden">
                <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-200 flex items-center text-amber-800 text-xs font-bold">
                    <i data-lucide="clock" class="w-4 h-4 ml-2 text-amber-600"></i>
                    <span>تخصيص المتعاقد بالساعات (جدول <code class="font-mono">contract_details</code>)</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">نوع العقد <span class="text-amber-700 font-normal">(من الإكسل)</span></label>
                        <select name="contract_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                            @foreach($lookups['contractTypes'] as $cType)
                                <option value="{{ $cType }}">{{ $cType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">عدد الساعات المعتمدة أسبوعياً</label>
                        <input name="approved_hours" value="8" type="number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">قيمة أجر الساعة (ريال)</label>
                        <input name="hourly_rate" value="5000" type="number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                </div>
            </div>

            <!-- أزرار الخطوة 2 -->
            <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="goToStep(1)" class="flex items-center px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5"></i>
                    <span>السابق: البيانات الأساسية</span>
                </button>
                <button type="button" onclick="nextStep(3)" class="flex items-center bg-univ-600 hover:bg-univ-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-univ-600/20 transition">
                    <span>التالي: المؤهلات والقرارات</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- المرحلة 3: المؤهلات والقرارات الرسمية -->
        <!-- ========================================== -->
        <div id="stepSection3" class="wizard-step hidden">
            <!-- رأس المرحلة -->
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center ml-3 font-bold border border-blue-200">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-base">الخطوة الثالثة: تسجيل المؤهلات العلمية والقرارات الإدارية</h4>
                        <p class="text-xs text-slate-500">جداول علائقية تراكمية (<code class="font-mono">qualifications</code> &amp; <code class="font-mono">administrative_decisions</code>)</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">3 من 4</span>
            </div>

            <div class="space-y-5">
                <!-- أ) القرار الإداري الرسمي (من الإكسل) -->
                <div class="p-4 bg-blue-50/40 rounded-xl border border-blue-100">
                    <h5 class="text-xs font-bold text-blue-800 mb-3 flex items-center">
                        <i data-lucide="scroll" class="w-4 h-4 ml-1.5"></i>
                        <span>أولاً: بيانات القرار الإداري الرسمي (administrative_decisions)</span>
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">رقم القرار الرسمي</label>
                            <input name="decision_number" value="ق/{{ rand(100, 999) }}" type="text" placeholder="مثال: ق/150" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">نوع القرار <span class="text-blue-700 font-normal">(من الإكسل)</span></label>
                            <select name="decision_type" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($lookups['decisionTypes'] as $dType)
                                    <option value="{{ $dType }}">{{ $dType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ صدور القرار</label>
                            <input name="issued_date" value="{{ date('Y-m-d') }}" type="date" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- ب) المؤهل العلمي المبدئي (من الإكسل) -->
                <div class="p-4 bg-emerald-50/40 rounded-xl border border-emerald-100">
                    <h5 class="text-xs font-bold text-emerald-800 mb-3 flex items-center">
                        <i data-lucide="award" class="w-4 h-4 ml-1.5"></i>
                        <span>ثانياً: تسجيل المؤهل العلمي المبدئي (qualifications)</span>
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">نوع الدرجة <span class="text-emerald-700 font-normal">(من الإكسل)</span></label>
                            <select name="degree_type" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                @foreach($lookups['degrees'] as $deg)
                                    <option value="{{ $deg }}">{{ $deg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">التخصص</label>
                            <input name="qual_specialization" list="specializationsList" value="هندسة برمجيات" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الجامعة المانحة <span class="text-emerald-700 font-normal">(من الإكسل)</span></label>
                            <input name="granting_institution" list="universitiesList" value="جامعة إب" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">سنة التخرج والدولة <span class="text-emerald-700 font-normal">(الإكسل)</span></label>
                            <div class="grid grid-cols-2 gap-1.5">
                                <input name="graduation_year" value="2022" type="number" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs font-medium">
                                <input name="country" list="countriesList" value="اليمن" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs font-medium">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- أزرار الخطوة 3 -->
            <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="goToStep(2)" class="flex items-center px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5"></i>
                    <span>السابق: التخصيص الوظيفي</span>
                </button>
                <button type="button" onclick="nextStep(4)" class="flex items-center bg-univ-600 hover:bg-univ-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-univ-600/20 transition">
                    <span>التالي: الأرشيف ورفع ملف الـ PDF</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- المرحلة 4: الأرشيف الرقمي ورفع ملف الـ PDF -->
        <!-- ========================================== -->
        <div id="stepSection4" class="wizard-step hidden">
            <!-- رأس المرحلة -->
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center ml-3 font-bold border border-purple-200">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-base">الخطوة الرابعة: الأرشفة الرقمية ورفع ملف الـ PDF الحقيقي</h4>
                        <p class="text-xs text-slate-500">ربط الوثيقة الرقمية بهوية مشفرة (<code class="font-mono">UUID</code>) ومسار التخزين الآمن</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">4 من 4</span>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">عنوان أو وصف الوثيقة الرسمية</label>
                        <input name="document_title" value="قرار التعيين والاعتماد الأكاديمي الرسمي" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">موقع الوثيقة في الشجرة الأرشيفية</label>
                        <select name="tree_category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500">
                            <option value="القرارات الإدارية">القرارات الإدارية والتعيينات</option>
                            <option value="المؤهلات العلمية">المؤهلات العلمية والشهادات</option>
                            <option value="الوثائق الثبوتية">الوثائق الثبوتية والبطائق</option>
                            <option value="المراسلات الرسمية">المراسلات الرسمية والإيفاد</option>
                        </select>
                    </div>
                </div>

                <!-- مربع رفع الملف التفاعلي -->
                <div class="border-2 border-dashed border-univ-400 bg-univ-50/30 hover:bg-univ-50 rounded-2xl p-6 text-center transition">
                    <i data-lucide="file-up" class="w-10 h-10 text-univ-600 mx-auto mb-2"></i>
                    <p class="text-xs font-bold text-slate-700 mb-1">اختر ملف PDF حقيقي لرفعه للأرشيف</p>
                    <p class="text-[11px] text-slate-500 mb-3">الصيغ المدعومة: PDF فقط (الحد الأقصى: 20 ميجابايت)</p>

                    <input type="file" name="document_file" id="pdfFileInput" accept="application/pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-univ-600 file:text-white hover:file:bg-univ-700 cursor-pointer">

                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-right flex items-center text-amber-800 text-[11px]">
                        <i data-lucide="info" class="w-4 h-4 ml-2 shrink-0 text-amber-600"></i>
                        <span>ملاحظة ذكية: إذا تركت الملف فارغاً، سيربط النظام تلقائياً إحدى وثائق الأرشيف الرسمية للجامعة للتجربة الفورية.</span>
                    </div>
                </div>
            </div>

            <!-- أزرار الخطوة 4 النهائية -->
            <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="goToStep(3)" class="flex items-center px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5"></i>
                    <span>السابق: المؤهلات والقرارات</span>
                </button>
                <button type="submit" id="submitBtn" class="flex items-center bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                    <i data-lucide="check-circle" class="w-4 h-4 ml-2"></i>
                    <span>حفظ وتخزين السجل الفعلي في قاعدة البيانات</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- القوائم المرجعية المستخرجة من الإكسل (Datalists) -->
<datalist id="specializationsList">
    @foreach($lookups['specializations'] as $spec)
        <option value="{{ $spec }}">
    @endforeach
</datalist>

<datalist id="adminJobsList">
    @foreach($lookups['adminJobTitles'] as $job)
        <option value="{{ $job }}">
    @endforeach
</datalist>

<datalist id="universitiesList">
    @foreach($lookups['universities'] as $univ)
        <option value="{{ $univ }}">
    @endforeach
</datalist>

<datalist id="countriesList">
    @foreach($lookups['countries'] as $country)
        <option value="{{ $country }}">
    @endforeach
</datalist>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    let selectedStaffType = 'academic';

    // فلترة الأقسام ديناميكياً عند اختيار الكلية أو المركز
    function filterDepartments(entityId) {
        const deptSelect = document.getElementById('departmentSelector');
        const options = deptSelect.querySelectorAll('option');

        let firstMatch = null;
        options.forEach(opt => {
            const optEntity = opt.getAttribute('data-entity');
            if (!entityId || optEntity === entityId) {
                opt.style.display = '';
                if (!firstMatch) firstMatch = opt;
            } else {
                opt.style.display = 'none';
            }
        });

        if (firstMatch && (!entityId || firstMatch.getAttribute('data-entity') === entityId)) {
            deptSelect.value = firstMatch.value;
        }
    }

    function goToStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('hidden'));

        const targetSection = document.getElementById('stepSection' + step);
        if (targetSection) {
            targetSection.classList.remove('hidden');
        }

        for (let i = 1; i <= 4; i++) {
            const tab = document.getElementById('stepTab' + i);
            const badge = document.getElementById('stepBadge' + i);

            if (i === step) {
                tab.className = 'step-tab flex items-center p-3 rounded-xl transition-all duration-200 text-right bg-univ-600/20 border border-univ-500 text-white shadow-sm';
                badge.className = 'w-8 h-8 rounded-lg bg-univ-600 text-white font-bold flex items-center justify-center text-xs ml-3 shrink-0 shadow ring-2 ring-univ-400';
            } else if (i < step) {
                tab.className = 'step-tab flex items-center p-3 rounded-xl transition-all duration-200 text-right bg-slate-800/80 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-800';
                badge.className = 'w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-xs ml-3 shrink-0';
            } else {
                tab.className = 'step-tab flex items-center p-3 rounded-xl transition-all duration-200 text-right bg-slate-800/40 border border-transparent text-slate-400 hover:text-white hover:bg-slate-800';
                badge.className = 'w-8 h-8 rounded-lg bg-slate-700 text-slate-300 font-bold flex items-center justify-center text-xs ml-3 shrink-0';
            }
        }

        currentStep = step;
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    function nextStep(step) {
        if (currentStep === 1) {
            const currentInputs = document.querySelectorAll('#stepSection1 input[required], #stepSection1 select[required]');
            let isValid = true;
            for (let input of currentInputs) {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    isValid = false;
                    break;
                }
            }
            if (!isValid) return;
        }

        goToStep(step);
    }

    function toggleStaffType(type) {
        selectedStaffType = type;

        document.getElementById('academicSubtypePanel').classList.add('hidden');
        document.getElementById('adminSubtypePanel').classList.add('hidden');
        document.getElementById('contractSubtypePanel').classList.add('hidden');

        document.getElementById('lblAcademic').className = 'cursor-pointer border-2 border-slate-200 bg-white p-3.5 rounded-xl flex items-center justify-between transition hover:border-univ-400';
        document.getElementById('lblAdmin').className = 'cursor-pointer border-2 border-slate-200 bg-white p-3.5 rounded-xl flex items-center justify-between transition hover:border-indigo-400';
        document.getElementById('lblContract').className = 'cursor-pointer border-2 border-slate-200 bg-white p-3.5 rounded-xl flex items-center justify-between transition hover:border-amber-400';

        if (type === 'academic') {
            document.getElementById('academicSubtypePanel').classList.remove('hidden');
            document.getElementById('lblAcademic').className = 'cursor-pointer border-2 border-univ-600 bg-univ-50/40 p-3.5 rounded-xl flex items-center justify-between shadow-sm transition';
        } else if (type === 'administrative') {
            document.getElementById('adminSubtypePanel').classList.remove('hidden');
            document.getElementById('lblAdmin').className = 'cursor-pointer border-2 border-indigo-600 bg-indigo-50/40 p-3.5 rounded-xl flex items-center justify-between shadow-sm transition';
        } else if (type === 'contract') {
            document.getElementById('contractSubtypePanel').classList.remove('hidden');
            document.getElementById('lblContract').className = 'cursor-pointer border-2 border-amber-600 bg-amber-50/40 p-3.5 rounded-xl flex items-center justify-between shadow-sm transition';
        }

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        goToStep(1);
    });
</script>
@endpush
