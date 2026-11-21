<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول الموحد | جامعة إب</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/official_web_logo.png') }}">

    <!-- خط Cairo المعتمد -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & Lucide Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d', // الأخضر الداكن المعتمد
                            950: '#052e16',
                        }
                    },
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800 antialiased">

    <!-- شريط علوي بسيط -->
    <header class="h-14 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
        <div class="flex items-center space-x-2 space-x-reverse">
            <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-8 w-auto object-contain">
            <span class="text-xs font-bold text-slate-700 mr-2 border-r border-slate-200 pr-2">نظام أرشفة وإدارة بيانات الكادر</span>
        </div>
        <a href="https://www.ibbuniv.edu.ye" target="_blank" class="text-xs text-slate-400 hover:text-brand-900 flex items-center transition">
            <span>ibbuniv.edu.ye</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1"></i>
        </a>
    </header>

    <!-- بطاقة تسجيل الدخول المركزية النقية والخالية من الحشو -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6">
            
            <!-- الشعار والعنوان الموجز -->
            <div class="text-center space-y-2">
                <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-16 w-auto mx-auto object-contain mb-2">
                <h1 class="text-lg font-bold text-slate-900">تسجيل الدخول الموحد</h1>
                <p class="text-xs text-slate-500">منظومة أرشفة وإدارة الكادر الجامعي</p>
            </div>

            <!-- تنبيهات الأخطاء -->
            @if(session('error'))
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="p-3 bg-brand-50 border border-brand-200 text-brand-900 text-xs rounded-xl flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-brand-900"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- نموذج تسجيل الدخول المباشر -->
            <form method="POST" action="{{ route('login.post') }}" id="loginForm" class="space-y-4">
                @csrf

                <!-- اسم المستخدم -->
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-700 mb-1">اسم المستخدم</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input type="text" id="username" name="username" value="{{ old('username', 'admin') }}" required autofocus
                               placeholder="admin" 
                               class="w-full pr-9 pl-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-brand-900 focus:ring-1 focus:ring-brand-900 transition">
                    </div>
                </div>

                <!-- كلمة المرور -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">كلمة المرور</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" id="password" name="password" value="admin123" required 
                               placeholder="••••••••" 
                               class="w-full pr-9 pl-9 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono focus:outline-none focus:border-brand-900 focus:ring-1 focus:ring-brand-900 transition">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- زر الدخول الرسمي بالأخضر الداكن الصريح -->
                <button type="submit" class="w-full bg-brand-900 hover:bg-brand-800 text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center space-x-2 space-x-reverse transition shadow-sm cursor-pointer">
                    <i data-lucide="log-in" class="w-4 h-4 ml-1.5"></i>
                    <span>دخول إلى النظام</span>
                </button>
            </form>

            <!-- خيار الدخول السريع للاختبار والمناقشة -->
            <div class="pt-4 border-t border-slate-100 text-center">
                <span class="text-[11px] font-bold text-slate-400 block mb-2">دخول تجريبي سريع:</span>
                <button type="button" onclick="fillAndSubmit('admin', 'admin123')" class="w-full p-2 rounded-xl border border-slate-200 hover:border-brand-900 hover:bg-brand-50 transition text-xs font-semibold text-slate-700 flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="shield-check" class="w-4 h-4 text-brand-900"></i>
                    <span>حساب مدير النظام (admin / admin123)</span>
                </button>
            </div>

        </div>
    </main>

    <!-- تذييل الصفحة -->
    <footer class="h-12 bg-white border-t border-slate-200 px-6 flex items-center justify-between text-xs text-slate-400">
        <span>جامعة إب © 2026</span>
        <span class="font-mono text-[11px]">archives.ibbuniv.edu.ye</span>
    </footer>

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

        function fillAndSubmit(user, pass) {
            document.getElementById('username').value = user;
            document.getElementById('password').value = pass;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
