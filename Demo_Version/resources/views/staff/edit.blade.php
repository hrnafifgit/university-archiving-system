@extends('layouts.app')

@section('title', 'تعديل بيانات الكادر | ' . $staff->full_name)
@section('page_title', 'تعديل بيانات الكادر')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">

    <!-- رأس الصفحة مع زر العودة -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-bold text-slate-900">تعديل بيانات: {{ $staff->full_name }}</h2>
            <span class="text-xs text-brand-900 font-mono font-semibold">الرقم الوظيفي: {{ $staff->job_number }}</span>
        </div>
        <a href="{{ route('staff.show', $staff->id) }}" class="flex items-center text-xs text-slate-500 hover:text-slate-800 font-semibold transition">
            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
            <span>إلغاء والعودة</span>
        </a>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs flex items-center">
        <i data-lucide="alert-circle" class="w-4 h-4 ml-2 shrink-0"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <!-- نموذج التعديل الكامل (RESTful PUT) -->
    <form action="{{ route('staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. البيانات الشخصية والأساسية -->
        <div>
            <h3 class="text-xs font-bold text-slate-800 mb-3 flex items-center">
                <i data-lucide="user" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                <span>البيانات الأساسية</span>
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">الاسم الرباعي <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $staff->full_name) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white focus:border-brand-900 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">الرقم الوظيفي <span class="text-rose-500">*</span></label>
                    <input type="text" name="job_number" value="{{ old('job_number', $staff->job_number) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono focus:bg-white focus:border-brand-900 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">الرقم الوطني / البطاقة <span class="text-rose-500">*</span></label>
                    <input type="text" name="national_id" value="{{ old('national_id', $staff->national_id) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono focus:bg-white focus:border-brand-900 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">النوع الاجتماعي (الجنس) <span class="text-rose-500">*</span></label>
                    <select name="gender" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        <option value="ذكر" {{ old('gender', $staff->gender) == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                        <option value="أنثى" {{ old('gender', $staff->gender) == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">تاريخ الميلاد</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $staff->birth_date ? $staff->birth_date->format('Y-m-d') : '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono focus:bg-white focus:border-brand-900 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono focus:bg-white focus:border-brand-900 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono focus:bg-white focus:border-brand-900 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">تاريخ التعيين بالجامعة</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', $staff->hire_date ? $staff->hire_date->format('Y-m-d') : '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono focus:bg-white focus:border-brand-900 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">الحالة الوظيفية <span class="text-rose-500">*</span></label>
                    <select name="job_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        @foreach($lookups['jobStatuses'] as $st)
                            <option value="{{ $st }}" {{ old('job_status', $staff->job_status) == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. الكلية والقسم والتصنيف -->
        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-800 mb-3 flex items-center">
                <i data-lucide="building-2" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                <span>الجهة التنظيمية وتصنيف الكادر</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">الكلية / المركز <span class="text-rose-500">*</span></label>
                    <select name="entity_id" id="entitySelect" onchange="filterDepartments()" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ $staff->department->entity_id == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">القسم / الإدارة <span class="text-rose-500">*</span></label>
                    <select name="department_id" id="deptSelect" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" data-entity="{{ $dept->entity_id }}" {{ old('department_id', $staff->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">تصنيف الكادر <span class="text-rose-500">*</span></label>
                    <select name="staff_type" id="staffTypeSelect" onchange="toggleSubtype(this.value)" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:border-brand-900 focus:outline-none">
                        <option value="academic" {{ old('staff_type', $staff->staff_type) == 'academic' ? 'selected' : '' }}>كادر أكاديمي</option>
                        <option value="administrative" {{ old('staff_type', $staff->staff_type) == 'administrative' ? 'selected' : '' }}>كادر إداري</option>
                        <option value="contract" {{ old('staff_type', $staff->staff_type) == 'contract' ? 'selected' : '' }}>متعاقد بالساعات</option>
                    </select>
                </div>
            </div>

            <!-- تفاصيل الكادر الأكاديمي -->
            <div id="academicPanel" class="mt-3 p-4 bg-brand-50/50 rounded-xl border border-brand-100 {{ old('staff_type', $staff->staff_type) == 'academic' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">الرتبة الأكاديمية</label>
                        <select name="rank_id" class="w-full bg-white border border-slate-200 rounded-lg p-2 focus:border-brand-900">
                            @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}" {{ ($staff->academicDetails && $staff->academicDetails->rank_id == $rank->id) ? 'selected' : '' }}>{{ $rank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">التخصص العام</label>
                        <input type="text" name="general_specialization" value="{{ old('general_specialization', $staff->academicDetails->general_specialization ?? '') }}" class="w-full bg-white border border-slate-200 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">التخصص الدقيق</label>
                        <input type="text" name="exact_specialization" value="{{ old('exact_specialization', $staff->academicDetails->exact_specialization ?? '') }}" class="w-full bg-white border border-slate-200 rounded-lg p-2">
                    </div>
                </div>
            </div>

            <!-- تفاصيل الكادر الإداري -->
            <div id="adminPanel" class="mt-3 p-4 bg-slate-50 rounded-xl border border-slate-200 {{ old('staff_type', $staff->staff_type) == 'administrative' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">المسمى الوظيفي</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $staff->administrativeDetails->job_title ?? '') }}" class="w-full bg-white border border-slate-200 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">الدرجة الوظيفية</label>
                        <select name="job_grade_id" class="w-full bg-white border border-slate-200 rounded-lg p-2">
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ ($staff->administrativeDetails && $staff->administrativeDetails->job_grade_id == $grade->id) ? 'selected' : '' }}>{{ $grade->grade_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">طبيعة العمل</label>
                        <input type="text" name="work_nature" value="{{ old('work_nature', $staff->administrativeDetails->work_nature ?? 'مكتبي') }}" class="w-full bg-white border border-slate-200 rounded-lg p-2">
                    </div>
                </div>
            </div>

            <!-- تفاصيل المتعاقد -->
            <div id="contractPanel" class="mt-3 p-4 bg-slate-50 rounded-xl border border-slate-200 {{ old('staff_type', $staff->staff_type) == 'contract' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">نوع العقد</label>
                        <input type="text" name="contract_type" value="{{ old('contract_type', $staff->contractDetails->contract_type ?? 'ساعات فصلي') }}" class="w-full bg-white border border-slate-200 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">الساعات المعتمدة أسبوعياً</label>
                        <input type="number" name="approved_hours" value="{{ old('approved_hours', $staff->contractDetails->approved_hours ?? 8) }}" class="w-full bg-white border border-slate-200 rounded-lg p-2 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">أجر الساعة (ريال)</label>
                        <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $staff->contractDetails->hourly_rate ?? 5000) }}" class="w-full bg-white border border-slate-200 rounded-lg p-2 font-mono">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. رفع وثيقة PDF جديدة للأرشيف إن رغب المستخدم -->
        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-800 mb-2 flex items-center">
                <i data-lucide="folder-up" class="w-4 h-4 ml-1.5 text-brand-900"></i>
                <span>إضافة وثيقة PDF جديدة للأرشيف (اختياري)</span>
            </h3>
            <p class="text-[11px] text-slate-400 mb-3">يمكنك إرفاق ملف PDF إضافي ليتم إدراجه مباشرة في شجرة المستندات الرقمية لهذا الكادر.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">ملف الـ PDF الجديد</label>
                    <input type="file" name="document_file" accept=".pdf" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">عنوان الوثيقة</label>
                    <input type="text" name="document_title" placeholder="قرار ترقية أو شهادة..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">تصنيف الشجرة</label>
                    <select name="tree_category" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                        <option value="القرارات الإدارية">القرارات الإدارية</option>
                        <option value="المؤهلات العلمية">المؤهلات العلمية</option>
                        <option value="الوثائق الشخصية">الوثائق الشخصية</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- أزرار الحفظ والإلغاء -->
        <div class="pt-5 border-t border-slate-100 flex items-center justify-end space-x-2.5 space-x-reverse">
            <a href="{{ route('staff.show', $staff->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                إلغاء
            </a>
            <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white font-bold px-6 py-2.5 rounded-xl text-xs flex items-center shadow-sm transition">
                <i data-lucide="check" class="w-4 h-4 ml-1.5"></i>
                <span>حفظ التعديلات في قاعدة البيانات</span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function filterDepartments() {
        const entityId = document.getElementById('entitySelect').value;
        const deptSelect = document.getElementById('deptSelect');
        const options = deptSelect.querySelectorAll('option');

        let firstMatch = null;
        options.forEach(opt => {
            if (!entityId || opt.getAttribute('data-entity') === entityId) {
                opt.style.display = '';
                if (!firstMatch) firstMatch = opt;
            } else {
                opt.style.display = 'none';
            }
        });

        if (firstMatch) {
            deptSelect.value = firstMatch.value;
        }
    }

    function toggleSubtype(type) {
        document.getElementById('academicPanel').classList.add('hidden');
        document.getElementById('adminPanel').classList.add('hidden');
        document.getElementById('contractPanel').classList.add('hidden');

        if (type === 'academic') {
            document.getElementById('academicPanel').classList.remove('hidden');
        } else if (type === 'administrative') {
            document.getElementById('adminPanel').classList.remove('hidden');
        } else if (type === 'contract') {
            document.getElementById('contractPanel').classList.remove('hidden');
        }
    }
</script>
@endpush
