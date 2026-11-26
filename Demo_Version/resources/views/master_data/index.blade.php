@extends('layouts.app')

@section('title', 'جامعة إب — الهيكل والبيانات المرجعية')

@section('content')
<div class="flex-1 flex flex-col min-h-0 bg-[#f8fafc] -m-4 md:-m-6 overflow-hidden">

    <!-- زر السهم الجانبي العائم للتنقل السريع في الشاشات الصغيرة فقط -->
    <button type="button" 
            id="floatingMasterNavBtn" 
            onclick="toggleMasterNavDrawer()" 
            class="md:hidden fixed right-0 top-1/2 -translate-y-1/2 z-30 bg-[#14532d] hover:bg-[#166534] text-white py-4 px-2 rounded-l-xl shadow-2xl border-l-2 border-t border-b border-[#166534] flex items-center justify-center cursor-pointer transition-all duration-300 hover:pr-3 group select-none"
            title="أقسام الهيكل والبيانات المرجعية (اضغط لفتح القائمة)">
        <i id="floatingNavArrow" class="fa-solid fa-chevron-left text-emerald-200 text-sm transition-transform duration-300 group-hover:-translate-x-1"></i>
    </button>

    <!-- خلفية تعتيم خفيفة للشاشات الصغيرة -->
    <div id="masterNavBackdrop" 
         onclick="closeMasterNavDrawer()" 
         class="md:hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 transition-opacity duration-300 opacity-0 pointer-events-none"></div>

    <!-- شريط الإجراءات والمسار الموحد في أعلى الشاشة (مطابق للصورة تماماً) -->
    <div class="bg-white border-b border-slate-200 py-3 px-5 flex flex-wrap items-center justify-between gap-3 shrink-0 shadow-2xs select-none">
        
        <!-- الجانب الأيمن: مسار التنقل وشارات الإحصاء -->
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-xs md:text-sm">
                <span class="text-slate-400 font-medium">الهيكل التنظيمي</span>
                <span class="text-slate-300">/</span>
                <h1 class="text-slate-900 font-black text-sm md:text-base">
                    @if($tab === 'org_units')
                        الكليات والأقسام
                    @elseif($tab === 'ranks')
                        الرتب العلمية
                    @elseif($tab === 'grades')
                        الدرجات الوظيفية
                    @elseif($tab === 'doc_types')
                        أنواع الوثائق
                    @elseif($tab === 'statuses')
                        الحالات الوظيفية
                    @elseif($tab === 'job_titles')
                        المسميات الوظيفية
                    @elseif($tab === 'decision_types')
                        أنواع القرارات
                    @elseif($tab === 'universities')
                        الجامعات
                    @endif
                </h1>
            </div>

            @if($tab === 'org_units')
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-building text-slate-500 text-[11px]"></i>
                        <span>{{ $faculties->count() }} جهة</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-folder text-slate-500 text-[11px]"></i>
                        <span>{{ $faculties->sum(fn($f) => $f->children->count()) }} قسم</span>
                    </span>
                </div>
            @endif
        </div>

        <!-- الجانب الأيسر: البحث + أيقونات التحكم + إضافة -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="relative w-44 sm:w-56">
                <input type="text" 
                       id="masterDataSearch" 
                       placeholder="بحث..." 
                       onkeyup="filterMasterTable(this.value)" 
                       class="w-full bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-[#14532d] rounded-xl px-3 pr-9 py-1.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none transition shadow-2xs font-medium">
                <i class="fa-solid fa-search absolute right-3 top-2 text-slate-400 text-xs"></i>
            </div>

            <button type="button" 
                    onclick="expandAllOrgTree()" 
                    class="w-8 h-8 bg-white hover:bg-slate-50 text-slate-600 rounded-xl transition border border-slate-200 flex items-center justify-center cursor-pointer shadow-2xs" 
                    title="توسيع الكل">
                <i class="fa-solid fa-up-right-and-down-left-from-center text-xs"></i>
            </button>
            <button type="button" 
                    onclick="collapseAllOrgTree()" 
                    class="w-8 h-8 bg-white hover:bg-slate-50 text-slate-600 rounded-xl transition border border-slate-200 flex items-center justify-center cursor-pointer shadow-2xs" 
                    title="طي الكل">
                <i class="fa-solid fa-down-left-and-up-right-to-center text-xs"></i>
            </button>

            <!-- الزر الرئيسي للإضافة -->
            @if($tab === 'org_units')
                <button type="button" onclick="openModal('modal-add-unit')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة جهة / كلية">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة جهة</span>
                </button>
            @elseif($tab === 'ranks')
                <button type="button" onclick="openModal('modal-add-rank')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة رتبة علمية">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة رتبة</span>
                </button>
            @elseif($tab === 'grades')
                <button type="button" onclick="openModal('modal-add-grade')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة درجة وظيفية">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة درجة</span>
                </button>
            @elseif($tab === 'doc_types')
                <button type="button" onclick="openModal('modal-add-doctype')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة نوع وثيقة">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة نوع</span>
                </button>
            @elseif($tab === 'statuses')
                <button type="button" onclick="openModal('modal-add-status')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة حالة وظيفية">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة حالة</span>
                </button>
            @elseif($tab === 'job_titles')
                <button type="button" onclick="openModal('modal-add-jobtitle')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة مسمى وظيفي">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة مسمى</span>
                </button>
            @elseif($tab === 'decision_types')
                <button type="button" onclick="openModal('modal-add-decisiontype')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة نوع قرار">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة نوع</span>
                </button>
            @elseif($tab === 'universities')
                <button type="button" onclick="openModal('modal-add-university')" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap" title="إضافة جامعة">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة جامعة</span>
                </button>
            @endif
        </div>
    </div>

    <!-- حاوية الأعمدة الثلاثة (القائمة الجانبية يميناً + الشجرة وسطاً + الفاحص يساراً) -->
    <div class="flex-1 flex gap-3 min-h-0 overflow-hidden p-3 md:p-4 pt-2">

        <!-- 1. القائمة الجانبية لأقسام الهيكل -->
        <aside id="masterNavDrawer" 
               class="fixed inset-y-0 right-0 z-50 w-64 md:w-60 lg:w-64 bg-white shadow-2xl md:shadow-xs border-l border-slate-200 md:border md:rounded-2xl flex flex-col transform transition-transform duration-300 ease-in-out translate-x-full md:translate-x-0 md:static select-none shrink-0 overflow-hidden"
               aria-label="أقسام الهيكل">
            
            <!-- ترويسة القائمة وحقل البحث -->
            <div class="bg-white p-3 border-b border-slate-200 shrink-0">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-[#14532d]"></i>
                        <span>أقسام الهيكل</span>
                    </h3>
                    <button type="button" 
                            onclick="closeMasterNavDrawer()" 
                            class="md:hidden w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition cursor-pointer"
                            title="إغلاق">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                
                <div class="relative">
                    <input type="text" 
                           id="categorySearchInput" 
                           onkeyup="filterCategories(this.value)" 
                           placeholder="بحث..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 pr-8 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#14532d] transition shadow-2xs font-medium">
                    <i class="fa-solid fa-search absolute right-2.5 top-2 text-slate-400 text-xs"></i>
                </div>
            </div>

            <!-- بطاقات التبويبات الثمانية النظيفة -->
            <div id="categoryCardsList" class="flex-1 overflow-y-auto p-2.5 space-y-1.5 custom-scrollbar">
                
                <!-- 1. الكليات والأقسام -->
                <a href="{{ route('master_data.index', ['tab' => 'org_units']) }}" 
                   data-category-name="الكليات والأقسام"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'org_units' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'org_units' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-sitemap text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">الكليات والأقسام</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'org_units' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $faculties->count() }}
                    </span>
                </a>

                <!-- 2. الرتب العلمية -->
                <a href="{{ route('master_data.index', ['tab' => 'ranks']) }}" 
                   data-category-name="الرتب العلمية"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'ranks' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'ranks' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-award text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">الرتب العلمية</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'ranks' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $ranks->count() }}
                    </span>
                </a>

                <!-- 3. الدرجات الوظيفية -->
                <a href="{{ route('master_data.index', ['tab' => 'grades']) }}" 
                   data-category-name="الدرجات الوظيفية"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'grades' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'grades' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-briefcase text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">الدرجات الوظيفية</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'grades' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $grades->count() }}
                    </span>
                </a>

                <!-- 4. أنواع الوثائق -->
                <a href="{{ route('master_data.index', ['tab' => 'doc_types']) }}" 
                   data-category-name="أنواع الوثائق"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'doc_types' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'doc_types' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-file-shield text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">أنواع الوثائق</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'doc_types' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $documentTypes->count() }}
                    </span>
                </a>

                <!-- 5. الحالات الوظيفية -->
                <a href="{{ route('master_data.index', ['tab' => 'statuses']) }}" 
                   data-category-name="الحالات الوظيفية"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'statuses' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'statuses' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-tags text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">الحالات الوظيفية</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'statuses' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $statuses->count() }}
                    </span>
                </a>

                <!-- 6. المسميات الوظيفية -->
                <a href="{{ route('master_data.index', ['tab' => 'job_titles']) }}" 
                   data-category-name="المسميات الوظيفية"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'job_titles' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'job_titles' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-users text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">المسميات الوظيفية</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'job_titles' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $jobTitles->count() }}
                    </span>
                </a>

                <!-- 7. أنواع القرارات -->
                <a href="{{ route('master_data.index', ['tab' => 'decision_types']) }}" 
                   data-category-name="أنواع القرارات"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'decision_types' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'decision_types' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-stamp text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">أنواع القرارات</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'decision_types' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $decisionTypes->count() }}
                    </span>
                </a>

                <!-- 8. الجامعات والمؤسسات -->
                <a href="{{ route('master_data.index', ['tab' => 'universities']) }}" 
                   data-category-name="الجامعات"
                   class="category-nav-card flex items-center justify-between p-2.5 rounded-xl transition-all duration-150 border {{ $tab === 'universities' ? 'border-[#14532d] bg-emerald-50/40 ring-1 ring-[#14532d]/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-slate-200/70 text-slate-800' }} group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition shrink-0 {{ $tab === 'universities' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#14532d]/10 group-hover:text-[#14532d]' }}">
                            <i class="fa-solid fa-graduation-cap text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">الجامعات</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold {{ $tab === 'universities' ? 'bg-[#14532d] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $universities->count() }}
                    </span>
                </a>

            </div>
        </aside>

        <!-- 2. العمود الأوسط: لوحة الشجرة الهرمية المركزية (Center Tree Panel) -->
        <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden relative">
            <div id="master-scroll-container" class="flex-1 overflow-y-auto min-h-0 relative no-scrollbar">


            {{-- ========================================================================= --}}
            {{-- 1. شجرة الكليات والأقسام (Hierarchical TreeView) --}}
            {{-- ========================================================================= --}}
            @if($tab === 'org_units')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        
                        <!-- العقدة الجذرية: جامعة إب -->
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-univ', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-univ">-</button>
                                    <i class="fa-solid fa-building-columns text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">جامعة إب</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-unit')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة جهة">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- الفروع الرئيسية للكليات والمراكز -->
                            <ul class="tree-branch" id="branch-root-univ">
                                @foreach($faculties as $fIdx => $fac)
                                    @php
                                        $fId = $fac->organizational_unit_id;
                                        $fChildren = $fac->children;
                                        $hasDepts = $fChildren->isNotEmpty();
                                        
                                        $facStaffRecords = $fac->employmentRecords->merge($fChildren->flatMap->employmentRecords);
                                        $facStaffCount = $facStaffRecords->unique('employee_id')->count();
                                        $facDocsCount = $facStaffRecords->flatMap(fn($r) => $r->employee?->documents ?? collect())->unique('document_id')->count();
                                    @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($fac->unit_name_ar) }}">
                                        
                                        <!-- سطر الكلية (ثابت ورسمي بدون اهتزاز أو قفز عند مرور المؤشر) -->
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-emerald-50/60 transition group cursor-pointer border border-transparent hover:border-emerald-200" onclick="toggleBranch('branch-fac-{{ $fId }}', this, event); selectUnitForInspector({ id: '{{ $fId }}', name: '{{ addslashes($fac->unit_name_ar) }}', parentName: 'جامعة إب', typeName: '{{ $fac->unit_type === 'faculty' ? 'كلية أكاديمية' : ($fac->unit_type === 'center' ? 'مركز جامعي' : 'إدارة عامة') }}', code: '{{ $fac->unit_code ?? ('FAC-' . $fId) }}', staffCount: {{ $facStaffCount }}, isFaculty: true, editData: {{ json_encode($fac) }} }, this);">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-fac-{{ $fId }}">
                                                    {{ $hasDepts ? '+' : '-' }}
                                                </button>
                                                <!-- أيقونة الكلية: صرح ومبنى أكاديمي رسمي بدلاً من المجلد -->
                                                <i class="fa-solid fa-building-columns text-[#14532d] text-base shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm hover:text-brand-900 transition truncate">{{ $fac->unit_name_ar }}</span>
                                                
                                                <!-- مؤشر إحصائي ثابت وهادئ لا يسبب أي اهتزاز -->
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $fChildren->count() }} قسم • {{ $facStaffCount }} كادر)</span>
                                            </div>

                                            <!-- أزرار الإجراءات للكلية -->
                                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                <button type="button" onclick="openAddDeptModal('{{ $fId }}', '{{ $fac->unit_name_ar }}')" 
                                                        class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs inline-flex items-center justify-center cursor-pointer shadow-2xs"
                                                        title="إضافة قسم">
                                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                                </button>
                                                <button type="button" onclick="openEditUnitModal({{ json_encode($fac) }})"
                                                        class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer"
                                                        title="تعديل بيانات الكلية">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" action="{{ route('master_data.units.destroy', $fId) }}" onsubmit="return confirm('تأكيد حذف الجهة: {{ $fac->unit_name_ar }}؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer"
                                                            title="حذف الكلية">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- فروع الأقسام التابعة للكلية -->
                                        <ul class="tree-branch hidden" id="branch-fac-{{ $fId }}">
                                            @forelse($fChildren as $cIdx => $child)
                                                @php
                                                    $cId = $child->organizational_unit_id;
                                                    $deptRecords = $child->employmentRecords;
                                                    $deptEmployees = $deptRecords->map(fn($r) => $r->employee)->filter()->unique('employee_id');
                                                    $deptDocs = $deptRecords->flatMap(fn($r) => $r->employee?->documents ?? collect())->unique('document_id');
                                                @endphp
                                                <li class="tree-item tree-department-node" data-search-target="{{ strtolower($child->unit_name_ar) }}">
                                                    
                                                    <!-- سطر القسم (ثابت ورسمي بدون اهتزاز أو قفز عند مرور المؤشر) -->
                                                    <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-blue-50/50 transition group cursor-pointer border border-transparent hover:border-blue-200" onclick="toggleBranch('branch-dept-{{ $cId }}', this, event); selectUnitForInspector({ id: '{{ $cId }}', parentUnitId: '{{ $fId }}', name: '{{ addslashes($child->unit_name_ar) }}', parentName: '{{ addslashes($fac->unit_name_ar) }}', typeName: 'قسم أكاديمي', code: '{{ $child->unit_code ?? ('DEP-' . $cId) }}', staffCount: {{ $deptEmployees->count() }}, isFaculty: false, editData: {{ json_encode($child) }} }, this);">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-dept-{{ $cId }}">
                                                                +
                                                            </button>
                                                            <!-- أيقونة القسم: مجلد أزرق سماوي للتمييز الفوري -->
                                                            <i class="fa-solid fa-folder text-blue-500 text-sm shrink-0 folder-icon"></i>
                                                            <span class="font-bold text-slate-900 text-sm truncate">{{ $child->unit_name_ar }}</span>
                                                            
                                                            <!-- مؤشر إحصائي ثابت وهادئ لا يسبب أي اهتزاز -->
                                                            <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $deptEmployees->count() }} كادر)</span>
                                                        </div>

                                                        <!-- أزرار الإجراءات للقسم -->
                                                        <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                            <a href="{{ route('employees.create') }}?faculty_id={{ $fId }}&dept_id={{ $cId }}" class="w-7 h-7 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs inline-flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة كادر">
                                                                <i class="fa-solid fa-user-plus text-[10px]"></i>
                                                            </a>
                                                            <button type="button" onclick="openEditUnitModal({{ json_encode($child) }})"
                                                                    class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer"
                                                                    title="تعديل بيانات القسم">
                                                                <i class="fa-solid fa-pen-to-square"></i>
                                                            </button>
                                                            <form method="POST" action="{{ route('master_data.units.destroy', $cId) }}" onsubmit="return confirm('تأكيد حذف القسم: {{ $child->unit_name_ar }}؟')" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                        class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer"
                                                                        title="حذف القسم">
                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <!-- تفرعات القسم: 1) الكادر 2) الأرشيف والوثائق (انتقال سريع مفلتر) -->
                                                    <ul class="tree-branch hidden" id="branch-dept-{{ $cId }}">
                                                        
                                                        <!-- أ) الكادر -->
                                                        <li class="tree-item">
                                                            <div class="tree-row flex items-center justify-between p-1.5 px-3 rounded-xl hover:bg-indigo-50/80 border border-transparent hover:border-indigo-200 transition group cursor-pointer"
                                                                 onclick="window.location.href='{{ route('employees.index') }}?college_id={{ $fId }}&dept_id={{ $cId }}'"
                                                                 title="عرض الكادر">
                                                                <div class="flex items-center gap-2 min-w-0">
                                                                    <i class="fa-solid fa-users text-indigo-600 text-xs shrink-0"></i>
                                                                    <span class="font-bold text-slate-800 text-xs">الكادر</span>
                                                                    <span class="font-mono text-[11px] bg-indigo-50 text-indigo-900 border border-indigo-200 px-2 py-0.5 rounded-full font-bold shrink-0">
                                                                        {{ $deptEmployees->count() }}
                                                                    </span>
                                                                </div>
                                                                <div class="flex items-center gap-1.5 shrink-0">
                                                                    <a href="{{ route('employees.create') }}?faculty_id={{ $fId }}&dept_id={{ $cId }}" 
                                                                       onclick="event.stopPropagation()"
                                                                       class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs inline-flex items-center justify-center shadow-2xs transition cursor-pointer"
                                                                       title="إضافة كادر">
                                                                        <i class="fa-solid fa-user-plus text-[10px]"></i>
                                                                    </a>
                                                                    <span class="w-7 h-7 text-xs text-indigo-700 bg-white border border-indigo-200 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition flex items-center justify-center shadow-2xs" title="عرض الكادر">
                                                                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>

                                                        <!-- ب) الأرشيف -->
                                                        <li class="tree-item mt-1">
                                                            <div class="tree-row flex items-center justify-between p-1.5 px-3 rounded-xl hover:bg-purple-50/80 border border-transparent hover:border-purple-200 transition group cursor-pointer"
                                                                 onclick="window.location.href='{{ route('archive.index') }}?college_id={{ $fId }}&dept_id={{ $cId }}'"
                                                                 title="عرض الأرشيف">
                                                                <div class="flex items-center gap-2 min-w-0">
                                                                    <i class="fa-solid fa-box-archive text-purple-700 text-xs shrink-0"></i>
                                                                    <span class="font-bold text-slate-800 text-xs">الأرشيف</span>
                                                                    <span class="font-mono text-[11px] bg-purple-50 text-purple-900 border border-purple-200 px-2 py-0.5 rounded-full font-bold shrink-0">
                                                                        {{ $deptDocs->count() }}
                                                                    </span>
                                                                </div>
                                                                <div class="flex items-center gap-1.5 shrink-0">
                                                                    <span class="w-7 h-7 text-xs text-purple-700 bg-white border border-purple-200 rounded-lg group-hover:bg-purple-700 group-hover:text-white transition flex items-center justify-center shadow-2xs" title="عرض الأرشيف">
                                                                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>

                                                    </ul>
                                                </li>
                                            @empty
                                                <li class="tree-item text-slate-400 text-xs italic p-2 px-4">
                                                    لا توجد أقسام مسجلة تابعة لهذه الجهة حتى الآن (اضغط "+ قسم" لإنشاء أول قسم).
                                                </li>
                                            @endforelse
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- 2. شجرة الرتب العلمية (Academic Ranks TreeView) --}}
            {{-- ========================================================================= --}}
            @elseif($tab === 'ranks')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-ranks', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-ranks">-</button>
                                    <i class="fa-solid fa-award text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">الرتب العلمية</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-rank')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة رتبة">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <ul class="tree-branch" id="branch-root-ranks">
                                @foreach($ranks as $rnk)
                                    @php
                                        $rnkEmployees = $rnk->employmentRecords->map(fn($r) => $r->employee)->filter()->unique('employee_id');
                                        $hasStaff = $rnkEmployees->isNotEmpty();
                                    @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($rnk->rank_name_ar) }}">
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-emerald-50/60 transition group cursor-pointer border border-transparent hover:border-emerald-200" onclick="toggleBranch('branch-rnk-{{ $rnk->academic_rank_id }}', this, event)">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-rnk-{{ $rnk->academic_rank_id }}">
                                                    {{ $hasStaff ? '+' : '-' }}
                                                </button>
                                                <i class="fa-solid fa-medal text-amber-600 text-base shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm hover:text-brand-900 transition truncate">{{ $rnk->rank_name_ar }}</span>
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $rnkEmployees->count() }} كادر)</span>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                <button type="button" onclick="openEditRankModal({{ json_encode($rnk) }})" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer" title="تعديل">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" action="{{ route('master_data.ranks.destroy', $rnk->academic_rank_id) }}" onsubmit="return confirm('تأكيد حذف الرتبة: {{ $rnk->rank_name_ar }}؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer" title="حذف">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- فروع الكادر -->
                                        <ul class="tree-branch hidden" id="branch-rnk-{{ $rnk->academic_rank_id }}">
                                            @forelse($rnkEmployees as $emp)
                                                <li class="tree-item tree-leaf-node cadre-hover-item" 
                                                    data-cadre-summary="{{ json_encode($emp->quick_summary_data) }}"
                                                    data-search-target="{{ strtolower($emp->full_name_ar . ' ' . $emp->employee_number) }}">
                                                    <div class="tree-row flex items-center justify-between p-1 px-2.5 rounded-lg hover:bg-emerald-50/50 transition">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <i class="fa-solid fa-user-tie text-emerald-700 text-xs shrink-0"></i>
                                                            <a href="{{ route('employees.show', $emp->employee_id) }}" class="font-bold text-brand-900 hover:underline text-xs flex items-center gap-1.5 truncate">
                                                                <span>{{ $emp->full_name_ar }}</span>
                                                                <span class="text-slate-400 font-mono font-normal">({{ $emp->employee_number }})</span>
                                                            </a>
                                                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 text-[11px] px-2 py-0.5 rounded-md font-bold border border-emerald-200/80 shrink-0" title="الكلية">
                                                                <i class="fa-solid fa-building-columns text-[9px] text-emerald-600"></i>
                                                                <span>{{ $emp->college_name }}</span>
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('employees.show', $emp->employee_id) }}" class="w-7 h-7 rounded-lg bg-white hover:bg-[#14532d] hover:text-white text-slate-600 border border-slate-200 transition shadow-2xs flex items-center justify-center shrink-0" title="عرض الملف">
                                                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="tree-item text-slate-400 text-xs italic p-1 px-3">لا يوجد كادر</li>
                                            @endforelse
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- 3. شجرة الدرجات الوظيفية (Job Grades TreeView) --}}
            {{-- ========================================================================= --}}
            @elseif($tab === 'grades')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-grades', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-grades">-</button>
                                    <i class="fa-solid fa-briefcase text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">الدرجات الوظيفية</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-grade')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة درجة">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <ul class="tree-branch" id="branch-root-grades">
                                @foreach($grades as $grd)
                                    @php
                                        $grdEmployees = $grd->employmentRecords->map(fn($r) => $r->employee)->filter()->unique('employee_id');
                                        $hasStaff = $grdEmployees->isNotEmpty();
                                    @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($grd->grade_name_ar) }}">
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-blue-50/50 transition group cursor-pointer border border-transparent hover:border-blue-200" onclick="toggleBranch('branch-grd-{{ $grd->job_grade_id }}', this, event)">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-grd-{{ $grd->job_grade_id }}">
                                                    {{ $hasStaff ? '+' : '-' }}
                                                </button>
                                                <i class="fa-solid fa-layer-group text-blue-600 text-base shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm hover:text-brand-900 transition truncate">{{ $grd->grade_name_ar }}</span>
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $grdEmployees->count() }} كادر)</span>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                <button type="button" onclick="openEditGradeModal({{ json_encode($grd) }})" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer" title="تعديل">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" action="{{ route('master_data.grades.destroy', $grd->job_grade_id) }}" onsubmit="return confirm('تأكيد حذف الدرجة: {{ $grd->grade_name_ar }}؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer" title="حذف">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <ul class="tree-branch hidden" id="branch-grd-{{ $grd->job_grade_id }}">
                                            @forelse($grdEmployees as $emp)
                                                <li class="tree-item tree-leaf-node cadre-hover-item"
                                                    data-cadre-summary="{{ json_encode($emp->quick_summary_data) }}"
                                                    data-search-target="{{ strtolower($emp->full_name_ar . ' ' . $emp->employee_number) }}">
                                                    <div class="tree-row flex items-center justify-between p-1 px-2.5 rounded-lg hover:bg-blue-50/50 transition">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <i class="fa-solid fa-user-tie text-blue-700 text-xs shrink-0"></i>
                                                            <a href="{{ route('employees.show', $emp->employee_id) }}" class="font-bold text-brand-900 hover:underline text-xs flex items-center gap-1.5 truncate">
                                                                <span>{{ $emp->full_name_ar }}</span>
                                                                <span class="text-slate-400 font-mono font-normal">({{ $emp->employee_number }})</span>
                                                            </a>
                                                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 text-[11px] px-2 py-0.5 rounded-md font-bold border border-emerald-200/80 shrink-0" title="الكلية">
                                                                <i class="fa-solid fa-building-columns text-[9px] text-emerald-600"></i>
                                                                <span>{{ $emp->college_name }}</span>
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('employees.show', $emp->employee_id) }}" class="w-7 h-7 rounded-lg bg-white hover:bg-[#14532d] hover:text-white text-slate-600 border border-slate-200 transition shadow-2xs flex items-center justify-center shrink-0" title="عرض الملف">
                                                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="tree-item text-slate-400 text-xs italic p-1 px-3">لا يوجد كادر</li>
                                            @endforelse
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- 4. شجرة أنواع وتصنيفات الوثائق (Document Types TreeView) --}}
            {{-- ========================================================================= --}}
            @elseif($tab === 'doc_types')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-doctypes', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-doctypes">-</button>
                                    <i class="fa-solid fa-file-shield text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">أنواع الوثائق</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-doctype')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة نوع">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <ul class="tree-branch" id="branch-root-doctypes">
                                @foreach($documentTypes as $dt)
                                    @php
                                        $dtDocs = $dt->documents;
                                        $hasDocs = $dtDocs->isNotEmpty();
                                    @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($dt->type_name_ar) }}">
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-purple-50/50 transition group cursor-pointer border border-transparent hover:border-purple-200" onclick="toggleBranch('branch-dt-{{ $dt->document_type_id }}', this, event)">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-dt-{{ $dt->document_type_id }}">
                                                    {{ $hasDocs ? '+' : '-' }}
                                                </button>
                                                <i class="fa-solid fa-file-lines text-purple-600 text-base shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm hover:text-brand-900 transition truncate">{{ $dt->type_name_ar }}</span>
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $dtDocs->count() }} وثيقة)</span>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                <button type="button" onclick="openEditDocTypeModal({{ json_encode($dt) }})" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer" title="تعديل">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" action="{{ route('master_data.doctypes.destroy', $dt->document_type_id) }}" onsubmit="return confirm('تأكيد حذف التصنيف: {{ $dt->type_name_ar }}؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer" title="حذف">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <ul class="tree-branch hidden" id="branch-dt-{{ $dt->document_type_id }}">
                                            @forelse($dtDocs as $doc)
                                                @php $v = $doc->currentVersion; @endphp
                                                <li class="tree-item tree-leaf-node" data-search-target="{{ strtolower($doc->document_title) }}">
                                                    <div class="tree-row flex items-center justify-between p-1 px-2.5 rounded-lg hover:bg-rose-50/50 transition">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <i class="fa-regular fa-file-pdf text-rose-700 text-sm shrink-0"></i>
                                                            <span class="font-bold text-slate-800 text-xs truncate max-w-md">{{ $doc->document_title }}</span>
                                                            @if($doc->employee)
                                                                <span class="text-slate-400 text-[11px]">({{ $doc->employee->full_name_ar }})</span>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center gap-1 shrink-0">
                                                            @if($v)
                                                                <a href="{{ route('archive.preview', $v->document_version_id) }}" target="_blank" class="w-7 h-7 rounded-lg bg-white hover:bg-slate-50 text-slate-700 hover:text-brand-900 border border-slate-200 transition shadow-2xs flex items-center justify-center" title="معاينة">
                                                                    <i class="fa-regular fa-eye text-xs"></i>
                                                                </a>
                                                                <a href="{{ route('archive.download', $v->document_version_id) }}" class="w-7 h-7 rounded-lg bg-white hover:bg-slate-50 text-slate-700 hover:text-brand-900 border border-slate-200 transition shadow-2xs flex items-center justify-center" title="تنزيل">
                                                                    <i class="fa-solid fa-download text-xs"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="tree-item text-slate-400 text-xs italic p-1 px-3">لا توجد وثائق</li>
                                            @endforelse
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- 5. شجرة الحالات والمواقف الوظيفية (Employment Statuses TreeView) --}}
            {{-- ========================================================================= --}}
            @elseif($tab === 'statuses')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-statuses', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-statuses">-</button>
                                    <i class="fa-solid fa-tags text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">الحالات الوظيفية</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-status')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة حالة">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <ul class="tree-branch" id="branch-root-statuses">
                                @foreach($statuses as $st)
                                    @php
                                        $stEmployees = $st->employmentRecords->map(fn($r) => $r->employee)->filter()->unique('employee_id');
                                        $hasStaff = $stEmployees->isNotEmpty();
                                    @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($st->status_name_ar) }}">
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-teal-50/50 transition group cursor-pointer border border-transparent hover:border-teal-200" onclick="toggleBranch('branch-st-{{ $st->employment_status_id }}', this, event)">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-st-{{ $st->employment_status_id }}">
                                                    {{ $hasStaff ? '+' : '-' }}
                                                </button>
                                                <i class="fa-solid fa-circle-dot text-teal-600 text-sm shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm hover:text-brand-900 transition truncate">{{ $st->status_name_ar }}</span>
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $stEmployees->count() }} كادر)</span>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                <button type="button" onclick="openEditStatusModal({{ json_encode($st) }})" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer" title="تعديل">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" action="{{ route('master_data.statuses.destroy', $st->employment_status_id) }}" onsubmit="return confirm('تأكيد حذف الحالة: {{ $st->status_name_ar }}؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer" title="حذف">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <ul class="tree-branch hidden" id="branch-st-{{ $st->employment_status_id }}">
                                            @forelse($stEmployees as $emp)
                                                <li class="tree-item tree-leaf-node cadre-hover-item"
                                                    data-cadre-summary="{{ json_encode($emp->quick_summary_data) }}"
                                                    data-search-target="{{ strtolower($emp->full_name_ar . ' ' . $emp->employee_number) }}">
                                                    <div class="tree-row flex items-center justify-between p-1 px-2.5 rounded-lg hover:bg-teal-50/50 transition">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <i class="fa-solid fa-user-tie text-teal-700 text-xs shrink-0"></i>
                                                            <a href="{{ route('employees.show', $emp->employee_id) }}" class="font-bold text-brand-900 hover:underline text-xs flex items-center gap-1.5 truncate">
                                                                <span>{{ $emp->full_name_ar }}</span>
                                                                <span class="text-slate-400 font-mono font-normal">({{ $emp->employee_number }})</span>
                                                            </a>
                                                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 text-[11px] px-2 py-0.5 rounded-md font-bold border border-emerald-200/80 shrink-0" title="الكلية">
                                                                <i class="fa-solid fa-building-columns text-[9px] text-emerald-600"></i>
                                                                <span>{{ $emp->college_name }}</span>
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('employees.show', $emp->employee_id) }}" class="w-7 h-7 rounded-lg bg-white hover:bg-[#14532d] hover:text-white text-slate-600 border border-slate-200 transition shadow-2xs flex items-center justify-center shrink-0" title="عرض الملف">
                                                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="tree-item text-slate-400 text-xs italic p-1 px-3">لا يوجد كادر</li>
                                            @endforelse
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- 6. شجرة المسميات الوظيفية (Job Titles TreeView) --}}
            {{-- ========================================================================= --}}
            @elseif($tab === 'job_titles')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-jobtitles', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-jobtitles">-</button>
                                    <i class="fa-solid fa-id-badge text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">المسميات الوظيفية</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-jobtitle')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة مسمى">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <ul class="tree-branch" id="branch-root-jobtitles">
                                @forelse($jobTitles as $jt)
                                    @php
                                        $jtEmployees = $jt->employees;
                                        $hasStaff = $jtEmployees->isNotEmpty();
                                    @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($jt->title_name_ar) }}">
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-indigo-50/50 transition group cursor-pointer border border-transparent hover:border-indigo-200" onclick="toggleBranch('branch-jt-{{ $jt->job_title_id }}', this, event)">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-jt-{{ $jt->job_title_id }}">
                                                    {{ $hasStaff ? '+' : '-' }}
                                                </button>
                                                <i class="fa-solid fa-id-badge text-indigo-600 text-base shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm hover:text-brand-900 transition truncate">{{ $jt->title_name_ar }}</span>
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $jtEmployees->count() }} كادر)</span>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition" onclick="event.stopPropagation()">
                                                <button type="button" onclick="openEditJobTitleModal({{ json_encode($jt) }})" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer" title="تعديل">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" action="{{ route('master_data.jobtitles.destroy', $jt->job_title_id) }}" onsubmit="return confirm('تأكيد حذف المسمى: {{ $jt->title_name_ar }}؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer" title="حذف">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <ul class="tree-branch hidden" id="branch-jt-{{ $jt->job_title_id }}">
                                            @forelse($jtEmployees as $emp)
                                                <li class="tree-item tree-leaf-node cadre-hover-item"
                                                    data-cadre-summary="{{ json_encode($emp->quick_summary_data) }}"
                                                    data-search-target="{{ strtolower($emp->full_name_ar . ' ' . $emp->employee_number) }}">
                                                    <div class="tree-row flex items-center justify-between p-1 px-2.5 rounded-lg hover:bg-indigo-50/50 transition">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <i class="fa-solid fa-user-tie text-indigo-700 text-xs shrink-0"></i>
                                                            <a href="{{ route('employees.show', $emp->employee_id) }}" class="font-bold text-brand-900 hover:underline text-xs flex items-center gap-1.5 truncate">
                                                                <span>{{ $emp->full_name_ar }}</span>
                                                                <span class="text-slate-400 font-mono font-normal">({{ $emp->employee_number }})</span>
                                                            </a>
                                                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 text-[11px] px-2 py-0.5 rounded-md font-bold border border-emerald-200/80 shrink-0" title="الكلية">
                                                                <i class="fa-solid fa-building-columns text-[9px] text-emerald-600"></i>
                                                                <span>{{ $emp->college_name }}</span>
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('employees.show', $emp->employee_id) }}" class="w-7 h-7 rounded-lg bg-white hover:bg-[#14532d] hover:text-white text-slate-600 border border-slate-200 transition shadow-2xs flex items-center justify-center shrink-0" title="عرض الملف">
                                                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="tree-item text-slate-400 text-xs italic p-1 px-3">لا يوجد كادر</li>
                                            @endforelse
                                        </ul>
                                    </li>
                                @empty
                                    <li class="tree-item text-slate-400 text-xs italic p-2 px-4">لا توجد مسميات</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- 7. شجرة أنواع القرارات الإدارية (Decision Types TreeView) --}}
            {{-- ========================================================================= --}}
            @elseif($tab === 'decision_types')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-decisiontypes', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-decisiontypes">-</button>
                                    <i class="fa-solid fa-stamp text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">أنواع القرارات</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-decisiontype')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة نوع">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <ul class="tree-branch" id="branch-root-decisiontypes">
                                @foreach($decisionTypes as $decT)
                                    @php
                                        $decDecisions = $decT->decisions;
                                        $hasDecs = $decDecisions->isNotEmpty();
                                    @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($decT->type_name_ar) }}">
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-amber-50/50 transition group cursor-pointer border border-transparent hover:border-amber-200" onclick="toggleBranch('branch-dect-{{ $decT->decision_type_id }}', this, event)">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-dect-{{ $decT->decision_type_id }}">
                                                    {{ $hasDecs ? '+' : '-' }}
                                                </button>
                                                <i class="fa-solid fa-file-signature text-amber-600 text-base shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm hover:text-brand-900 transition truncate">{{ $decT->type_name_ar }}</span>
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $decDecisions->count() }} قرار)</span>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                <button type="button" onclick="openEditDecisionTypeModal({{ json_encode($decT) }})" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer" title="تعديل">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" action="{{ route('master_data.decisiontypes.destroy', $decT->decision_type_id) }}" onsubmit="return confirm('تأكيد حذف نوع القرار: {{ $decT->type_name_ar }}؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer" title="حذف">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <ul class="tree-branch hidden" id="branch-dect-{{ $decT->decision_type_id }}">
                                            @forelse($decDecisions as $decision)
                                                <li class="tree-item tree-leaf-node" data-search-target="{{ strtolower($decision->decision_number) }}">
                                                    <div class="tree-row flex items-center justify-between p-1 px-2.5 rounded-lg hover:bg-amber-50/50 transition">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <i class="fa-solid fa-file-signature text-amber-700 text-xs shrink-0"></i>
                                                            <span class="font-bold text-slate-800 text-xs">قرار رقم: {{ $decision->decision_number }}</span>
                                                            @if($decision->employee)
                                                                <span class="text-slate-400 text-[11px]">({{ $decision->employee->full_name_ar }})</span>
                                                            @endif
                                                        </div>
                                                        <span class="font-mono text-slate-500 text-xs">{{ $decision->issue_date ? $decision->issue_date->format('Y-m-d') : '-' }}</span>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="tree-item text-slate-400 text-xs italic p-1 px-3">لا توجد قرارات</li>
                                            @endforelse
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- 8. شجرة الجامعات (Universities TreeView) --}}
            {{-- ========================================================================= --}}
            @elseif($tab === 'universities')
                <div id="treeViewContainer" class="p-4 sm:p-6 bg-white min-h-full font-sans select-none overflow-x-auto text-sm">
                    <div class="tree-root-wrapper">
                        <div class="tree-node root-tree-node">
                            <div class="tree-row flex items-center justify-between p-2 px-3 rounded-xl bg-slate-100/90 border border-slate-300 hover:bg-slate-200/70 transition group cursor-pointer" onclick="toggleBranch('branch-root-universities', this, event)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-500 text-slate-800 rounded-2xs flex items-center justify-center font-mono text-[11px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-root-universities">-</button>
                                    <i class="fa-solid fa-graduation-cap text-[#14532d] text-base shrink-0"></i>
                                    <span class="font-black text-slate-900 text-sm tracking-wide">الجامعات</span>
                                </div>
                                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openModal('modal-add-university')" class="w-7 h-7 bg-[#14532d] hover:bg-[#166534] text-white rounded-lg text-xs transition flex items-center justify-center shadow-2xs cursor-pointer" title="إضافة جامعة">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            @php
                                $groupedUniversities = $universities->groupBy(fn($u) => $u->country?->country_name_ar ?? 'جامعات أخرى');
                            @endphp

                            <ul class="tree-branch" id="branch-root-universities">
                                @foreach($groupedUniversities as $countryName => $uList)
                                    @php $cSlug = md5($countryName); @endphp
                                    <li class="tree-item tree-faculty-node" data-search-target="{{ strtolower($countryName) }}">
                                        <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-emerald-50/60 transition group cursor-pointer border border-transparent hover:border-emerald-200" onclick="toggleBranch('branch-country-{{ $cSlug }}', this, event)">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-country-{{ $cSlug }}">
                                                    +
                                                </button>
                                                <i class="fa-solid fa-earth-americas text-emerald-600 text-base shrink-0"></i>
                                                <span class="font-black text-slate-900 text-sm truncate">{{ $countryName }}</span>
                                                <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $uList->count() }} جامعة)</span>
                                            </div>
                                        </div>

                                        <ul class="tree-branch hidden" id="branch-country-{{ $cSlug }}">
                                            @foreach($uList as $univ)
                                                @php
                                                    $uStaff = $univ->qualifications->map(fn($q) => $q->employee)->filter()->unique('employee_id');
                                                    $hasGrads = $uStaff->isNotEmpty();
                                                @endphp
                                                <li class="tree-item tree-department-node" data-search-target="{{ strtolower($univ->university_name_ar) }}">
                                                    <div class="tree-row flex items-center justify-between p-1.5 px-2.5 rounded-xl hover:bg-blue-50/50 transition group cursor-pointer border border-transparent hover:border-blue-200" onclick="toggleBranch('branch-univ-{{ $univ->university_id }}', this, event)">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <button type="button" class="tree-expander w-4 h-4 bg-white border border-slate-400 text-slate-700 rounded-2xs flex items-center justify-center font-mono text-[10px] font-bold shrink-0 cursor-pointer shadow-2xs" data-target="branch-univ-{{ $univ->university_id }}">
                                                                {{ $hasGrads ? '+' : '-' }}
                                                            </button>
                                                            <i class="fa-solid fa-building-columns text-blue-600 text-sm shrink-0"></i>
                                                            <span class="font-bold text-slate-900 text-sm truncate">{{ $univ->university_name_ar }}</span>
                                                            <span class="text-[11px] font-mono text-slate-400 font-normal shrink-0">({{ $uStaff->count() }} خريج)</span>
                                                        </div>

                                                        <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150" onclick="event.stopPropagation()">
                                                            <button type="button" onclick="openEditUniversityModal({{ json_encode($univ) }})" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-brand-900 hover:text-white text-slate-600 transition border border-slate-300 text-xs cursor-pointer" title="تعديل">
                                                                <i class="fa-solid fa-pen-to-square"></i>
                                                            </button>
                                                            <form method="POST" action="{{ route('master_data.universities.destroy', $univ->university_id) }}" onsubmit="return confirm('تأكيد حذف الجامعة: {{ $univ->university_name_ar }}؟')" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-400 transition border border-slate-300 text-xs cursor-pointer" title="حذف">
                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <ul class="tree-branch hidden" id="branch-univ-{{ $univ->university_id }}">
                                                        @forelse($uStaff as $emp)
                                                            <li class="tree-item tree-leaf-node cadre-hover-item"
                                                                data-cadre-summary="{{ json_encode($emp->quick_summary_data) }}"
                                                                data-search-target="{{ strtolower($emp->full_name_ar . ' ' . $emp->employee_number) }}">
                                                                <div class="tree-row flex items-center justify-between p-1 px-2.5 rounded-lg hover:bg-emerald-50/50 transition">
                                                                    <div class="flex items-center gap-2 min-w-0">
                                                                        <i class="fa-solid fa-user-graduate text-blue-700 text-xs shrink-0"></i>
                                                                        <a href="{{ route('employees.show', $emp->employee_id) }}" class="font-bold text-brand-900 hover:underline text-xs flex items-center gap-1.5 truncate">
                                                                            <span>{{ $emp->full_name_ar }}</span>
                                                                            <span class="text-slate-400 font-mono font-normal">({{ $emp->employee_number }})</span>
                                                                        </a>
                                                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 text-[11px] px-2 py-0.5 rounded-md font-bold border border-emerald-200/80 shrink-0" title="الكلية">
                                                                            <i class="fa-solid fa-building-columns text-[9px] text-emerald-600"></i>
                                                                            <span>{{ $emp->college_name }}</span>
                                                                        </span>
                                                                    </div>
                                                                    <a href="{{ route('employees.show', $emp->employee_id) }}" class="w-7 h-7 rounded-lg bg-white hover:bg-[#14532d] hover:text-white text-slate-600 border border-slate-200 transition shadow-2xs flex items-center justify-center shrink-0" title="عرض الملف">
                                                                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                                                    </a>
                                                                </div>
                                                            </li>
                                                        @empty
                                                            <li class="tree-item text-slate-400 text-xs italic p-1 px-3">لا يوجد كادر</li>
                                                        @endforelse
                                                    </ul>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    <!-- 3. العمود الأيسر: لوحة التفاصيل المختصرة (Left Inspector Panel) -->
    @if($tab === 'org_units')
    <aside id="unitInspectorPanel" class="hidden xl:flex flex-col w-64 lg:w-68 shrink-0 bg-white border border-slate-200 shadow-xs rounded-2xl overflow-hidden select-none">
        
        <!-- ترويسة لوحة الفحص -->
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-[#14532d] text-sm"></i>
                <h3 class="text-xs font-black text-slate-800">التفاصيل</h3>
            </div>
            <span id="inspectorUnitTypeBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
                قسم أكاديمي
            </span>
        </div>

        <!-- بطاقة الجهة المحددة والمؤشرات -->
        <div class="p-3.5 flex-1 overflow-y-auto space-y-3 text-xs">
            
            <!-- تفاصيل الاسم والحالة -->
            <div class="p-3 bg-slate-50/70 border border-slate-200 rounded-2xl">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <h4 id="inspectorUnitTitle" class="text-sm font-black text-slate-900 leading-snug truncate">قسم الجغرافيا</h4>
                        <p id="inspectorUnitSubtitle" class="text-[11px] text-slate-500 font-medium truncate">كلية الآداب</p>
                    </div>
                    <span id="inspectorUnitStatus" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                        نشط
                    </span>
                </div>

                <!-- بطاقة إحصاء الكادر -->
                <div class="mt-2.5 p-2.5 px-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between shadow-2xs">
                    <div>
                        <div id="inspectorStaffCount" class="text-xl font-black text-slate-900 font-mono leading-none">0</div>
                        <div class="text-[11px] font-bold text-slate-500 mt-1">الكادر</div>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-base border border-emerald-100">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <!-- قائمة البيانات الوصفية -->
                <div class="mt-3 space-y-2 text-[11px] divide-y divide-slate-100 font-medium">
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-slate-400">النوع</span>
                        <span id="inspectorMetaType" class="font-bold text-slate-700">قسم أكاديمي</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-400">يتبع</span>
                        <span id="inspectorMetaParent" class="font-bold text-slate-700">جامعة إب</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-400">الرمز</span>
                        <span id="inspectorMetaCode" class="font-bold font-mono text-slate-700">—</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-400">الحالة</span>
                        <span class="font-bold text-emerald-700 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            نشط
                        </span>
                    </div>
                </div>
            </div>

            <!-- أزرار الإجراءات السريعة -->
            <div class="space-y-2">
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" 
                            onclick="inspectorEditCurrentUnit()" 
                            class="px-2.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold flex items-center justify-center gap-1.5 transition shadow-2xs cursor-pointer" title="تعديل">
                        <i class="fa-solid fa-pen-to-square text-slate-500 text-xs"></i>
                        <span>تعديل</span>
                    </button>
                    <a id="inspectorViewStaffBtn" 
                       href="{{ route('employees.index') }}" 
                       class="px-2.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold flex items-center justify-center gap-1.5 transition shadow-2xs" title="عرض الكادر">
                        <i class="fa-solid fa-users text-slate-500 text-xs"></i>
                        <span>الكادر</span>
                    </a>
                </div>
                
                <a id="inspectorAddStaffBtn" 
                   href="{{ route('employees.create') }}" 
                   class="w-full px-3 py-2 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white text-xs font-bold flex items-center justify-center gap-1.5 transition shadow-xs">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>إضافة كادر</span>
                </a>

                <button type="button" 
                        id="inspectorStatsBtn"
                        onclick="showUnitStatsModal()" 
                        class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-950 text-xs font-bold flex items-center justify-center gap-1.5 transition cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-chart-pie text-emerald-700 text-xs"></i>
                    <span id="inspectorStatsBtnText">الإحصائيات</span>
                </button>
            </div>

        </div>
    </aside>
    @endif

</div>
</div>

<!-- ========================================================================= -->
<!-- النوافذ المنبثقة للإضافة والتعديل عبر كافة الأقسام (Modals) -->
<!-- ========================================================================= -->

<!-- نافذة الإحصائيات الشاملة للكلية أو القسم (College & Unit Complete Statistics Modal) -->
<div id="modal-unit-stats" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        
        <!-- ترويسة نافذة الإحصائيات -->
        <div class="bg-gradient-to-r from-emerald-950 via-[#14532d] to-slate-900 text-white p-4 px-6 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-emerald-300 text-xl shadow-xs">
                    <i class="fa-solid fa-chart-pie" id="statsModalIcon"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 id="statsModalUnitName" class="text-base font-black text-white">إحصائيات الكلية</h3>
                        <span id="statsModalUnitTypeBadge" class="text-[10px] bg-emerald-500/30 border border-emerald-400/40 text-emerald-200 px-2.5 py-0.5 rounded-full font-bold">كلية أكاديمية</span>
                    </div>
                    <p class="text-xs text-emerald-200/80 font-medium">
                        الجهة الأم: <span id="statsModalParentName" class="text-white font-bold">جامعة إب</span>
                        <span class="mx-2 text-white/30">•</span>
                        الرمز المرجعي: <span id="statsModalUnitCode" class="text-white font-mono font-bold">—</span>
                    </p>
                </div>
            </div>
            <button onclick="closeModal('modal-unit-stats')" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center font-bold text-sm transition cursor-pointer">
                ✕
            </button>
        </div>

        <!-- جسم النافذة ومؤشرات الإحصائيات -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6 bg-slate-50/50">
            
            <!-- مؤشر التحميل -->
            <div id="statsModalLoading" class="py-12 flex flex-col items-center justify-center gap-3 text-slate-500">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-emerald-700"></i>
                <span class="text-xs font-bold">جاري تجميع الإحصائيات والبيانات الحية من قاعدة البيانات...</span>
            </div>

            <!-- حاوية المحتوى الفعلي -->
            <div id="statsModalBody" class="hidden space-y-6">

                <!-- 1. البطاقات الإحصائية الأربع الرئيسية (KPI Cards) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    
                    <!-- بطاقة الأقسام -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between text-slate-500 mb-1">
                            <span class="text-xs font-bold">الأقسام التابعة</span>
                            <i class="fa-solid fa-sitemap text-blue-600 text-sm"></i>
                        </div>
                        <div id="statsKpiDepartments" class="text-2xl font-black text-slate-900 font-mono">0</div>
                        <span class="text-[10px] text-slate-400 font-medium">قسم أكاديمي معتمد</span>
                    </div>

                    <!-- بطاقة إجمالي الكادر -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between text-slate-500 mb-1">
                            <span class="text-xs font-bold">إجمالي الكادر</span>
                            <i class="fa-solid fa-users text-indigo-600 text-sm"></i>
                        </div>
                        <div id="statsKpiStaff" class="text-2xl font-black text-slate-900 font-mono">0</div>
                        <span class="text-[10px] text-slate-400 font-medium">عضو تدريسي ووظيفي</span>
                    </div>

                    <!-- بطاقة على رأس العمل -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between text-slate-500 mb-1">
                            <span class="text-xs font-bold">على رأس العمل</span>
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        </div>
                        <div id="statsKpiActive" class="text-2xl font-black text-emerald-700 font-mono">0</div>
                        <span class="text-[10px] text-emerald-600 font-medium">حالة نشطة ومباشرة</span>
                    </div>

                    <!-- بطاقة الوثائق والأرشيف -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between text-slate-500 mb-1">
                            <span class="text-xs font-bold">وثائق الأرشيف</span>
                            <i class="fa-solid fa-box-archive text-purple-600 text-sm"></i>
                        </div>
                        <div id="statsKpiDocs" class="text-2xl font-black text-purple-700 font-mono">0</div>
                        <span class="text-[10px] text-purple-600 font-medium">ملف مؤرشف ومفهرس</span>
                    </div>

                </div>

                <!-- 2. شبكة التفاصيل: الأقسام + توزيع الرتب والحالات -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    
                    <!-- جدول توزيع الأقسام التابعة -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-col">
                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 font-bold text-slate-800">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-folder-tree text-blue-600"></i>
                                <span>توزيع الأقسام والكادر</span>
                            </span>
                            <span id="statsDeptsBadge" class="bg-blue-50 text-blue-800 border border-blue-200 px-2 py-0.2 rounded-full text-[10px] font-mono">0 قسم</span>
                        </div>
                        <div id="statsDepartmentsContainer" class="space-y-2 overflow-y-auto max-h-56 pr-1 no-scrollbar">
                            <!-- يتم ملؤها ديناميكياً -->
                        </div>
                    </div>

                    <!-- توزيع الرتب الأكاديمية والنوع الاجتماعي -->
                    <div class="space-y-4">
                        
                        <!-- توزيع الرتب -->
                        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 font-bold text-slate-800">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-graduation-cap text-indigo-600"></i>
                                    <span>توزيع الرتب الأكاديمية</span>
                                </span>
                            </div>
                            <div id="statsRanksContainer" class="flex flex-wrap gap-1.5">
                                <!-- يتم ملؤها ديناميكياً -->
                            </div>
                        </div>

                        <!-- الحالات الوظيفية والنوع الاجتماعي -->
                        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 font-bold text-slate-800">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-chart-column text-teal-600"></i>
                                    <span>الحالة الوظيفية والتوزيع الديمغرافي</span>
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 flex items-center justify-between">
                                    <span class="text-slate-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-mars text-blue-600"></i> ذكور
                                    </span>
                                    <span id="statsMaleCount" class="font-mono font-bold text-slate-900">0</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 flex items-center justify-between">
                                    <span class="text-slate-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-venus text-rose-500"></i> إناث
                                    </span>
                                    <span id="statsFemaleCount" class="font-mono font-bold text-slate-900">0</span>
                                </div>
                            </div>
                            <div id="statsStatusesContainer" class="flex flex-wrap gap-1.5">
                                <!-- يتم ملؤها ديناميكياً -->
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- تذييل النافذة والإجراءات المباشرة -->
        <div class="p-4 px-6 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2">
                <a id="statsModalViewStaffLink" href="#" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 border border-indigo-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-users text-indigo-600"></i>
                    <span>عرض كافة كوادر الكلية</span>
                </a>
                <a id="statsModalViewArchiveLink" href="#" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-box-archive text-purple-600"></i>
                    <span>عرض أرشيف ووثائق الكلية</span>
                </a>
            </div>
            <button type="button" onclick="closeModal('modal-unit-stats')" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                إغلاق النافذة
            </button>
        </div>

    </div>
</div>

<!-- 1. Modal إضافة جهة / قسم -->
<div id="modal-add-unit" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-md shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-sitemap text-primary"></i>
                <span id="modalAddUnitTitle">إضافة جهة أو قسم بالهيكل</span>
            </h3>
            <button onclick="closeModal('modal-add-unit')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.units.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الجهة الرئيسية التابعة لها (الكلية / المركز):</label>
                <select name="parent_unit_id" id="modal_parent_unit_id" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none bg-slate-50 font-bold">
                    <option value="">-- مستوى رئيسي (كلية / مركز / إدارة عامة) --</option>
                    @foreach($faculties as $f)
                        <option value="{{ $f->organizational_unit_id }}">{{ $f->unit_name_ar }}</option>
                    @endforeach
                </select>
                <!-- صندوق عرض الكلية الثابتة عند الضغط على إضافة قسم لكلية معينة -->
                <div id="modal_parent_fixed_box" class="hidden bg-emerald-50 border border-emerald-300 p-2.5 rounded-xl text-xs font-bold text-emerald-950 flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-building-columns text-[#14532d]"></i>
                        <span id="modal_parent_fixed_name">اسم الكلية</span>
                    </div>
                    <span class="text-[10px] bg-white text-emerald-800 font-bold px-2 py-0.5 rounded border border-emerald-200">الكلية محددة تلقائياً وثابتة</span>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">نوع الوحدة: <span class="text-rose-500">*</span></label>
                <select name="unit_type" id="modal_unit_type" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none bg-slate-50">
                    <option value="faculty">كلية</option>
                    <option value="department">قسم أكاديمي</option>
                    <option value="center">مركز أكاديمي / بحثي</option>
                    <option value="administration">إدارة عامة</option>
                </select>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div class="col-span-1">
                    <label class="block font-bold text-slate-700 text-xs mb-1">الرمز:</label>
                    <input type="text" name="unit_code" placeholder="مثال: ENG" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
                </div>
                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 text-xs mb-1">الاسم بالعربية: <span class="text-rose-500">*</span></label>
                    <input type="text" name="unit_name_ar" required placeholder="مثال: قسم الهندسة المدنية" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الاسم بالإنجليزية (اختياري):</label>
                <input type="text" name="unit_name_en" placeholder="مثال: Civil Engineering Department" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-unit')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ وإضافة</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal إضافة رتبة علمية -->
<div id="modal-add-rank" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-award text-primary"></i>
                <span>إضافة رتبة علمية</span>
            </h3>
            <button onclick="closeModal('modal-add-rank')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.ranks.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الرتبة بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="rank_name_ar" required placeholder="مثال: أستاذ زائر" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                    <input type="text" name="rank_code" required placeholder="مثال: VIS-PROF" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">ترتيب الأقدمية: <span class="text-rose-500">*</span></label>
                    <input type="number" name="rank_order" required value="{{ $ranks->count() + 1 }}" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono text-center">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-rank')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal إضافة درجة وظيفية -->
<div id="modal-add-grade" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-briefcase text-primary"></i>
                <span>إضافة درجة وظيفية</span>
            </h3>
            <button onclick="closeModal('modal-add-grade')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.grades.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الدرجة بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="grade_name_ar" required placeholder="مثال: الدرجة الخاصة" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">رمز الدرجة: <span class="text-rose-500">*</span></label>
                <input type="text" name="grade_code" required placeholder="مثال: SPEC" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-grade')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Modal إضافة نوع وثيقة -->
<div id="modal-add-doctype" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-file-shield text-primary"></i>
                <span>إضافة نوع وثيقة</span>
            </h3>
            <button onclick="closeModal('modal-add-doctype')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.doctypes.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الوثيقة بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_name_ar" required placeholder="مثال: شهادة خبرة أكاديمية" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_code" required placeholder="مثال: EXP_CERT" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="add_req_default" name="is_required_by_default" value="1" class="rounded text-brand-900">
                <label for="add_req_default" class="text-xs font-bold text-slate-700 cursor-pointer">وثيقة إلزامية في الملف الأكاديمي</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-doctype')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Modal إضافة حالة وظيفية -->
<div id="modal-add-status" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-tags text-primary"></i>
                <span>إضافة حالة وظيفية</span>
            </h3>
            <button onclick="closeModal('modal-add-status')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.statuses.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الحالة بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="status_name_ar" required placeholder="مثال: مكلف بمهام إدارية" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                <input type="text" name="status_code" required placeholder="مثال: ASSIGNED" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="add_active_status" name="is_active_status" value="1" checked class="rounded text-brand-900">
                <label for="add_active_status" class="text-xs font-bold text-slate-700 cursor-pointer">تعتبر حالة نشطة (على رأس العمل)</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-status')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- 6. Modal إضافة مسمى وظيفي -->
<div id="modal-add-jobtitle" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-tag text-primary"></i>
                <span>إضافة مسمى وظيفي</span>
            </h3>
            <button onclick="closeModal('modal-add-jobtitle')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.jobtitles.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">المسمى بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="title_name_ar" required placeholder="مثال: منسق أكاديمي" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">المسمى بالإنجليزية (اختياري):</label>
                <input type="text" name="title_name_en" placeholder="Academic Coordinator" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-jobtitle')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- 7. Modal إضافة نوع قرار -->
<div id="modal-add-decisiontype" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-stamp text-primary"></i>
                <span>إضافة نوع قرار إداري</span>
            </h3>
            <button onclick="closeModal('modal-add-decisiontype')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.decisiontypes.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">نوع القرار بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_name_ar" required placeholder="مثال: قرار تفرغ جزئي" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_code" required placeholder="مثال: PART_SABB" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="add_requires_end_date" name="requires_end_date" value="1" class="rounded text-brand-900">
                <label for="add_requires_end_date" class="text-xs font-bold text-slate-700 cursor-pointer">يتطلب تحديد تاريخ نهاية (مثل الإجازات)</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-decisiontype')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- 8. Modal إضافة جامعة -->
<div id="modal-add-university" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-graduation-cap text-primary"></i>
                <span>إضافة جامعة أو مؤسسة مانحة</span>
            </h3>
            <button onclick="closeModal('modal-add-university')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('master_data.universities.store') }}" method="POST" class="p-5 text-sm space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">اسم الجامعة بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="university_name_ar" required placeholder="مثال: جامعة الملك سعود" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الدولة: <span class="text-rose-500">*</span></label>
                <select name="country_id" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none bg-slate-50">
                    <option value="">-- حدد الدولة --</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->country_id }}">{{ $c->country_name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الاسم بالإنجليزية (اختياري):</label>
                <input type="text" name="university_name_en" placeholder="King Saud University" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-university')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- Modals التعديل (Edit Modals) -->
