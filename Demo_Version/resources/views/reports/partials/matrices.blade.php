        <div class="flex-1 bg-white border border-slate-200 shadow-xs flex flex-col min-h-0 rounded-2xl overflow-hidden">
            
            <!-- شريط التبديل المكتبي السريع للمصفوفات والمؤشرات (High-Density Compact Toolbar) -->
            <div class="p-2 px-3 border-b border-slate-200 bg-slate-50/80 flex flex-wrap items-center justify-between gap-2 print:hidden select-none shrink-0">
                
                <!-- أزرار الإحصائيات مدمجة في شريط أفقي رشيق ومختصر -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <!-- لوحة المؤشرات المركزية -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'star1']) }}"
                       class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5 border {{ $matrixCode === 'star1' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="لوحة المؤشرات الإجمالية والمخططات البيانية">
                        <i class="fa-solid fa-gauge-high text-xs {{ $matrixCode === 'star1' ? 'text-emerald-200' : 'text-[#14532d]' }}"></i>
                        <span>لوحة المؤشرات</span>
                    </a>

                    <span class="w-[1px] h-4 bg-slate-200 mx-0.5 hidden sm:inline-block"></span>

                    <!-- 1. القسم -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh1']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh1' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية أعضاء هيئة التدريس بحسب القسم">
                        <span>حسب القسم</span>
                    </a>

                    <!-- 2. حالة الراتب -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh2']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh2' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية بحسب الحالة بكشف الراتب">
                        <span>حالة الراتب</span>
                    </a>

                    <!-- 3. الحالة الأكاديمية -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh3']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh3' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية بحسب الحالة بالشؤون الأكاديمية">
                        <span>الحالة الأكاديمية</span>
                    </a>

                    <!-- 4. درجة الراتب -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh4']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh4' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية بحسب الدرجة بكشف الراتب">
                        <span>درجة الراتب</span>
                    </a>

                    <!-- 5. الدرجة الأكاديمية -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh5']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh5' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية بحسب الدرجة بالشؤون الأكاديمية">
                        <span>الدرجة الأكاديمية</span>
                    </a>

                    <!-- 6. المؤهل -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh6']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh6' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية بحسب المؤهل العلمي">
                        <span>المؤهل العلمي</span>
                    </a>

                    <!-- 7. الموقف الوظيفي -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh7']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh7' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية بحسب الموقف الوظيفي">
                        <span>الموقف الوظيفي</span>
                    </a>

                    <!-- 8. الجنس -->
                    <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh8']) }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 border {{ $matrixCode === 'eh8' ? 'bg-[#14532d] text-white border-[#14532d] shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200' }}"
                       title="إحصائية بحسب النوع / الجنس">
                        <span>النوع / الجنس</span>
                    </a>
                </div>

                <!-- أدوات العرض (كلاهما / الجدول / المخطط) لمصفوفات الإحصاء -->
                @if($matrixCode !== 'star1')
                    <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl p-1 text-xs shadow-2xs">
                        <button type="button" onclick="toggleMatrixView('both')" id="btn-view-both" class="px-3 py-1 rounded-lg font-bold transition bg-[#14532d] text-white shadow-2xs cursor-pointer">
                            كلاهما
                        </button>
                        <button type="button" onclick="toggleMatrixView('table')" id="btn-view-table" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-[#14532d] cursor-pointer">
                            الجدول
                        </button>
                        <button type="button" onclick="toggleMatrixView('chart')" id="btn-view-chart" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-[#14532d] cursor-pointer">
                            المخطط
                        </button>
                    </div>
                @endif
            </div>

            <!-- محتوى المصفوفة المحددة -->
            <div class="flex-1 overflow-auto p-3 bg-slate-50 print:bg-white print:p-0">
                <div class="bg-white p-4 border border-slate-300 shadow-sm print:border-none print:shadow-none print:p-0">
                    
                    {{-- 1. لوحة المؤشرات والمخططات البيانية star1 (مطابقة لـ star1 في الإكسل 1:1) --}}
                    @if($matrixCode === 'star1')
                        <!-- ترويسة لوحة star1 مع الشعار والعنوان المركزي -->
                        <div class="border-b-2 border-slate-700 pb-3 mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <x-university-logo class="w-16 h-16" />
                                <div class="text-right">
                                    <div class="text-xs font-bold text-slate-700">الجمهورية اليمنية</div>
                                    <div class="text-sm font-black text-primary">جامعة إب</div>
                                    <div class="text-[11px] font-bold text-slate-500">نيابة الشؤون الأكاديمية</div>
                                </div>
                            </div>

                            <div class="text-center">
                                <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-wide">
                                    احصائيات اجمالية ومخططات بيانية
                                </h1>
                                <p class="text-[11px] text-slate-600 font-bold mt-0.5">لوحة القيادة والمؤشرات المركزية الرسمية لكادر الجامعة</p>
                            </div>

                            <div class="text-left font-mono text-[11px] text-slate-700 bg-slate-50 p-2 rounded border border-slate-200">
                                <div>التاريخ: <strong>{{ date('Y-m-d') }}م</strong></div>
                                <div>إجمالي الكادر: <strong class="text-primary text-sm font-black">{{ $matrixData['totalStaff'] }}</strong></div>
                            </div>
                        </div>

                        <!-- شبكة الجداول الستة والمخططات البيانية مطابقة للصورة a7d44c87 1:1 -->
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 text-xs select-none">
                            
                            <!-- =================== العمود الأيمن (4 أعمدة): الجنس، الدرجة، الموقف الوظيفي =================== -->
                            <div class="xl:col-span-4 space-y-4">
                                
                                <!-- 1. جدول الجنس + الرسم الدائري -->
                                <div class="border border-slate-400 bg-white shadow-2xs">
                                    <div class="flex items-stretch divide-x divide-x-reverse divide-slate-300">
                                        <!-- جدول الجنس -->
                                        <div class="w-1/2">
                                            <table class="w-full text-right text-[11px] border-collapse">
                                                <thead style="background-color: #203764; color: #ffffff;">
                                                    <tr>
                                                        <th class="p-1.5 text-center font-black">النوع / الجنس</th>
                                                        <th class="p-1.5 text-center font-black w-12">العدد</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-200">
                                                    @foreach($matrixData['genderStats'] as $label => $cnt)
                                                        <tr>
                                                            <td class="p-1.5 pr-2 font-bold">{{ $label }}</td>
                                                            <td class="p-1.5 text-center font-mono font-black text-primary">{{ $cnt }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class="bg-slate-100 font-black border-t-2 border-slate-400">
                                                        <td class="p-1.5 pr-2">اجمالي</td>
                                                        <td class="p-1.5 text-center font-mono font-black">{{ array_sum($matrixData['genderStats']) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- الرسم الدائري للجنس -->
                                        <div class="w-1/2 p-2 flex flex-col items-center justify-center bg-slate-50">
                                            <span class="text-[10px] font-bold text-slate-700 mb-1">احصائية عدد الذكور والاناث</span>
                                            <div class="w-28 h-28 relative">
                                                <canvas id="chart-gender"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. جدول الدرجة الحالية + الرسم البياني -->
                                <div class="border border-slate-400 bg-white shadow-2xs p-2">
                                    <div class="text-center font-bold text-slate-700 text-[11px] mb-1">احصائية بحسب الدرجة الحالية</div>
                                    <div class="h-36 mb-2">
                                        <canvas id="chart-rank"></canvas>
                                    </div>
                                    <table class="w-full text-right text-[11px] border-collapse">
                                        <thead style="background-color: #203764; color: #ffffff;">
                                            <tr>
                                                <th class="p-1.5 text-center font-black">الدرجة الحالية</th>
                                                <th class="p-1.5 text-center font-black w-14">العدد</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            @foreach($matrixData['rankStats'] as $label => $cnt)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="p-1 pr-2 font-bold text-slate-800">{{ $label }}</td>
                                                    <td class="p-1 text-center font-mono font-black text-primary">{{ $cnt }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-slate-100 font-black border-t-2 border-slate-400">
                                                <td class="p-1.5 pr-2">اجمالي</td>
                                                <td class="p-1.5 text-center font-mono font-black">{{ array_sum($matrixData['rankStats']) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 3. جدول الموقف الوظيفي + الرسم البياني -->
                                <div class="border border-slate-400 bg-white shadow-2xs p-2">
                                    <div class="text-center font-bold text-slate-700 text-[11px] mb-1">احصائية بحسب الموقف الوظيفي</div>
                                    <div class="h-32 mb-2">
                                        <canvas id="chart-appointment"></canvas>
                                    </div>
                                    <table class="w-full text-right text-[11px] border-collapse">
                                        <thead style="background-color: #443a60; color: #ffffff;">
                                            <tr>
                                                <th class="p-1.5 text-center font-black">الموقف الوظيفي</th>
                                                <th class="p-1.5 text-center font-black w-14">العدد</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            @foreach($matrixData['appointmentBasisStats'] as $label => $cnt)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="p-1 pr-2 font-bold text-slate-800">{{ $label }}</td>
                                                    <td class="p-1 text-center font-mono font-black text-primary">{{ $cnt }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-slate-100 font-black border-t-2 border-slate-400">
                                                <td class="p-1.5 pr-2">اجمالي</td>
                                                <td class="p-1.5 text-center font-mono font-black">{{ array_sum($matrixData['appointmentBasisStats']) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <!-- =================== العمود الأوسط (4 أعمدة): الحالة بالراتب والمؤهل العلمي =================== -->
                            <div class="xl:col-span-4 space-y-4">
                                
                                <!-- 4. جدول الحالة بكشف الراتب + الرسم البياني الأخضر -->
                                <div class="border border-slate-400 bg-white shadow-2xs p-2">
                                    <div class="bg-[#d9ead3] p-2 border border-[#b6d7a8] rounded-xs mb-2">
                                        <div class="text-center font-bold text-slate-800 text-[11px] mb-1">احصائية بحسب الحالة ك راتب</div>
                                        <div class="h-40">
                                            <canvas id="chart-payroll"></canvas>
                                        </div>
                                    </div>

                                    <table class="w-full text-right text-[11px] border-collapse">
                                        <thead style="background-color: #385723; color: #ffffff;">
                                            <tr>
                                                <th class="p-1.5 text-center font-black">الحالة بكشف الراتب</th>
                                                <th class="p-1.5 text-center font-black w-14">العدد</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-[#e2efda]/40">
                                            @foreach($matrixData['payrollStats'] as $label => $cnt)
                                                <tr class="hover:bg-emerald-100/50">
                                                    <td class="p-1 pr-2 font-bold text-slate-800">{{ $label }}</td>
                                                    <td class="p-1 text-center font-mono font-black text-emerald-900">{{ $cnt }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-emerald-100 font-black border-t-2 border-emerald-600">
                                                <td class="p-1.5 pr-2">اجمالي</td>
                                                <td class="p-1.5 text-center font-mono font-black">{{ array_sum($matrixData['payrollStats']) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 5. جدول المؤهل العلمي + الرسم البياني -->
                                <div class="border border-slate-400 bg-white shadow-2xs p-2">
                                    <div class="text-center font-bold text-slate-700 text-[11px] mb-1">احصائية بحسب المؤهل العلمي</div>
                                    <div class="h-36 mb-2">
                                        <canvas id="chart-qualification"></canvas>
                                    </div>
                                    <table class="w-full text-right text-[11px] border-collapse">
                                        <thead style="background-color: #544e15; color: #ffffff;">
                                            <tr>
                                                <th class="p-1.5 text-center font-black">المؤهل العلمي</th>
                                                <th class="p-1.5 text-center font-black w-14">العدد</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            @foreach($matrixData['qualificationStats'] as $label => $cnt)
                                                <tr class="hover:bg-amber-50">
                                                    <td class="p-1 pr-2 font-bold text-slate-800">{{ $label }}</td>
                                                    <td class="p-1 text-center font-mono font-black text-amber-950">{{ $cnt }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-amber-100/70 font-black border-t-2 border-amber-600">
                                                <td class="p-1.5 pr-2">اجمالي</td>
                                                <td class="p-1.5 text-center font-mono font-black">{{ array_sum($matrixData['qualificationStats']) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <!-- =================== العمود الأيسر (4 أعمدة): الحالة شئون أكاديمية =================== -->
                            <div class="xl:col-span-4 space-y-4">
                                
                                <!-- 6. جدول الحالة بشؤون أكاديمية + الرسم البياني -->
                                <div class="border border-slate-400 bg-white shadow-2xs p-2">
                                    <div class="text-center font-bold text-slate-700 text-[11px] mb-1">احصائية بحسب الحالة ش اكاديمية</div>
                                    <div class="h-44 mb-2">
                                        <canvas id="chart-academic"></canvas>
                                    </div>
                                    <table class="w-full text-right text-[11px] border-collapse">
                                        <thead style="background-color: #70232e; color: #ffffff;">
                                            <tr>
                                                <th class="p-1.5 text-center font-black">الحالة شئون اكاديمية</th>
                                                <th class="p-1.5 text-center font-black w-14">العدد</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-[#fce4d6]/40">
                                            @foreach($matrixData['academicStatusStats'] as $label => $cnt)
                                                <tr class="hover:bg-rose-100/50">
                                                    <td class="p-1 pr-2 font-bold text-slate-800">{{ $label }}</td>
                                                    <td class="p-1 text-center font-mono font-black text-rose-900">{{ $cnt }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-rose-100 font-black border-t-2 border-rose-600">
                                                <td class="p-1.5 pr-2">اجمالي</td>
                                                <td class="p-1.5 text-center font-mono font-black">{{ array_sum($matrixData['academicStatusStats']) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                        </div>

                    {{-- 2. مصفوفات Cross-tabulation (eh1 إلى eh8) المطابقة تماماً لورقة الإكسل والرسم البياني --}}
                    @else
                        <!-- =================== الصفحة الأولى: ورقة الجدول الإحصائي A4 كاملة بالعرض =================== -->
                        <div id="matrix-table-container" class="print-table-sheet">
                            <!-- ترويسة التقرير الرسمية لصفحة الجدول المطابقة للإكسل 1:1 -->
                            <div class="border-b-2 border-slate-700 pb-2 mb-3 flex items-center justify-between flex-wrap gap-2 select-none print:pb-1.5 print:mb-2">
                                <!-- الجانب الأيمن: شعار الجمهورية اليمنية وجامعة إب -->
                                <div class="flex items-center gap-2.5">
                                    <x-university-logo class="w-12 h-12 print:w-11 print:h-11" />
                                    <div class="text-right">
                                        <div class="text-[10px] font-bold text-slate-700 leading-tight">الجمهورية اليمنية</div>
                                        <div class="text-xs font-black text-primary leading-tight">جامعة إب</div>
                                        <div class="text-[9.5px] font-bold text-slate-500 leading-tight">نيابة الشؤون الأكاديمية</div>
                                    </div>
                                </div>

                                <!-- الوسط: العنوان الرسمي للإحصائية -->
                                <div class="text-center flex-1 min-w-[280px]">
                                    <h2 class="text-sm sm:text-base font-black text-slate-900 leading-tight print:text-sm">
                                        احصائية بعدد اعضاء هيئة التدريس في الكليات والمراكز بحسب {{ $matrixData['colHeader'] }}
                                    </h2>
                                    <p class="text-[10px] font-mono text-slate-500 mt-0.5">رمز المصفوفة المرجعية: <strong class="text-primary font-bold">{{ strtoupper($matrixCode) }}</strong> | التاريخ: <strong>{{ date('Y/m/d') }}م</strong></p>
                                </div>

                                <!-- الجانب الأيسر: شارة التصفية الرسمية المطابقة للإكسل (مستمر) -->
                                <div class="flex items-center gap-2">
                                    <span class="bg-white border-2 border-black px-3 py-0.5 text-xs font-black shadow-2xs print:px-2.5 print:py-0 print:text-[11px]">
                                        مستمر
                                    </span>
                                </div>
                            </div>

                            @if($matrixCode === 'eh1' && !empty($matrixData['collegeGroups']))
                                <!-- =================== العرض المنظم لإحصائية الأقسام بحسب الكليات (eh1) =================== -->
                                
                                <!-- شريط تصفية واختيار الكلية والمؤشرات السريعة (مخفي عند الطباعة) -->
                                <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-3 flex flex-wrap items-center justify-between gap-3 select-none print:hidden shadow-2xs">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <label for="eh1-college-filter" class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                            <i class="fa-solid fa-filter text-[#14532d]"></i>
                                            <span>عرض أقسام كلية محددة:</span>
                                        </label>
                                        <select id="eh1-college-filter" onchange="filterEh1College(this.value)" 
                                                class="text-xs bg-white border border-slate-300 rounded-lg px-3 py-1.5 font-bold text-slate-800 focus:outline-none focus:border-[#14532d] shadow-2xs">
                                            <option value="all">كافة الكليات والمراكز (عرض شامل منظم)</option>
                                            @foreach($matrixData['collegeGroups'] as $fName => $g)
                                                <option value="college-group-{{ $loop->index }}">
                                                    {{ $fName }} ({{ $g['total'] }} كادر - {{ count($g['departments']) }} قسم)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- مؤشرات إحصائية موجزة -->
                                    <div class="flex items-center gap-2 text-xs flex-wrap">
                                        <span class="bg-white border border-slate-200 px-2.5 py-1 rounded-lg font-bold text-slate-700">
                                            الكليات والمراكز: <strong class="text-[#14532d] font-mono">{{ count($matrixData['collegeGroups']) }}</strong>
                                        </span>
                                        <span class="bg-white border border-slate-200 px-2.5 py-1 rounded-lg font-bold text-slate-700">
                                            الأقسام الأكاديمية: <strong class="text-[#14532d] font-mono">{{ $matrixData['totalDeptsCount'] }}</strong>
                                        </span>
                                        <span class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-3 py-1 rounded-lg font-black">
                                            إجمالي الكادر التدريسي: <strong class="font-mono">{{ $matrixData['grandTotal'] }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <!-- جداول الأقسام المنظمة لكل كلية على حدة -->
                                <div class="space-y-5" id="eh1-colleges-wrapper">
                                    @php
                                        $univProf = 0; $univAssoc = 0; $univAssist = 0; $univLect = 0; $univTa = 0; $univOther = 0;
                                        $univMale = 0; $univFemale = 0;
                                    @endphp

                                    @foreach($matrixData['collegeGroups'] as $fName => $group)
                                        @php
                                            $depts = $group['departments'] ?? [];
                                            $hasStaff = $group['total'] > 0;
                                            $cProf = 0; $cAssoc = 0; $cAssist = 0; $cLect = 0; $cTa = 0; $cOther = 0;
                                        @endphp

                                        <div class="eh1-college-card border border-slate-300 rounded-xl overflow-hidden shadow-2xs bg-white print:border-black print:rounded-none print:shadow-none print:mb-4"
                                             id="college-group-{{ $loop->index }}">
                                            
                                            <!-- ترويسة الكلية مع شارات الإحصاء -->
                                            <div class="bg-slate-100/90 border-b border-slate-300 p-2.5 px-3.5 flex items-center justify-between flex-wrap gap-2 print:bg-slate-200 print:py-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full {{ $hasStaff ? 'bg-emerald-600' : 'bg-slate-400' }} print:hidden"></span>
                                                    <h3 class="font-black text-slate-900 text-xs sm:text-sm print:text-xs">
                                                        {{ $fName }}
                                                    </h3>
                                                </div>
                                                <div class="flex items-center gap-1.5 text-[11px] font-bold">
                                                    <span class="bg-white border border-slate-300 px-2 py-0.5 rounded text-slate-700 print:border-black">
                                                        الأقسام: <strong class="font-mono">{{ count($depts) }}</strong>
                                                    </span>
                                                    <span class="bg-blue-50 border border-blue-200 text-blue-900 px-2 py-0.5 rounded font-mono print:border-black">
                                                        ذكور: {{ $group['male'] }}
                                                    </span>
                                                    <span class="bg-rose-50 border border-rose-200 text-rose-900 px-2 py-0.5 rounded font-mono print:border-black">
                                                        إناث: {{ $group['female'] }}
                                                    </span>
                                                    <span class="bg-[#14532d] text-white px-2.5 py-0.5 rounded font-black font-mono print:bg-black">
                                                        المجموع: {{ $group['total'] }} كادر
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- جدول الأقسام التابعة للكلية -->
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-right text-xs border-collapse">
                                                    <thead>
                                                        <tr class="bg-[#fce4d6] text-black font-black text-center text-[11px] print:text-[10px]">
                                                            <th class="p-1.5 border border-black w-10">م</th>
                                                            <th class="p-1.5 border border-black text-right min-w-[200px]">اسم القسم الأكاديمي</th>
                                                            <th class="p-1.5 border border-black w-18">أستاذ دكتور</th>
                                                            <th class="p-1.5 border border-black w-18">أستاذ مشارك</th>
                                                            <th class="p-1.5 border border-black w-18">أستاذ مساعد</th>
                                                            <th class="p-1.5 border border-black w-16">مدرس</th>
                                                            <th class="p-1.5 border border-black w-16">معيد</th>
                                                            <th class="p-1.5 border border-black w-16">غير محدد</th>
                                                            <th class="p-1.5 border border-black w-16 bg-blue-100/60">ذكور</th>
                                                            <th class="p-1.5 border border-black w-16 bg-rose-100/60">إناث</th>
                                                            <th class="p-1.5 border border-black w-22 bg-[#d9e1f2]">إجمالي القسم</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-200">
                                                        @forelse($depts as $deptName => $d)
                                                            @php
                                                                $cProf += $d['prof'];
                                                                $cAssoc += $d['assoc_prof'];
                                                                $cAssist += $d['assist_prof'];
                                                                $cLect += $d['lecturer'];
                                                                $cTa += $d['ta'];
                                                                $cOther += $d['other_rank'];
                                                            @endphp
                                                            <tr class="hover:bg-amber-50/40 {{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                                                                <td class="p-1 border border-black text-center font-mono font-bold text-slate-600 print:text-black">
                                                                    {{ $loop->iteration }}
                                                                </td>
                                                                <td class="p-1 px-2.5 border border-black font-bold text-slate-900 text-right print:text-black">
                                                                    {{ $deptName }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold {{ $d['prof'] > 0 ? 'text-black' : 'text-slate-300 print:text-black' }}">
                                                                    {{ $d['prof'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold {{ $d['assoc_prof'] > 0 ? 'text-black' : 'text-slate-300 print:text-black' }}">
                                                                    {{ $d['assoc_prof'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold {{ $d['assist_prof'] > 0 ? 'text-black' : 'text-slate-300 print:text-black' }}">
                                                                    {{ $d['assist_prof'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold {{ $d['lecturer'] > 0 ? 'text-black' : 'text-slate-300 print:text-black' }}">
                                                                    {{ $d['lecturer'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold {{ $d['ta'] > 0 ? 'text-black' : 'text-slate-300 print:text-black' }}">
                                                                    {{ $d['ta'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold {{ $d['other_rank'] > 0 ? 'text-black' : 'text-slate-300 print:text-black' }}">
                                                                    {{ $d['other_rank'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold bg-blue-50/40 text-blue-900 print:bg-white print:text-black">
                                                                    {{ $d['male'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-bold bg-rose-50/40 text-rose-900 print:bg-white print:text-black">
                                                                    {{ $d['female'] }}
                                                                </td>
                                                                <td class="p-1 border border-black text-center font-mono font-black bg-[#d9e1f2] text-black">
                                                                    {{ $d['total'] }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="11" class="p-3 text-center text-slate-400 font-bold border border-black">
                                                                    لا توجد أقسام مسجلة تابعة لهذه الكلية حالياً
                                                                </td>
                                                            </tr>
                                                        @endforelse

                                                        <!-- سطر إجمالي الكلية -->
                                                        <tr class="bg-[#d9e1f2] font-black text-black border-t-2 border-black">
                                                            <td colspan="2" class="p-1.5 px-3 border border-black text-right">
                                                                إجمالي {{ $fName }}
                                                            </td>
                                                            <td class="p-1.5 border border-black text-center font-mono">{{ $cProf }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono">{{ $cAssoc }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono">{{ $cAssist }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono">{{ $cLect }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono">{{ $cTa }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono">{{ $cOther }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono bg-blue-100/60 text-blue-950">{{ $group['male'] }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono bg-rose-100/60 text-rose-950">{{ $group['female'] }}</td>
                                                            <td class="p-1.5 border border-black text-center font-mono font-black text-sm text-[#14532d]">{{ $group['total'] }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        @php
                                            $univProf += $cProf;
                                            $univAssoc += $cAssoc;
                                            $univAssist += $cAssist;
                                            $univLect += $cLect;
                                            $univTa += $cTa;
                                            $univOther += $cOther;
                                            $univMale += $group['male'];
                                            $univFemale += $group['female'];
                                        @endphp
                                    @endforeach

                                    <!-- سطر الإجمالي العام للجامعة لكافة الأقسام والكليات -->
                                    <div class="border-2 border-black rounded-xl overflow-hidden shadow-sm bg-white print:border-black print:rounded-none">
                                        <table class="w-full text-right text-xs border-collapse">
                                            <thead>
                                                <tr class="bg-[#14532d] text-white font-black text-center text-xs print:bg-black">
                                                    <th class="p-2 border border-black text-right" style="width: auto;">
                                                        الإجمالي العام لكافة أقسام كليات ومراكز جامعة إب
                                                    </th>
                                                    <th class="p-2 border border-black w-18">أستاذ دكتور</th>
                                                    <th class="p-2 border border-black w-18">أستاذ مشارك</th>
                                                    <th class="p-2 border border-black w-18">أستاذ مساعد</th>
                                                    <th class="p-2 border border-black w-16">مدرس</th>
                                                    <th class="p-2 border border-black w-16">معيد</th>
                                                    <th class="p-2 border border-black w-16">غير محدد</th>
                                                    <th class="p-2 border border-black w-16">ذكور</th>
                                                    <th class="p-2 border border-black w-16">إناث</th>
                                                    <th class="p-2 border border-black w-22 bg-[#0f3d21] text-amber-200 text-sm">الإجمالي العام</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="bg-emerald-50 font-black text-black text-center text-xs print:bg-slate-200">
                                                    <td class="p-2 border border-black text-right font-black">
                                                        المجموع الكلي المعتمد ({{ count($matrixData['collegeGroups']) }} جهة أكاديمية)
                                                    </td>
                                                    <td class="p-2 border border-black font-mono text-sm">{{ $univProf }}</td>
                                                    <td class="p-2 border border-black font-mono text-sm">{{ $univAssoc }}</td>
                                                    <td class="p-2 border border-black font-mono text-sm">{{ $univAssist }}</td>
                                                    <td class="p-2 border border-black font-mono text-sm">{{ $univLect }}</td>
                                                    <td class="p-2 border border-black font-mono text-sm">{{ $univTa }}</td>
                                                    <td class="p-2 border border-black font-mono text-sm">{{ $univOther }}</td>
                                                    <td class="p-2 border border-black font-mono text-sm text-blue-900">{{ $univMale }}</td>
                                                    <td class="p-2 border border-black font-mono text-sm text-rose-900">{{ $univFemale }}</td>
                                                    <td class="p-2 border border-black font-mono font-black text-base bg-[#d9e1f2] text-[#14532d]">{{ $matrixData['grandTotal'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <script>
                                    function filterEh1College(val) {
                                        const cards = document.querySelectorAll('.eh1-college-card');
                                        if (val === 'all') {
                                            cards.forEach(c => c.style.display = 'block');
                                        } else {
                                            cards.forEach(c => {
                                                if (c.id === val) {
                                                    c.style.display = 'block';
                                                } else {
                                                    c.style.display = 'none';
                                                }
                                            });
                                        }
                                    }
                                </script>

                            @else
                                <!-- 1. جدول المصفوفة الشبكي المعتمد المطابق للإكسل 1:1 لمصفوفات eh2 - eh8 -->
                                <div class="overflow-x-auto pb-1 print:pb-0">
                                    <table class="w-full text-right text-xs border-collapse border border-black shadow-2xs matrix-table">
                                        <thead>
                                            <tr>
                                                <!-- العمود الأول من اليمين: رأس مشترك للتصنيف والجهة -->
                                                <th class="p-1.5 border border-black bg-[#fce4d6] text-black font-black text-center min-w-[190px] print:min-w-0 print:py-1">
                                                    <div class="text-[10.5px] font-black print:text-[10px]">{{ $matrixData['colHeader'] }}</div>
                                                    <div class="border-t border-black my-0.5"></div>
                                                    <div class="text-[10.5px] font-black print:text-[10px]">{{ $matrixData['rowHeader'] }}</div>
                                                </th>

                                                <!-- أعمدة التصنيفات باللون البيج الأصلي للإكسل #fce4d6 -->
                                                @foreach($matrixData['columns'] as $col)
                                                    <th class="p-1.5 border border-black bg-[#fce4d6] text-black font-black text-center text-xs print:text-[10px] print:py-1 min-w-[65px] print:min-w-0">
                                                        {{ $col }}
                                                    </th>
                                                @endforeach

                                                <!-- عمود الإجمالي على أقصى اليسار باللون الأزرق الفاتح #d9e1f2 -->
                                                <th class="p-1.5 border border-black bg-[#d9e1f2] text-black font-black text-center text-xs print:text-[10px] print:py-1 min-w-[70px] print:min-w-0">
                                                    الإجمالي
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($matrixData['faculties'] as $f)
                                                @php
                                                    $fName = is_object($f) ? $f->unit_name_ar : (string)$f;
                                                    $rTotal = $matrixData['rowTotals'][$fName] ?? 0;
                                                @endphp
                                                <tr class="hover:bg-amber-50/40">
                                                    <!-- اسم الكلية / المركز -->
                                                    <td class="p-1 px-2.5 border border-black font-bold text-black text-right text-xs print:text-[9.5px] print:py-0.5 bg-white">
                                                        {{ $fName }}
                                                    </td>

                                                    <!-- خلايا البيانات المتوافقة مع الإكسل -->
                                                    @foreach($matrixData['columns'] as $col)
                                                        @php $val = $matrixData['matrix'][$fName][$col] ?? 0; @endphp
                                                        <td class="p-1 border border-black text-center font-mono font-black text-xs print:text-[9.5px] print:py-0.5 bg-white text-black">
                                                            {{ $val }}
                                                        </td>
                                                    @endforeach

                                                    <!-- إجمالي الصف باللون الأزرق الفاتح #d9e1f2 -->
                                                    <td class="p-1 border border-black text-center font-mono font-black text-xs print:text-[9.5px] print:py-0.5 bg-[#d9e1f2] text-black">
                                                        {{ $rTotal }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                            <!-- سطر الإجمالي العام في قاع الجدول باللون الأزرق الفاتح #d9e1f2 كاملاً -->
                                            <tr class="bg-[#d9e1f2] font-black text-black border-t-2 border-black">
                                                <td class="p-1.5 px-2.5 border border-black text-center font-black text-xs print:text-[10px] print:py-1 bg-[#d9e1f2] text-black">
                                                    الإجمالي العام
                                                </td>
                                                @foreach($matrixData['columns'] as $col)
                                                    @php $cTotal = $matrixData['colTotals'][$col] ?? 0; @endphp
                                                    <td class="p-1.5 border border-black text-center font-mono font-black text-xs print:text-[10px] print:py-1 bg-[#d9e1f2] text-black">
                                                        {{ $cTotal }}
                                                    </td>
                                                @endforeach
                                                <td class="p-1.5 border border-black text-center font-mono font-black text-sm print:text-[11px] bg-[#d9e1f2] text-black">
                                                    {{ $matrixData['grandTotal'] }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- تذييل التوقيعات لصفحة الجدول عند الطباعة -->
                            <div class="mt-4 pt-2 border-t border-slate-400 grid grid-cols-3 gap-4 text-center text-[10px] font-bold select-none print:mt-3 print:pt-1.5">
                                <div class="space-y-4 print:space-y-3">
                                    <p>إعداد / أخصائي الإحصاء</p>
                                    <p class="text-slate-400 font-mono">........................</p>
                                </div>
                                <div class="space-y-4 print:space-y-3">
                                    <p>مدير عام الشؤون الأكاديمية</p>
                                    <p class="text-slate-400 font-mono">........................</p>
                                </div>
                                <div class="space-y-4 print:space-y-3">
                                    <p>رئيس الجامعة / نائب رئيس الجامعة</p>
                                    <p class="text-slate-400 font-mono">........................</p>
                                </div>
                            </div>
                        </div>

                        <!-- =================== الصفحة الثانية: ورقة الرسم البياني A4 منفصلة بالعرض =================== -->
                        <div id="matrix-chart-container" class="print-chart-sheet mt-6 pt-4 border-t-2 border-slate-300 print:mt-0 print:pt-0 print:border-none">
                            
                            <!-- ترويسة التقرير الرسمية لصفحة المخطط البياني في الطباعة -->
                            <div class="border-b-2 border-slate-700 pb-2 mb-3 hidden print:flex items-center justify-between select-none print:pb-1.5 print:mb-2">
                                <div class="flex items-center gap-2.5">
                                    <x-university-logo class="w-12 h-12 print:w-11 print:h-11" />
                                    <div class="text-right">
                                        <div class="text-[10px] font-bold text-slate-700 leading-tight">الجمهورية اليمنية</div>
                                        <div class="text-xs font-black text-primary leading-tight">جامعة إب</div>
                                        <div class="text-[9.5px] font-bold text-slate-500 leading-tight">نيابة الشؤون الأكاديمية</div>
                                    </div>
                                </div>
                                <div class="text-center flex-1">
                                    <h2 class="text-sm font-black text-slate-900 leading-tight">
                                        المخطط البياني لإحصائية أعضاء هيئة التدريس بالكليات والمراكز بحسب {{ $matrixData['colHeader'] }}
                                    </h2>
                                    <p class="text-[10px] font-mono text-slate-500 mt-0.5">رمز المصفوفة المرجعية: <strong class="text-primary font-bold">{{ strtoupper($matrixCode) }}</strong> | التاريخ: <strong>{{ date('Y/m/d') }}م</strong></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="bg-white border-2 border-black px-3 py-0.5 text-xs font-black">مستمر</span>
                                </div>
                            </div>

                            <div class="bg-white p-3 border border-slate-300 rounded shadow-xs print:border-none print:p-0">
                                <h3 class="text-sm font-black text-slate-900 text-center mb-2 print:hidden">
                                    رسم بياني لاعداد اعضاء هيئة التدريس بالكليات والمراكز بحسب {{ $matrixData['colHeader'] }}
                                </h3>
                                <div class="h-[460px] print:h-[135mm] w-full">
                                    <canvas id="chart-matrix-clustered"></canvas>
                                </div>
                            </div>

                            <!-- تذييل التوقيعات لصفحة الرسم البياني عند الطباعة -->
                            <div class="mt-4 pt-2 border-t border-slate-400 grid grid-cols-3 gap-4 text-center text-[10px] font-bold select-none print:mt-3 print:pt-1.5">
                                <div class="space-y-4 print:space-y-3">
                                    <p>إعداد / أخصائي الإحصاء</p>
                                    <p class="text-slate-400 font-mono">........................</p>
                                </div>
                                <div class="space-y-4 print:space-y-3">
                                    <p>مدير عام الشؤون الأكاديمية</p>
                                    <p class="text-slate-400 font-mono">........................</p>
                                </div>
                                <div class="space-y-4 print:space-y-3">
                                    <p>رئيس الجامعة / نائب رئيس الجامعة</p>
                                    <p class="text-slate-400 font-mono">........................</p>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
{{-- تكامل مكتبة Chart.js وإضافة DataLabels لكافة تقارير المصفوفات (star1 و eh1 إلى eh8) --}}
@if($reportType === 'matrices')
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
    <script>
        // دالة التبديل بين عرض الجدول والمخطط البياني في تقارير المصفوفات
        function toggleMatrixView(mode) {
            const tbl = document.getElementById('matrix-table-container');
            const chrt = document.getElementById('matrix-chart-container');
            const btnBoth = document.getElementById('btn-view-both');
            const btnTbl = document.getElementById('btn-view-table');
            const btnChrt = document.getElementById('btn-view-chart');

            [btnBoth, btnTbl, btnChrt].forEach(b => {
                if (b) {
                    b.className = 'px-2 py-0.5 rounded font-bold transition text-slate-600 hover:text-primary cursor-pointer';
                }
            });

            if (mode === 'table') {
                if (tbl) tbl.classList.remove('hidden');
                if (chrt) chrt.classList.add('hidden');
                if (btnTbl) btnTbl.className = 'px-2 py-0.5 rounded font-bold transition bg-primary text-white shadow-2xs cursor-pointer';
            } else if (mode === 'chart') {
                if (tbl) tbl.classList.add('hidden');
                if (chrt) chrt.classList.remove('hidden');
                if (btnChrt) btnChrt.className = 'px-2 py-0.5 rounded font-bold transition bg-primary text-white shadow-2xs cursor-pointer';
            } else {
                if (tbl) tbl.classList.remove('hidden');
                if (chrt) chrt.classList.remove('hidden');
                if (btnBoth) btnBoth.className = 'px-2 py-0.5 rounded font-bold transition bg-primary text-white shadow-2xs cursor-pointer';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            // تسجيل إضافة كتابة الأرقام أعلى الأعمدة كما في إكسل (DataLabels)
            if (typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
            }

            @if($matrixCode === 'star1')
                const commonBarOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            offset: 1,
                            font: { weight: 'bold', size: 9 },
                            color: '#1e293b',
                            formatter: function(val) { return val > 0 ? val : ''; }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { font: { size: 9 }, stepSize: 5 },
                            grid: { color: '#e2e8f0' }
                        },
                        x: {
                            ticks: { font: { size: 8.5, weight: 'bold' }, maxRotation: 45, minRotation: 0 },
                            grid: { display: false }
                        }
                    }
                };

                // 1. رسم الجنس (Pie Chart)
                const genderCtx = document.getElementById('chart-gender');
                if (genderCtx) {
                    new Chart(genderCtx, {
                        type: 'pie',
                        data: {
                            labels: {!! json_encode(array_keys($matrixData['genderStats'] ?? [])) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($matrixData['genderStats'] ?? [])) !!},
                                backgroundColor: ['#4f81bd', '#c0504d'],
                                borderWidth: 1,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9, weight: 'bold' } } },
                                datalabels: {
                                    color: '#ffffff',
                                    font: { weight: 'bold', size: 10 },
                                    formatter: function(val) { return val; }
                                }
                            }
                        }
                    });
                }

                // 2. رسم الدرجة الحالية (Bar Chart)
                const rankCtx = document.getElementById('chart-rank');
                if (rankCtx) {
                    new Chart(rankCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_keys($matrixData['rankStats'] ?? [])) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($matrixData['rankStats'] ?? [])) !!},
                                backgroundColor: '#ea580c',
                                borderRadius: 2
                            }]
                        },
                        options: commonBarOptions
                    });
                }

                // 3. رسم الموقف الوظيفي (Bar Chart)
                const aptCtx = document.getElementById('chart-appointment');
                if (aptCtx) {
                    new Chart(aptCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_keys($matrixData['appointmentBasisStats'] ?? [])) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($matrixData['appointmentBasisStats'] ?? [])) !!},
                                backgroundColor: '#7e22ce',
                                borderRadius: 2
                            }]
                        },
                        options: commonBarOptions
                    });
                }

                // 4. رسم الحالة بكشف الراتب (Bar Chart داخل الصندوق الأخضر)
                const payrollCtx = document.getElementById('chart-payroll');
                if (payrollCtx) {
                    new Chart(payrollCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_keys($matrixData['payrollStats'] ?? [])) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($matrixData['payrollStats'] ?? [])) !!},
                                backgroundColor: '#dc2626',
                                borderRadius: 2
                            }]
                        },
                        options: commonBarOptions
                    });
                }

                // 5. رسم المؤهل العلمي (Bar Chart)
                const qualCtx = document.getElementById('chart-qualification');
                if (qualCtx) {
                    new Chart(qualCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_keys($matrixData['qualificationStats'] ?? [])) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($matrixData['qualificationStats'] ?? [])) !!},
                                backgroundColor: '#991b1b',
                                borderRadius: 2
                            }]
                        },
                        options: commonBarOptions
                    });
                }

                // 6. رسم الحالة بشؤون أكاديمية (Bar Chart)
                const acadCtx = document.getElementById('chart-academic');
                if (acadCtx) {
                    new Chart(acadCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_keys($matrixData['academicStatusStats'] ?? [])) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($matrixData['academicStatusStats'] ?? [])) !!},
                                backgroundColor: '#d97706',
                                borderRadius: 2
                            }]
                        },
                        options: commonBarOptions
                    });
                }
            @else
                // رسم المخطط الشريطي لمصفوفات الكليات (eh1 إلى eh8) المطابق للصورة 2
                const matrixChartCtx = document.getElementById('chart-matrix-clustered');
                if (matrixChartCtx) {
                    new Chart(matrixChartCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($matrixData['chartLabels'] ?? []) !!},
                            datasets: {!! json_encode($matrixData['chartDatasets'] ?? []) !!}
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'left',
                                    labels: {
                                        boxWidth: 12,
                                        font: { size: 10, weight: 'bold' },
                                        padding: 8
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    offset: 1,
                                    font: { weight: 'bold', size: 9 },
                                    color: '#000000',
                                    formatter: function(val) {
                                        return val > 0 ? val : '';
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: { size: 10, weight: 'bold' }
                                    },
                                    grid: { color: '#e2e8f0' }
                                },
                                x: {
                                    ticks: {
                                        font: { size: 9, weight: 'bold' },
                                        maxRotation: 45,
                                        minRotation: 20
                                    },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                }
            @endif
        });
    </script>
@endif
