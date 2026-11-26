        <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden">
            
            <!-- نموذج الفلترة والبحث المكتبي (Print Hidden) -->
            <form method="GET" action="{{ route('reports.index') }}" class="p-3 border-b border-slate-200 bg-slate-50/70 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 text-xs shrink-0 print:hidden">
                <input type="hidden" name="type" value="roster_19">

                <!-- 1. الكلية -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">الكلية / المركز:</label>
                    <select name="faculty_id" id="roster_faculty_id" onchange="filterDepartmentsByFaculty(this.value)" class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg font-bold">
                        <option value="">-- كافة الكليات والمراكز --</option>
                        @foreach($faculties as $f)
                            <option value="{{ $f->organizational_unit_id }}" {{ request('faculty_id') == $f->organizational_unit_id ? 'selected' : '' }}>{{ $f->unit_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. القسم -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">القسم العلمي:</label>
                    <select name="department_id" id="roster_department_id" class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg font-bold">
                        <option value="">-- كافة الأقسام --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->organizational_unit_id }}" 
                                    data-faculty-id="{{ $d->parent_unit_id }}" 
                                    {{ request('department_id') == $d->organizational_unit_id ? 'selected' : '' }}>
                                {{ $d->unit_name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. الرتبة الأكاديمية -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">الدرجة الحالية (الرتبة):</label>
                    <select name="rank_id" class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg">
                        <option value="">-- كافة الرتب --</option>
                        @foreach($ranks as $r)
                            <option value="{{ $r->academic_rank_id }}" {{ request('rank_id') == $r->academic_rank_id ? 'selected' : '' }}>{{ $r->rank_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. الموقف الوظيفي -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">الموقف الوظيفي:</label>
                    <select name="appointment_basis" class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg">
                        <option value="">-- كافة المواقف --</option>
                        @foreach($distinctAppointmentBases as $b)
                            <option value="{{ $b }}" {{ request('appointment_basis') == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 5. الحالة بكشف الراتب -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">الحالة بكشف الراتب:</label>
                    <select name="payroll_status" class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg">
                        <option value="">-- الكل --</option>
                        @foreach($distinctPayrollStatuses as $ps)
                            <option value="{{ $ps }}" {{ request('payroll_status') == $ps ? 'selected' : '' }}>{{ $ps }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 6. الوضع الحالي الأكاديمي -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">الوضع الحالي (أكاديمي):</label>
                    <select name="status_id" class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg">
                        <option value="">-- كافة الحالات --</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->employment_status_id }}" {{ request('status_id') == $st->employment_status_id ? 'selected' : '' }}>{{ $st->status_name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 7. الجنس -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">الجنس:</label>
                    <select name="gender" class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg">
                        <option value="">-- الكل --</option>
                        <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>ذكور</option>
                        <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>إناث</option>
                    </select>
                </div>

                <!-- 8. بحث عام -->
                <div class="col-span-1 lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">بحث بالاسم أو الرقم الوظيفي أو الوطني:</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="اكتب للبحث..." class="w-full border border-slate-200 p-1.5 bg-white focus:outline-none focus:border-[#14532d] text-xs rounded-lg">
                </div>

                <!-- أزرار التصفية والتصفير والتصدير -->
                <div class="flex items-end gap-1.5 flex-wrap">
                    <button type="submit" class="bg-[#14532d] hover:bg-[#166534] text-white px-4 py-1.5 font-bold text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-filter text-emerald-200 text-[10px]"></i> تصفية
                    </button>
                    <a href="{{ route('reports.index', ['type' => 'roster_19']) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 font-bold text-xs rounded-lg transition border border-slate-200">
                        تصفير
                    </a>
                    <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'roster_19'])) }}" class="bg-emerald-700 hover:bg-emerald-800 text-white px-3.5 py-1.5 font-bold text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer shadow-xs border border-emerald-800" title="تصدير الكشف الحالي إلى ملف إكسل منسق">
                        <i class="fa-solid fa-file-excel text-emerald-100 text-xs"></i> تصدير إكسل
                    </a>
                </div>
            </form>

            <!-- محتوى الكشف المطبوع بـ 19 عموداً (Landscape A4) -->
            <div class="flex-1 overflow-auto p-2 bg-slate-50 print:bg-white print:p-0">
                <div class="bg-white p-3 border border-slate-300 shadow-sm print:border-none print:shadow-none print:p-0">
                    
                    <!-- ترويسة الكشف الرسمية + بطاقة الفلاتر العلوية (مطابقة لـ Sheet2 Row 1-4) -->
                    <div class="border-b-2 border-primary pb-2 mb-2">
                        <div class="flex justify-between items-center mb-1.5">
                            <div class="text-right">
                                <h4 class="text-[10px] font-bold text-slate-600">جامعة إب | الشؤون الأكاديمية</h4>
                                <h2 class="text-sm font-black text-primary">كشف بيانات الكادر التدريسي في الجامعة</h2>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-university-logo class="w-10 h-10" />
                            </div>
                            <div class="text-left font-mono text-[10px] text-slate-600">
                                <div>التاريخ: <strong>{{ date('Y-m-d') }}م</strong></div>
                                <div>إجمالي السجلات: <strong class="text-primary text-xs">{{ $rosterEmployees->count() }}</strong></div>
                            </div>
                        </div>

                        <!-- بطاقة محددات التصفية المطبقة (مطابقة لترويسة Sheet2) -->
                        <div class="bg-slate-100 border border-slate-300 p-1.5 rounded text-[10px] flex flex-wrap gap-x-4 gap-y-1">
                            <div>الجهة: <strong class="text-primary">{{ $appliedFilters['الجهة / الكلية'] ?? 'كافة الكليات' }}</strong></div>
                            <div>القسم: <strong class="text-primary">{{ $appliedFilters['القسم'] ?? 'كافة الأقسام' }}</strong></div>
                            <div>الرتبة: <strong class="text-primary">{{ $appliedFilters['الدرجة الحالية'] ?? 'الكل' }}</strong></div>
                            <div>الموقف الوظيفي: <strong class="text-primary">{{ $appliedFilters['الموقف الوظيفي'] ?? 'الكل' }}</strong></div>
                            <div>الحالة بكشف الراتب: <strong class="text-primary">{{ $appliedFilters['الحالة بكشف الراتب'] ?? 'الكل' }}</strong></div>
                            <div>الوضع الحالي: <strong class="text-primary">{{ $appliedFilters['الوضع الحالي'] ?? 'الكل' }}</strong></div>
                            <div>الجنس: <strong class="text-primary">{{ $appliedFilters['الجنس'] ?? 'الكل' }}</strong></div>
                            <div class="font-bold text-emerald-800">إجمالي العدد المفلتر: {{ $rosterEmployees->count() }}</div>
                        </div>
                    </div>

                    <!-- جدول الـ 19 عموداً الرسمي (Landscape) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-[10px] border border-slate-400 border-collapse whitespace-nowrap">
                            <thead class="bg-primary text-white font-bold sticky top-0 select-none">
                                <tr>
                                    <th class="p-1 border border-primary-dark text-center w-6">م</th>
                                    <th class="p-1 border border-primary-dark">الاسم الرباعي</th>
                                    <th class="p-1 border border-primary-dark text-center">الجنس</th>
                                    <th class="p-1 border border-primary-dark text-center">تاريخ الميلاد</th>
                                    <th class="p-1 border border-primary-dark text-center">الموقف الوظيفي</th>
                                    <th class="p-1 border border-primary-dark text-center">الرقم الوطني</th>
                                    <th class="p-1 border border-primary-dark text-center">الرقم الوظيفي</th>
                                    <th class="p-1 border border-primary-dark text-center">الحالة بكشف الراتب</th>
                                    <th class="p-1 border border-primary-dark">الوظيفة بكشف الراتب</th>
                                    <th class="p-1 border border-primary-dark text-center">الدرجة بكشف الراتب</th>
                                    <th class="p-1 border border-primary-dark text-center">تعيين أول مرة</th>
                                    <th class="p-1 border border-primary-dark">الجهة المعين بها</th>
                                    <th class="p-1 border border-primary-dark text-center">تعيين أكاديمي</th>
                                    <th class="p-1 border border-primary-dark">المؤهل عند التعيين</th>
                                    <th class="p-1 border border-primary-dark text-center">الدرجة المعين فيها</th>
                                    <th class="p-1 border border-primary-dark">الجهة (الكلية)</th>
                                    <th class="p-1 border border-primary-dark">الإدارة / القسم</th>
                                    <th class="p-1 border border-primary-dark text-center">الدرجة الحالية</th>
                                    <th class="p-1 border border-primary-dark text-center">الوضع الحالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($rosterEmployees as $idx => $emp)
                                    @php
                                        $empRec = $emp->active_employment ?? $emp->currentEmployment ?? $emp->latestEmployment ?? $emp->employmentRecords->sortByDesc('start_date')->first();
                                        $ou = $empRec?->organizationalUnit;
                                        $faculty = ($ou && $ou->parent && $ou->parent->unit_type !== 'university') ? $ou->parent : $ou;
                                        $dept = ($ou && $ou->parent_unit_id && $ou->parent_unit_id !== $ou->organizational_unit_id) ? $ou : null;
                                    @endphp
                                    <tr class="hover:bg-slate-50 {{ $idx % 2 == 1 ? 'bg-slate-50/60' : '' }}">
                                        <td class="p-1 border border-slate-300 text-center font-mono font-bold">{{ $idx + 1 }}</td>
                                        <td class="p-1 border border-slate-300 font-bold text-primary">
                                            <a href="{{ route('reports.index', ['type' => 'individual', 'employee_id' => $emp->employee_id]) }}" class="hover:underline">
                                                {{ $emp->full_name_ar }}
                                            </a>
                                        </td>
                                        <td class="p-1 border border-slate-300 text-center">{{ ($emp->gender === 'F') ? 'أنثى' : 'ذكر' }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-mono">{{ $emp->date_of_birth ? $emp->date_of_birth->format('Y') : '-' }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-semibold">{{ $empRec?->appointment_basis ?? 'مثبت' }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-mono">{{ $emp->national_id ?? '-' }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-mono font-bold">{{ $emp->employee_number ?? '-' }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-bold text-primary">{{ $empRec?->payroll_status ?? 'غير محدد' }}</td>
                                        <td class="p-1 border border-slate-300">{{ $empRec?->payroll_job_title ?? 'غير محدد' }}</td>
                                        <td class="p-1 border border-slate-300 text-center">{{ $empRec?->payroll_job_grade ?? 'غير محدد' }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-mono">{{ $empRec?->first_appointment_date ? $empRec->first_appointment_date->format('Y-m-d') : '-' }}</td>
                                        <td class="p-1 border border-slate-300 text-slate-600">{{ $empRec?->initial_appointing_entity ?? 'جامعة إب' }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-mono">{{ $empRec?->academic_appointment_date ? $empRec->academic_appointment_date->format('Y-m-d') : '-' }}</td>
                                        <td class="p-1 border border-slate-300 font-medium">{{ $empRec?->qualification_at_appointment ?? ($emp->qualifications->first()?->level?->level_name_ar ?? '—') }}</td>
                                        <td class="p-1 border border-slate-300 text-center">{{ $empRec?->initial_rank ?? '—' }}</td>
                                        <td class="p-1 border border-slate-300 font-bold">{{ $emp->college_name }}</td>
                                        <td class="p-1 border border-slate-300">{{ $emp->department_name }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-bold text-primary">{{ $emp->rank_name }}</td>
                                        <td class="p-1 border border-slate-300 text-center font-bold text-emerald-800">{{ $emp->status_name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="19" class="p-8 text-center text-slate-400 font-bold">
                                            لا توجد سجلات مطابقة للفلترة الحالية.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>

        <!-- سكريبت الفلترة الحركية للأقسام التابعة للكلية المحددة فقط -->
        <script>
            (function() {
                const facultySelect = document.getElementById('roster_faculty_id');
                const deptSelect = document.getElementById('roster_department_id');
                if (!facultySelect || !deptSelect) return;

                // الاحتفاظ بكافة الأقسام المرجعية مع معرفات كلياتها
                const allDepartments = Array.from(deptSelect.options)
                    .filter(opt => opt.value !== '')
                    .map(opt => ({
                        value: opt.value,
                        text: opt.text,
                        facultyId: opt.getAttribute('data-faculty-id') || ''
                    }));

                window.filterDepartmentsByFaculty = function(selectedFacultyId, preserveSelected = false) {
                    const currentSelectedDept = preserveSelected ? deptSelect.value : '';

                    deptSelect.innerHTML = '';

                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = selectedFacultyId ? '-- كافة أقسام الكلية المحددة --' : '-- كافة الأقسام --';
                    deptSelect.appendChild(defaultOpt);

                    let hasMatched = false;
                    allDepartments.forEach(d => {
                        if (!selectedFacultyId || d.facultyId === selectedFacultyId) {
                            const opt = document.createElement('option');
                            opt.value = d.value;
                            opt.textContent = d.text;
                            opt.setAttribute('data-faculty-id', d.facultyId);
                            if (d.value === currentSelectedDept) {
                                opt.selected = true;
                                hasMatched = true;
                            }
                            deptSelect.appendChild(opt);
                        }
                    });

                    if (!hasMatched && preserveSelected && currentSelectedDept) {
                        deptSelect.value = '';
                    }
                };

                // تشغيل تلقائي عند التحميل إذا كانت هناك كلية محددة في الفلتر الحالي
                if (facultySelect.value) {
                    window.filterDepartmentsByFaculty(facultySelect.value, true);
                }
            })();
        </script>
