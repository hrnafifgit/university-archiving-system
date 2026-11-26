<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'جامعة إب - نظام إدارة وأرشفة الكادر')</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/official_web_logo.png') }}">

    <!-- خط Cairo المعتمد -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d', // الأخضر الداكن المعتمد
                            950: '#052e16',
                        },
                        primary: '#14532d',
                        'primary-dark': '#052e16',
                        'primary-light': '#166534',
                        'primary-surface': '#f0fdf4',
                        gold: '#14532d',
                        dark: '#1e293b',
                        muted: '#64748b',
                    },
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                        cairo: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        html { font-size: 16px; height: 100%; }
        html.compact-mode { font-size: 14px !important; }
        body { font-family: 'Cairo', sans-serif; background-color: #f8fafc; color: #1e293b; }
        
        #report-preview-container, .allow-zoom, [data-allow-zoom] {
            touch-action: pinch-zoom pan-x pan-y;
        }
        input, select, textarea {
            font-family: 'Cairo', sans-serif;
            border-radius: 0.625rem !important;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .no-scrollbar::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
        .no-scrollbar { -ms-overflow-style: none !important; scrollbar-width: none !important; }

        @media print {
            body { background: #ffffff !important; overflow: visible !important; height: auto !important; }
            header, aside, footer, .print\:hidden, #globalQuickAddModal { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; background: transparent !important; overflow: visible !important; height: auto !important; }
            @page { size: auto; margin: 6mm 8mm; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }

        /* تحسينات حركة القائمة الجانبية (Sidebar) */
        #mainAppSidebar {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), min-width 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;
            width: 15rem;
            min-width: 15rem;
            max-width: 15rem;
            overflow: hidden !important;
            overflow-x: hidden !important;
        }
        #mainAppSidebar.sidebar-closed {
            width: 0 !important;
            min-width: 0 !important;
            opacity: 0 !important;
            overflow: hidden !important;
            border-left: none !important;
            pointer-events: none !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden leading-relaxed antialiased">

    <!-- زر عائم لإعادة فتح القائمة الجانبية عند طيها بالكامل -->
    <button type="button" id="sidebarFloatingOpenBtn" onclick="toggleMainSidebar()" 
            class="hidden fixed top-3 right-3 z-50 bg-white/95 backdrop-blur-xs border border-slate-200 hover:border-brand-900 text-brand-900 hover:bg-brand-50 p-2.5 rounded-2xl shadow-md transition-all flex items-center gap-2 cursor-pointer group" 
            title="إظهار القائمة الجانبية">
        <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-6 w-auto object-contain">
        <i class="fa-solid fa-bars text-sm text-brand-900"></i>
    </button>

    <!-- جسم التطبيق الحاوي: القائمة الجانبية + مساحة العمل الرئيسية تمتد لكامل الشاشة -->
    <div class="flex flex-1 overflow-hidden min-h-0 relative h-full">
        
        <!-- القائمة الجانبية (Sidebar) تضم الشعار وروابط الملاحة وأدوات المستخدم المدمجة - ثابتة وبدون أي تمرير جانبي -->
        <aside id="mainAppSidebar" class="bg-white border-l border-slate-200 flex flex-col justify-between shrink-0 z-30 select-none shadow-xs h-full overflow-hidden overflow-x-hidden">
            
            <!-- ترويسة القائمة: الشعار وزر الطي الداخلي فقط بدون نصوص، ثابتة تماماً بدون تمرير -->
            <div class="h-16 px-3.5 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center" title="جامعة إب">
                    <img src="{{ asset('images/official_web_logo.png') }}" alt="جامعة إب" class="h-10 w-auto object-contain">
                </a>
                <button type="button" onclick="toggleMainSidebar()" class="text-slate-400 hover:text-brand-900 hover:bg-slate-100 p-2 rounded-xl transition cursor-pointer" title="طي القائمة الجانبية">
                    <i class="fa-solid fa-angles-right text-xs"></i>
                </button>
            </div>

            <!-- روابط الملاحة الرأسية (تمرير رأسي فقط مع منع أي تمرير أفقي نهائياً) -->
            <div class="flex flex-col flex-1 overflow-y-auto overflow-x-hidden no-scrollbar">
                <nav class="p-3 space-y-1.5 font-bold text-sm overflow-x-hidden">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="لوحة التحكم">
                        <i class="fa-solid fa-chart-pie ml-3 text-base {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>الرئيسية</span>
                    </a>

                    <a href="{{ route('employees.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('employees.*') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="دليل الكادر الأكاديمي والإداري">
                        <i class="fa-solid fa-users ml-3 text-base {{ request()->routeIs('employees.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>الكادر</span>
                    </a>

                    <a href="{{ route('decisions.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('decisions.*') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="القرارات الإدارية والإجازات">
                        <i class="fa-solid fa-stamp ml-3 text-base {{ request()->routeIs('decisions.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>القرارات</span>
                    </a>

                    <a href="{{ route('reports.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('reports.*') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="مركز التقارير والكشوفات">
                        <i class="fa-solid fa-file-contract ml-3 text-base {{ request()->routeIs('reports.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>التقارير</span>
                    </a>

                    <a href="{{ route('archive.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('archive.*') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="الأرشيف والملفات الرقمية">
                        <i class="fa-solid fa-folder-tree ml-3 text-base {{ request()->routeIs('archive.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>الأرشيف</span>
                    </a>

                    <a href="{{ route('master_data.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('master_data.*') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="الهيكل الأكاديمي والبيانات المرجعية">
                        <i class="fa-solid fa-sitemap ml-3 text-base {{ request()->routeIs('master_data.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>الهيكل</span>
                    </a>

                    <a href="{{ route('workflow.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('workflow.*') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="مسار الاعتماد والتأكيد المتسلسل">
                        <i class="fa-solid fa-clipboard-check ml-3 text-base {{ request()->routeIs('workflow.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>الاعتمادات</span>
                    </a>

                    <a href="{{ route('security.roles.index') }}" 
                       class="flex items-center px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap {{ request()->routeIs('security.*') ? 'bg-brand-900 text-white shadow-xs' : 'text-slate-600 hover:text-brand-900 hover:bg-slate-100' }}" 
                       title="الأدوار والصلاحيات التفصيلية">
                        <i class="fa-solid fa-user-shield ml-3 text-base {{ request()->routeIs('security.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>الصلاحيات</span>
                    </a>
                </nav>
            </div>

            <!-- أسفل القائمة الجانبية: أدوات المستخدم وأيقونات مدمجة متداولة -->
            <div class="p-3 border-t border-slate-100 bg-slate-50/70 shrink-0 overflow-hidden">
                <div class="flex items-center justify-between gap-1">
                    <div class="flex items-center gap-2 overflow-hidden" title="{{ session('logged_username', 'admin') }}">
                        <div class="w-8 h-8 rounded-xl bg-brand-900 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                            <i class="fa-solid fa-user text-xs"></i>
                        </div>
                        <div class="overflow-hidden leading-tight">
                            <p class="text-xs font-bold text-slate-800 truncate font-mono">{{ session('logged_username', 'admin') }}</p>
                            <span class="text-[10px] text-emerald-700 font-bold flex items-center gap-1 truncate">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate">{{ session('logged_role_name', 'مدير نظام') }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- أزرار الأدوات بأيقونات متداولة بدون نصوص مكررة -->
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- زر تبديل الكثافة مريح/مضغوط -->
                        <button type="button" onclick="toggleDensityMode()" id="densityToggleBtn" 
                                class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 flex items-center justify-center text-xs transition cursor-pointer" 
                                title="التبديل بين الحجم المريح والمضغوط">
                            <i class="fa-solid fa-text-height text-[11px] text-brand-900"></i>
                        </button>

                        <!-- رابط بوابة الجامعة -->
                        <a href="https://www.ibbuniv.edu.ye" target="_blank" rel="noopener" 
                           class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 flex items-center justify-center text-xs transition" 
                           title="بوابة جامعة إب الرسمية">
                            <i class="fa-solid fa-globe text-[11px] text-slate-500"></i>
                        </a>

                        <!-- زر تسجيل الخروج -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-rose-50 hover:border-rose-200 text-slate-500 hover:text-rose-600 flex items-center justify-center text-xs transition cursor-pointer" title="تسجيل الخروج">
                                <i class="fa-solid fa-power-off text-[11px]"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- مساحة العمل الرئيسية النظيفة (أبيض ورمادي فاتح ومسافات رحبة) -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-6 flex flex-col min-h-0 bg-[#f8fafc]">
        @if(session('success'))
            <div class="bg-brand-50 border-r-4 border-brand-900 text-brand-950 px-3 py-2 rounded-xl text-xs font-bold mb-2 flex items-center gap-2 shadow-xs shrink-0">
                <i class="fa-solid fa-circle-check text-brand-900 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-r-4 border-rose-600 text-rose-950 px-3 py-2 rounded-xl text-xs font-bold mb-2 flex items-center gap-2 shadow-xs shrink-0">
                <i class="fa-solid fa-circle-exclamation text-rose-700 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="bg-rose-50 border-r-4 border-rose-600 text-rose-950 p-3 rounded-xl text-xs font-bold mb-2 shadow-xs shrink-0">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-rose-700 text-sm"></i>
                    <span>تنبيه: تعذر إتمام العملية بسبب الأخطاء التالية:</span>
                </div>
                <ul class="list-disc list-inside text-[11px] text-rose-800 pr-4 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
    </div>

    <script>
        // نافذة الإضافة السريعة للتصنيفات من أي قائمة منسدلة (+) - الحفاظ على كامل المنطق الأصلي
        window.openQuickAdd = function(entityType, targetSelectId, labelText, titleText, parentId = null) {
            const modal = document.getElementById('globalQuickAddModal');
            if (!modal) return;
            document.getElementById('quickAddType').value = entityType || '';
            document.getElementById('quickAddTargetSelectId').value = targetSelectId || '';
            document.getElementById('quickAddParentId').value = parentId || '';
            document.getElementById('quickAddLabel').innerText = labelText || 'الاسم الجديد:';
            document.getElementById('quickAddTitle').innerHTML = `<i class="fa-solid fa-plus-circle text-brand-900"></i> ${titleText || 'إضافة جديدة'}`;
            const input = document.getElementById('quickAddInput');
            input.value = '';
            const errBox = document.getElementById('quickAddError');
            if (errBox) errBox.classList.add('hidden');

            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            setTimeout(() => input.focus(), 100);
        };

        window.closeQuickAdd = function() {
            const modal = document.getElementById('globalQuickAddModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
            const targetSelectId = document.getElementById('quickAddTargetSelectId')?.value;
            if (targetSelectId) {
                const targetSelect = document.getElementById(targetSelectId);
                if (targetSelect && targetSelect.value === '__add_new__') {
                    targetSelect.value = targetSelect.dataset.previousValue || '';
                }
            }
        };

        window.submitQuickAdd = function(e) {
            e.preventDefault();
            const type = document.getElementById('quickAddType').value;
            const targetSelectId = document.getElementById('quickAddTargetSelectId').value;
            let parentId = document.getElementById('quickAddParentId').value;
            const name = document.getElementById('quickAddInput').value.trim();
            const btn = document.getElementById('quickAddSubmitBtn');
            const errBox = document.getElementById('quickAddError');

            if (!name) return;

            if (type === 'department' && !parentId) {
                const parentSelect = document.getElementById('desktop_faculty_select') || document.getElementById('faculty_select') || document.getElementById('college_id') || document.getElementById('parent_unit_id');
                if (parentSelect && parentSelect.value && parentSelect.value !== '__add_new__') {
                    parentId = parentSelect.value;
                }
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...';
            if (errBox) errBox.classList.add('hidden');

            let url = '';
            let payload = { name: name };

            if (type === 'faculty') {
                url = '{{ route("quick.add.unit") }}';
                payload.type = 'faculty';
            } else if (type === 'department') {
                url = '{{ route("quick.add.unit") }}';
                payload.type = 'department';
                payload.parent_id = parentId;
            } else if (type === 'rank') {
                url = '{{ route("quick.add.rank") }}';
            } else if (type === 'grade') {
                url = '{{ route("quick.add.grade") }}';
            } else if (type === 'status') {
                url = '{{ route("quick.add.status") }}';
            } else if (type === 'doc_type') {
                url = '{{ route("quick.add.doctype") }}';
            } else if (type === 'qual_level') {
                url = '{{ route("quick.add.quallevel") }}';
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> حفظ وإدراج';
                if (data.success) {
                    const select = document.getElementById(targetSelectId);
                    if (select) {
                        const opt = document.createElement('option');
                        opt.value = data.id;
                        opt.text = data.name;
                        opt.selected = true;
                        if (type === 'department' && parentId) {
                            opt.setAttribute('data-parent', parentId);
                        }
                        const addNewOpt = select.querySelector('option[value="__add_new__"]');
                        if (addNewOpt) {
                            select.insertBefore(opt, addNewOpt);
                        } else {
                            select.appendChild(opt);
                        }
                        select.dataset.previousValue = data.id;
                        select.dispatchEvent(new Event('change'));
                    }
                    closeQuickAdd();
                } else {
                    if (errBox) {
                        errBox.innerText = data.message || 'حدث خطأ أثناء الحفظ.';
                        errBox.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> حفظ وإدراج';
                if (errBox) {
                    errBox.innerText = 'فشل الاتصال: ' + err.message;
                    errBox.classList.remove('hidden');
                }
            });
        };
    </script>

    <!-- نافذة الإضافة السريعة للتصنيفات والقوائم المنسدلة (Quick Add Modal) - مظهر أبيض وأخضر داكن -->
    <div id="globalQuickAddModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 select-none" style="display: none; z-index: 99999;">
        <div class="bg-white border border-slate-200 w-full max-w-sm rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-white border-b border-slate-100 px-4 py-3 flex items-center justify-between">
                <span id="quickAddTitle" class="font-bold text-xs text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-brand-900"></i> إضافة تصنيف جديد
                </span>
                <button type="button" onclick="closeQuickAdd()" class="text-slate-400 hover:text-slate-700 font-bold text-lg cursor-pointer transition">&times;</button>
            </div>
            <form id="quickAddForm" onsubmit="submitQuickAdd(event)" class="p-4 text-xs space-y-3">
                <input type="hidden" id="quickAddType">
                <input type="hidden" id="quickAddTargetSelectId">
                <input type="hidden" id="quickAddParentId">
                
                <div>
                    <label id="quickAddLabel" class="block font-semibold mb-1 text-slate-700">اسم العنصر الجديد:</label>
                    <input type="text" id="quickAddInput" required class="w-full border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:border-brand-900 text-xs font-semibold rounded-xl" placeholder="اكتب الاسم هنا...">
                </div>

                <div id="quickAddError" class="hidden text-rose-700 bg-rose-50 border border-rose-200 p-2 rounded-xl text-[11px]"></div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeQuickAdd()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer">إلغاء</button>
                    <button type="submit" id="quickAddSubmitBtn" class="bg-brand-900 hover:bg-brand-800 text-white px-4 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-check"></i> حفظ وإدراج
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- نظام منع التكبير والتصغير المكتبي العام -->
    <script>
        (function() {
            window.addEventListener('wheel', function(e) {
                if (e.ctrlKey) {
                    const localZoomArea = e.target.closest('#report-preview-container, .allow-zoom, [data-allow-zoom]');
                    if (!localZoomArea) { e.preventDefault(); }
                }
            }, { passive: false });

            window.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    const key = e.key;
                    if (key === '+' || key === '-' || key === '=' || key === '_' || key === '0' || e.code === 'NumpadAdd' || e.code === 'NumpadSubtract') {
                        e.preventDefault();
                        if (document.getElementById('report-preview-container')) {
                            if (typeof window.zoomStep === 'function') {
                                if (key === '+' || key === '=' || e.code === 'NumpadAdd') { window.zoomStep(0.05); }
                                else if (key === '-' || key === '_' || e.code === 'NumpadSubtract') { window.zoomStep(-0.05); }
                            }
                            if (key === '0' && typeof window.setZoom === 'function') { window.setZoom(0.90); }
                        }
                    }
                }
            }, { passive: false });
        })();

        function toggleDensityMode() {
            const isCompact = document.documentElement.classList.toggle('compact-mode');
            localStorage.setItem('uas_density_mode', isCompact ? 'compact' : 'comfortable');
            updateDensityBtn();
        }
        function updateDensityBtn() {
            const isCompact = document.documentElement.classList.contains('compact-mode');
            const txt = document.getElementById('densityToggleText');
            if (txt) {
                txt.textContent = isCompact ? 'مضغوط' : 'مريح';
            }
        }
        if (localStorage.getItem('uas_density_mode') === 'compact') {
            document.documentElement.classList.add('compact-mode');
        }
        document.addEventListener('DOMContentLoaded', updateDensityBtn);

        if (window.lucide) {
            lucide.createIcons();
        }

        // التحكم في طي وإظهار القائمة الجانبية عند النقر على شعار جامعة إب
        function toggleMainSidebar() {
            const sidebar = document.getElementById('mainAppSidebar');
            if (!sidebar) return;
            const isClosed = sidebar.classList.toggle('sidebar-closed');
            localStorage.setItem('uas_main_sidebar_closed', isClosed ? 'true' : 'false');
            updateSidebarUI(isClosed);
        }

        function updateSidebarUI(isClosed) {
            const floatBtn = document.getElementById('sidebarFloatingOpenBtn');
            if (floatBtn) {
                if (isClosed) {
                    floatBtn.classList.remove('hidden');
                } else {
                    floatBtn.classList.add('hidden');
                }
            }
        }

        // استعادة حالة القائمة الجانبية المحفوظة
        (function() {
            const savedState = localStorage.getItem('uas_main_sidebar_closed');
            if (savedState === 'true') {
                const sidebar = document.getElementById('mainAppSidebar');
                if (sidebar) {
                    sidebar.classList.add('sidebar-closed');
                    updateSidebarUI(true);
                }
            } else {
                updateSidebarUI(false);
            }
        })();

        // التحقق الشامل من حجم الملفات المرفوعة قبل الإرسال
        window.validateFileSize = function(input, maxMB) {
            if (input && input.files && input.files[0]) {
                const file = input.files[0];
                const fileSizeMB = file.size / (1024 * 1024);
                if (fileSizeMB > maxMB) {
                    alert('تنبيه: حجم الملف المحدد (' + fileSizeMB.toFixed(2) + ' ميجابايت) يتجاوز الحد الأقصى المسموح به (' + maxMB + ' ميجابايت).\nيرجى اختيار ملف أصغر حجماً.');
                    input.value = '';
                    return false;
                }
            }
            return true;
        };
    </script>

    @yield('scripts')
</body>
</html>
