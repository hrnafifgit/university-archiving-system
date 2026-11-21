<!-- 1. نافذة تعديل البيانات الأساسية -->
<div id="modal-edit-basic" class="fixed inset-0 bg-slate-900/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
            <span class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[#14532d]"></i> تعديل البيانات الشخصية
            </span>
            <button onclick="closeModal('modal-edit-basic')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">✕</button>
        </div>
        <form method="POST" action="{{ route('employees.update.basic', $employee->employee_id) }}" enctype="multipart/form-data" class="p-4 space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-2.5">
                <div class="col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الاسم الرباعي الكامل <span class="text-rose-600">*</span></label>
                    <input type="text" name="full_name_ar" required value="{{ $employee->full_name_ar }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">النوع الاجتماعي <span class="text-rose-600">*</span></label>
                    <select name="gender" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                        <option value="male" {{ $employee->gender == 'male' ? 'selected' : '' }}>ذكر</option>
                        <option value="female" {{ $employee->gender == 'female' ? 'selected' : '' }}>أنثى</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الرقم الوظيفي</label>
                    <input type="text" name="employee_number" value="{{ $employee->employee_number }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono font-bold focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">الرقم الوطني / الهوية</label>
                    <input type="text" name="national_id" value="{{ $employee->national_id }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono font-bold focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم الهاتف / الواتساب</label>
                    <input type="text" name="phone_number" value="{{ $employee->phone_number }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ $employee->email }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ الميلاد</label>
                    <input type="date" name="date_of_birth" value="{{ $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '' }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">محل الميلاد</label>
                    <input type="text" name="place_of_birth" value="{{ $employee->place_of_birth }}" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">العنوان السكني</label>
                    <input type="text" name="address" value="{{ $employee->address }}" placeholder="المحافظة - المديرية - الحي" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-[#14532d]">
                </div>
                <div class="col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تحديث الصورة الشخصية الرسمية (JPG/PNG)</label>
                    <input type="file" name="photo_file" accept="image/*" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs file:py-1 file:px-2.5 file:bg-[#14532d] file:text-white file:border-0 file:rounded-md file:text-xs text-xs">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-basic')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-1.5 rounded-lg text-xs font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-1.5 rounded-lg text-xs shadow-xs cursor-pointer">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
