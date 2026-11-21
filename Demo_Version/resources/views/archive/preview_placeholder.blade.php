<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة الوثيقة</title>
    <script src="{{ asset('vendor/tailwindcss/tailwind.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts/cairo/cairo.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>body { font-family: 'Cairo', Tahoma, sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 select-none">
    <div class="max-w-md w-full bg-white border border-slate-300 rounded-sm shadow-md p-6 text-center space-y-4">
        
        <div class="w-16 h-16 mx-auto bg-rose-50 border-2 border-rose-200 rounded-full flex items-center justify-center text-rose-600 text-2xl shadow-inner">
            <i class="fa-solid fa-file-pdf"></i>
        </div>

        <div>
            <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300 mb-2">
                <i class="fa-solid fa-clock"></i> بانتظار رفع النسخة الرقمية
            </span>
            <h2 class="text-sm font-black text-slate-800 leading-tight">
                {{ $version->document->document_title ?? $version->original_file_name }}
            </h2>
            <p class="text-xs font-mono font-bold text-slate-500 mt-1">
                اسم الملف: {{ $version->original_file_name }}
            </p>
        </div>

        <div class="bg-slate-50 border border-slate-200 p-3 rounded text-right text-[11.5px] space-y-1 text-slate-700 font-medium">
            <div class="flex justify-between">
                <span class="text-slate-500">رقم الوثيقة / القيد:</span>
                <span class="font-mono font-bold text-slate-900">{{ $version->document->document_number ?? 'غير مسجل' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">تاريخ التسجيل:</span>
                <span class="font-mono">{{ $version->created_at ? $version->created_at->format('Y-m-d') : '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">الحالة الأرشيفية:</span>
                <span class="text-emerald-700 font-bold">مقيد في السجل الورقي</span>
            </div>
        </div>

        <div class="pt-2">
            <button type="button" 
                    onclick="if (window.parent && window.parent.openModal) { window.parent.openModal('modal-new-doc'); }" 
                    class="w-full bg-[#0a6168] hover:bg-[#064247] text-white font-bold py-2 px-4 rounded text-xs transition flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <i class="fa-solid fa-upload text-amber-400"></i>
                <span>رفع وأرشفة النسخة الإلكترونية (PDF) الآن</span>
            </button>
        </div>

    </div>
</body>
</html>
