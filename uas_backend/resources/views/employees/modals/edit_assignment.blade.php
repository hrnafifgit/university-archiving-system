<!-- 6. نافذة تعديل السجل الوظيفي وبيانات التسكين -->
<div id="modal-edit-assignment" class="fixed inset-0 bg-slate-900/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
            <span class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[#14532d]"></i> تعديل بيانات التسكين
            </span>
            <button onclick="closeModal('modal-edit-assignment')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">✕</button>
        </div>
        <form id="form-edit-assignment" method="POST" action="" class="p-4 space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الفئة الوظيفية <span class="text-rose-600">*</span></label>
                    <select name="employee_category_id" id="edit_assign_category_id" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->employee_category_id }}">{{ $cat->category_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الجهة / القسم <span class="text-rose-600">*</span></label>
                    <select name="organizational_unit_id" id="edit_assign_unit_id" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
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
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الموقف الوظيفي <span class="text-rose-600">*</span></label>
                    <select name="employment_status_id" id="edit_assign_status_id" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        @foreach($statuses as $st)
                            <option value="{{ $st->employment_status_id }}">{{ $st->status_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الرتبة العلمية</label>
                    <select name="academic_rank_id" id="edit_assign_rank_id" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        <option value="">-- بدون رتبة --</option>
                        @foreach($ranks as $rnk)
                            <option value="{{ $rnk->academic_rank_id }}">{{ $rnk->rank_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الدرجة المالية</label>
                    <select name="job_grade_id" id="edit_assign_grade_id" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                        <option value="">-- بدون درجة --</option>
                        @foreach($grades as $grd)
                            <option value="{{ $grd->job_grade_id }}">{{ $grd->grade_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ سريان التكليف <span class="text-rose-600">*</span></label>
                    <input type="date" name="start_date" id="edit_assign_start_date" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono font-bold focus:bg-white focus:border-[#14532d]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ الانتهاء</label>
                    <input type="date" name="end_date" id="edit_assign_end_date" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">أساس التكليف</label>
                    <input type="text" name="appointment_basis" id="edit_assign_basis" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                </div>

                <!-- حقول كشف الراتب والرقابة المالية -->
                <div class="col-span-2 border-t border-slate-100 pt-2.5 mt-1">
                    <span class="text-[11px] font-bold text-slate-800 block mb-2">
                        بيانات كشف الراتب والتعيين
                    </span>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">الحالة بكشف الراتب</label>
                            <input type="text" name="payroll_status" id="edit_assign_payroll_status" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">الوظيفة بكشف الراتب</label>
                            <input type="text" name="payroll_job_title" id="edit_assign_payroll_job_title" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">الدرجة بكشف الراتب</label>
                            <input type="text" name="payroll_job_grade" id="edit_assign_payroll_job_grade" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">المؤهل عند التعيين</label>
                            <input type="text" name="qualification_at_appointment" id="edit_assign_qual_appointment" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">تاريخ التعيين بالوظيفة أول مرة</label>
                            <input type="date" name="first_appointment_date" id="edit_assign_first_date" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 font-mono text-xs focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">تاريخ التعيين بالجامعة أكاديمي</label>
                            <input type="date" name="academic_appointment_date" id="edit_assign_academic_date" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2 py-1 font-mono text-xs focus:bg-white">
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-assignment')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-1.5 rounded-lg text-xs font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-1.5 rounded-lg text-xs shadow-xs cursor-pointer">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
