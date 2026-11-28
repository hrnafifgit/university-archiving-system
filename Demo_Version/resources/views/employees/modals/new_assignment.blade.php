<!-- 2. نافذة حركة وظيفية / تكليف رسمي جديد -->
<div id="modal-new-assignment" class="fixed inset-0 bg-slate-900/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
            <span class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-arrow-right-arrow-left text-[#14532d]"></i> حركة وظيفية جديدة
            </span>
            <button onclick="closeModal('modal-new-assignment')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">✕</button>
        </div>
        <form method="POST" action="{{ route('employees.assignments.store', $employee->employee_id) }}" class="p-4 space-y-3 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الفئة الوظيفية <span class="text-rose-600">*</span></label>
                    <select name="employee_category_id" id="new_assign_category_id" required onchange="toggleAssignGradeRank(this)" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        <option value="" disabled selected>-- اختر الفئة الوظيفية --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->employee_category_id }}" data-name="{{ $cat->category_name_ar }}">{{ $cat->category_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-700">الجهة / القسم <span class="text-rose-600">*</span></label>
                        <button type="button" onclick="openQuickAdd('department', 'assignment_unit_select', 'اسم القسم الجديد:', 'إضافة قسم')" class="text-[#14532d] hover:underline px-1.5 py-0.2 bg-emerald-50 rounded text-[10px] font-bold flex items-center gap-0.5 transition cursor-pointer" title="إضافة قسم غير موجود">
                            <i class="fa-solid fa-plus text-[9px]"></i> إضافة
                        </button>
                    </div>
                    <select name="organizational_unit_id" id="assignment_unit_select" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        <option value="" disabled selected>-- اختر الكلية والقسم التابع له --</option>
                        @foreach($colleges as $col)
                            <optgroup label="{{ $col->unit_name_ar }}">
                                @foreach($col->children as $dept)
                                    <option value="{{ $dept->organizational_unit_id }}">{{ $dept->unit_name_ar }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-700">الموقف الوظيفي <span class="text-rose-600">*</span></label>
                        <button type="button" onclick="openQuickAdd('status', 'assignment_status_select', 'اسم الموقف الجديد:', 'إضافة موقف')" class="text-[#14532d] hover:underline px-1.5 py-0.2 bg-emerald-50 rounded text-[10px] font-bold flex items-center gap-0.5 transition cursor-pointer" title="إضافة موقف غير موجود">
                            <i class="fa-solid fa-plus text-[9px]"></i> إضافة
                        </button>
                    </div>
                    <select name="employment_status_id" id="assignment_status_select" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        <option value="" disabled selected>-- اختر الموقف / الحالة الوظيفية --</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->employment_status_id }}">{{ $st->status_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="new_assign_rank_wrap">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-700">الرتبة العلمية (للأكاديمي)</label>
                        <button type="button" onclick="openQuickAdd('rank', 'assignment_rank_select', 'اسم الرتبة العلمية الجديدة:', 'إضافة رتبة')" class="text-[#14532d] hover:underline px-1.5 py-0.2 bg-emerald-50 rounded text-[10px] font-bold flex items-center gap-0.5 transition cursor-pointer" title="إضافة رتبة غير موجودة">
                            <i class="fa-solid fa-plus text-[9px]"></i> إضافة
                        </button>
                    </div>
                    <select name="academic_rank_id" id="assignment_rank_select" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        <option value="">-- بدون رتبة --</option>
                        @foreach($ranks as $rnk)
                            <option value="{{ $rnk->academic_rank_id }}">{{ $rnk->rank_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="new_assign_grade_wrap">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-700">الدرجة الوظيفية (للإداري 1–15)</label>
                        <button type="button" onclick="openQuickAdd('grade', 'assignment_grade_select', 'اسم الدرجة المالية الجديدة:', 'إضافة درجة')" class="text-[#14532d] hover:underline px-1.5 py-0.2 bg-emerald-50 rounded text-[10px] font-bold flex items-center gap-0.5 transition cursor-pointer" title="إضافة درجة غير موجودة">
                            <i class="fa-solid fa-plus text-[9px]"></i> إضافة
                        </button>
                    </div>
                    <select name="job_grade_id" id="assignment_grade_select" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                        <option value="">-- بدون درجة --</option>
                        @foreach($grades as $grd)
                            <option value="{{ $grd->job_grade_id }}">{{ $grd->grade_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ سريان التكليف <span class="text-rose-600">*</span></label>
                    <input type="date" name="start_date" required value="" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono font-bold focus:bg-white focus:border-[#14532d]">
                </div>

                <div>
                    @php
                        $nextAssDecNum = (\App\Models\EmployeeDecision::selectRaw("COALESCE(MAX(CAST(REGEXP_REPLACE(decision_number, '[^0-9]', '', 'g') AS INTEGER)), 202) as max_num")->value('max_num') ?? 202) + 1;
                    @endphp
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-bold text-slate-700">رقم القرار المنظم</label>
                        <span class="text-[9px] font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">ترقيم تلقائي</span>
                    </div>
                    <input type="text" name="decision_number" value="{{ $nextAssDecNum }}" placeholder="رقم القرار الإداري (تلقائي)" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">أساس وسبب التكليف <span class="text-rose-600">*</span></label>
                    <input type="text" name="appointment_basis" required value="" placeholder="مثال: ترقية علمية، نقل إداري، تكليف رسمي..." class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                </div>

                <!-- حقول كشف الراتب والرقابة المالية -->
                <div class="col-span-2 border-t border-slate-100 pt-2.5 mt-1">
                    <span class="text-[11px] font-bold text-slate-800 block mb-2">
                        بيانات كشف الراتب والتعيين
                    </span>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">الحالة بكشف الراتب</label>
                            <input type="text" name="payroll_status" value="" placeholder="مثال: على رأس العمل، مستمر..." class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">الوظيفة بكشف الراتب</label>
                            <input type="text" name="payroll_job_title" placeholder="هيئة تدريس، مدرس..." class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">الدرجة بكشف الراتب</label>
                            <input type="text" name="payroll_job_grade" placeholder="أستاذ، أستاذ مشارك..." class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">المؤهل عند التعيين</label>
                            <input type="text" name="qualification_at_appointment" placeholder="دكتوراه، ماجستير..." class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">تاريخ التعيين بالوظيفة أول مرة</label>
                            <input type="date" name="first_appointment_date" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 font-mono text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">تاريخ التعيين بالجامعة أكاديمي</label>
                            <input type="date" name="academic_appointment_date" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 font-mono text-xs focus:bg-white">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">الوضع الحالي / الملاحظات</label>
                            <div class="flex items-center gap-1.5">
                                <select name="notes" id="new_assign_notes_select" data-use-name-as-value="true" data-quick-type="payroll_status" data-quick-title="إضافة حالة وضع حالي" data-quick-label="اسم الوضع الحالي الجديد:" onchange="handleQuickAddChange(this)" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold focus:bg-white">
                                    <option value="متواجد">متواجد</option>
                                    <option value="منقطع">منقطع</option>
                                    <option value="غير محدد">غير محدد</option>
                                    <option value="موفد للدراسة">موفد للدراسة</option>
                                    <option value="إجازة">إجازة</option>
                                    @foreach($statuses as $st)
                                        @if(!in_array($st->status_name_ar, ['متواجد','منقطع','غير محدد','موفد للدراسة','إجازة']))
                                            <option value="{{ $st->status_name_ar }}">{{ $st->status_name_ar }}</option>
                                        @endif
                                    @endforeach
                                    <option value="__add_new__" class="font-bold text-[#14532d] bg-amber-50">+ إضافة حالة جديدة...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-new-assignment')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-1.5 rounded-lg text-xs font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-1.5 rounded-lg text-xs shadow-xs cursor-pointer">تثبيت الحركة</button>
            </div>
        </form>
    </div>
</div>
<script>
function toggleAssignGradeRank(sel) {
    const opt = sel.options[sel.selectedIndex];
    const name = (opt ? (opt.dataset.name || opt.text) : '').trim();
    const rankWrap = document.getElementById('new_assign_rank_wrap');
    const gradeWrap = document.getElementById('new_assign_grade_wrap');
    if (!rankWrap || !gradeWrap) return;
    if (name.includes('إداري')) {
        gradeWrap.classList.remove('hidden');
        rankWrap.classList.add('hidden');
    } else if (name.includes('أكاديمي')) {
        rankWrap.classList.remove('hidden');
        gradeWrap.classList.add('hidden');
    } else {
        rankWrap.classList.remove('hidden');
        gradeWrap.classList.remove('hidden');
    }
}
</script>
