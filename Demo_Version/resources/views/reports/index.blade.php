@extends('layouts.app')

@section('title', 'مركز التقارير وبيان الحالة | نظام الأرشفة الجامعي')
@section('page_title', 'مركز التقارير وبيان الحالة الرسمية')
@section('page_subtitle', 'تصدير وطباعة بيان الحالة الأكاديمية والوظيفية الرسمية لجامعة إب')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- شريط الإجراءات والطباعة -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
        <div class="flex items-center space-x-2 space-x-reverse text-sm">
            <span class="text-slate-500">التقرير المعروض:</span>
            <span class="font-bold text-slate-800">بيان حالة أكاديمية ووظيفية موحدة (رسمي)</span>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse">
            <button onclick="window.print()" class="flex items-center bg-univ-600 hover:bg-univ-700 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-md shadow-univ-600/20 transition">
                <i data-lucide="printer" class="w-4 h-4 ml-1.5"></i>
                <span>طباعة التقرير الرسمي</span>
            </button>
            <button onclick="alert('جاري تصدير التقرير كملف PDF رسمي...')" class="flex items-center bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold transition">
                <i data-lucide="download" class="w-4 h-4 ml-1.5"></i>
                <span>تصدير PDF</span>
            </button>
        </div>
    </div>

    <!-- نموذج التقرير الرسمي القابل للطباعة (Printable Sheet) -->
    <div class="relative bg-white shadow-xl rounded-2xl p-12 text-slate-800 border border-slate-200 print:shadow-none print:border-none print:p-0 overflow-hidden">
        
        <!-- علامة مائية لشعار جامعة إب في منتصف التقرير -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.035] pointer-events-none select-none">
            <img src="{{ asset('images/ibb_logo.png') }}" alt="علامة مائية جامعة إب" class="w-96 h-96 object-contain">
        </div>

        <!-- الترويسة الرسمية -->
        <div class="relative z-10 border-b-2 border-navy-900 pb-6 mb-8">
            <div class="flex items-center justify-between">
                <div class="text-right text-xs leading-relaxed font-bold text-slate-700">
                    <p class="text-slate-500">الجمهورية اليمنية</p>
                    <p class="text-slate-500">وزارة التعليم العالي والبحث العلمي</p>
                    <p class="text-univ-700 font-black text-sm">جامعة إب — Ibb University</p>
                    <p class="text-[11px] text-slate-600">الإدارة العامة للشؤون الأكاديمية والأرشيف</p>
                    <span class="inline-flex items-center text-[10px] text-univ-700 font-mono bg-univ-50 px-2 py-0.5 rounded border border-univ-200 mt-1">
                        archives.ibbuniv.edu.ye
                    </span>
                </div>
                <div class="text-center">
                    <div class="h-20 px-3 py-1 rounded-2xl bg-white border border-slate-200 flex items-center justify-center mx-auto mb-1 shadow-sm">
                        <img src="{{ asset('images/official_web_logo.png') }}" alt="شعار جامعة إب الرسمي" class="h-16 w-auto object-contain">
                    </div>
                    <span class="text-[9px] text-slate-500 font-mono font-bold tracking-wider">IBB-UNIV-OFFICIAL-STATEMENT</span>
                </div>
                <div class="text-left text-xs leading-relaxed font-bold text-slate-700 font-mono">
                    <p>الرقم: ب.ح / {{ $staff->job_number }}/2026</p>
                    <p>التاريخ: {{ date('Y/m/d') }}م</p>
                    <p>المرفقات: شهادات وقرارات مؤرشفة</p>
                    <div class="mt-2 inline-block bg-slate-50 p-1.5 rounded-lg border border-slate-200 text-center">
                        <span class="block text-[8px] text-slate-400 font-sans">رمز التحقق الإلكتروني</span>
                        <span class="text-[10px] text-navy-800 font-mono font-black">IBB-{{ substr(md5($staff->id . $staff->job_number), 0, 8) }}</span>
                    </div>
                </div>
            </div>
            <h2 class="text-center text-lg font-black text-navy-900 mt-6 underline decoration-univ-600 decoration-2 underline-offset-8">
                بيان حالة أكاديمية ووظيفية موحدة
            </h2>
        </div>

        <!-- 1. البيانات الشخصية والوظيفية -->
        <div class="mb-8">
            <h3 class="text-sm font-bold text-univ-800 bg-slate-100 px-3 py-1.5 rounded-lg mb-4 inline-block">
                أولاً: البيانات الشخصية والأساسية
            </h3>
            <div class="grid grid-cols-2 gap-y-3 text-xs">
                <div><span class="text-slate-500 font-semibold ml-2">الاسم الرباعي:</span> <span class="font-bold text-slate-900">{{ $staff->full_name }}</span></div>
                <div><span class="text-slate-500 font-semibold ml-2">الرقم الوظيفي:</span> <span class="font-mono font-bold text-slate-900">{{ $staff->job_number }}</span></div>
                <div><span class="text-slate-500 font-semibold ml-2">الرقم الوطني:</span> <span class="font-mono font-bold text-slate-900">{{ $staff->national_id }}</span></div>
                <div><span class="text-slate-500 font-semibold ml-2">الجهة التنظيمية:</span> <span class="font-bold text-slate-900">{{ $staff->department->entity->name }}</span></div>
                <div><span class="text-slate-500 font-semibold ml-2">القسم العلمي:</span> <span class="font-bold text-slate-900">{{ $staff->department->name }}</span></div>
                <div><span class="text-slate-500 font-semibold ml-2">الحالة الوظيفية:</span> <span class="font-bold text-emerald-700">{{ $staff->job_status }}</span></div>
                <div><span class="text-slate-500 font-semibold ml-2">الرتبة العلمية:</span> <span class="font-bold text-slate-900">{{ $staff->academicDetails->rank->name ?? 'عضو هيئة تدريس' }}</span></div>
                <div><span class="text-slate-500 font-semibold ml-2">تاريخ التعيين:</span> <span class="font-mono font-bold text-slate-900">{{ $staff->hire_date ? $staff->hire_date->format('Y/m/d') : '2012/09/01' }}</span></div>
            </div>
        </div>

        <!-- 2. المؤهلات العلمية المعتمدة -->
        <div class="mb-8">
            <h3 class="text-sm font-bold text-univ-800 bg-slate-100 px-3 py-1.5 rounded-lg mb-4 inline-block">
                ثانياً: المؤهلات والشهادات العلمية المعتمدة
            </h3>
            <table class="w-full text-right text-xs border border-slate-200">
                <thead class="bg-slate-50 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-2 border-l border-slate-200">الدرجة</th>
                        <th class="p-2 border-l border-slate-200">التخصص</th>
                        <th class="p-2 border-l border-slate-200">الجامعة المانحة والدولة</th>
                        <th class="p-2 border-l border-slate-200">سنة التخرج</th>
                        <th class="p-2">عنوان الأطروحة / الرسالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($staff->qualifications as $qual)
                    <tr>
                        <td class="p-2 border-l border-slate-200 font-bold">{{ $qual->degree_type }}</td>
                        <td class="p-2 border-l border-slate-200">{{ $qual->specialization }}</td>
                        <td class="p-2 border-l border-slate-200">{{ $qual->granting_institution }} ({{ $qual->country }})</td>
                        <td class="p-2 border-l border-slate-200 font-mono">{{ $qual->graduation_year }}</td>
                        <td class="p-2 text-slate-600">{{ $qual->thesis_title ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 3. سجل القرارات والترقيات -->
        <div class="mb-8">
            <h3 class="text-sm font-bold text-univ-800 bg-slate-100 px-3 py-1.5 rounded-lg mb-4 inline-block">
                ثالثاً: سجل القرارات الإدارية والترقيات العلمية
            </h3>
            <table class="w-full text-right text-xs border border-slate-200">
                <thead class="bg-slate-50 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-2 border-l border-slate-200">رقم القرار</th>
                        <th class="p-2 border-l border-slate-200">نوع القرار</th>
                        <th class="p-2 border-l border-slate-200">تاريخ الصدور</th>
                        <th class="p-2 border-l border-slate-200">حالة القرار</th>
                        <th class="p-2">الحيثيات والبيان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($staff->decisions as $dec)
                    <tr>
                        <td class="p-2 border-l border-slate-200 font-mono font-bold">{{ $dec->decision_number }}</td>
                        <td class="p-2 border-l border-slate-200 font-bold">{{ $dec->decision_type }}</td>
                        <td class="p-2 border-l border-slate-200 font-mono">{{ $dec->issued_date }}</td>
                        <td class="p-2 border-l border-slate-200 text-emerald-700 font-bold">{{ $dec->status }}</td>
                        <td class="p-2 text-slate-600">{{ $dec->notes }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- التوقيعات والاعتماد الرسمي -->
        <div class="mt-12 pt-6 border-t-2 border-navy-900 grid grid-cols-3 text-center text-xs font-bold text-slate-800">
            <div>
                <p>المختص بالأرشيف الإلكتروني</p>
                <p class="mt-8">...............................</p>
            </div>
            <div>
                <p>مدير عام الشؤون الأكاديمية</p>
                <p class="mt-8">...............................</p>
            </div>
            <div>
                <p class="text-navy-900 font-black">أ.د / رئيس جامعة إب</p>
                <div class="w-16 h-16 border-2 border-emerald-600 text-univ-700 rounded-full flex items-center justify-center text-[9px] mx-auto mt-2 rotate-[-5deg] font-bold">
                    ختم الجامعة<br>الرسمي المعتمد
                </div>
            </div>
        </div>

        <!-- تذييل التحقق الرقمي الرسمي -->
        <div class="mt-8 pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-[10px] text-slate-500">
            <div class="flex items-center space-x-2 space-x-reverse mb-2 sm:mb-0">
                <i data-lucide="shield-check" class="w-4 h-4 text-univ-600"></i>
                <span>وثيقة رسمية صادرة إلكترونياً من بوابة الأرشيف المركزي الموحد: <strong class="text-navy-900 font-mono">https://archives.ibbuniv.edu.ye</strong></span>
            </div>
            <div class="font-mono text-slate-400">
                UUID: {{ $staff->uuid ?? 'IBB-ARC-' . substr(sha1($staff->id . $staff->job_number), 0, 16) }}
            </div>
        </div>

    </div>
</div>
@endsection
