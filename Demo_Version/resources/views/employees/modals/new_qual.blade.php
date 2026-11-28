<!-- 3. نافذة إضافة مؤهل أكاديمي -->
<div id="modal-new-qual" class="fixed inset-0 bg-slate-900/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
            <span class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-graduation-cap text-[#14532d]"></i> إضافة مؤهل علمي
            </span>
            <button onclick="closeModal('modal-new-qual')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">✕</button>
        </div>
        <form method="POST" action="{{ route('employees.qualifications.store', $employee->employee_id) }}" class="p-4 space-y-3 text-xs">
            @csrf
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11px] font-bold text-slate-700">المستوى العلمي <span class="text-rose-600">*</span></label>
                    <button type="button" onclick="openQuickAdd('qual_level', 'qual_level_assignment_select', 'اسم المستوى التعليمي الجديد:', 'إضافة مستوى')" class="text-[#14532d] hover:underline px-1.5 py-0.2 bg-emerald-50 rounded text-[10px] font-bold flex items-center gap-0.5 transition cursor-pointer" title="إضافة مستوى جديد">
                        <i class="fa-solid fa-plus text-[9px]"></i> إضافة
                    </button>
                </div>
                <select name="qualification_level_id" id="qual_level_assignment_select" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                    @foreach($qualLevels as $ql)
                        <option value="{{ $ql->qualification_level_id }}">{{ $ql->level_name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">عنوان الدرجة والتخصص <span class="text-rose-600">*</span></label>
                <input type="text" name="degree_title" required placeholder="مثال: دكتوراه في علوم الحاسوب" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">الجامعة المانحة للمؤهل</label>
                <input type="text" name="university_name" placeholder="مثال: جامعة إب، جامعة صنعاء..." class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الدولة</label>
                    <select name="country_id" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                        <option value="">-- اختر الدولة --</option>
                        @foreach($countries ?? [] as $c)
                            <option value="{{ $c->country_id }}">{{ $c->country_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">التخصص</label>
                    <select name="specialization_id" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                        <option value="">-- اختر التخصص --</option>
                        @foreach($specializations ?? [] as $sp)
                            <option value="{{ $sp->specialization_id }}">{{ $sp->specialization_name_ar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">نمط الدراسة (ماجستير/دكتوراه)</label>
                    <select name="study_mode" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                        <option value="">-- غير محدد --</option>
                        <option value="داخلي">داخلي</option>
                        <option value="خارجي">خارجي</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">قرار الإيفاد (للموفدين)</label>
                    <select name="dispatch_decision_id" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                        <option value="">-- بدون قرار إيفاد --</option>
                        @foreach($dispatchDecisions ?? [] as $dd)
                            <option value="{{ $dd->decision_id }}">{{ $dd->decision_number }} — {{ \Illuminate\Support\Str::limit($dd->summary, 28) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ التخرج</label>
                    <input type="date" name="graduation_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">التقدير / المعدل</label>
                    <input type="text" name="grade_or_gpa" placeholder="امتياز" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                </div>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="is_highest" name="is_highest" value="1" checked class="rounded border-slate-300 text-[#14532d] focus:ring-[#14532d]">
                <label for="is_highest" class="text-[11px] font-bold text-slate-800 cursor-pointer">اعتماد كأعلى مؤهل علمي</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-new-qual')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-1.5 rounded-lg text-xs font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-1.5 rounded-lg text-xs shadow-xs cursor-pointer">إضافة المؤهل</button>
            </div>
        </form>
    </div>
</div>
