<!-- 5. نافذة تعديل المؤهل الأكاديمي -->
<div id="modal-edit-qual" class="fixed inset-0 bg-slate-900/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
            <span class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-graduation-cap text-[#14532d]"></i> تعديل المؤهل العلمي
            </span>
            <button onclick="closeModal('modal-edit-qual')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">✕</button>
        </div>
        <form id="form-edit-qual" method="POST" action="" class="p-4 space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">المستوى العلمي <span class="text-rose-600">*</span></label>
                <select name="qualification_level_id" id="edit_qual_level_id" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                    @foreach($qualLevels as $ql)
                        <option value="{{ $ql->qualification_level_id }}">{{ $ql->level_name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">عنوان الدرجة والتخصص <span class="text-rose-600">*</span></label>
                <input type="text" name="degree_title" id="edit_qual_degree_title" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">الجامعة المانحة للمؤهل</label>
                <input type="text" name="university_name" id="edit_qual_university_name" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ التخرج</label>
                    <input type="date" name="graduation_date" id="edit_qual_graduation_date" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">التقدير / المعدل</label>
                    <input type="text" name="grade_or_gpa" id="edit_qual_grade_or_gpa" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                </div>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_is_highest" name="is_highest" value="1" class="rounded border-slate-300 text-[#14532d] focus:ring-[#14532d]">
                <label for="edit_is_highest" class="text-[11px] font-bold text-slate-800 cursor-pointer">اعتماد كأعلى مؤهل علمي</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-qual')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-1.5 rounded-lg text-xs font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-1.5 rounded-lg text-xs shadow-xs cursor-pointer">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
