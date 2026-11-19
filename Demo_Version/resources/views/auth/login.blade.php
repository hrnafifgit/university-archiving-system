<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول الموحد | جامعة إب</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/ibb_logo.png') }}">

    <!-- الخطوط المحلية وتنسيقات Tailwind و Lucide -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800 antialiased bg-slate-100">

    <!-- شريط علوي رسمي -->
    <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-3 space-x-reverse">
            <img src="{{ asset('images/ibb_logo.png') }}" alt="جامعة إب" class="h-11 w-auto object-contain">
            <div>
                <span class="text-xs font-bold text-slate-800 block">الجمهورية اليمنية — جامعة إب</span>
                <span class="text-[10px] text-slate-500 font-semibold">بوابة الأرشفة والتوثيق الإلكتروني الموحد</span>
            </div>
        </div>
        <div class="flex items-center space-x-2 space-x-reverse text-xs font-semibold text-slate-500">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>بوابة موظفي ومسؤولي الأرشيف</span>
        </div>
    </header>

    <!-- بطاقة تسجيل الدخول المركزية عبر كلمة المرور -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-xl p-8 space-y-6">
            
            <!-- الشعار والعنوان -->
            <div class="text-center space-y-2">
                <div class="w-20 h-20 mx-auto rounded-2xl bg-brand-50 border-2 border-brand-100 flex items-center justify-center shadow-inner p-2.5">
                    <img src="{{ asset('images/ibb_logo.png') }}" alt="جامعة إب" class="w-full h-full object-contain">
                </div>
                <h1 class="text-xl font-black text-slate-900 mt-2">
                    تسجيل الدخول للنظام
                </h1>
                <p class="text-xs text-slate-500 font-semibold">
                    أدخل كلمة المرور المعتمدة للدخول إلى لوحة التحكم والعمليات
                </p>
            </div>

            <!-- تنبيه الأخطاء -->
            @if($errors->any())
            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-xs flex items-center shadow-sm">
                <i data-lucide="alert-circle" class="w-4 h-4 ml-2 text-rose-600 shrink-0"></i>
                <span class="font-bold">{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- نموذج تسجيل الدخول عبر كلمة المرور -->
            <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-4">
                @csrf

                <!-- حقل كلمة المرور البارز (Focus) -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>كلمة المرور الرسمية:</span>
                        <span class="text-[10px] text-brand-900 font-bold bg-brand-50 px-2 py-0.5 rounded-md">افتراضياً: 123</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-5 h-5 text-brand-900"></i>
                        </div>
                        <input type="password" name="password" id="password" value="123" required autofocus
                            placeholder="أدخل كلمة المرور (123)"
                            class="w-full pr-11 pl-11 py-3 bg-slate-50 hover:bg-white focus:bg-white border-2 border-slate-200 focus:border-brand-900 rounded-2xl text-sm font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-brand-900/20 transition-all shadow-sm">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" id="eyeIcon" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- حساب الدخول (مخفي افتراضياً أو يمكن إظهاره) -->
                <div id="usernameSection" class="hidden pt-1">
                    <label for="login" class="block text-xs font-bold text-slate-600 mb-1">اسم المستخدم أو البريد (اختياري)</label>
                    <input type="text" name="login" id="login" value="admin@ibbuniv.edu.ye"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-900">
                </div>

                <!-- خيارات الحفظ وتذكرني -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center space-x-1.5 space-x-reverse cursor-pointer">
                        <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-brand-900 focus:ring-brand-900 border-slate-300">
                        <span class="text-slate-600 font-bold mr-1">تذكر تسجيل الدخول في هذا الجهاز</span>
                    </label>
                    <button type="button" onclick="document.getElementById('usernameSection').classList.toggle('hidden')" class="text-[11px] text-brand-900 hover:underline font-bold">
                        تحديد مستخدم
                    </button>
                </div>

                <!-- زر تسجيل الدخول الكبير -->
                <button type="submit" class="w-full bg-brand-900 hover:bg-brand-800 active:scale-[0.98] text-white font-black py-3 px-4 rounded-2xl text-sm flex items-center justify-center space-x-2 space-x-reverse transition shadow-lg shadow-brand-900/20">
                    <i data-lucide="log-in" class="w-5 h-5 ml-1.5"></i>
                    <span>تسجيل الدخول والمتابعة</span>
                </button>
            </form>

            <!-- خيارات الدخول السريعة بضغطة زر -->
            <div class="pt-4 border-t border-slate-100 text-center">
                <span class="text-[11px] font-bold text-slate-400 block mb-2">الدخول السريع بالحسابات الرسمية:</span>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="quickLogin('admin@ibbuniv.edu.ye', 'password')" class="p-2 text-center rounded-xl border border-slate-200 hover:border-brand-900 hover:bg-brand-50 transition text-[11px] font-bold text-slate-700">
                        <i data-lucide="shield" class="w-3.5 h-3.5 mx-auto mb-1 text-brand-900"></i>
                        <span>مدير النظام</span>
                    </button>
                    <button type="button" onclick="quickLogin('academic@ibbuniv.edu.ye', 'password')" class="p-2 text-center rounded-xl border border-slate-200 hover:border-brand-900 hover:bg-brand-50 transition text-[11px] font-bold text-slate-700">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 mx-auto mb-1 text-brand-900"></i>
                        <span>الشؤون الأكاديمية</span>
                    </button>
                    <button type="button" onclick="quickLogin('archivist@ibbuniv.edu.ye', 'password')" class="p-2 text-center rounded-xl border border-slate-200 hover:border-brand-900 hover:bg-brand-50 transition text-[11px] font-bold text-slate-700">
                        <i data-lucide="archive" class="w-3.5 h-3.5 mx-auto mb-1 text-brand-900"></i>
                        <span>مختص الأرشفة</span>
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- تذييل الصفحة -->
    <footer class="h-12 bg-white border-t border-slate-200 px-6 flex items-center justify-between text-xs text-slate-500 font-semibold">
        <span>جامعة إب — الإدارة العامة للشؤون الأكاديمية © {{ date('Y') }}</span>
        <span class="font-mono text-[11px]">نظام الأرشفة الموحد (UAS)</span>
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

        function quickLogin(email, password) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
