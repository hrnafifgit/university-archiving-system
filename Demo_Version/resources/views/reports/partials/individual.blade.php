        <div class="flex-1 flex gap-2 min-h-0 overflow-hidden">
            
            <!-- قائمة الكادر الجانبية للاختيار السريع عالي الكثافة (Print Hidden) -->
            <div class="w-56 sm:w-64 bg-white border border-slate-200 shadow-xs flex flex-col shrink-0 min-h-0 print:hidden rounded-2xl overflow-hidden">
                <div class="p-2 px-2.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-1.5">
                    <form method="GET" action="{{ route('reports.index') }}" class="flex-1 flex items-center">
                        <input type="hidden" name="type" value="individual">
                        <div class="relative w-full">
                            <input type="text" name="search_emp" value="{{ request('search_emp') }}" placeholder="بحث بالاسم أو الرقم..." 
                                   class="w-full text-xs border border-slate-200 px-2.5 py-1 bg-white focus:outline-none focus:border-[#14532d] pr-6 font-bold rounded-lg transition">
                            <i class="fa-solid fa-search absolute right-2 top-2 text-slate-400 text-[10px]"></i>
                        </div>
                    </form>
                    <span class="text-[10px] font-mono font-bold bg-emerald-50 text-emerald-900 px-2 py-0.5 rounded-lg border border-emerald-200 shrink-0">
                        {{ count($employeeList) }}
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto divide-y divide-slate-100 min-h-0">
                    @forelse($employeeList as $emp)
                        <a href="{{ route('reports.index', ['type' => 'individual', 'employee_id' => $emp->employee_id]) }}" 
                           class="p-2 px-2.5 block transition hover:bg-slate-50 {{ $employee && $employee->employee_id === $emp->employee_id ? 'bg-emerald-50/80 border-r-3 border-[#14532d] font-bold' : '' }}">
                            <div class="text-xs text-slate-900 truncate">{{ $emp->full_name_ar }}</div>
                            <div class="text-[10px] text-slate-500 font-mono flex items-center justify-between mt-0.5">
                                <span>{{ $emp->employee_number ?? '-' }}</span>
                                @if($employee && $employee->employee_id === $emp->employee_id)
                                    <span class="text-[#14532d] font-bold text-[9px]">معروض الآن</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-slate-400 text-xs">لا يوجد كادر مطابق</div>
                    @endforelse
                </div>
            </div>

            <!-- منطقة المعاينة المباشرة مع شريط أدوات الزوم التفاعلي (Live Canvas Preview) -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden bg-slate-200/70 rounded-2xl border border-slate-200 relative print:bg-white print:border-none">
                
                <!-- شريط أدوات الزوم والتحكم في عرض الورقة بالكامل -->
                <div class="bg-white/95 backdrop-blur-xs border-b border-slate-300 p-1 px-3 flex items-center justify-between shrink-0 shadow-xs z-30 select-none print:hidden">
                    
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-dark flex items-center gap-1.5">
                            <i class="fa-solid fa-file-contract text-primary"></i>
                            <span class="truncate max-w-[200px]">{{ $employee ? $employee->full_name_ar : 'معاينة بيان الحالة' }}</span>
                        </span>
                    </div>

                    <!-- أزرار التقريب والتبعيد والقرص باللمس -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <!-- زر توسيط الشاشة وضبط أبعاد الوثيقة على 90% -->
                        <button type="button" onclick="fitToPage()" 
                                class="bg-primary-surface hover:bg-primary text-primary hover:text-white font-bold px-2 py-0.5 rounded text-xs transition border border-primary/30 flex items-center gap-1 cursor-pointer shadow-2xs"
                                title="توسيط الشاشة وضبط أبعاد الوثيقة على 90%">
                            <i class="fa-solid fa-expand text-gold text-[10px]"></i>
                            <span>توسيط الشاشة</span>
                        </button>

                        <div class="h-4 w-px bg-slate-300 mx-0.5"></div>

                        <!-- زر تصغير (تبعيد الزوم) -->
                        <button type="button" onclick="zoomStep(-0.05)" 
                                class="bg-slate-100 hover:bg-slate-200 text-dark font-bold w-6 h-6 rounded flex items-center justify-center transition border border-slate-300 cursor-pointer shadow-2xs"
                                title="تصغير (تبعيد الزوم)">
                            <i class="fa-solid fa-minus text-[10px]"></i>
                        </button>

                        <!-- شارة نسبة الزوم الحالية -->
                        <span id="zoom-percentage-badge" class="font-mono font-bold text-xs bg-slate-50 border border-slate-300 px-2 py-0.5 rounded text-primary min-w-[50px] text-center shadow-2xs">
                            90%
                        </span>

                        <!-- زر تكبير (تقريب الزوم) -->
                        <button type="button" onclick="zoomStep(0.05)" 
                                class="bg-slate-100 hover:bg-slate-200 text-dark font-bold w-6 h-6 rounded flex items-center justify-center transition border border-slate-300 cursor-pointer shadow-2xs"
                                title="تكبير (تقريب الزوم)">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                        </button>

                        <!-- الحجم الطبيعي 100% -->
                        <button type="button" onclick="setZoom(1.0)" 
                                class="bg-slate-100 hover:bg-slate-200 text-dark font-bold px-1.5 py-0.5 rounded text-[11px] transition border border-slate-300 cursor-pointer"
                                title="الحجم الأصلي الطبيعي 100%">
                            100%
                        </button>
                    </div>

                </div>

                <!-- مساحة المعاينة الحاوية القابلة للتمرير والقرص باللمس (مسموح فيها بالزوم المحلي المستقل) -->
                <div id="report-preview-container" class="allow-zoom flex-1 overflow-auto p-2 flex justify-center items-start min-h-0 relative select-none print:p-0 print:overflow-visible" data-allow-zoom="true">
                    @if(!$employee)
                        <div class="bg-white p-6 text-center rounded border border-slate-300 my-auto text-xs shadow-md">
                            <i class="fa-solid fa-user-tie text-3xl text-slate-300 mb-2 block"></i>
                            <p class="font-bold text-slate-600">اختر أحد أعضاء الكادر من القائمة لعرض بيان حالته الرسمي.</p>
                        </div>
                    @else
                        @php
                            $primaryRec = $employee->active_employment ?? $employee->currentEmployment ?? $employee->latestEmployment ?? $employee->employmentRecords->sortByDesc('start_date')->first();
                            $ou = $primaryRec?->organizationalUnit;
                            $faculty = ($ou && $ou->parent && $ou->parent->unit_type !== 'university') ? $ou->parent : $ou;
                            $dept = ($ou && $ou->parent_unit_id && $ou->parent_unit_id !== $ou->organizational_unit_id) ? $ou : null;
                            
                            // المؤهلات الثلاثة
                            $bachelor = $qualificationsByLevel['bachelor'];
                            $master = $qualificationsByLevel['master'];
                            $phd = $qualificationsByLevel['phd'];

                            // التخصص الدقيق من الملاحظات أو من المؤهلات
                            $exactSpec = '';
                            if ($employee->notes && str_contains($employee->notes, 'التخصص الدقيق:')) {
                                $exactSpec = trim(str_replace('التخصص الدقيق:', '', $employee->notes));
                            } elseif ($phd && ($phd->specialization?->name_ar || $phd->degree_title)) {
                                $exactSpec = $phd->specialization?->name_ar ?? $phd->degree_title;
                            } elseif ($master && ($master->specialization?->name_ar || $master->degree_title)) {
                                $exactSpec = $master->specialization?->name_ar ?? $master->degree_title;
                            } elseif ($bachelor && ($bachelor->specialization?->name_ar || $bachelor->degree_title)) {
                                $exactSpec = $bachelor->specialization?->name_ar ?? $bachelor->degree_title;
                            } else {
                                $exactSpec = '—';
                            }

                            // رقم الملف المرجعي
                            $docNum = $employee->documents->first()?->document_number ?? (str_replace('TEMP_', '', $employee->employee_number));
                            if (is_numeric($docNum)) {
                                $docNum = (int)$docNum;
                            }

                            // الجنس
                            $genderText = ($employee->gender === 'F' || $employee->gender === 'female' || $employee->gender === 'انثى') ? 'انثى' : 'ذكر';
                        @endphp

                        <!-- مستند A4 الرسمي مع التحجيم الأصلي المدمج دون انفصال الطبقات -->
                        <div id="a4-paper" class="bg-white border border-slate-400 shadow-xl p-5 text-slate-900 text-xs space-y-2.5 print:border-none print:shadow-none print:p-0 print:max-w-none print:w-full shrink-0"
                             style="width: 800px; min-width: 800px; max-width: 800px; font-family: Arial, 'Cairo', Tahoma, sans-serif;">
                        
                        <!-- 1. الترويسة المعتمدة المطابقة للصورة -->
                        <div class="flex justify-between items-start select-none pt-1">
                            <!-- اليمين: الشعار الرسمي المعتمد لجامعة إب -->
                            <div class="w-36 text-right">
                                <img src="{{ asset('images/logo.png') }}" alt="جامعة إب" class="h-[88px] w-auto object-contain inline-block drop-shadow-xs" onerror="this.onerror=null; this.src='/images/logo.png'">
                            </div>

                            <!-- الوسط: بانر عنوان بيان الحالة واسم الموظف والموقف الوظيفي -->
                            <div class="flex-1 flex flex-col items-center justify-center text-center px-2">
                                <!-- البانر الأزرق الفاتح -->
                                <div class="px-8 py-1 rounded-sm border border-[#9bb7d4] text-[#113f67] font-black text-sm tracking-wide shadow-2xs"
                                     style="background-color: #d8e5f2;">
                                    بيان حالة الكادر التدريسي بالجامعة
                                </div>

                                <!-- الخاصة بـ / الاسم -->
                                <div class="font-bold text-[13px] text-dark mt-1">
                                    الخاصة بـ / <span class="font-black text-black">{{ $employee->full_name_ar }}</span>
                                </div>

                                <!-- بطاقة الموقف الوظيفي -->
                                <table class="mt-1 text-center font-bold text-xs border border-black border-collapse" style="width: auto;">
                                    <tr>
                                        <td style="background-color: #eae5d9; border: 1px solid black; padding: 2px 10px; font-size: 11px;">الموقف الوظيفي</td>
                                        <td style="background-color: #ffffff; border: 1px solid black; padding: 2px 14px; font-weight: bold; font-size: 11px;">{{ $primaryRec?->appointment_basis ?? 'مثبت' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- اليسار: صندوق رقم الملف -->
                            <div class="w-32 flex justify-end">
                                <table class="border border-black border-collapse text-center font-bold text-xs" style="width: 75px;">
                                    <tr>
                                        <td style="background-color: #eae5d9; border: 1px solid black; padding: 2px; font-size: 10.5px;">رقم الملف</td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: #ffffff; border: 1px solid black; padding: 3px; font-size: 12px; font-family: monospace;">
                                            {{ $docNum }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- 2. جدول البيانات الشخصية (الـ 4 أسطر بـ 4 أعمدة مطابقة لـ prnn B7:R13) -->
                        <table class="w-full text-center border border-black border-collapse text-[11px] font-bold" style="border: 1.5px solid black;">
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; width: 14%; padding: 3px;">الرقم</td>
                                <td style="background-color: #ffffff; border: 1px solid black; width: 36%; padding: 3px; font-family: monospace;">{{ $docNum }}</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; width: 16%; padding: 3px;">الرقم الوطني</td>
                                <td style="background-color: #ffffff; border: 1px solid black; width: 34%; padding: 3px; font-family: monospace;">{{ $employee->national_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 3px;">الاسم</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px;" class="font-black">{{ $employee->full_name_ar }}</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 3px;">الرقم الوظيفي</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px; font-family: monospace;">{{ $employee->employee_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 3px;">الجنس</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px;">{{ $genderText }}</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 3px;">رقم الجوال</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px; font-family: monospace;">{{ $employee->phone_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 3px;">تاريخ الميلاد</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px; font-family: monospace;">{{ $employee->date_of_birth ? $employee->date_of_birth->format('Y') : '-' }}</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 3px;">مكان الميلاد</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px;">{{ $employee->place_of_birth ?? '-' }}</td>
                            </tr>
                        </table>

                        <!-- 3. جدول البيانات بحسب كشف الراتب (مطابق لـ prnn B15:R16) -->
                        <table class="w-full text-center border border-black border-collapse text-[11px] font-bold" style="border: 1.5px solid black;">
                            <tr>
                                <td rowspan="2" style="background-color: #eae5d9; border: 1px solid black; width: 14%; padding: 4px; font-size: 10px;">البيانات بحسب كشف الراتب</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; width: 28%; padding: 3px;">الحالة بحسب كشف الراتب</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; width: 30%; padding: 3px;">الوظيفة الحالية بكشف الراتب</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; width: 28%; padding: 3px;">الدرجة بحسب كشف الراتب</td>
                            </tr>
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px;">{{ $primaryRec?->payroll_status ?: ($primaryRec?->employmentStatus?->status_name_ar ?: 'غير محدد') }}</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px;">{{ $primaryRec?->payroll_job_title ?: ($primaryRec?->academicRank?->rank_name_ar ?: 'غير محدد') }}</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 3px;">{{ $primaryRec?->payroll_job_grade ?: ($primaryRec?->jobGrade?->grade_name_ar ?: 'غير محدد') }}</td>
                            </tr>
                        </table>

                        <!-- 4. جدول الشؤون الأكاديمية والشهادات والقرارات (الشبكة المدمجة الموحدة) -->
                        <table class="w-full text-center border border-black border-collapse text-[10.5px] font-bold" style="border: 1.5px solid black;">
                            
                            <!-- الترويسة الرئيسية الكاملة -->
                            <tr>
                                <td colspan="5" style="background-color: #eae5d9; border: 1px solid black; padding: 4px; font-size: 11.5px; font-weight: bold;">
                                    البيانات بحسب الملفات بالادارة العامة للشئون الاكاديمية
                                </td>
                            </tr>

                            <!-- السطر 1: بيانات التعيين / بيانات الشهادات العلمية / شهادة البكالوريوس -->
                            <tr>
                                <td style="background-color: #fce4d6; border: 1px solid black; width: 44%; padding: 3px; font-size: 11px;">بيانات التعيين</td>
                                <td rowspan="19" style="background-color: #eae5d9; border: 1px solid black; width: 26px; padding: 4px; writing-mode: vertical-rl; transform: rotate(180deg); font-weight: bold; font-size: 11px; white-space: nowrap;">
                                    بيانات الشهادات العلمية
                                </td>
                                <td colspan="3" style="background-color: #fce4d6; border: 1px solid black; width: 52%; padding: 3px; font-size: 11px;">شهادة البكالوريوس</td>
                            </tr>

                            <!-- السطر 2: تاريخ التعيين أول مرة / التخصص بك -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">تاريخ التعيين بالوظيفة لاول مرة</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; width: 14%; padding: 2.5px;">التخصص</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $bachelor ? ($bachelor->specialization?->name_ar ?: ($bachelor->degree_title ?: '—')) : '—' }}
                                </td>
                            </tr>

                            <!-- السطر 3: القيمة / التقدير بك -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $primaryRec?->first_appointment_date ? $primaryRec->first_appointment_date->format('Y/m/d') : '—' }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">التقدير</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $bachelor ? ($bachelor->grade_or_gpa ?: '—') : '—' }}
                                </td>
                            </tr>

                            <!-- السطر 4: الجهة المعين بها / تاريخها بك -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الجهة المعين بها</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">تاريخها</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $bachelor?->graduation_date ? $bachelor->graduation_date->format('Y') : ($bachelor?->award_date ? $bachelor->award_date->format('Y') : '—') }}
                                </td>
                            </tr>

                            <!-- السطر 5: القيمة / الجامعة بك -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $primaryRec?->initial_appointing_entity ?: 'جامعة إب' }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الجامعة</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $bachelor?->university?->university_name_ar ?: ($bachelor?->notes ?: '—') }}
                                </td>
                            </tr>

                            <!-- السطر 6: تاريخ التعيين بالجامعة أكاديمي / الدولة بك -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">تاريخ التعين بالجامعة اكاديمي</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الدولة</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $bachelor?->country?->country_name_ar ?: 'اليمن' }}
                                </td>
                            </tr>

                            <!-- السطر 7: القيمة / ترويسة شهادة الماجستير -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $primaryRec?->academic_appointment_date ? $primaryRec->academic_appointment_date->format('Y/m/d') : '—' }}
                                </td>
                                <td colspan="3" style="background-color: #fce4d6; border: 1px solid black; padding: 3px; font-size: 11px;">شهادة الماجستير</td>
                            </tr>

                            <!-- السطر 8: المؤهل عند التعيين / التخصص مج -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">المؤهل عند التعيين بالجامعة</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">التخصص</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $master ? ($master->specialization?->name_ar ?: ($master->degree_title ?: '—')) : '—' }}
                                </td>
                            </tr>

                            <!-- السطر 9: القيمة / التقدير مج -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $primaryRec?->qualification_at_appointment ?: ($bachelor ? 'بكالوريوس' : '—') }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">التقدير</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $master ? ($master->grade_or_gpa ?: '—') : '—' }}
                                </td>
                            </tr>

                            <!-- السطر 10: الدرجة المعين فيها / تاريخها مج -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الدرجة المعين فيها</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">تاريخها</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $master?->graduation_date ? $master->graduation_date->format('Y') : ($master?->award_date ? $master->award_date->format('Y') : '—') }}
                                </td>
                            </tr>

                            <!-- السطر 11: القيمة / الجامعة مج -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $primaryRec?->initial_rank ?: 'معيد' }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الجامعة</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $master?->university?->university_name_ar ?: ($master?->notes ?: '—') }}
                                </td>
                            </tr>

                            <!-- السطر 12: الكلية / الدولة مج -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الكلية</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الدولة</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $master?->country?->country_name_ar ?: '—' }}
                                </td>
                            </tr>

                            <!-- السطر 13: القيمة / ترويسة شهادة الدكتوراه -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $faculty?->unit_name_ar ?: $employee->college_name }}
                                </td>
                                <td colspan="3" style="background-color: #fce4d6; border: 1px solid black; padding: 3px; font-size: 11px;">شهادة الدكتوراة</td>
                            </tr>

                            <!-- السطر 14: القسم / التخصص دك -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">القسم</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">التخصص</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $phd ? ($phd->specialization?->name_ar ?: ($phd->degree_title ?: '—')) : '—' }}
                                </td>
                            </tr>

                            <!-- السطر 15: القيمة / التقدير دك -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $dept?->unit_name_ar ?: $employee->department_name }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">التقدير</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $phd ? ($phd->grade_or_gpa ?: '—') : '—' }}
                                </td>
                            </tr>

                            <!-- السطر 16: الدرجة الحالية / تاريخها دك -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الدرجة الحالية</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">تاريخها</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $phd?->graduation_date ? $phd->graduation_date->format('Y') : ($phd?->award_date ? $phd->award_date->format('Y') : '—') }}
                                </td>
                            </tr>

                            <!-- السطر 17: القيمة (أزرق سماوي) / الجامعة دك -->
                            <tr>
                                <td style="background-color: #e6f0fa; border: 1px solid black; padding: 2.5px; font-weight: bold; color: #000;">
                                    {{ $primaryRec?->academicRank?->rank_name_ar ?: ($employee->rank_name ?: '—') }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الجامعة</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $phd?->university?->university_name_ar ?: ($phd?->notes ?: '—') }}
                                </td>
                            </tr>

                            <!-- السطر 18: الوضع الحالي / الدولة دك -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الوضع الحالي</td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الدولة</td>
                                <td colspan="2" style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $phd?->country?->country_name_ar ?: '—' }}
                                </td>
                            </tr>

                            <!-- السطر 19: القيمة (وردي فاتح) / التخصص الدقيق (أخضر نعناعي) -->
                            <tr>
                                <td style="background-color: #fde8e8; border: 1px solid black; padding: 2.5px; font-weight: bold; color: #000;">
                                    {{ $primaryRec?->employmentStatus?->status_name_ar ?: ($employee->status_name ?: '—') }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">التخصص الدقيق</td>
                                <td colspan="2" style="background-color: #e2f5ee; border: 1px solid black; padding: 2.5px; font-weight: bold;">
                                    {{ $exactSpec }}
                                </td>
                            </tr>

                            <!-- السطر 20: الملف الإلكتروني / خاص بأخر القرارات (ترويسة كاملة) -->
                            <tr>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">الملف الالكتروني</td>
                                <td colspan="4" style="background-color: #fce4d6; border: 1px solid black; padding: 3px; font-size: 11px;">
                                    خاص بأخر القرارات / ايفاد / تفرغ / اجازة
                                </td>
                            </tr>

                            <!-- السطر 21: القيمة (PDF) / رقم آخر قرار + تاريخ بدايته -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $docNum }}.PDF
                                </td>
                                <td colspan="2" style="background-color: #eae5d9; border: 1px solid black; width: 14%; padding: 2.5px;">رقم اخر قرار</td>
                                <td style="background-color: #ffffff; border: 1px solid black; width: 12%; padding: 2.5px; font-family: monospace;">
                                    {{ $latestDecision?->decision_number ?? '0' }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; width: 13%; padding: 2.5px;">تاريخ بدايته</td>
                                <td style="background-color: #ffffff; border: 1px solid black; width: 15%; padding: 2.5px; font-family: monospace;">
                                    {{ $latestDecision?->effective_date ? $latestDecision->effective_date->format('Y/m/d') : '0' }}
                                </td>
                            </tr>

                            <!-- السطر 22: فتح الملف / نوعه + تاريخ نهايته -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; color: #888;">
                                    فتح الملف
                                </td>
                                <td colspan="2" style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">نوعة</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $latestDecision?->type?->type_name_ar ?? '0' }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">تاريخ نهايته</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $latestDecision?->expiry_date ? $latestDecision->expiry_date->format('Y/m/d') : '0' }}
                                </td>
                            </tr>

                            <!-- السطر 23: رقم الربط / مدته + حالة القرار -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px; font-family: monospace;">
                                    {{ $docNum }}
                                </td>
                                <td colspan="2" style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">مدته</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $latestDecision ? 'عام' : '' }}
                                </td>
                                <td style="background-color: #eae5d9; border: 1px solid black; padding: 2.5px;">حالة القرار</td>
                                <td style="background-color: #ffffff; border: 1px solid black; padding: 2.5px;">
                                    {{ $latestDecision?->status?->status_name_ar ?? '' }}
                                </td>
                            </tr>

                            <!-- السطر 24: الملاحظات في الأسفل -->
                            <tr>
                                <td colspan="5" style="border: none; padding: 0;">
                                    <table class="w-full text-center border-collapse" style="border-top: 1px solid black;">
                                        <tr>
                                            <td style="background-color: #ffffff; border: 1px solid black; width: 85%; padding: 3px; font-weight: bold;">
                                                0
                                            </td>
                                            <td style="background-color: #eae5d9; border: 1px solid black; width: 15%; padding: 3px; font-weight: bold;">
                                                ملاحظات
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                        </table>

                    </div>
                @endif
            </div>

            </div>

        </div>
