<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول الموحد | نظام الأرشفة الجامعي - جامعة إب</title>
    
    <!-- Favicon الرسمي لجامعة إب -->
    <link rel="icon" type="image/png" href="{{ asset('images/ibb_logo.png') }}">

    <!-- الخط الرسمي: Cairo العربي -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- مكتبة الأيقونات Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- محرك Tailwind CSS مع ألوان وهوية جامعة إب الرسمية -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                    },
                    colors: {
                        univ: {
                            50:  '#f0fdf9',
                            100: '#ccfbef',
                            200: '#99f6df',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#248874',
                            600: '#248874',
                            700: '#1e7261',
                            800: '#164c42',
                            900: '#0f3830',
                            950: '#08201b',
                        },
                        navy: {
                            800: '#0a254e',
                            900: '#031a3d',
                            950: '#021027',
                        },
                        gold: {
                            400: '#fbbf24',
                            500: '#f4bf00',
                            600: '#d97706',
                        },
                        surface: '#f3f8f9'
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-surface min-h-screen flex flex-col justify-between text-slate-800 antialiased selection:bg-univ-500 selection:text-white">

    <!-- شريط البوابة الفرعية الرسمي (Subdomain Top Bar) -->
    <header class="bg-navy-950 text-slate-300 text-xs border-b border-navy-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center justify-between">
            <div class="flex items-center space-x-3 space-x-reverse">
                <span class="inline-flex items-center text-emerald-400 font-bold">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 ml-1 text-emerald-400"></i>
                    البوابة الرسمية الموحدة لنظام الأرشفة
                </span>
                <span class="text-slate-600">|</span>
                <span class="font-mono text-slate-300 text-[11px] font-bold">archives.ibbuniv.edu.ye</span>
            </div>
            <div class="flex items-center space-x-4 space-x-reverse text-[11px]">
                <a href="https://www.ibbuniv.edu.ye" target="_blank" rel="noopener" class="hover:text-emerald-400 transition flex items-center">
                    <span>موقع الجامعة الرئيسي</span>
                    <i data-lucide="external-link" class="w-3 h-3 mr-1 text-slate-400"></i>
                </a>
                <span class="text-slate-600">|</span>
                <span class="text-slate-400">هاتف الدعم: 04408069</span>
            </div>
        </div>
    </header>

    <!-- منطقة التسجيل المركزية -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden">
            
            <!-- العمود الأيمن: البانر التعريفي والهوية الرسمية (5 أعمدة) -->
            <div class="lg:col-span-5 bg-gradient-to-br from-navy-950 via-navy-900 to-univ-950 text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
                <!-- تأثيرات إضاءة الخلفية -->
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-gold-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- الترويسة والشعارات -->
                <div class="relative z-10 space-y-6">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-14 h-14 rounded-2xl bg-white p-2 flex items-center justify-center shadow-lg border border-emerald-400/30 shrink-0">
                            <img src="{{ asset('images/ibb_logo.png') }}" alt="شعار جامعة إب" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-emerald-400">الجمهورية اليمنية</span>
                            <span class="block text-xs font-semibold text-slate-300">وزارة التعليم العالي والبحث العلمي</span>
                            <span class="block text-sm font-black text-white">جامعة إب — Ibb University</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10 space-y-3">
                        <div class="inline-flex items-center text-[10px] bg-emerald-500/20 text-emerald-300 font-bold px-2.5 py-1 rounded-full border border-emerald-500/30">
                            <i data-lucide="lock" class="w-3 h-3 ml-1 text-emerald-400"></i>
                            نظام تسجيل الدخول المركزي الموحد (SSO)
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white leading-snug">
                            منظومة الأرشفة والتوثيق الأكاديمي والإداري
                        </h2>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            بوابة آمنة ومخصصة لموظفي ومسؤولي شؤون الكادر الأكاديمي والإداري بجامعة إب، لإدارة ملفات الكادر والقرارات والوثائق المؤرشفة وفق المعايير المؤسسية (3NF).
                        </p>
                    </div>

                    <!-- مزايا المنظومة -->
                    <div class="space-y-2 pt-2 text-xs text-slate-300">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>أرشفة إلكترونية مشفرة بروابط UUID الرسمية.</span>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>تكامل كامل مع بيانات 18 كلية ومركز علمي بجامعة إب.</span>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>توليد بيانات الحالة الأكاديمية والوظيفية الرسمية.</span>
                        </div>
                    </div>
                </div>

                <!-- تذييل الجانب الأيمن -->
                <div class="relative z-10 pt-8 mt-6 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400">
                    <span>الإصدار المعتمد 2026</span>
                    <span class="font-mono text-emerald-400">v2.4 Enterprise</span>
                </div>
            </div>

            <!-- العمود الأيسر: نموذج تسجيل الدخول + الدخول السريع (7 أعمدة) -->
            <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-between">
                
                <div>
                    <!-- رأس النموذج -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-black text-navy-900">تسجيل الدخول إلى النظام</h3>
                            <p class="text-xs text-slate-500 mt-1">أدخل بيانات الحساب الجامعي المعتمد للوصول إلى لوحة التحكم</p>
                        </div>
                        <div class="hidden sm:block">
                            <img src="{{ asset('images/official_web_logo.png') }}" alt="شعار جامعة إب" class="h-10 w-auto object-contain">
                        </div>
                    </div>

                    <!-- التنبيهات ورسائل النجاح / الخطأ -->
                    @if(session('info'))
                    <div class="mb-5 p-3.5 bg-blue-50 border-r-4 border-blue-600 rounded-xl text-blue-800 text-xs flex items-center">
                        <i data-lucide="info" class="w-4 h-4 ml-2 text-blue-600 shrink-0"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-5 p-3.5 bg-rose-50 border-r-4 border-rose-600 rounded-xl text-rose-800 text-xs flex items-center">
                        <i data-lucide="alert-circle" class="w-4 h-4 ml-2 text-rose-600 shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    <!-- نموذج الإدخال الرئيسي -->
                    <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-4">
                        @csrf

                        <!-- اسم المستخدم أو البريد الإلكتروني -->
                        <div>
                            <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">
                                البريد الإلكتروني الجامعي أو اسم المستخدم
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <input type="text" name="login" id="login" value="{{ old('login', 'admin@ibbuniv.edu.ye') }}" required autofocus
                                    placeholder="admin@ibbuniv.edu.ye أو admin"
                                    class="w-full pr-10 pl-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500 focus:border-univ-500 transition">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">يمكنك استخدام: admin@ibbuniv.edu.ye أو اسم المستخدم المختصر admin</span>
                        </div>

                        <!-- كلمة المرور -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-bold text-slate-700">كلمة المرور</label>
                                <span class="text-[10px] text-slate-400">تقبل كلمة المرور الرسمية أو كلمة نظام الإكسل (123)</span>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="key-round" class="w-4 h-4"></i>
                                </div>
                                <input type="password" name="password" id="password" value="password" required
                                    placeholder="••••••••"
                                    class="w-full pr-10 pl-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-univ-500 focus:border-univ-500 transition">
                                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- تذكرني وخيارات الدخول -->
                        <div class="flex items-center justify-between text-xs pt-1">
                            <label class="flex items-center space-x-2 space-x-reverse cursor-pointer">
                                <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-univ-600 focus:ring-univ-500 border-slate-300">
                                <span class="text-slate-600 font-medium">تذكر تسجيل دخولي على هذا الجهاز</span>
                            </label>
                            <span class="text-univ-700 font-bold hover:underline cursor-pointer" onclick="alert('لإعادة تعيين كلمة المرور يرجى مراجعة إدارة تقنية المعلومات ومركز الحاسوب بجامعة إب')">
                                نسيت كلمة المرور؟
                            </span>
                        </div>

                        <!-- زر الدخول -->
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-gradient-to-l from-univ-600 to-univ-700 hover:from-univ-500 hover:to-univ-600 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-lg shadow-univ-600/30 flex items-center justify-center space-x-2 space-x-reverse transition transform active:scale-[0.99]">
                                <i data-lucide="log-in" class="w-4 h-4 ml-2"></i>
                                <span>دخول إلى نظام الأرشفة الجامعي</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- قسم الدخول السريع للاختبار والمناقشة (Quick Demo Sign-In) -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 flex items-center">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-gold-500 ml-1.5"></i>
                            تسجيل دخول سريع للتجربة والتقييم (One-Click Demo)
                        </span>
                        <span class="text-[10px] bg-slate-100 text-slate-600 font-semibold px-2 py-0.5 rounded">مخصص للجنة والتحكيم</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <!-- حساب 1: مسؤول الأرشيف المركزي -->
                        <button type="button" onclick="fillAndSubmit('admin@ibbuniv.edu.ye', 'password')" class="text-right p-2.5 rounded-xl border border-slate-200 hover:border-univ-500 hover:bg-univ-50/50 transition group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-bold text-slate-800 group-hover:text-univ-700">مسؤول الأرشيف</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            </div>
                            <span class="text-[10px] text-slate-500 block truncate">admin@ibbuniv.edu.ye</span>
                            <span class="text-[9px] text-slate-400 font-mono mt-0.5 block">كلمة المرور: password أو 123</span>
                        </button>

                        <!-- حساب 2: مدير الشؤون الأكاديمية -->
                        <button type="button" onclick="fillAndSubmit('academic@ibbuniv.edu.ye', 'password')" class="text-right p-2.5 rounded-xl border border-slate-200 hover:border-univ-500 hover:bg-univ-50/50 transition group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-bold text-slate-800 group-hover:text-univ-700">الشؤون الأكاديمية</span>
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            </div>
                            <span class="text-[10px] text-slate-500 block truncate">academic@ibbuniv.edu.ye</span>
                            <span class="text-[9px] text-slate-400 font-mono mt-0.5 block">كلمة المرور: password</span>
                        </button>

                        <!-- حساب 3: فني التوثيق والأرشفة -->
                        <button type="button" onclick="fillAndSubmit('archivist@ibbuniv.edu.ye', 'password')" class="text-right p-2.5 rounded-xl border border-slate-200 hover:border-univ-500 hover:bg-univ-50/50 transition group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-bold text-slate-800 group-hover:text-univ-700">فني الأرشفة</span>
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            </div>
                            <span class="text-[10px] text-slate-500 block truncate">archivist@ibbuniv.edu.ye</span>
                            <span class="text-[9px] text-slate-400 font-mono mt-0.5 block">كلمة المرور: password</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- تذييل الصفحة المؤسسي -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>جميع الحقوق محفوظة © {{ date('Y') }} جامعة إب — الإدارة العامة للشؤون الأكاديمية والأرشيف الإلكتروني</p>
            <div class="flex items-center space-x-3 space-x-reverse text-[11px]">
                <span class="flex items-center text-emerald-600 font-semibold">
                    <i data-lucide="shield" class="w-3.5 h-3.5 ml-1"></i>
                    اتصال آمن ومحمي ببروتوكول تشفير SSL
                </span>
            </div>
        </div>
    </footer>

    <!-- تهيئة الأيقونات والوظائف التفاعلية -->
    <script>
        lucide.createIcons();

        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        function fillAndSubmit(email, password) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
