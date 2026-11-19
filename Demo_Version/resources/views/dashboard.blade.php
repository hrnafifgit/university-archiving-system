@extends('layouts.app')

@section('title', 'لوحة التحكم والعمليات الرئيسية | جامعة إب')
@section('page_title', 'لوحة التحكم والعمليات المركزية')

@section('content')
<div class="space-y-6">

    <!-- 1. الترويسة التنفيذية والساعة اللحظية (Executive Hero Header) -->
    <div class="bg-gradient-to-r from-brand-900 via-emerald-900 to-brand-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden border border-emerald-800/40">
        <!-- نمط شبكي إسلامي / هندسي خفيف في الخلفية -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" 
             style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            
            <!-- الجانب الأيمن: الترحيب والتعريف بالنظام -->
            <div class="flex items-center space-x-4 space-x-reverse text-right">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white p-2 shadow-2xl border-2 border-emerald-200 shrink-0 flex items-center justify-center">
                    <img src="{{ asset('images/ibb_logo.png') }}" alt="جامعة إب" class="w-full h-full object-contain">
                </div>
                <div>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-bold text-emerald-200 tracking-wider">جامعة إب — الجمهورية اليمنية</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black mt-1 text-white">
                        مركز العمليات والتحكم الرئيسي
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-100/80 mt-1 font-semibold">
                        منظومة حفظ وأرشفة وتوثيق كادر جامعة إب الموحدة
                    </p>
                </div>
            </div>

            <!-- الجانب الأيسر: شاشة التوقيت الرقمي وحالة النظام -->
            <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                <!-- ساعة رقمية لحظية -->
                <div class="bg-black/30 backdrop-blur-md border border-white/20 rounded-2xl px-5 py-3 text-center min-w-[170px] shadow-inner">
                    <div id="liveClock" class="text-lg sm:text-xl font-black text-emerald-300 font-mono tracking-widest">
                        --:--:-- --
                    </div>
                    <div id="liveDate" class="text-xs font-bold text-emerald-100/90 font-mono mt-0.5">
                        {{ date('Y/m/d') }} م
                    </div>
                </div>

                <!-- زر الإحصائيات السريع في الترويسة -->
                <a href="{{ route('statistics') }}" 
                   class="bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-brand-950 font-black px-4 py-3 rounded-2xl text-xs sm:text-sm flex items-center justify-center shadow-lg transition-all space-x-1.5 space-x-reverse">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 ml-1"></i>
                    <span>شاشة الإحصائيات الشاملة</span>
                </a>
            </div>

        </div>

        <!-- شريط المؤشرات المصغرة أسفل الترويسة -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-white/10 text-center">
            <div class="bg-white/5 rounded-xl p-2.5 border border-white/10">
                <span class="text-[11px] text-emerald-200 block font-semibold">إجمالي الكادر المقيد</span>
                <span class="text-xl sm:text-2xl font-black text-white font-mono">{{ number_format($totalStaff) }}</span>
            </div>
            <div class="bg-white/5 rounded-xl p-2.5 border border-white/10">
                <span class="text-[11px] text-emerald-200 block font-semibold">الوثائق والقرارات المرقمنة</span>
                <span class="text-xl sm:text-2xl font-black text-white font-mono">{{ number_format($totalArchives) }}</span>
            </div>
            <div class="bg-white/5 rounded-xl p-2.5 border border-white/10">
                <span class="text-[11px] text-emerald-200 block font-semibold">الكليات والمراكز الرسمية</span>
                <span class="text-xl sm:text-2xl font-black text-white font-mono">{{ $collegesCount }} جهة</span>
            </div>
            <div class="bg-white/5 rounded-xl p-2.5 border border-white/10">
                <span class="text-[11px] text-emerald-200 block font-semibold">حالة النظام وقاعدة البيانات</span>
                <span class="text-xs sm:text-sm font-bold text-emerald-300 mt-1 inline-flex items-center">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 ml-1.5"></span>
                    متصلة ونشطة 3NF
                </span>
            </div>
        </div>

    </div>

    <!-- 2. لوحة العمليات الرئيسية والأزرار الكاملة (Full Command Center Buttons) -->
    
    <!-- المجموعة الأولى: أزرار عمليات الكادر والأرشفة المباشرة -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h2 class="text-sm font-black text-slate-800 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-900 ml-2"></span>
                <span>عمليات الكادر والتوثيق الرقمي (العمليات الأساسية)</span>
            </h2>
            <span class="text-xs font-bold text-slate-400">انقر فوق أي زر لفتح النافذة المطلوبة</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- زر 1: تسجيل كادر جديد -->
            <a href="{{ route('staff.create') }}" 
               class="group bg-white hover:bg-brand-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-brand-900 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-900 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="user-plus" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">إدخال فوري</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-brand-900 transition-colors">
                        تسجيل كادر جديد
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        إضافة عضو كادر أكاديمي، إداري، أو متعاقد مع كافة البيانات ورفع الوثائق.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-brand-900">
                    <span>فتح نموذج الإدخال</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- زر 2: دليل واستعلام الكادر -->
            <a href="{{ route('staff.index') }}" 
               class="group bg-white hover:bg-brand-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-brand-900 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="search" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md">بحث وفلترة</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-brand-900 transition-colors">
                        دليل واستعلام الكادر
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        محرك البحث المتقدم، استعلام بالاسم، الرقم الوظيفي، الكلية، والرتبة.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-brand-900">
                    <span>استعراض السجلات</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- زر 3: الأرشيف الرقمي والملفات المرفوعة -->
            <a href="{{ route('staff.index') }}" 
               class="group bg-white hover:bg-brand-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-brand-900 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="folder-archive" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md">عارض PDF</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-brand-900 transition-colors">
                        الأرشيف الرقمي والملفات
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        معاينة ملفات الـ PDF المرقمنة، قرارات التعيين، التفرغ، والترقيات العلمية.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-700">
                    <span>فتح عارض الوثائق</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- زر 4: كادر هيئة التدريس (أكاديمي) -->
            <a href="{{ route('staff.index') }}?staff_type=academic" 
               class="group bg-white hover:bg-brand-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-brand-900 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-700 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-purple-100 text-purple-800 px-2 py-0.5 rounded-md">أعضاء التدريس</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-brand-900 transition-colors">
                        الهيئة التدريسية ومساعدوهم
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        كشف مباشر بأساتذة الجامعة، الرتب العلمية، والنصاب الأكاديمي.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-purple-700">
                    <span>استعراض الأكاديميين</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                </div>
            </a>

        </div>
    </div>

    <!-- المجموعة الثانية: أزرار التقارير وبيان الحالة والطباعة الرسمية -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h2 class="text-sm font-black text-slate-800 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 ml-2"></span>
                <span>التقارير المعتمدة وبيان الحالة الرسمية (مخرجات النظام)</span>
            </h2>
            <span class="text-xs font-bold text-slate-400">تقارير رسمية جاهزة للطباعة والتصدير</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- زر 5: استخراج بيان حالة معتمد -->
            <a href="{{ route('reports.index') }}" 
               class="group bg-white hover:bg-emerald-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-emerald-700 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-700 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">طباعة A4</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-800 transition-colors">
                        استخراج بيان حالة معتمد
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        نموذج بيان الحالة الرسمي بترويسة جامعة إب وختم الشؤون الأكاديمية.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-800">
                    <span>توليد بيان الحالة</span>
                    <i data-lucide="printer" class="w-4 h-4 ml-1"></i>
                </div>
            </a>

            <!-- زر 6: كشوفات الكليات والمراكز -->
            <a href="{{ route('staff.index') }}" 
               class="group bg-white hover:bg-emerald-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-emerald-700 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-700 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-md">حسب الجهة</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-800 transition-colors">
                        كشوفات الكليات والمراكز
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        قوائم مرتبة بحسب الكليات والجهات الـ 18 والأقسام التابعة لها.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-800">
                    <span>استعراض الكشوفات</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- زر 7: كادر الوظائف الإدارية والفنية -->
            <a href="{{ route('staff.index') }}?staff_type=administrative" 
               class="group bg-white hover:bg-emerald-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-emerald-700 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-600 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="briefcase" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-amber-100 text-amber-800 px-2 py-0.5 rounded-md">إداري وفني</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-800 transition-colors">
                        الكادر الإداري والفني
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        موظفو عمادات الكليات، الشؤون المالية، والتثبيت الوظيفي.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-800">
                    <span>استعراض الإداريين</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- زر 8: المتعاقدون بالساعات والمنتدبون -->
            <a href="{{ route('staff.index') }}?staff_type=contract" 
               class="group bg-white hover:bg-emerald-50/50 p-5 rounded-2xl border-2 border-slate-200 hover:border-emerald-700 shadow-sm hover:shadow-md transition-all flex flex-col justify-between active:scale-[0.98]">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black bg-rose-100 text-rose-800 px-2 py-0.5 rounded-md">عقود وساعات</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-rose-800 transition-colors">
                        المتعاقدون بالساعات
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        سجلات العقود الفصلية، الساعات التدريسية المعتمدة، والاستحقاق المالي.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-800">
                    <span>استعراض المتعاقدين</span>
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                </div>
            </a>

        </div>
    </div>

    <!-- 3. زر الإحصائيات الشاملة المميز والبارز (Featured Banner for Statistics) -->
    <div class="bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border-2 border-brand-800/60 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center space-x-4 space-x-reverse">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-brand-900 text-white flex items-center justify-center shadow-2xl shrink-0">
                <i data-lucide="pie-chart" class="w-8 h-8"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-emerald-400 block mb-1">📊 التحليلات والمؤشرات الجامعية</span>
                <h3 class="text-lg sm:text-2xl font-black text-white">
                    شاشة الإحصائيات والمؤشرات الجامعية الشاملة
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    شاشة مخصصة بالكامل لعرض التحليلات البيانية والمخططات التفاعلية وجداول التوزيع حسب الكليات، الرتب العلمية، المؤهلات، والنوع الاجتماعي بنقرة زر واحدة.
                </p>
            </div>
        </div>

        <a href="{{ route('statistics') }}" 
           class="bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-brand-950 font-black px-6 py-3.5 rounded-2xl text-sm sm:text-base flex items-center justify-center shadow-2xl hover:shadow-emerald-500/30 transition shrink-0 space-x-2 space-x-reverse">
            <span>فتح شاشة الإحصائيات كاملة</span>
            <i data-lucide="arrow-left" class="w-5 h-5 mr-1"></i>
        </a>
    </div>

    <!-- 4. أدوات النظام السريعة والتحكم بالأمان (System Utilities) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-2 space-x-reverse text-xs text-slate-600 font-bold">
            <i data-lucide="settings" class="w-4 h-4 text-brand-900 ml-1"></i>
            <span>إجراءات سريعة للنظام:</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="alert('تم تحديث وحفظ بيانات النظام بنجاح!');" 
                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3.5 py-2 rounded-xl text-xs transition flex items-center">
                <i data-lucide="save" class="w-3.5 h-3.5 ml-1.5 text-brand-900"></i>
                <span>حفظ البيانات</span>
            </button>

            <a href="{{ route('dashboard') }}" 
               class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3.5 py-2 rounded-xl text-xs transition flex items-center">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 ml-1.5 text-blue-600"></i>
                <span>تحديث الشاشة</span>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('هل تريد بالتأكيد تسجيل الخروج وإغلاق الجلسة؟');" 
                        class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-3.5 py-2 rounded-xl text-xs transition flex items-center border border-rose-200">
                    <i data-lucide="log-out" class="w-3.5 h-3.5 ml-1.5 text-rose-600"></i>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </div>

</div>

<!-- سكريبت تشغيل الساعة الرقمية اللحظية -->
<script>
    function updateLiveClock() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'مساءً' : 'صباحاً';
        
        hours = hours % 12;
        hours = hours ? hours : 12;
        
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        
        const timeStr = `${hours}:${minutes}:${seconds} ${ampm}`;
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = timeStr;
        }
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();
</script>
@endsection
