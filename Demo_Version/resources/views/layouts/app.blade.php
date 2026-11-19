<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'جامعة إب | نظام الأرشفة الإلكتروني')</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/official_web_logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/official_web_logo.png') }}">

    <!-- خط Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Cairo', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d', // الأخضر الداكن الأساسي
                            950: '#052e16',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8fafc; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col h-screen overflow-hidden antialiased">

    <!-- جسم التطبيق (Sidebar + Main Content) -->
    <div class="flex flex-1 overflow-hidden">
        
        <!-- القائمة الجانبية (Sidebar) - نمط أبيض ناصع بأخضر داكن وبدون حشو -->
        <aside class="w-64 bg-white border-l border-slate-200 flex flex-col justify-between flex-shrink-0 z-20">
            <div>
                <!-- الشعار الرسمي -->
                <div class="h-16 px-5 border-b border-slate-100 flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 space-x-reverse">
                        <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-10 w-auto object-contain">
                    </a>
                </div>

                <!-- روابط الملاحة المباشرة بالأيقونات التعبيرية -->
                <nav class="p-3 space-y-1 text-xs font-semibold">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 ml-2.5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>لوحة التحكم</span>
                    </a>

                    <a href="{{ route('staff.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('staff.index') ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                        <i data-lucide="users" class="w-4 h-4 ml-2.5 {{ request()->routeIs('staff.index') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>دليل الكادر</span>
                    </a>

                    <a href="{{ route('analytics.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('analytics.index') ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 ml-2.5 {{ request()->routeIs('analytics.index') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>المؤشرات والإحصائيات</span>
                    </a>

                    <a href="{{ route('staff.create') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('staff.create') ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                        <i data-lucide="user-plus" class="w-4 h-4 ml-2.5 {{ request()->routeIs('staff.create') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>إضافة كادر</span>
                    </a>

                    <a href="{{ route('staff.show', 1) }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('staff.show') ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                        <i data-lucide="folder-open" class="w-4 h-4 ml-2.5 {{ request()->routeIs('staff.show') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>الأرشيف الرقمي</span>
                    </a>

                    <a href="{{ route('reports.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('reports.index') ? 'bg-brand-900 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-900' }}">
                        <i data-lucide="file-text" class="w-4 h-4 ml-2.5 {{ request()->routeIs('reports.index') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>بيان الحالة</span>
                    </a>
                </nav>
            </div>

            <!-- بطاقة المستخدم وتسجيل الخروج -->
            <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg bg-brand-900 text-white flex items-center justify-center font-bold text-xs ml-2">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name ?? 'مسؤول الأرشيف' }}</p>
                            <span class="text-[10px] text-brand-900 font-semibold block">متصل</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="تسجيل الخروج">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- المحتوى الرئيسي للموقع -->
        <main class="flex-1 flex flex-col overflow-y-auto">
            
            <!-- الشريط العلوي النظيف (Header) -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-10 flex-shrink-0">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <h1 class="text-base font-bold text-slate-900">
                        @yield('page_title', 'نظام الأرشفة')
                    </h1>
                </div>

                <div class="flex items-center space-x-3 space-x-reverse">
                    <!-- رابط الموقع الرسمي للجامعة -->
                    <a href="https://www.ibbuniv.edu.ye" target="_blank" rel="noopener" class="hidden sm:flex items-center text-xs text-slate-500 hover:text-brand-900 font-semibold transition" title="موقع جامعة إب الرسمي">
                        <span>ibbuniv.edu.ye</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1"></i>
                    </a>

                    <!-- زر الإضافة السريع بأيقونة واضحة -->
                    <a href="{{ route('staff.create') }}" class="flex items-center bg-brand-900 hover:bg-brand-800 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4 ml-1"></i>
                        <span>إضافة كادر</span>
                    </a>
                </div>
            </header>

            <!-- جسم الصفحة الرئيسي -->
            <div class="p-6 sm:p-8 space-y-6 flex-1 max-w-7xl w-full mx-auto">
                @yield('content')
            </div>

            <!-- تذييل الصفحة البسيط -->
            <footer class="py-3 px-8 border-t border-slate-200 bg-white text-slate-400 text-xs flex items-center justify-between">
                <span>جامعة إب — نظام الأرشفة الإلكتروني الموحد © {{ date('Y') }}</span>
                <span class="font-mono text-[11px] text-brand-900 font-bold">archives.ibbuniv.edu.ye</span>
            </footer>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