<!-- تعديل جهة / قسم -->
<div id="modal-edit-unit" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-md shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل بيانات الجهة أو القسم</span>
            </h3>
            <button onclick="closeModal('modal-edit-unit')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-unit" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الاسم بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="unit_name_ar" id="edit_unit_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الاسم بالإنجليزية:</label>
                <input type="text" name="unit_name_en" id="edit_unit_name_en" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الرمز:</label>
                <input type="text" name="unit_code" id="edit_unit_code" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-unit')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<!-- تعديل رتبة -->
<div id="modal-edit-rank" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل الرتبة العلمية</span>
            </h3>
            <button onclick="closeModal('modal-edit-rank')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-rank" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الرتبة: <span class="text-rose-500">*</span></label>
                <input type="text" name="rank_name_ar" id="edit_rank_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                    <input type="text" name="rank_code" id="edit_rank_code" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">الأقدمية: <span class="text-rose-500">*</span></label>
                    <input type="number" name="rank_order" id="edit_rank_order" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono text-center">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-rank')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- تعديل درجة -->
<div id="modal-edit-grade" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل الدرجة الوظيفية</span>
            </h3>
            <button onclick="closeModal('modal-edit-grade')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-grade" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الدرجة: <span class="text-rose-500">*</span></label>
                <input type="text" name="grade_name_ar" id="edit_grade_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">رمز الدرجة: <span class="text-rose-500">*</span></label>
                <input type="text" name="grade_code" id="edit_grade_code" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-grade')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- تعديل نوع وثيقة -->
