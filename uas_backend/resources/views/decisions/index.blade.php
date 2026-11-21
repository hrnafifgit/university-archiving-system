@extends('layouts.app')

@section('title', 'جامعة إب - القرارات والحركات الإدارية')

@section('content')
<div class="flex-1 flex flex-col min-h-0 gap-1 pb-0">

    <!-- 1. شريط العمليات والتبويبات الموحد المبسط النظيف -->
    <div class="bg-white border border-slate-200 p-4 px-5 flex items-center justify-between gap-4 shadow-xs shrink-0 rounded-2xl select-none">
        
        <!-- التبويبات الفرعية وحقول التصفية المدمجة في سطر واحد -->
        <div class="flex items-center gap-3 flex-1 min-w-0 text-sm">
            
            <!-- أزرار التبويبات المكتبيّة -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200 shrink-0">
                <a href="{{ route('decisions.index', ['tab' => 'decisions']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $tab === 'decisions' ? 'bg-[#14532d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}">
                    <span>القرارات</span>
                    <span class="text-xs px-2 py-0.5 rounded font-mono font-bold {{ $tab === 'decisions' ? 'bg-[#052e16] text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $decisions->total() }}
                    </span>
                </a>
                <a href="{{ route('decisions.index', ['tab' => 'absences']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $tab === 'absences' ? 'bg-[#14532d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}">
                    <span>الإجازات والتفرغ</span>
                    <span class="text-xs px-2 py-0.5 rounded font-mono font-bold {{ $tab === 'absences' ? 'bg-[#052e16] text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $absences->total() }}
                    </span>
                </a>
            </div>

            <!-- خط فاصل رأسي -->
            <div class="h-8 w-px bg-slate-200 shrink-0"></div>

            <!-- نموذج البحث والتصفية المباشر -->
            @if($tab === 'decisions')
                <form method="GET" action="{{ route('decisions.index') }}" class="flex items-center gap-2.5 flex-1 min-w-0">
                    <input type="hidden" name="tab" value="decisions">
                    
                    <!-- حقل البحث الموسع المريح -->
                    <div class="relative w-80 md:w-96 lg:w-[420px] shrink-0">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث برقم القرار، الموضوع، أو اسم الكادر..." 
                               class="w-full bg-slate-50/70 border border-slate-200 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#14532d] focus:bg-white pr-10 font-medium rounded-xl shadow-2xs transition">
                        <i class="fa-solid fa-search absolute right-3.5 top-3.5 text-slate-400 text-sm"></i>
                    </div>

                    <!-- نوع القرار الأساسي -->
                    <div class="w-56 md:w-64 shrink-0">
                        <select name="type_id" class="w-full bg-slate-50/70 border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-xl shadow-2xs transition">
                            <option value="">-- كافة أنواع القرارات --</option>
                            @foreach($decisionTypes as $dt)
                                <option value="{{ $dt->decision_type_id }}" {{ request('type_id') == $dt->decision_type_id ? 'selected' : '' }}>
                                    {{ $dt->type_name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- أزرار التصفية -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold py-2.5 px-4 rounded-xl text-sm transition flex items-center gap-2 cursor-pointer shadow-xs whitespace-nowrap">
                            <i class="fa-solid fa-filter text-xs"></i> بحث
                        </button>
                        @if(request()->anyFilled(['search', 'type_id']))
                            <a href="{{ route('decisions.index', ['tab' => 'decisions']) }}" class="bg-white hover:bg-slate-100 text-slate-600 font-bold py-2.5 px-3 rounded-xl text-sm transition border border-slate-200 text-center shrink-0 shadow-2xs" title="إلغاء التصفية">
                                <i class="fa-solid fa-rotate-left text-xs"></i>
                            </a>
                        @endif
                    </div>
                </form>
            @else
                <form method="GET" action="{{ route('decisions.index') }}" class="flex items-center gap-2.5 flex-1 min-w-0">
                    <input type="hidden" name="tab" value="absences">
                    
                    <!-- حقل البحث الموسع -->
                    <div class="relative w-80 md:w-96 lg:w-[420px] shrink-0">
                        <input type="text" name="search_abs" value="{{ request('search_abs') }}" placeholder="بحث باسم الكادر، الرقم الوظيفي، أو الجهة..." 
                               class="w-full bg-slate-50/70 border border-slate-200 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#14532d] focus:bg-white pr-10 font-medium rounded-xl shadow-2xs transition">
                        <i class="fa-solid fa-search absolute right-3.5 top-3.5 text-slate-400 text-sm"></i>
                    </div>

                    <!-- أزرار التصفية -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold py-2.5 px-4 rounded-xl text-sm transition flex items-center gap-2 cursor-pointer shadow-xs whitespace-nowrap">
                            <i class="fa-solid fa-filter text-xs"></i> بحث
                        </button>
                        @if(request()->filled('search_abs'))
                            <a href="{{ route('decisions.index', ['tab' => 'absences']) }}" class="bg-white hover:bg-slate-100 text-slate-600 font-bold py-2.5 px-3 rounded-xl text-sm transition border border-slate-200 text-center shrink-0 shadow-2xs" title="إلغاء التصفية">
                                <i class="fa-solid fa-rotate-left text-xs"></i>
                            </a>
                        @endif
                    </div>
                </form>
            @endif

        </div>

        <!-- زر الإضافة والتسجيل المكتبي -->
        <div class="flex items-center gap-2.5 shrink-0">
            @if($tab === 'decisions')
                <button onclick="document.getElementById('newDecisionModal').classList.remove('hidden')" 
                        class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-2.5 rounded-xl text-sm transition flex items-center gap-2 shadow-xs cursor-pointer shrink-0">
                    <i class="fa-solid fa-plus text-xs"></i> إصدار قرار
                </button>
            @else
                <button onclick="document.getElementById('newAbsenceModal').classList.remove('hidden')" 
                        class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-2.5 rounded-xl text-sm transition flex items-center gap-2 shadow-xs cursor-pointer shrink-0">
                    <i class="fa-solid fa-plus text-xs"></i> تقييد تفرغ / إجازة
                </button>
            @endif
        </div>

    </div>

    <!-- 2. جدول البيانات المكتبي عالي الكثافة الممتد لأسفل الشاشة (Data Grid) -->
    <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden relative">
        
        <div id="decisions-scroll-container" class="flex-1 overflow-y-auto min-h-0 relative no-scrollbar">
            @if($tab === 'decisions')
                <!-- جدول القرارات -->
                <table class="w-full text-right text-sm border-collapse">
                    <thead class="bg-slate-50 text-slate-800 font-bold sticky top-0 z-20 select-none text-sm border-b border-slate-200 shadow-2xs">
                        <tr>
                            <th class="py-3.5 px-4 w-14 text-center text-slate-500 font-bold">#</th>
                            <th class="py-3.5 px-4 w-36 text-center font-mono text-slate-700">رقم القرار</th>
                            <th class="py-3.5 px-4 w-40 text-slate-700">نوع القرار</th>
                            <th class="py-3.5 px-4 w-60 text-slate-900">الكادر المعني</th>
                            <th class="py-3.5 px-4 w-32 text-center text-slate-700">تاريخ الصدور</th>
                            <th class="py-3.5 px-4 w-32 text-center text-slate-700">تاريخ السريان</th>
                            <th class="py-3.5 px-4 w-28 text-center text-slate-700">الحالة</th>
                            <th class="py-3.5 px-4 text-slate-700">موضوع القرار وموجزه</th>
                            <th class="py-3.5 px-4 w-32 text-center text-slate-700">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800 text-sm">
                        @forelse($decisions as $index => $dec)
                            <tr class="hover:bg-slate-50 transition {{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/40' }}">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-sm">{{ $decisions->firstItem() + $index }}</td>
                                <td class="py-3.5 px-4 font-mono font-bold text-brand-900 text-center text-sm whitespace-nowrap">
                                    {{ $dec->decision_number }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-900 font-bold whitespace-nowrap">
                                    {{ $dec->type?->type_name_ar ?? 'عام' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($dec->employee)
                                        <a href="{{ route('employees.show', $dec->employee->employee_id) }}" class="font-bold text-brand-900 hover:underline flex items-center gap-2">
                                            <span>{{ $dec->employee->full_name_ar }}</span>
                                            <span class="text-xs text-slate-400 font-mono font-normal">({{ $dec->employee->employee_number }})</span>
                                        </a>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-slate-700 whitespace-nowrap">
                                    {{ $dec->issue_date ? $dec->issue_date->format('Y-m-d') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-slate-700 whitespace-nowrap">
                                    {{ $dec->effective_start_date ? $dec->effective_start_date->format('Y-m-d') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $dec->status?->status_name_ar === 'ساري' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                        {{ $dec->status?->status_name_ar ?? 'ساري' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-800 truncate max-w-md" title="{{ $dec->summary }}">
                                    {{ $dec->summary }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($dec->documents->count() > 0 && $dec->documents->first()->currentVersion)
                                            <a href="{{ route('archive.preview', $dec->documents->first()->currentVersion->document_version_id) }}" target="_blank" class="p-1.5 px-2 text-slate-600 hover:text-brand-900 hover:bg-slate-100 rounded-lg border border-slate-200 transition text-xs shadow-2xs font-bold" title="معاينة وثيقة القرار PDF">
                                                <i class="fa-solid fa-file-pdf text-[#14532d]"></i>
                                            </a>
                                        @endif
                                        <button type="button" onclick="editDecision({{ json_encode($dec) }})" class="p-1.5 px-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg border border-slate-200 transition text-xs cursor-pointer shadow-2xs font-bold" title="تعديل القرار">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form method="POST" action="{{ route('decisions.destroy', $dec->decision_id) }}" class="inline" onsubmit="return confirm('تأكيد حذف هذا القرار؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 px-2 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg border border-slate-200 transition text-xs cursor-pointer shadow-2xs" title="حذف القرار">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-16 text-center text-slate-400 font-medium bg-white">
                                    <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3 block"></i>
                                    <span class="text-base">لا توجد قرارات مطابقة لمعايير البحث.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <!-- جدول الإجازات والتفرغ -->
                <table class="w-full text-right text-xs border-collapse">
                    <thead class="bg-white text-slate-800 font-bold sticky top-0 z-20 select-none text-xs border-b border-slate-200 shadow-2xs">
                        <tr>
                            <th class="py-2.5 px-3 w-12 text-center text-slate-500 font-bold">#</th>
                            <th class="py-2.5 px-3 w-52 text-slate-900">الكادر الأكاديمي</th>
                            <th class="py-2.5 px-3 w-36 text-slate-700">نوع الحركة</th>
                            <th class="py-2.5 px-3 w-28 text-center text-slate-700">تاريخ البدء</th>
                            <th class="py-2.5 px-3 w-28 text-center text-slate-700">تاريخ الانتهاء</th>
                            <th class="py-2.5 px-3 w-48 text-slate-700">الجهة / الدولة المضيفة</th>
                            <th class="py-2.5 px-3 w-24 text-center text-slate-700">الحالة</th>
                            <th class="py-2.5 px-3 text-slate-700">ملاحظات وسند التفرغ</th>
                            <th class="py-2.5 px-3 w-28 text-center text-slate-700">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-800 text-xs">
                        @forelse($absences as $index => $abs)
                            <tr class="hover:bg-slate-50/80 transition {{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/30' }}">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono text-xs">{{ $absences->firstItem() + $index }}</td>
                                <td class="py-2.5 px-3">
                                    <a href="{{ route('employees.show', $abs->employee->employee_id) }}" class="font-bold text-primary hover:underline flex items-center gap-1.5">
                                        <span>{{ $abs->employee->full_name_ar }}</span>
                                        <span class="text-[11px] text-slate-400 font-mono font-normal">({{ $abs->employee->employee_number }})</span>
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 font-bold whitespace-nowrap text-slate-800">
                                    @switch($abs->absence_type)
                                        @case('sabbatical') تفرغ علمي @break
                                        @case('study_leave') إيفاد دراسي @break
                                        @case('unpaid_leave') إجازة بدون راتب @break
                                        @case('sick_leave') إجازة مرضية @break
                                        @case('delegation') إعارة / انتداب @break
                                        @default أخرى
                                    @endswitch
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono text-slate-600 whitespace-nowrap">
                                    {{ $abs->start_date ? $abs->start_date->format('Y-m-d') : '—' }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono text-slate-600 whitespace-nowrap">
                                    {{ $abs->end_date ? $abs->end_date->format('Y-m-d') : '—' }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-700 truncate max-w-xs">
                                    {{ $abs->destination_or_host ?? '—' }}
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if($abs->status === 'active')
                                        <span class="bg-amber-50 text-amber-800 text-[11px] font-medium px-2 py-0.5 rounded border border-amber-200">سارية</span>
                                    @elseif($abs->status === 'completed')
                                        <span class="bg-emerald-50 text-emerald-800 text-[11px] font-medium px-2 py-0.5 rounded border border-emerald-200">منتهية</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-600 text-[11px] font-medium px-2 py-0.5 rounded border border-slate-200">ملغاة</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-slate-500 text-xs truncate max-w-sm" title="{{ $abs->notes }}">
                                    {{ $abs->notes ?? '—' }}
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="editAbsence({{ json_encode($abs) }})" class="p-1 px-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded border border-slate-200 transition text-xs cursor-pointer" title="تعديل الإجازة">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form method="POST" action="{{ route('decisions.absences.destroy', $abs->absence_id) }}" class="inline" onsubmit="return confirm('تأكيد حذف حركة الإجازة / التفرغ؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 px-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded border border-slate-200 transition text-xs cursor-pointer" title="حذف">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 font-medium bg-white">
                                    <i class="fa-solid fa-plane-slash text-3xl text-slate-300 mb-2 block"></i>
                                    لا توجد حركات تفرغ أو إجازات مسجلة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <!-- مؤشر التمرير الهادئ (رمز ^ معكوس / v) للدلالة على توفر بيانات إضافية للتمرير -->
        <button type="button" id="scroll-down-indicator" onclick="scrollDecisionsDown()" 
                class="hidden absolute bottom-3 left-1/2 -translate-x-1/2 z-30 transition-all duration-300 bg-white/95 hover:bg-white text-slate-600 hover:text-primary border border-slate-200/90 rounded-full w-8 h-8 shadow-sm flex items-center justify-center cursor-pointer select-none"
                title="توجد بيانات إضافية (انقر أو مرر للتمرير لأسفل)">
            <i class="fa-solid fa-chevron-down text-primary text-[11px] animate-subtle-bounce"></i>
        </button>

    </div>

</div>

<!-- Modal إصدار قرار إداري رسمي -->
<div id="newDecisionModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-lg flex flex-col shadow-2xl rounded-2xl overflow-hidden">
        
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between shrink-0">
            <span class="font-bold text-xs text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-stamp text-[#14532d]"></i> إصدار قرار إداري رسمي
            </span>
            <button onclick="document.getElementById('newDecisionModal').classList.add('hidden')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">&times;</button>
        </div>

        <form method="POST" action="{{ route('decisions.store') }}" enctype="multipart/form-data" class="p-4 text-xs space-y-3">
            @csrf

            <div>
                <label class="block font-bold mb-1 text-slate-700">الكادر المعني بالقرار:</label>
                <select name="employee_id" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                    <option value="">-- حدد الكادر المعني --</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->employee_id }}">{{ $e->full_name_ar }} ({{ $e->employee_number }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">نوع القرار:</label>
                    <select name="decision_type_id" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        @foreach($decisionTypes as $dt)
                            <option value="{{ $dt->decision_type_id }}">{{ $dt->type_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">رقم القرار الرسمي:</label>
                    <input type="text" name="decision_number" required placeholder="مثال: ق/ر/2026/104" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono font-medium rounded-lg">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ الصدور:</label>
                    <input type="date" name="issue_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ السريان:</label>
                    <input type="date" name="effective_start_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">الحالة:</label>
                    <select name="decision_status_id" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        @foreach($decisionStatuses as $ds)
                            <option value="{{ $ds->decision_status_id }}">{{ $ds->status_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold mb-1 text-slate-700">موضوع وموجز القرار:</label>
                <textarea name="summary" rows="2" required placeholder="موجز ما نص عليه القرار الإداري..." class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white rounded-lg"></textarea>
            </div>

            <div>
                <label class="block font-bold mb-1 text-slate-700">وثيقة القرار الممسوحة ضوئياً (PDF حتى 20MB):</label>
                <input type="file" name="decision_file" accept="application/pdf" class="w-full bg-slate-50/70 border border-slate-200 p-1.5 text-xs file:py-1 file:px-2.5 file:bg-[#14532d] file:text-white file:border-0 file:rounded-md file:text-xs text-xs rounded-lg">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('newDecisionModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white px-4 py-1.5 rounded-lg font-bold flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-check text-xs"></i> حفظ القرار
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal تقييد حركة إجازة / تفرغ -->
<div id="newAbsenceModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-lg flex flex-col shadow-2xl rounded-2xl overflow-hidden">
        
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between shrink-0">
            <span class="font-bold text-xs text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-plane-departure text-[#14532d]"></i> تقييد تفرغ / إجازة رسمية
            </span>
            <button onclick="document.getElementById('newAbsenceModal').classList.add('hidden')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">&times;</button>
        </div>

        <form method="POST" action="{{ route('decisions.absences.store') }}" class="p-4 text-xs space-y-3">
            @csrf

            <div>
                <label class="block font-bold mb-1 text-slate-700">الكادر:</label>
                <select name="employee_id" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                    <option value="">-- حدد الكادر المعني --</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->employee_id }}">{{ $e->full_name_ar }} ({{ $e->employee_number }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">نوع الحركة:</label>
                    <select name="absence_type" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        <option value="sabbatical">تفرغ علمي</option>
                        <option value="study_leave">إيفاد دراسي</option>
                        <option value="unpaid_leave">إجازة بدون راتب</option>
                        <option value="sick_leave">إجازة مرضية</option>
                        <option value="delegation">إعارة / انتداب</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">الجهة / الدولة المضيفة:</label>
                    <input type="text" name="destination_or_host" placeholder="مثال: جامعة القاهرة - مصر" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white rounded-lg">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ البدء:</label>
                    <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ الانتهاء:</label>
                    <input type="date" name="end_date" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">الحالة:</label>
                    <select name="status" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        <option value="active">سارية</option>
                        <option value="completed">منتهية</option>
                        <option value="cancelled">ملغاة</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold mb-1 text-slate-700">ملاحظات وسند التفرغ:</label>
                <textarea name="notes" rows="2" placeholder="رقم القرار المستند عليه أو الملاحظات..." class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white rounded-lg"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('newAbsenceModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white px-4 py-1.5 rounded-lg font-bold flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-check text-xs"></i> حفظ السجل
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal تعديل قرار إداري رسمي -->
<div id="editDecisionModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-lg flex flex-col shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between shrink-0">
            <span class="font-bold text-xs text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[#14532d]"></i> تعديل بيانات القرار الإداري
            </span>
            <button onclick="document.getElementById('editDecisionModal').classList.add('hidden')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">&times;</button>
        </div>
        <form id="form-edit-decision" method="POST" action="" enctype="multipart/form-data" class="p-4 text-xs space-y-3">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">نوع القرار:</label>
                    <select name="decision_type_id" id="edit_dec_type_id" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        @foreach($decisionTypes as $dt)
                            <option value="{{ $dt->decision_type_id }}">{{ $dt->type_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">رقم القرار الرسمي:</label>
                    <input type="text" name="decision_number" id="edit_dec_number" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono font-medium rounded-lg">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ الصدور:</label>
                    <input type="date" name="issue_date" id="edit_dec_issue_date" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ السريان:</label>
                    <input type="date" name="effective_start_date" id="edit_dec_effective_start_date" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">الحالة:</label>
                    <select name="decision_status_id" id="edit_dec_status_id" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        @foreach($decisionStatuses as $ds)
                            <option value="{{ $ds->decision_status_id }}">{{ $ds->status_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-bold mb-1 text-slate-700">موضوع وموجز القرار:</label>
                <textarea name="summary" id="edit_dec_summary" rows="2" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white rounded-lg"></textarea>
            </div>
            <div>
                <label class="block font-bold mb-1 text-slate-700">تحديث وثيقة القرار PDF (اختياري):</label>
                <input type="file" name="decision_file" accept="application/pdf" class="w-full bg-slate-50/70 border border-slate-200 p-1.5 text-xs file:py-1 file:px-2.5 file:bg-[#14532d] file:text-white file:border-0 file:rounded-md file:text-xs text-xs rounded-lg">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('editDecisionModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white px-4 py-1.5 rounded-lg font-bold flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-check text-xs"></i> حفظ التعديل
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal تعديل حركة إجازة / تفرغ -->
<div id="editAbsenceModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 select-none">
    <div class="bg-white border border-slate-200 w-full max-w-lg flex flex-col shadow-2xl rounded-2xl overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between shrink-0">
            <span class="font-bold text-xs text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[#14532d]"></i> تعديل حركة التفرغ / الإجازة
            </span>
            <button onclick="document.getElementById('editAbsenceModal').classList.add('hidden')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">&times;</button>
        </div>
        <form id="form-edit-absence" method="POST" action="" class="p-4 text-xs space-y-3">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">نوع الحركة:</label>
                    <select name="absence_type" id="edit_abs_type" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        <option value="sabbatical">تفرغ علمي</option>
                        <option value="study_leave">إيفاد دراسي</option>
                        <option value="unpaid_leave">إجازة بدون راتب</option>
                        <option value="sick_leave">إجازة مرضية</option>
                        <option value="delegation">إعارة / انتداب</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">الجهة / الدولة المضيفة:</label>
                    <input type="text" name="destination_or_host" id="edit_abs_host" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white rounded-lg">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ البدء:</label>
                    <input type="date" name="start_date" id="edit_abs_start_date" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">تاريخ الانتهاء:</label>
                    <input type="date" name="end_date" id="edit_abs_end_date" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-mono rounded-lg">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">الحالة:</label>
                    <select name="status" id="edit_abs_status" required class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white font-medium rounded-lg">
                        <option value="active">سارية</option>
                        <option value="completed">منتهية</option>
                        <option value="cancelled">ملغاة</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-bold mb-1 text-slate-700">ملاحظات وسند التفرغ:</label>
                <textarea name="notes" id="edit_abs_notes" rows="2" class="w-full bg-slate-50/70 border border-slate-200 p-2 text-xs focus:outline-none focus:border-[#14532d] focus:bg-white rounded-lg"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('editAbsenceModal').classList.add('hidden')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white px-4 py-1.5 rounded-lg font-bold flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-check text-xs"></i> حفظ التعديل
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const scrollContainer = document.getElementById('decisions-scroll-container');
    const scrollIndicator = document.getElementById('scroll-down-indicator');

    // دالة فحص وتحديث حالة مؤشر التمرير السفلي (رمز ^ معكوس)
    function updateScrollIndicator() {
        if (!scrollIndicator || !scrollContainer) return;
        const canScroll = scrollContainer.scrollHeight > scrollContainer.clientHeight + 10;
        const isNearBottom = (scrollContainer.scrollTop + scrollContainer.clientHeight) >= (scrollContainer.scrollHeight - 25);

        if (canScroll && !isNearBottom) {
            scrollIndicator.classList.remove('hidden');
        } else {
            scrollIndicator.classList.add('hidden');
        }
    }

    // التمرير السلس للأسفل عند النقر على الرمز
    function scrollDecisionsDown() {
        if (!scrollContainer) return;
        scrollContainer.scrollBy({
            top: 220,
            behavior: 'smooth'
        });
    }

    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', updateScrollIndicator);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(updateScrollIndicator, 200);
    });

    window.addEventListener('resize', updateScrollIndicator);

    function editDecision(dec) {
        document.getElementById('form-edit-decision').action = '/decisions/' + dec.decision_id;
        document.getElementById('edit_dec_type_id').value = dec.decision_type_id || '';
        document.getElementById('edit_dec_number').value = dec.decision_number || '';
        document.getElementById('edit_dec_issue_date').value = dec.issue_date ? dec.issue_date.substring(0, 10) : '';
        document.getElementById('edit_dec_effective_start_date').value = dec.effective_start_date ? dec.effective_start_date.substring(0, 10) : '';
        document.getElementById('edit_dec_status_id').value = dec.decision_status_id || '';
        document.getElementById('edit_dec_summary').value = dec.summary || '';
        document.getElementById('editDecisionModal').classList.remove('hidden');
    }

    function editAbsence(abs) {
        document.getElementById('form-edit-absence').action = '/decisions/absences/' + abs.absence_id;
        document.getElementById('edit_abs_type').value = abs.absence_type || 'sabbatical';
        document.getElementById('edit_abs_host').value = abs.destination_or_host || '';
        document.getElementById('edit_abs_start_date').value = abs.start_date ? abs.start_date.substring(0, 10) : '';
        document.getElementById('edit_abs_end_date').value = abs.end_date ? abs.end_date.substring(0, 10) : '';
        document.getElementById('edit_abs_status').value = abs.status || 'active';
        document.getElementById('edit_abs_notes').value = abs.notes || '';
        document.getElementById('editAbsenceModal').classList.remove('hidden');
    }
</script>
@endsection
