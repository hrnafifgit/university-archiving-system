<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'جامعة إب | نظام الأرشفة الجامعي الإلكتروني')</title>
    <!-- أيقونة الهوية الرسمية لجامعة إب (Favicon) -->
    <link rel="icon" type="image/png" href="{{ asset('images/ibb_logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/ibb_logo.png') }}">

    <!-- خط القاهرة Cairo الرسمي -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
                        univ: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        },
                        gold: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .univ-gradient {
            background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #065f46 100%);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <!-- القائمة الجانبية بالهوية المؤسسية (Sidebar) -->
    <aside class="w-68 bg-slate-900 text-white flex flex-col justify-between shadow-2xl flex-shrink-0 z-20 border-l border-slate-800">
        <div>
            <!-- شعار وهوية جامعة إب الرسمية -->
            <div class="h-24 flex items-center px-5 border-b border-slate-800 bg-slate-950/80">
                <div class="w-14 h-14 rounded-2xl bg-white p-1.5 flex items-center justify-center border-2 border-emerald-500/40 shadow-lg shadow-emerald-900/40 shrink-0">
                    <img src="{{ asset('images/ibb_logo.png') }}" alt="شعار جامعة إب" class="w-full h-full object-contain filter drop-shadow">
                </div>
                <div class="mr-3 overflow-hidden">
                    <div class="flex items-center space-x-1.5 space-x-reverse">
                        <h1 class="font-black text-sm tracking-wide text-white">جامعة إب</h1>
                        <span class="text-[9px] bg-emerald-500/20 text-emerald-300 font-bold px-1.5 py-0.2 rounded border border-emerald-500/30">1996م</span>
                    </div>
                    <p class="text-[11px] text-emerald-400 font-semibold truncate mt-0.5">نظام الأرشفة الإلكتروني</p>
                    <p class="text-[9px] text-slate-400 font-normal truncate">Ibb University Archives</p>
                </div>
            </div>

            <!-- روابط الملاحة الرئيسية -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-univ-600 text-white shadow-md shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 ml-3"></i>
                    <span>لوحة التحكم والقيادة</span>
                </a>
                <a href="{{ route('staff.index') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('staff.index') ? 'bg-univ-600 text-white shadow-md shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i data-lucide="users" class="w-4 h-4 ml-3"></i>
                    <span>دليل كادر الجامعة (استعلام)</span>
                </a>
                <a href="{{ route('staff.create') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('staff.create') ? 'bg-univ-600 text-white shadow-md shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i data-lucide="user-plus" class="w-4 h-4 ml-3"></i>
                    <span>إضافة وتسجيل كادر جديد</span>
                </a>
                <a href="{{ route('staff.show', 1) }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('staff.show') ? 'bg-univ-600 text-white shadow-md shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i data-lucide="folder-git-2" class="w-4 h-4 ml-3"></i>
                    <span>الأرشيف الرقمي وعارض PDF</span>
                </a>
                <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('reports.index') ? 'bg-univ-600 text-white shadow-md shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i data-lucide="file-text" class="w-4 h-4 ml-3"></i>
                    <span>مركز التقارير وبيان الحالة</span>
                </a>
            </nav>
        </div>

        <!-- معلومات المستخدم والجهة الرسمية -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            <div class="flex items-center mb-2">
                <div class="relative">
                    <img class="w-10 h-10 rounded-full border-2 border-emerald-500 p-0.5 object-cover" src="https://ui-avatars.com/api/?name=أحمد+الشميري&background=047857&color=fff" alt="User">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 rounded-full border-2 border-slate-900"></span>
                </div>
                <div class="mr-3 overflow-hidden">
                    <p class="text-xs font-bold text-white truncate">أ. أحمد الشميري</p>
                    <p class="text-[10px] text-emerald-400 font-medium truncate">مسؤول شؤون الكادر والأرشفة</p>
                </div>
            </div>
            <div class="text-[9px] text-slate-500 text-center border-t border-slate-800/80 pt-2 font-mono">
                جامعة إب - الإدارة العامة للشؤون الأكاديمية
            </div>
        </div>
    </aside>

    <!-- المحتوى الرئيسي للموقع -->
    <main class="flex-1 flex flex-col overflow-y-auto">
        <!-- الشريط العلوي المؤسسي (Top Header) -->
        <header class="h-20 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-10 flex-shrink-0 shadow-sm">
            <div class="flex items-center">
                <!-- شارة الهوية المؤسسية الرسمية -->
                <div class="hidden lg:flex items-center ml-4 pl-4 border-l border-slate-200">
                    <img src="{{ asset('images/ibb_logo.png') }}" alt="جامعة إب" class="w-9 h-9 object-contain ml-2">
                    <div>
                        <span class="block text-[11px] font-bold text-slate-800 leading-tight">جامعة إب</span>
                        <span class="block text-[9px] text-slate-400">الجمهورية اليمنية 🇾🇪</span>
                    </div>
                </div>

                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-800 flex items-center">
                        @yield('page_title', 'نظام الأرشفة الجامعي')
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        @yield('page_subtitle', 'جامعة إب - منصة التحول الرقمي وإدارة وثائق الكادر')
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-3 space-x-reverse">
                <!-- مؤشر التقويم الأكاديمي -->
                <div class="hidden md:flex items-center text-xs text-slate-600 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200 font-medium">
                    <i data-lucide="calendar" class="w-4 h-4 ml-2 text-univ-700"></i>
                    <span>العام الجامعي: 1447هـ / 2026م</span>
                </div>

                <!-- زر الإضافة السريع -->
                <a href="{{ route('staff.create') }}" class="flex items-center bg-univ-600 hover:bg-univ-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-univ-600/20 transition">
                    <i data-lucide="user-plus" class="w-4 h-4 ml-1.5"></i>
                    <span>إضافة كادر</span>
                </a>
            </div>
        </header>

        <!-- جسم الصفحة -->
        <div class="p-6 sm:p-8 space-y-6 flex-1">
            @yield('content')
        </div>

        <!-- تذييل الصفحة الرسمي (Official Footer) -->
        <footer class="mt-auto py-4 px-8 border-t border-slate-200 bg-white text-slate-500 text-xs flex flex-col sm:flex-row items-center justify-between">
            <div class="flex items-center space-x-2 space-x-reverse mb-2 sm:mb-0">
                <img src="{{ asset('images/ibb_logo.png') }}" alt="جامعة إب" class="w-5 h-5 object-contain ml-1 opacity-70">
                <span class="font-bold text-slate-700">نظام الأرشفة الجامعي - جامعة إب</span>
                <span class="text-slate-300">|</span>
                <span>الإدارة العامة للشؤون الأكاديمية ونظم المعلومات</span>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse text-[11px] text-slate-400 font-mono">
                <span>UAS Enterprise Edition v2.4</span>
                <span>•</span>
                <span>© 2026 جميع الحقوق محفوظة لجامعة إب</span>
            </div>
        </footer>
    </main>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