<div id="modal-edit-doctype" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل نوع الوثيقة</span>
            </h3>
            <button onclick="closeModal('modal-edit-doctype')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-doctype" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الوثيقة: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_name_ar" id="edit_doctype_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_code" id="edit_doctype_code" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_is_required_by_default" name="is_required_by_default" value="1" class="rounded text-brand-900">
                <label for="edit_is_required_by_default" class="text-xs font-bold text-slate-700 cursor-pointer">وثيقة إلزامية</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-doctype')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- تعديل حالة وظيفية -->
<div id="modal-edit-status" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل الحالة الوظيفية</span>
            </h3>
            <button onclick="closeModal('modal-edit-status')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-status" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">مسمى الحالة: <span class="text-rose-500">*</span></label>
                <input type="text" name="status_name_ar" id="edit_status_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                <input type="text" name="status_code" id="edit_status_code" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_is_active_status" name="is_active_status" value="1" class="rounded text-brand-900">
                <label for="edit_is_active_status" class="text-xs font-bold text-slate-700 cursor-pointer">حالة نشطة</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-status')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- تعديل مسمى وظيفي -->
<div id="modal-edit-jobtitle" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل المسمى الوظيفي</span>
            </h3>
            <button onclick="closeModal('modal-edit-jobtitle')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-jobtitle" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">المسمى بالعربية: <span class="text-rose-500">*</span></label>
                <input type="text" name="title_name_ar" id="edit_title_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">المسمى بالإنجليزية:</label>
                <input type="text" name="title_name_en" id="edit_title_name_en" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-jobtitle')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- تعديل نوع قرار -->
