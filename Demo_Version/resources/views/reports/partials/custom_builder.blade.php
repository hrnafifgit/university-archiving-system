        <div class="flex-1 flex flex-col min-h-0 gap-2 pb-0">
            
            <!-- لوحة التحكم وتحديد الأعمدة المدمجة بهوية النظام (Print Hidden) -->
            <form method="GET" action="{{ route('reports.index') }}" class="bg-white border border-slate-200 shadow-xs shrink-0 rounded-2xl select-none print:hidden flex flex-col overflow-hidden">
                <input type="hidden" name="type" value="custom_builder">

                <!-- شريط الإجراءات والفلترة العلوي المدمج -->
                <div class="p-2.5 px-3.5 bg-slate-50/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <!-- اختيار الكلية / الجهة -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <label class="font-bold text-slate-700 text-xs whitespace-nowrap">الجهة / الكلية:</label>
                            <select name="faculty_id" class="bg-white border border-slate-200 px-2.5 py-1 text-xs text-slate-800 font-bold focus:outline-none focus:border-[#14532d] rounded-lg min-w-[200px]">
                                <option value="all">-- كافة الكليات والمراكز (الكل) --</option>
                                @foreach($faculties as $f)
                                    <option value="{{ $f->organizational_unit_id }}" {{ request('faculty_id') == $f->organizational_unit_id ? 'selected' : '' }}>
                                        {{ $f->unit_name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- شارة إحصائية سريعة -->
                        <span class="text-[11px] font-mono bg-emerald-50 text-emerald-900 px-2.5 py-0.5 rounded-lg border border-emerald-200 font-bold">
                            النتائج: <strong class="text-slate-900">{{ count($customReportData) }}</strong> كادر | الأعمدة: <strong id="custom-selected-count-badge" class="text-[#14532d]">{{ count($selectedColumns) }}</strong>
                        </span>
                    </div>

                    <!-- أزرار العمليات والتصدير والطباعة وطي لوحة الأعمدة -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <!-- زر قفل / طي وإظهار لوحة اختيار الأعمدة لتوفير المساحة -->
                        <button type="button" onclick="toggleColumnsPanel()" id="btn-toggle-columns" 
                                class="bg-white hover:bg-slate-100 text-slate-800 font-bold px-3 py-1 rounded-lg text-xs border border-slate-200 transition cursor-pointer flex items-center gap-1.5 shadow-2xs"
                                title="طي أو إظهار لوحة اختيار الأعمدة لتوفير المساحة للشاشة والجدول">
                            <i id="icon-toggle-columns" class="fa-solid fa-chevron-up text-[#14532d] text-xs"></i>
                            <span id="text-toggle-columns">طي خيارات الأعمدة</span>
                        </button>

                        <button type="button" onclick="document.querySelectorAll('.cb-col-check').forEach(c => c.checked = true); updateColumnsCount();" 
                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-2.5 py-1 rounded-lg text-xs border border-slate-200 transition cursor-pointer">
                            تحديد الكل
                        </button>
                        <button type="button" onclick="document.querySelectorAll('.cb-col-check').forEach(c => c.checked = false); updateColumnsCount();" 
                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-2.5 py-1 rounded-lg text-xs border border-slate-200 transition cursor-pointer">
                            إلغاء التحديد
                        </button>
                        
                        <button type="submit" 
                                class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1 rounded-lg text-xs transition border border-[#14532d] flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            <i class="fa-solid fa-arrows-rotate text-emerald-200 text-xs"></i>
                            <span>توليد التقرير</span>
                        </button>

                        <button type="button" onclick="window.print()" 
                                class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-3.5 py-1 rounded-lg text-xs transition border border-[#14532d] flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            <i class="fa-solid fa-print text-white text-xs"></i>
                            <span>طباعة رسمية</span>
                        </button>
                    </div>
                </div>

                <!-- شبكة اختيار الأعمدة الحديثة عالية الكثافة (قابلة للطي والإغلاق) -->
                <div id="columns-selection-panel" class="transition-all duration-200">
                    <div class="p-2 bg-white">
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-1.5 text-xs">
                            @foreach($availableColumns as $cKey => $cLabel)
                                <label class="flex items-center gap-1.5 p-1 px-1.5 rounded-xs border border-slate-200 hover:bg-primary-surface hover:border-primary/40 cursor-pointer transition select-none text-[11px] font-semibold text-slate-800">
                                    <input type="checkbox" name="columns[]" value="{{ $cKey }}" 
                                           onchange="updateColumnsCount()"
                                           class="cb-col-check w-3.5 h-3.5 accent-primary rounded-xs cursor-pointer" 
                                           {{ in_array($cKey, $selectedColumns) ? 'checked' : '' }}>
                                    <span class="truncate" title="{{ $cLabel }}">{{ $cLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <!-- شريط سفلي سريع للطي بعد الاختيار -->
                    <div onclick="toggleColumnsPanel()" 
                         class="bg-slate-50 hover:bg-slate-100 border-t border-slate-200 py-0.5 text-center cursor-pointer text-[10.5px] font-bold text-slate-500 hover:text-primary transition flex items-center justify-center gap-1 select-none">
                        <i class="fa-solid fa-chevron-up text-[9px]"></i>
                        <span>طي لوحة خيارات الأعمدة لتوفير المساحة للجدول</span>
                    </div>
                </div>

            </form>

            <script>
                function updateColumnsCount() {
                    const checked = document.querySelectorAll('.cb-col-check:checked').length;
                    const badge = document.getElementById('custom-selected-count-badge');
                    if (badge) badge.textContent = checked;

                    const panel = document.getElementById('columns-selection-panel');
                    const text = document.getElementById('text-toggle-columns');
                    if (panel && panel.classList.contains('hidden') && text) {
                        text.textContent = `إظهار الأعمدة (${checked})`;
                    }
                }

                function toggleColumnsPanel() {
                    const panel = document.getElementById('columns-selection-panel');
                    const icon = document.getElementById('icon-toggle-columns');
                    const text = document.getElementById('text-toggle-columns');
                    const btn = document.getElementById('btn-toggle-columns');
                    if (!panel) return;

                    const isHidden = panel.classList.contains('hidden');
                    if (isHidden) {
                        panel.classList.remove('hidden');
                        if (icon) icon.className = 'fa-solid fa-chevron-up text-primary text-[10px]';
                        if (text) text.textContent = 'طي خيارات الأعمدة';
                        if (btn) btn.className = 'bg-white hover:bg-slate-100 text-slate-800 font-bold px-2.5 py-0.5 rounded text-[11px] border border-slate-300 transition cursor-pointer flex items-center gap-1.5 shadow-2xs';
                        localStorage.setItem('uas_custom_columns_collapsed', 'false');
                    } else {
                        panel.classList.add('hidden');
                        if (icon) icon.className = 'fa-solid fa-chevron-down text-primary text-[10px]';
                        const count = document.querySelectorAll('.cb-col-check:checked').length;
                        if (text) text.textContent = `إظهار الأعمدة (${count})`;
                        if (btn) btn.className = 'bg-primary-surface hover:bg-primary/10 text-primary font-bold px-2.5 py-0.5 rounded text-[11px] border border-primary/40 transition cursor-pointer flex items-center gap-1.5 shadow-2xs';
                        localStorage.setItem('uas_custom_columns_collapsed', 'true');
                    }
                }

                document.addEventListener('DOMContentLoaded', function () {
                    // إذا كان المستخدم قد اختار سابقاً طي الأعمدة، يتم طيها تلقائياً لتوفير المساحة
                    if (localStorage.getItem('uas_custom_columns_collapsed') === 'true') {
                        const panel = document.getElementById('columns-selection-panel');
                        const icon = document.getElementById('icon-toggle-columns');
                        const text = document.getElementById('text-toggle-columns');
                        const btn = document.getElementById('btn-toggle-columns');
                        if (panel) {
                            panel.classList.add('hidden');
                            if (icon) icon.className = 'fa-solid fa-chevron-down text-primary text-[10px]';
                            const count = document.querySelectorAll('.cb-col-check:checked').length;
                            if (text) text.textContent = `إظهار الأعمدة (${count})`;
                            if (btn) btn.className = 'bg-primary-surface hover:bg-primary/10 text-primary font-bold px-2.5 py-0.5 rounded text-[11px] border border-primary/40 transition cursor-pointer flex items-center gap-1.5 shadow-2xs';
                        }
                    }
                });
            </script>

            <!-- منطقة عرض التقرير المخصص المتكاملة الممتدة لأسفل الشاشة (Data Grid) -->
            <div class="flex-1 bg-white border border-slate-300 shadow-2xs flex flex-col min-h-0 rounded-xs overflow-hidden print:border-none print:shadow-none">
                
                <div class="flex-1 overflow-auto min-h-0 p-3 print:p-0">
                    
                    <!-- الترويسة المعتمدة للتقرير المخصص (تظهر في الطباعة والمعاينة) -->
                    <div class="border-b-2 border-primary pb-2 mb-2 select-none">
                        <div class="flex items-center justify-between mb-1">
                            <div class="text-right text-[11px] font-bold text-slate-700 leading-snug">
                                <div class="font-black text-primary">الجمهورية اليمنية</div>
                                <div>جامعة إب - نيابة الشؤون الأكاديمية</div>
                                <div class="text-[10px] text-muted">مركز تقارير ومصفوفات الكادر</div>
                            </div>
                            <x-university-logo class="w-14 h-14" />
                            <div class="text-left text-[11px] font-mono text-slate-700 leading-snug">
                                <div>التاريخ: <span class="font-bold text-dark">{{ date('Y/m/d') }}</span></div>
                                <div>الجهة: <span class="font-bold text-primary">{{ $selectedFacultyName }}</span></div>
                                <div>العدد: <span class="font-bold text-gold">{{ count($customReportData) }}</span></div>
                            </div>
                        </div>

                        <h2 class="text-center text-sm md:text-base font-black text-dark tracking-wide mt-0.5">
                            تقرير بيانات الكادر الأكاديمي — {{ $selectedFacultyName }}
                        </h2>
                    </div>

                    <!-- جدول الأعمدة المخصصة عالي الكثافة الموحد مع باقي جداول النظام -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs border-collapse">
                            <thead class="bg-primary text-white font-bold sticky top-0 z-10 select-none text-[11px] border-b-2 border-gold shadow-xs">
                                <tr class="divide-x divide-x-reverse divide-primary-dark whitespace-nowrap">
                                    @foreach($selectedColumns as $cKey)
                                        <th class="py-1 px-2 text-center">
                                            {{ $availableColumns[$cKey] ?? $cKey }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 font-semibold text-dark text-[11px]">
                                @forelse($customReportData as $idx => $row)
                                    <tr class="hover:bg-primary-surface transition divide-x divide-x-reverse divide-slate-200 {{ $idx % 2 == 0 ? 'bg-white' : 'bg-[#fcfdfd]' }} whitespace-nowrap">
                                        @foreach($selectedColumns as $cKey)
                                            @php
                                                $isNumericOrCode = in_array($cKey, ['serial', 'employee_number', 'national_id', 'date_of_birth', 'bachelor_date', 'master_date', 'phd_date', 'phone_number', 'first_appointment_date', 'academic_appointment_date', 'decision_date']);
                                                $isCentered = $isNumericOrCode || in_array($cKey, ['gender', 'appointment_basis', 'payroll_status', 'payroll_job_grade', 'initial_rank', 'academic_rank', 'academic_status', 'decision_status']);
                                            @endphp
                                            <td class="py-1 px-2 {{ $isCentered ? 'text-center' : 'text-right' }} {{ $isNumericOrCode ? 'font-mono text-slate-700' : '' }} {{ $cKey === 'serial' ? 'font-mono font-bold text-muted w-10 text-center' : '' }} {{ $cKey === 'full_name_ar' ? 'font-bold text-primary' : '' }}">
                                                @if($cKey === 'doc_link' && !empty($row['doc_name']))
                                                    <span class="text-primary hover:underline font-bold cursor-pointer">فتح الملف</span>
                                                @else
                                                    {{ $row[$cKey] ?? '—' }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($selectedColumns) }}" class="py-12 text-center text-muted font-bold">
                                            <i class="fa-solid fa-inbox text-3xl text-slate-300 mb-2 block"></i>
                                            لا توجد بيانات مطابقة لمعايير التقرير المحددة.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-slate-100 font-bold text-dark border-t-2 border-slate-300">
                                <tr>
                                    <td colspan="{{ count($selectedColumns) }}" class="py-1 px-2 text-center text-xs text-muted font-mono">
                                        إجمالي عدد السجلات المطابقة: <strong class="text-primary font-bold">{{ count($customReportData) }}</strong> عضواً
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- تذييل التوقيعات عند الطباعة الرسمية -->
                    <div class="mt-6 pt-3 border-t border-slate-300 grid grid-cols-3 gap-4 text-center text-[10.5px] font-bold select-none">
                        <div class="space-y-6">
                            <p>المختص / إعداد التقرير</p>
                            <p class="text-slate-300 font-mono">.................................</p>
                        </div>
                        <div class="space-y-6">
                            <p>مدير عام الشؤون الأكاديمية</p>
                            <p class="text-slate-300 font-mono">.................................</p>
                        </div>
                        <div class="space-y-6">
                            <p>نائب رئيس الجامعة للشؤون الأكاديمية</p>
                            <p class="text-slate-300 font-mono">.................................</p>
                        </div>
                    </div>

                </div>

            </div>

        </div>
