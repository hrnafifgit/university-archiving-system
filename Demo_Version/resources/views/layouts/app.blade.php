<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'جامعة إب | نظام الأرشفة والتوثيق الجامعي الموحد')</title>
    <!-- أيقونة الهوية الرسمية لجامعة إب (Favicon) -->
    <link rel="icon" type="image/png" href="{{ asset('images/official_web_logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/official_web_logo.png') }}">

    <!-- خط القاهرة Cairo الرسمي المعتمد في موقع جامعة إب -->
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
                        // الألوان الرسمية المعتمدة المستخرجة بدقة من سيرفر جامعة إب:
                        univ: {
                            50: '#f0f9f7',
                            100: '#def1ed',
                            200: '#bee3db',
                            300: '#90cfc3',
                            400: '#5db6a5',
                            500: '#399d8b',
                            600: '#248874', // اللون الزمردي الرسمي لجامعة إب (#248874)
                            700: '#1e7261',
                            800: '#1a5b4f',
                            900: '#164c42',
                            950: '#0b2b25',
                        },
                        navy: {
                            700: '#0b2ba7',
                            800: '#07214a',
                            900: '#031a3d', // اللون الكحلي الرسمي لموقع جامعة إب (#031a3d)
                            950: '#021027',
                        },
                        gold: {
                            400: '#ffa000',
                            500: '#f4bf00', // اللون الذهبي الأكاديمي للجامعة (#f4bf00)
                            600: '#d99b00',
                        },
                        surface: '#f3f8f9', // الرمادي الفاتح المعتمد في خلفيات موقع الجامعة (#f3f8f9)
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f3f8f9; }
        .univ-gradient {
            background: linear-gradient(135deg, #031a3d 0%, #164c42 50%, #248874 100%);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-surface text-slate-800 flex flex-col h-screen overflow-hidden">

    <!-- 1. الشريط المؤسسي العلوي الموحد (Subdomain Portal Bar: archives.ibbuniv.edu.ye) -->
    <div class="bg-navy-950 text-slate-300 text-[11px] py-1.5 px-6 flex items-center justify-between border-b border-white/10 shrink-0 z-30">
        <div class="flex items-center space-x-3 space-x-reverse">
            <a href="https://www.ibbuniv.edu.ye" target="_blank" class="flex items-center text-univ-400 hover:text-univ-300 font-bold transition">
                <i data-lucide="external-link" class="w-3.5 h-3.5 ml-1"></i>
                <span>بوابة جامعة إب الرئيسية (ibbuniv.edu.ye)</span>
            </a>
            <span class="text-slate-600 hidden sm:inline">•</span>
            <span class="text-slate-400 hidden sm:inline">الإدارة العامة للشؤون الأكاديمية — إدارة شؤون الكادر</span>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse">
            <span class="flex items-center text-emerald-400 font-mono font-bold bg-white/5 px-2.5 py-0.5 rounded border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse ml-1.5"></span>
                https://archives.ibbuniv.edu.ye
            </span>
            <a href="mailto:contact@ibbuniv.edu.ye" class="hidden md:flex items-center text-slate-400 hover:text-white transition">
                <i data-lucide="mail" class="w-3.5 h-3.5 ml-1 text-univ-400"></i>
                <span>contact@ibbuniv.edu.ye</span>
            </a>
        </div>
    </div>

    <!-- 2. جسم التطبيق الأساسي (Sidebar + Main Content) -->
    <div class="flex flex-1 overflow-hidden">
        <!-- القائمة الجانبية بالهوية المؤسسية الكحلية الداكنة (#031a3d) -->
        <aside class="w-68 bg-navy-900 text-white flex flex-col justify-between shadow-2xl flex-shrink-0 z-20 border-l border-navy-800">
            <div>
                <!-- الشعار الرسمي الحي لجامعة إب المخصص للخلفيات الداكنة (سُحب من خادم الجامعة) -->
                <div class="p-4 border-b border-navy-800 bg-navy-950/70">
                    <div class="flex items-center justify-center p-2 rounded-xl bg-white/5 border border-white/10 shadow-inner">
                        <img src="{{ asset('images/official_web_logo_dark.png') }}" alt="جامعة إب - Ibb University" class="h-11 w-auto object-contain filter drop-shadow">
                    </div>
                    <div class="mt-2 text-center">
                        <span class="text-[10px] text-univ-400 font-bold block">منظومة الأرشفة والتوثيق الإلكتروني الموحد</span>
                        <span class="text-[9px] text-slate-400 block font-mono">Electronic Archiving & Staff DMS</span>
                    </div>
                </div>

                <!-- روابط الملاحة الرئيسية بألوان الهوية الرسمية -->
                <nav class="p-4 space-y-1.5 text-xs font-semibold">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-univ-600 text-white shadow-lg shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }} transition">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 ml-3 text-gold-500"></i>
                        <span>لوحة التحكم والقيادة</span>
                    </a>
                    <a href="{{ route('staff.index') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('staff.index') ? 'bg-univ-600 text-white shadow-lg shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }} transition">
                        <i data-lucide="users" class="w-4 h-4 ml-3 text-univ-400"></i>
                        <span>دليل واستعلام كادر الجامعة</span>
                    </a>
                    <a href="{{ route('staff.create') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('staff.create') ? 'bg-univ-600 text-white shadow-lg shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }} transition">
                        <i data-lucide="user-plus" class="w-4 h-4 ml-3 text-emerald-400"></i>
                        <span>معالج تسجيل كادر جديد</span>
                    </a>
                    <a href="{{ route('staff.show', 1) }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('staff.show') ? 'bg-univ-600 text-white shadow-lg shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }} transition">
                        <i data-lucide="folder-git-2" class="w-4 h-4 ml-3 text-gold-400"></i>
                        <span>الأرشيف الرقمي وعارض PDF</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('reports.index') ? 'bg-univ-600 text-white shadow-lg shadow-univ-600/30 font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }} transition">
                        <i data-lucide="file-text" class="w-4 h-4 ml-3 text-cyan-400"></i>
                        <span>مركز التقارير وبيان الحالة</span>
                    </a>
                </nav>
            </div>

            <!-- بطاقة المستخدم والاعتماد المؤسسي -->
            <div class="p-4 border-t border-navy-800 bg-navy-950/80">
                <div class="flex items-center mb-2">
                    <div class="relative">
                        <img class="w-10 h-10 rounded-full border-2 border-univ-500 p-0.5 object-cover" src="https://ui-avatars.com/api/?name=أحمد+الشميري&background=248874&color=fff" alt="User">
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-univ-500 rounded-full border-2 border-navy-950"></span>
                    </div>
                    <div class="mr-3 overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">أ. أحمد الشميري</p>
                        <p class="text-[10px] text-univ-300 font-medium truncate">مسؤول شؤون الكادر والأرشفة</p>
                    </div>
                </div>
                <div class="text-[9px] text-slate-400 text-center border-t border-navy-800/80 pt-2 font-mono flex items-center justify-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ml-1.5"></span>
                    <span>جامعة إب — منصة معتمدة 2026م</span>
                </div>
            </div>
        </aside>

        <!-- المحتوى الرئيسي للموقع -->
        <main class="flex-1 flex flex-col overflow-y-auto">
            <!-- الشريط العلوي المؤسسي (Header) -->
            <header class="h-20 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-10 flex-shrink-0 shadow-sm">
                <div class="flex items-center">
                    <!-- الشعار الفاتح الحي لجامعة إب (المسحوب من موقع الجامعة) -->
                    <div class="hidden lg:flex items-center ml-4 pl-4 border-l border-slate-200">
                        <img src="{{ asset('images/official_web_logo.png') }}" alt="شعار جامعة إب" class="h-10 w-auto object-contain">
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
                    <!-- شارة العام الجامعي الرسمية -->
                    <div class="hidden md:flex items-center text-xs text-slate-700 bg-univ-50 px-3.5 py-2 rounded-xl border border-univ-200 font-semibold">
                        <i data-lucide="calendar" class="w-4 h-4 ml-2 text-univ-700"></i>
                        <span>العام الجامعي: 1447هـ / 2026م</span>
                    </div>

                    <!-- زر الإضافة السريع -->
                    <a href="{{ route('staff.create') }}" class="flex items-center bg-univ-600 hover:bg-univ-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-univ-600/25 transition">
                        <i data-lucide="user-plus" class="w-4 h-4 ml-1.5"></i>
                        <span>إضافة كادر</span>
                    </a>
                </div>
            </header>

            <!-- جسم الصفحة الرئيسي -->
            <div class="p-6 sm:p-8 space-y-6 flex-1">
                @yield('content')
            </div>

            <!-- تذييل الصفحة الرسمي لجامعة إب (Institutional Footer) -->
            <footer class="mt-auto py-4 px-8 border-t border-slate-200 bg-white text-slate-500 text-xs flex flex-col sm:flex-row items-center justify-between shadow-inner">
                <div class="flex items-center space-x-2 space-x-reverse mb-2 sm:mb-0">
                    <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-6 w-auto object-contain ml-2 opacity-80">
                    <span class="font-bold text-slate-700">جامعة إب — بوابة الأرشفة الإلكترونية والتوثيق</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-[11px] text-slate-500">نيابة الشؤون الأكاديمية ونظم المعلومات</span>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse text-[11px] text-slate-400 font-mono">
                    <span class="text-univ-700 font-bold font-sans">هاتف: 04408069</span>
                    <span>•</span>
                    <span>UAS v2.4 (Enterprise Edition)</span>
                    <span>•</span>
                    <span>© 2026 جميع الحقوق محفوظة لجامعة إب</span>
                </div>
            </footer>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