<div id="modal-edit-decisiontype" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل نوع القرار</span>
            </h3>
            <button onclick="closeModal('modal-edit-decisiontype')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-decisiontype" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">نوع القرار: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_name_ar" id="edit_decisiontype_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الرمز: <span class="text-rose-500">*</span></label>
                <input type="text" name="type_code" id="edit_decisiontype_code" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_requires_end_date" name="requires_end_date" value="1" class="rounded text-brand-900">
                <label for="edit_requires_end_date" class="text-xs font-bold text-slate-700 cursor-pointer">يتطلب تاريخ نهاية</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-decisiontype')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- تعديل جامعة -->
<div id="modal-edit-university" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-sm shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-white border-b border-slate-100 p-4 px-5 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>تعديل بيانات الجامعة</span>
            </h3>
            <button onclick="closeModal('modal-edit-university')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>
        <form id="form-edit-university" method="POST" action="" class="p-5 text-sm space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">اسم الجامعة: <span class="text-rose-500">*</span></label>
                <input type="text" name="university_name_ar" id="edit_university_name_ar" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الدولة: <span class="text-rose-500">*</span></label>
                <select name="country_id" id="edit_university_country_id" required class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none bg-slate-50">
                    @foreach($countries as $c)
                        <option value="{{ $c->country_id }}">{{ $c->country_name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">الاسم بالإنجليزية:</label>
                <input type="text" name="university_name_en" id="edit_university_name_en" class="w-full border border-slate-300 p-2.5 text-xs rounded-xl focus:border-primary focus:outline-none font-mono">
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-university')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- شريط خفيف عائم لمعلومات الكادر عند تمرير المؤشر -->
<div id="cadreHoverCard" 
     class="fixed z-50 hidden opacity-0 transition-opacity duration-150 pointer-events-none bg-white/95 backdrop-blur-md border border-emerald-500/40 shadow-2xl rounded-2xl p-3.5 w-84 max-w-sm text-xs text-slate-800 select-none ring-1 ring-black/5"
     style="top: 0; left: 0;">
    
    <!-- الترويسة: اسم الكادر والرقم الوظيفي -->
    <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-100">
        <div class="flex items-center gap-2 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-[#14532d] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="min-w-0">
                <div id="cardEmpName" class="font-black text-slate-900 text-xs truncate"></div>
                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono">
                    <span>رقم وظيفي:</span>
                    <span id="cardEmpNumber" class="font-bold text-emerald-800 bg-emerald-50 px-1 rounded border border-emerald-200"></span>
                </div>
            </div>
        </div>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
            بطاقة كادر
        </span>
    </div>

    <!-- شبكة البيانات: الكلية، القسم، الرتبة، الدرجة، الموقف، الوظيفة بالراتب -->
    <div class="grid grid-cols-2 gap-2 text-[11px]">
        <!-- الكلية -->
        <div class="bg-slate-50/90 p-2 rounded-xl border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-building-columns text-emerald-600 text-[10px]"></i> الكلية:
            </span>
            <span id="cardEmpCollege" class="font-bold text-slate-800 truncate block mt-0.5"></span>
        </div>

        <!-- القسم -->
        <div class="bg-slate-50/90 p-2 rounded-xl border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-sitemap text-blue-600 text-[10px]"></i> القسم:
            </span>
            <span id="cardEmpDept" class="font-bold text-slate-800 truncate block mt-0.5"></span>
        </div>

        <!-- الرتبة العلمية -->
        <div class="bg-slate-50/90 p-2 rounded-xl border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-award text-amber-500 text-[10px]"></i> الرتبة العلمية:
            </span>
            <span id="cardEmpRank" class="font-bold text-slate-800 truncate block mt-0.5"></span>
        </div>

        <!-- الدرجة المالية -->
        <div class="bg-slate-50/90 p-2 rounded-xl border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-briefcase text-indigo-600 text-[10px]"></i> الدرجة المالية:
            </span>
            <span id="cardEmpGrade" class="font-bold text-slate-800 truncate block mt-0.5"></span>
        </div>

        <!-- الموقف الوظيفي -->
        <div class="bg-slate-50/90 p-2 rounded-xl border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-tags text-teal-600 text-[10px]"></i> الموقف الوظيفي:
            </span>
            <span id="cardEmpStatus" class="font-bold text-slate-800 truncate block mt-0.5"></span>
        </div>

        <!-- الوظيفة بكشف الراتب / المسمى -->
        <div class="bg-slate-50/90 p-2 rounded-xl border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-user-tag text-purple-600 text-[10px]"></i> الوظيفة بالراتب:
            </span>
            <span id="cardEmpJobTitle" class="font-bold text-slate-800 truncate block mt-0.5"></span>
        </div>
    </div>

    <!-- شريط سفلي: حالة المرتب + تاريخ التعيين -->
    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-medium">
        <div class="flex items-center gap-1 truncate max-w-[170px]">
            <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
            <span>المرتب:</span>
            <strong id="cardEmpPayroll" class="text-slate-800 font-bold truncate"></strong>
        </div>
        <div class="flex items-center gap-1 shrink-0">
            <i class="fa-regular fa-calendar-check text-blue-600"></i>
            <span>التعيين:</span>
            <strong id="cardEmpHireDate" class="font-mono text-slate-800 font-bold"></strong>
        </div>
    </div>
</div>

<!-- خطوط التوصيل المنقطة والأنماط الشجرية المطابقة لدليل الحسابات الكلاسيكي -->
<style>
    .tree-branch {
        list-style: none;
        margin: 0;
        padding-right: 22px;
        position: relative;
        border-right: 1px dotted #94a3b8;
        margin-right: 10px;
    }
    .tree-item {
        position: relative;
        padding: 3px 0;
        list-style: none;
    }
    .tree-branch > .tree-item::before {
        content: '';
        position: absolute;
        right: -22px;
        top: 17px;
        width: 18px;
        border-bottom: 1px dotted #94a3b8;
    }
    .tree-expander {
        user-select: none;
        transition: all 0.15s ease-in-out;
    }
    .tree-expander:hover {
        border-color: #14532d;
        background-color: #f0fdf4;
        color: #14532d;
    }
    .tree-row {
        user-select: none;
        min-height: 38px;
        box-sizing: border-box;
        transition: background-color 0.12s ease, border-color 0.12s ease;
    }
    .tree-row:hover {
        transform: none !important;
    }
</style>

@endsection

@section('scripts')
<script>
    // دالة الفحص الحي واختيار الجهة / القسم (Inspector Panel Logic)
    let currentSelectedUnitData = null;

    function selectUnitForInspector(data, element) {
        currentSelectedUnitData = data;
        
        const titleEl = document.getElementById('inspectorUnitTitle');
        const subtitleEl = document.getElementById('inspectorUnitSubtitle');
        const badgeEl = document.getElementById('inspectorUnitTypeBadge');
        const metaTypeEl = document.getElementById('inspectorMetaType');
        const metaParentEl = document.getElementById('inspectorMetaParent');
        const metaCodeEl = document.getElementById('inspectorMetaCode');
        const staffCountEl = document.getElementById('inspectorStaffCount');
        const viewStaffBtn = document.getElementById('inspectorViewStaffBtn');
        const addStaffBtn = document.getElementById('inspectorAddStaffBtn');

        if (titleEl) titleEl.innerText = data.name;
        if (subtitleEl) subtitleEl.innerText = data.parentName;
        if (badgeEl) badgeEl.innerText = data.typeName;
        if (metaTypeEl) metaTypeEl.innerText = data.typeName;
        if (metaParentEl) metaParentEl.innerText = data.parentName;
        if (metaCodeEl) metaCodeEl.innerText = data.code || '—';
        if (staffCountEl) staffCountEl.innerText = data.staffCount || '0';

        if (data.isFaculty) {
            if (viewStaffBtn) viewStaffBtn.href = '/employees?college_id=' + data.id;
            if (addStaffBtn) addStaffBtn.href = '/employees/create?faculty_id=' + data.id;
        } else {
            if (viewStaffBtn) viewStaffBtn.href = '/employees?dept_id=' + data.id;
            if (addStaffBtn) addStaffBtn.href = '/employees/create?faculty_id=' + (data.parentUnitId || '') + '&dept_id=' + data.id;
        }

        const statsBtnText = document.getElementById('inspectorStatsBtnText');
        if (statsBtnText) {
            statsBtnText.innerText = 'الإحصائيات';
        }

        // تمييز السطر المختار بصرياً
        document.querySelectorAll('#treeViewContainer .tree-row').forEach(row => {
            row.classList.remove('is-selected-unit', 'bg-emerald-50/90', 'border-emerald-300', 'ring-1', 'ring-emerald-400');
        });

        if (element) {
            const targetRow = element.classList.contains('tree-row') ? element : element.closest('.tree-row');
            if (targetRow) {
                targetRow.classList.add('is-selected-unit', 'bg-emerald-50/90', 'border-emerald-300', 'ring-1', 'ring-emerald-400');
            }
        }
    }

    function inspectorEditCurrentUnit() {
        if (currentSelectedUnitData && currentSelectedUnitData.editData) {
            openEditUnitModal(currentSelectedUnitData.editData);
        } else {
            alert('يرجى اختيار جهة أو قسم من الشجرة أولاً.');
        }
    }

    // عرض الإحصائيات الشاملة للكلية أو القسم المختار
    function showUnitStatsModal() {
        if (!currentSelectedUnitData || !currentSelectedUnitData.id) {
            alert('يرجى النقر أولاً على كلية أو قسم في الشجرة لعرض إحصائياته.');
            return;
        }

        const modal = document.getElementById('modal-unit-stats');
        if (!modal) return;
        modal.classList.remove('hidden');

        const loadingEl = document.getElementById('statsModalLoading');
        const bodyEl = document.getElementById('statsModalBody');
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (bodyEl) bodyEl.classList.add('hidden');

        // ملء البيانات المبدئية للترويسة فوراً
        const unitNameEl = document.getElementById('statsModalUnitName');
        const parentNameEl = document.getElementById('statsModalParentName');
        const unitCodeEl = document.getElementById('statsModalUnitCode');
        const typeBadgeEl = document.getElementById('statsModalUnitTypeBadge');
        if (unitNameEl) unitNameEl.innerText = currentSelectedUnitData.name || 'إحصائيات الجهة';
        if (parentNameEl) parentNameEl.innerText = currentSelectedUnitData.parentName || 'جامعة إب';
        if (unitCodeEl) unitCodeEl.innerText = currentSelectedUnitData.code || '—';
        if (typeBadgeEl) typeBadgeEl.innerText = currentSelectedUnitData.typeName || 'جهة أكاديمية';

        // ربط أزرار الإجراءات للفلترة الفورية
        const staffLink = document.getElementById('statsModalViewStaffLink');
        const archiveLink = document.getElementById('statsModalViewArchiveLink');
        if (currentSelectedUnitData.isFaculty) {
            if (staffLink) staffLink.href = '/employees?college_id=' + currentSelectedUnitData.id;
            if (archiveLink) archiveLink.href = '/archive?college_id=' + currentSelectedUnitData.id;
        } else {
            if (staffLink) staffLink.href = '/employees?dept_id=' + currentSelectedUnitData.id;
            if (archiveLink) archiveLink.href = '/archive?dept_id=' + currentSelectedUnitData.id;
        }

        // جلب الإحصائيات الكاملة عبر الـ API الحي
        fetch('/master-data/unit-stats/' + currentSelectedUnitData.id)
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                const stats = data.stats;
                const kpiDepts = document.getElementById('statsKpiDepartments');
                const kpiStaff = document.getElementById('statsKpiStaff');
                const kpiActive = document.getElementById('statsKpiActive');
                const kpiDocs = document.getElementById('statsKpiDocs');
                const maleEl = document.getElementById('statsMaleCount');
                const femaleEl = document.getElementById('statsFemaleCount');

                if (kpiDepts) kpiDepts.innerText = stats.departments_count || '0';
                if (kpiStaff) kpiStaff.innerText = stats.staff_count || '0';
                if (kpiActive) kpiActive.innerText = stats.active_staff_count || '0';
                if (kpiDocs) kpiDocs.innerText = stats.documents_count || '0';
                if (maleEl) maleEl.innerText = stats.male_count || '0';
                if (femaleEl) femaleEl.innerText = stats.female_count || '0';

                // قائمة توزيع الأقسام التابعة
                const deptsContainer = document.getElementById('statsDepartmentsContainer');
                const deptsBadge = document.getElementById('statsDeptsBadge');
                if (deptsContainer) {
                    if (stats.departments && stats.departments.length > 0) {
                        if (deptsBadge) deptsBadge.innerText = stats.departments.length + ' قسم';
                        deptsContainer.innerHTML = stats.departments.map(d => `
                            <div class="p-2.5 px-3 bg-slate-50 hover:bg-blue-50/60 rounded-xl border border-slate-200 transition flex items-center justify-between">
                                <div class="min-w-0 pr-1">
                                    <span class="font-bold text-slate-800 text-xs block truncate">${d.name}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">${d.code}</span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 text-[11px] font-mono">
                                    <span class="bg-indigo-50 text-indigo-900 border border-indigo-200 px-2 py-0.5 rounded-lg font-bold">${d.staff_count} كادر</span>
                                    <span class="bg-purple-50 text-purple-900 border border-purple-200 px-2 py-0.5 rounded-lg font-bold">${d.docs_count} وثيقة</span>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        if (deptsBadge) deptsBadge.innerText = '0 قسم';
                        deptsContainer.innerHTML = '<div class="text-slate-400 text-xs p-4 text-center italic">لا توجد أقسام تابعة مسجلة لهذه الجهة.</div>';
                    }
                }

                // توزيع الرتب الأكاديمية
                const ranksContainer = document.getElementById('statsRanksContainer');
                if (ranksContainer) {
                    const ranks = stats.ranks || {};
                    const rankKeys = Object.keys(ranks);
                    if (rankKeys.length > 0) {
                        ranksContainer.innerHTML = rankKeys.map(r => `
                            <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-950 border border-indigo-200 px-2.5 py-1 rounded-xl font-bold text-[11px]">
                                <span>${r}</span>
                                <span class="bg-indigo-200 text-indigo-900 px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold">${ranks[r]}</span>
                            </span>
                        `).join('');
                    } else {
                        ranksContainer.innerHTML = '<span class="text-slate-400 text-xs italic">لا توجد رتب مسجلة حالياً.</span>';
                    }
                }

                // الحالات الوظيفية
                const statusesContainer = document.getElementById('statsStatusesContainer');
                if (statusesContainer) {
                    const statuses = stats.statuses || {};
                    const statusKeys = Object.keys(statuses);
                    if (statusKeys.length > 0) {
                        statusesContainer.innerHTML = statusKeys.map(s => `
                            <span class="inline-flex items-center gap-1.5 bg-teal-50 text-teal-950 border border-teal-200 px-2.5 py-1 rounded-xl font-bold text-[11px]">
                                <span>${s}</span>
                                <span class="bg-teal-200 text-teal-900 px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold">${statuses[s]}</span>
                            </span>
                        `).join('');
                    } else {
                        statusesContainer.innerHTML = '<span class="text-slate-400 text-xs italic">لا توجد حالات محددة.</span>';
                    }
                }

                if (loadingEl) loadingEl.classList.add('hidden');
                if (bodyEl) bodyEl.classList.remove('hidden');
            })
            .catch(err => {
                console.error('Error fetching unit stats:', err);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-rose-600 font-bold text-xs flex flex-col items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                            <span>تعذر تحميل إحصائيات الجهة، يرجى المحاولة لاحقاً.</span>
                        </div>
                    `;
                }
            });
    }

    // تصفية بطاقات أقسام الهيكل والبيانات
    function filterCategories(query) {
        const q = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.category-nav-card');
        cards.forEach(card => {
            const text = (card.getAttribute('data-category-name') || '') + ' ' + card.innerText.toLowerCase();
            if (q === '' || text.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // التهيئة التلقائية عند تحميل الصفحة لاختيار أول جهة أو قسم في الفاحص
    document.addEventListener('DOMContentLoaded', function() {
        const firstDept = document.querySelector('.tree-department-node .tree-row') || document.querySelector('.tree-faculty-node .tree-row');
        if (firstDept) {
            firstDept.click();
        }
    });

    function openModal(id) {
        if (id === 'modal-add-unit') {
            const titleEl = document.getElementById('modalAddUnitTitle');
            const parentSelect = document.getElementById('modal_parent_unit_id');
            const fixedBox = document.getElementById('modal_parent_fixed_box');
            const unitTypeSelect = document.getElementById('modal_unit_type');
            if (titleEl) titleEl.innerText = 'إضافة جهة أو قسم بالهيكل';
            if (parentSelect) parentSelect.classList.remove('hidden');
            if (fixedBox) fixedBox.classList.add('hidden');
            if (unitTypeSelect) unitTypeSelect.classList.remove('pointer-events-none', 'bg-slate-100');
        }
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    function openAddDeptModal(facultyId, facultyName) {
        openModal('modal-add-unit');
        const titleEl = document.getElementById('modalAddUnitTitle');
        const parentSelect = document.getElementById('modal_parent_unit_id');
        const fixedBox = document.getElementById('modal_parent_fixed_box');
        const fixedName = document.getElementById('modal_parent_fixed_name');
        const unitTypeSelect = document.getElementById('modal_unit_type');

        if (titleEl) titleEl.innerText = 'إضافة قسم جديد يتبع: ' + facultyName;
        if (parentSelect) {
            parentSelect.value = facultyId;
            parentSelect.classList.add('hidden');
        }
        if (fixedBox && fixedName) {
            fixedName.innerText = facultyName;
            fixedBox.classList.remove('hidden');
        }
        if (unitTypeSelect) {
            unitTypeSelect.value = 'department';
            unitTypeSelect.classList.add('pointer-events-none', 'bg-slate-100');
        }
    }

    function openEditUnitModal(unit) {
        document.getElementById('form-edit-unit').action = '/master-data/units/' + unit.organizational_unit_id;
        document.getElementById('edit_unit_name_ar').value = unit.unit_name_ar || '';
        document.getElementById('edit_unit_name_en').value = unit.unit_name_en || '';
        document.getElementById('edit_unit_code').value = unit.unit_code || '';
        openModal('modal-edit-unit');
    }

    function openEditRankModal(rank) {
        document.getElementById('form-edit-rank').action = '/master-data/ranks/' + rank.academic_rank_id;
        document.getElementById('edit_rank_name_ar').value = rank.rank_name_ar || '';
        document.getElementById('edit_rank_code').value = rank.rank_code || '';
        document.getElementById('edit_rank_order').value = rank.rank_order || 1;
        openModal('modal-edit-rank');
    }

    function openEditGradeModal(grade) {
        document.getElementById('form-edit-grade').action = '/master-data/grades/' + grade.job_grade_id;
        document.getElementById('edit_grade_name_ar').value = grade.grade_name_ar || '';
        document.getElementById('edit_grade_code').value = grade.grade_code || '';
        openModal('modal-edit-grade');
    }

    function openEditDocTypeModal(doctype) {
        document.getElementById('form-edit-doctype').action = '/master-data/doctypes/' + doctype.document_type_id;
        document.getElementById('edit_doctype_name_ar').value = doctype.type_name_ar || '';
        document.getElementById('edit_doctype_code').value = doctype.type_code || '';
        document.getElementById('edit_is_required_by_default').checked = !!doctype.is_required_by_default;
        openModal('modal-edit-doctype');
    }

    function openEditStatusModal(status) {
        document.getElementById('form-edit-status').action = '/master-data/statuses/' + status.employment_status_id;
        document.getElementById('edit_status_name_ar').value = status.status_name_ar || '';
        document.getElementById('edit_status_code').value = status.status_code || '';
        document.getElementById('edit_is_active_status').checked = !!status.is_active_status;
        openModal('modal-edit-status');
    }

    function openEditJobTitleModal(jt) {
        document.getElementById('form-edit-jobtitle').action = '/master-data/jobtitles/' + jt.job_title_id;
        document.getElementById('edit_title_name_ar').value = jt.title_name_ar || '';
        document.getElementById('edit_title_name_en').value = jt.title_name_en || '';
        openModal('modal-edit-jobtitle');
    }

    function openEditDecisionTypeModal(dt) {
        document.getElementById('form-edit-decisiontype').action = '/master-data/decisiontypes/' + dt.decision_type_id;
        document.getElementById('edit_decisiontype_name_ar').value = dt.type_name_ar || '';
        document.getElementById('edit_decisiontype_code').value = dt.type_code || '';
        document.getElementById('edit_requires_end_date').checked = !!dt.requires_end_date;
        openModal('modal-edit-decisiontype');
    }

    function openEditUniversityModal(u) {
        document.getElementById('form-edit-university').action = '/master-data/universities/' + u.university_id;
        document.getElementById('edit_university_name_ar').value = u.university_name_ar || '';
        document.getElementById('edit_university_name_en').value = u.university_name_en || '';
        document.getElementById('edit_university_country_id').value = u.country_id || '';
        openModal('modal-edit-university');
    }

    // دالة طي وتوسيع أي فرع في الشجرة الهرمية
    function toggleBranch(branchId, triggerEl, event) {
        if (event) event.stopPropagation();
        const branch = document.getElementById(branchId);
        if (!branch) return;

        const isHidden = branch.classList.contains('hidden');
        const container = triggerEl.closest('.tree-item, .root-tree-node') || triggerEl;
        const expander = container.querySelector('.tree-expander[data-target="' + branchId + '"]');
        const folderIcon = container.querySelector('.folder-icon');

        if (isHidden) {
            branch.classList.remove('hidden');
            if (expander) expander.innerText = '-';
            if (folderIcon) {
                folderIcon.classList.remove('fa-folder');
                folderIcon.classList.add('fa-folder-open');
            }
        } else {
            branch.classList.add('hidden');
            if (expander) expander.innerText = '+';
            if (folderIcon) {
                folderIcon.classList.remove('fa-folder-open');
                folderIcon.classList.add('fa-folder');
            }
        }
    }

    function expandAllOrgTree() {
        document.querySelectorAll('#treeViewContainer .tree-branch').forEach(b => b.classList.remove('hidden'));
        document.querySelectorAll('#treeViewContainer .tree-expander').forEach(btn => btn.innerText = '-');
        document.querySelectorAll('#treeViewContainer .folder-icon').forEach(icon => {
            icon.classList.remove('fa-folder');
            icon.classList.add('fa-folder-open');
        });
    }

    function collapseAllOrgTree() {
        document.querySelectorAll('#treeViewContainer .tree-branch').forEach(b => {
            if (!b.id.startsWith('branch-root-')) {
                b.classList.add('hidden');
            }
        });
        document.querySelectorAll('#treeViewContainer .tree-expander').forEach(btn => {
            const tgt = btn.getAttribute('data-target') || '';
            if (!tgt.startsWith('branch-root-')) {
                btn.innerText = '+';
            }
        });
        document.querySelectorAll('#treeViewContainer .tree-faculty-node .folder-icon, #treeViewContainer .tree-department-node .folder-icon').forEach(icon => {
            icon.classList.remove('fa-folder-open');
            icon.classList.add('fa-folder');
        });
    }

    // تصفية ذكية تدعم الشجرة الهرمية في كل الأقسام
    function filterMasterTable(query) {
        const q = (query || '').toLowerCase().trim();
        const treeContainer = document.getElementById('treeViewContainer');
        if (!treeContainer) return;

        const items = treeContainer.querySelectorAll('.tree-faculty-node, .tree-department-node, .tree-leaf-node');
        if (q === '') {
            items.forEach(el => el.style.display = '');
            return;
        }

        items.forEach(el => el.style.display = 'none');

        items.forEach(el => {
            const targetText = (el.getAttribute('data-search-target') || '') + ' ' + el.innerText.toLowerCase();
            if (targetText.includes(q)) {
                el.style.display = '';
                let p = el.parentElement;
                while (p && p !== treeContainer) {
                    if (p.classList.contains('tree-branch')) {
                        p.classList.remove('hidden');
                        const parentNode = p.closest('.tree-item, .root-tree-node');
                        if (parentNode) {
                            const exp = parentNode.querySelector('.tree-expander[data-target="' + p.id + '"]');
                            if (exp) exp.innerText = '-';
                            const fIcon = parentNode.querySelector('.folder-icon');
                            if (fIcon) {
                                fIcon.classList.remove('fa-folder');
                                fIcon.classList.add('fa-folder-open');
                            }
                            parentNode.style.display = '';
                        }
                    }
                    p = p.parentElement;
                }
            }
        });
    }

    // دوال القائمة الجانبية للشاشات الصغيرة
    function toggleMasterNavDrawer() {
        const drawer = document.getElementById('masterNavDrawer');
        if (!drawer) return;
        if (drawer.classList.contains('translate-x-full')) {
            openMasterNavDrawer();
        } else {
            closeMasterNavDrawer();
        }
    }

    function openMasterNavDrawer() {
        const drawer = document.getElementById('masterNavDrawer');
        const backdrop = document.getElementById('masterNavBackdrop');
        const floatingArrow = document.getElementById('floatingNavArrow');
        if (drawer) drawer.classList.remove('translate-x-full');
        if (backdrop) {
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100', 'pointer-events-auto');
        }
        if (floatingArrow) floatingArrow.classList.add('rotate-180');
    }

    function closeMasterNavDrawer() {
        const drawer = document.getElementById('masterNavDrawer');
        const backdrop = document.getElementById('masterNavBackdrop');
        const floatingArrow = document.getElementById('floatingNavArrow');
        if (drawer) drawer.classList.add('translate-x-full');
        if (backdrop) {
            backdrop.classList.remove('opacity-100', 'pointer-events-auto');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
        }
        if (floatingArrow) floatingArrow.classList.remove('rotate-180');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMasterNavDrawer();
    });

    // تفعيل شريط معلومات الكادر الخفيف عند تمرير المؤشر (Hover Card/Ribbon)
    (function initCadreHoverCard() {
        const card = document.getElementById('cadreHoverCard');
        if (!card) return;

        let hideTimeout = null;

        document.addEventListener('mouseover', function(e) {
            const item = e.target.closest('[data-cadre-summary]');
            if (!item) return;

            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }

            try {
                const raw = item.getAttribute('data-cadre-summary');
                if (!raw) return;
                const data = JSON.parse(raw);

                const setElText = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.textContent = val || '—';
                        el.title = val || '';
                    }
                };

                setElText('cardEmpName', data.name);
                setElText('cardEmpNumber', data.number);
                setElText('cardEmpCollege', data.college);
                setElText('cardEmpDept', data.department);
                setElText('cardEmpRank', data.rank);
                setElText('cardEmpGrade', data.grade);
                setElText('cardEmpStatus', data.status);
                setElText('cardEmpJobTitle', data.job_title);
                setElText('cardEmpPayroll', data.payroll_status);
                setElText('cardEmpHireDate', data.appointment_date);

                const rect = item.getBoundingClientRect();
                const cardW = 340;
                const cardH = 220;

                // التموضع الذكي بحيث لا يخرج الشريط عن حدود الشاشة إطلاقاً
                let top = rect.bottom + 6;
                let left = rect.left + 20;

                if (top + cardH > window.innerHeight - 10) {
                    top = Math.max(10, rect.top - cardH - 6);
                }

                if (left + cardW > window.innerWidth - 16) {
                    left = window.innerWidth - cardW - 16;
                }
                if (left < 16) {
                    left = 16;
                }

                card.style.top = `${top}px`;
                card.style.left = `${left}px`;

                card.classList.remove('hidden');
                requestAnimationFrame(() => {
                    card.classList.remove('opacity-0');
                    card.classList.add('opacity-100');
                });
            } catch (err) {
                console.error('Hover card parse error:', err);
            }
        });

        document.addEventListener('mouseout', function(e) {
            const item = e.target.closest('[data-cadre-summary]');
            if (!item) return;

            if (e.relatedTarget && item.contains(e.relatedTarget)) {
                return;
            }

            hideTimeout = setTimeout(() => {
                card.classList.remove('opacity-100');
                card.classList.add('opacity-0');
                setTimeout(() => {
                    if (card.classList.contains('opacity-0')) {
                        card.classList.add('hidden');
                    }
                }, 150);
            }, 60);
        });
    })();
</script>
@endsection
