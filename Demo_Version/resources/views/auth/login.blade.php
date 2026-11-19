<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | نظام الأرشفة الجامعي - جامعة إب</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/official_web_logo.png') }}">

    <!-- خط Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Cairo', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            800: '#166534',
                            900: '#14532d', // الأخضر الداكن المعتمد
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
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800 antialiased">

    <!-- شريط علوي بسيط -->
    <header class="h-14 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
        <div class="flex items-center space-x-2 space-x-reverse">
            <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-8 w-auto object-contain">
            <span class="text-xs font-bold text-slate-700 mr-2 border-r border-slate-200 pr-2">نظام الأرشفة الإلكتروني</span>
        </div>
        <a href="https://www.ibbuniv.edu.ye" target="_blank" class="text-xs text-slate-400 hover:text-brand-900 flex items-center transition">
            <span>ibbuniv.edu.ye</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1"></i>
        </a>
    </header>

    <!-- بطاقة تسجيل الدخول المركزية - نقية وخالية من الحشو -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6">
            
            <!-- الشعار والعنوان الموجز -->
            <div class="text-center space-y-2">
                <div class="w-14 h-14 mx-auto rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-900">
                    <i data-lucide="shield-check" class="w-7 h-7"></i>
                </div>
                <h1 class="text-lg font-bold text-slate-900">تسجيل الدخول الموحد</h1>
                <p class="text-xs text-slate-500">بوابة الأرشفة والتوثيق الإلكتروني</p>
            </div>

            <!-- التنبيهات -->
            @if(session('info'))
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-xs flex items-center">
                <i data-lucide="info" class="w-4 h-4 ml-2 text-brand-900 shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs flex items-center">
                <i data-lucide="alert-circle" class="w-4 h-4 ml-2 text-rose-600 shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- نموذج الدخول -->
            <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-4">
                @csrf

                <!-- الحساب -->
                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-700 mb-1">اسم المستخدم أو البريد</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="login" id="login" value="{{ old('login', 'admin@ibbuniv.edu.ye') }}" required autofocus
                            placeholder="admin@ibbuniv.edu.ye"
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
                        <input type="password" name="password" id="password" value="password" required
                            placeholder="••••••••"
                            class="w-full pr-9 pl-9 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono focus:outline-none focus:border-brand-900 focus:ring-1 focus:ring-brand-900 transition">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- تذكرني -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-1.5 space-x-reverse cursor-pointer">
                        <input type="checkbox" name="remember" value="1" checked class="w-3.5 h-3.5 rounded text-brand-900 focus:ring-brand-900 border-slate-300">
                        <span class="text-slate-600">تذكر تسجيل دخولي</span>
                    </label>
                </div>

                <!-- زر تسجيل الدخول بالأخضر الداكن الصريح -->
                <button type="submit" class="w-full bg-brand-900 hover:bg-brand-800 text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center space-x-2 space-x-reverse transition shadow-sm">
                    <i data-lucide="log-in" class="w-4 h-4 ml-1.5"></i>
                    <span>دخول</span>
                </button>
            </form>

            <!-- خيارات الدخول السريع للاختبار والمناقشة -->
            <div class="pt-4 border-t border-slate-100">
                <span class="text-[11px] font-bold text-slate-400 block mb-2 text-center">دخول تجريبي سريع:</span>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillAndSubmit('admin@ibbuniv.edu.ye', 'password')" class="p-2 text-center rounded-lg border border-slate-200 hover:border-brand-900 hover:bg-brand-50 transition text-[11px] font-semibold text-slate-700">
                        <i data-lucide="shield" class="w-3.5 h-3.5 mx-auto mb-1 text-brand-900"></i>
                        <span>المدير</span>
                    </button>
                    <button type="button" onclick="fillAndSubmit('academic@ibbuniv.edu.ye', 'password')" class="p-2 text-center rounded-lg border border-slate-200 hover:border-brand-900 hover:bg-brand-50 transition text-[11px] font-semibold text-slate-700">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 mx-auto mb-1 text-brand-900"></i>
                        <span>الأكاديمي</span>
                    </button>
                    <button type="button" onclick="fillAndSubmit('archivist@ibbuniv.edu.ye', 'password')" class="p-2 text-center rounded-lg border border-slate-200 hover:border-brand-900 hover:bg-brand-50 transition text-[11px] font-semibold text-slate-700">
                        <i data-lucide="archive" class="w-3.5 h-3.5 mx-auto mb-1 text-brand-900"></i>
                        <span>الأرشيف</span>
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- تذييل الصفحة -->
    <footer class="h-12 bg-white border-t border-slate-200 px-6 flex items-center justify-between text-xs text-slate-400">
        <span>جامعة إب © {{ date('Y') }}</span>
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

        function fillAndSubmit(email, password) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
