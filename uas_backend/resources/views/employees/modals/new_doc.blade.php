<!-- 4. نافذة أرشفة وثيقة -->
<div id="modal-new-doc" class="fixed inset-0 bg-slate-900/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
            <span class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-file-pdf text-[#14532d]"></i> أرشفة وثيقة رسمية
            </span>
            <button onclick="closeModal('modal-new-doc')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-base cursor-pointer transition">✕</button>
        </div>
        <form method="POST" action="{{ route('employees.documents.store', $employee->employee_id) }}" enctype="multipart/form-data" class="p-4 space-y-3 text-xs">
            @csrf
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11px] font-bold text-slate-700">نوع الوثيقة <span class="text-rose-600">*</span></label>
                    <button type="button" onclick="openQuickAdd('doc_type', 'doc_type_modal_select', 'اسم تصنيف الوثيقة الجديد:', 'إضافة نوع وثيقة')" class="text-[#14532d] hover:underline px-1.5 py-0.2 bg-emerald-50 rounded text-[10px] font-bold flex items-center gap-0.5 transition cursor-pointer" title="إضافة نوع وثيقة جديد">
                        <i class="fa-solid fa-plus text-[9px]"></i> إضافة
                    </button>
                </div>
                <select name="document_type_id" id="doc_type_modal_select" required class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
                    @foreach($docTypes as $dtp)
                        <option value="{{ $dtp->document_type_id }}">{{ $dtp->type_name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">عنوان الوثيقة <span class="text-rose-600">*</span></label>
                <input type="text" name="document_title" required placeholder="مثال: قرار ترقية علمية" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold focus:bg-white focus:border-[#14532d]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم الوثيقة / القرار</label>
                <input type="text" name="document_number" placeholder="رقم القيد أو القرار" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono focus:bg-white focus:border-[#14532d]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">ملف الوثيقة (PDF أو صورة حتى 50MB) <span class="text-rose-600">*</span></label>
                <input type="file" name="document_file" required accept="application/pdf,image/*" class="w-full bg-slate-50/70 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs file:py-1 file:px-2.5 file:bg-[#14532d] file:text-white file:border-0 file:rounded-md file:text-xs text-xs">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-new-doc')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-1.5 rounded-lg text-xs font-bold border border-slate-200 cursor-pointer">إلغاء</button>
                <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-1.5 rounded-lg text-xs shadow-xs cursor-pointer">رفع وأرشفة</button>
            </div>
        </form>
    </div>
</div>
