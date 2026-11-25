<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة الوثيقة</title>
    <!-- Tailwind CSS (CDN مع بديل محلي مدمج) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap');
        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6 select-none">

    <div class="max-w-md w-full bg-white border border-slate-200/90 rounded-2xl shadow-md overflow-hidden text-center transition-all">
        
        <!-- الشريط العلوي والوسام -->
        <div class="p-6 pb-2">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 text-2xl shadow-2xs mb-3">
                <i class="fa-solid fa-file-circle-exclamation"></i>
            </div>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200 mb-2">
                <i class="fa-solid fa-clock-rotate-left text-amber-700"></i>
                <span>بانتظار رفع النسخة الرقمية</span>
            </span>

            <h2 class="text-base font-black text-slate-800 leading-snug px-2 mt-1">
                {{ $version->document->document_title ?? $version->original_file_name }}
            </h2>

            @if(!empty($version->original_file_name) && $version->original_file_name !== ($version->document->document_title ?? ''))
                <p class="text-xs font-mono font-medium text-slate-400 mt-1">
                    اسم الملف: {{ $version->original_file_name }}
                </p>
            @endif
        </div>

        <!-- جدول بيانات القيد الأرشيفي المعتمد -->
        <div class="px-5 my-2">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-right text-xs space-y-2 text-slate-700 font-medium">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">رقم الوثيقة / القيد:</span>
                    <span class="font-mono font-bold text-slate-900 bg-white border border-slate-200 px-2 py-0.5 rounded">
                        {{ $version->document->document_number ?? 'غير مسجل' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">تصنيف الوثيقة:</span>
                    <span class="font-bold text-slate-800">
                        {{ $version->document->documentType?->type_name_ar ?? 'وثيقة رسمية' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">تاريخ التسجيل:</span>
                    <span class="font-mono text-slate-700">
                        {{ $version->document->document_date ? $version->document->document_date->format('Y-m-d') : ($version->created_at ? $version->created_at->format('Y-m-d') : '-') }}
                    </span>
                </div>
                <div class="flex items-center justify-between pt-1 border-t border-slate-200/80">
                    <span class="text-slate-500">الحالة الأرشيفية:</span>
                    <span class="text-emerald-800 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded text-[11px] inline-flex items-center gap-1">
                        <i class="fa-solid fa-circle-check text-[10px] text-emerald-600"></i>
                        <span>مقيد في السجل الورقي</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- نموذج رفع النسخة الرقمية المباشر -->
        <form method="POST" action="{{ route('archive.versions.store', $version->document_id) }}" enctype="multipart/form-data" class="p-5 pt-2 space-y-3">
            @csrf

            <!-- منطقة إسقاط واختيار الملف التفاعلية -->
            <label class="block border-2 border-dashed border-emerald-300 hover:border-emerald-500 bg-emerald-50/40 hover:bg-emerald-50/80 transition rounded-xl p-4 cursor-pointer relative group">
                <i class="fa-solid fa-cloud-arrow-up text-2xl text-[#14532d] group-hover:scale-110 transition-transform"></i>
                <div class="text-xs font-bold text-slate-800 mt-1.5">
                    انقر لاختيار ملف الـ PDF أو اسحبه إلى هنا
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                    الصيغ المدعومة: PDF أو صور (JPG, PNG) • الحد الأقصى: 25MB
                </div>
                <input type="file" name="document_file" id="directFileInput" required accept="application/pdf,image/*" 
                       onchange="handleDirectFileSelect(this)" 
                       class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
            </label>

            <!-- اسم الملف المختار قبل الرفع -->
            <div id="selectedFileInfo" class="hidden p-2 bg-emerald-100/70 border border-emerald-300 text-emerald-950 rounded-xl text-xs font-bold flex items-center justify-between">
                <span class="truncate flex items-center gap-1.5">
                    <i class="fa-solid fa-file-pdf text-[#14532d]"></i>
                    <span id="selectedFileName">الملف المختار</span>
                </span>
                <span id="selectedFileSize" class="font-mono text-[10px] text-emerald-800 bg-white px-2 py-0.5 rounded">0 MB</span>
            </div>

            <!-- زر الرفع والأرشفة -->
            <button type="submit" id="btnSubmitDirect" class="w-full bg-[#14532d] hover:bg-[#166534] text-white font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <i class="fa-solid fa-arrow-up-from-bracket text-xs"></i>
                <span>رفع وأرشفة النسخة الإلكترونية (PDF) الآن</span>
            </button>

            <!-- زر ثانوي لفتح النافذة الشاملة في الإطار الرئيسي -->
            <button type="button" 
                    onclick="if (window.parent && window.parent.openModal) { window.parent.openModal('modal-new-doc'); }" 
                    class="text-[11px] font-bold text-slate-500 hover:text-slate-800 transition block mx-auto pt-1 cursor-pointer hover:underline">
                أو استخدام نافذة الأرشفة المتعددة الخيارات
            </button>
        </form>

    </div>

    <script>
        function handleDirectFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeMB = file.size / (1024 * 1024);

                if (sizeMB > 25) {
                    alert('تنبيه: حجم الملف المحدد (' + sizeMB.toFixed(2) + ' ميجابايت) يتجاوز الحد الأقصى المسموح به (25 ميجابايت).\nيرجى اختيار ملف أصغر حجماً.');
                    input.value = '';
                    document.getElementById('selectedFileInfo').classList.add('hidden');
                    return;
                }

                document.getElementById('selectedFileName').innerText = file.name;
                document.getElementById('selectedFileSize').innerText = sizeMB.toFixed(2) + ' MB';
                document.getElementById('selectedFileInfo').classList.remove('hidden');
                
                // تعديل نص الزر للتأكيد
                const btn = document.getElementById('btnSubmitDirect');
                btn.classList.add('ring-2', 'ring-emerald-400', 'animate-pulse');
            }
        }
    </script>

</body>
</html>