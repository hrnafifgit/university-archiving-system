@extends('layouts.app')

@section('title', 'جامعة إب - مركز التقارير والإحصائيات الرسمي')

@section('content')
<div class="flex-1 flex flex-col min-h-0 gap-1 pb-0">

    <!-- شريط التحكم والتبديل المكتبي الموحد عالي الكثافة (Single Merged Desktop Toolbar) -->
    <div class="bg-white border border-slate-200 p-3 px-4 flex flex-wrap items-center justify-between gap-3 shadow-xs shrink-0 rounded-2xl select-none print:hidden">
        
        <!-- التبويبات الفرعية للتقارير -->
        <div class="flex items-center gap-2 text-sm">
            <!-- 1. استمارة بيان الحالة -->
            <a href="{{ route('reports.index', ['type' => 'individual']) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ $reportType === 'individual' ? 'bg-[#14532d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
               title="بيان الحالة الفردية للكادر التدريسي">
                <i class="fa-solid fa-id-card text-sm"></i>
                <span>بيان الحالة</span>
            </a>

            <!-- 2. كشف الكادر الشامل -->
            <a href="{{ route('reports.index', ['type' => 'roster_19']) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ $reportType === 'roster_19' ? 'bg-[#14532d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
               title="كشف الكادر الشامل المفلتر">
                <i class="fa-solid fa-table-list text-sm"></i>
                <span>كشف الكادر الشامل</span>
            </a>

            <!-- 3. تقرير مخصص -->
            <a href="{{ route('reports.index', ['type' => 'custom_builder']) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ $reportType === 'custom_builder' ? 'bg-[#14532d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
               title="تقرير مخصص حسب اختيار الأعمدة">
                <i class="fa-solid fa-sliders text-sm"></i>
                <span>تقرير مخصص</span>
            </a>

            <!-- 4. تقارير الإحصائيات -->
            <a href="{{ route('reports.index', ['type' => 'matrices', 'matrix' => 'eh2']) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ $reportType === 'matrices' ? 'bg-[#14532d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
               title="تقارير الإحصائيات والمصفوفات البيانية">
                <i class="fa-solid fa-chart-pie text-sm"></i>
                <span>تقارير الإحصائيات</span>
            </a>
        </div>

        <!-- أزرار الإجراءات السريعة (تصدير وطباعة) -->
        <div class="flex items-center gap-2.5 text-sm shrink-0">
            @if($reportType === 'matrices' && $matrixCode !== 'star1')
                <a href="{{ route('reports.matrix.export', ['matrix' => $matrixCode]) }}"
                   class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-xl text-sm transition flex items-center gap-2 shadow-2xs cursor-pointer border border-emerald-800">
                    <i class="fa-solid fa-file-excel text-emerald-100 text-sm"></i> <span>تصدير إكسل</span>
                </a>
            @elseif($reportType === 'roster_19' || $reportType === 'custom_builder')
                <a href="{{ route('reports.export') }}"
                   class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-xl text-sm transition flex items-center gap-2 shadow-2xs cursor-pointer border border-emerald-800">
                    <i class="fa-solid fa-file-excel text-emerald-100 text-sm"></i> <span>تصدير إكسل</span>
                </a>
            @endif

            <button onclick="window.print()"
                    class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-4 py-2 rounded-xl text-sm transition flex items-center gap-2 shadow-2xs cursor-pointer border border-[#14532d]">
                <i class="fa-solid fa-print text-white text-sm"></i>
                <span>طباعة رسمية</span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- تفريغ النماذج حسب التبويب المحدد عبر قوالب مجزأة ومنظمة --}}
    @if($reportType === 'individual')
        @include('reports.partials.individual')
    @elseif($reportType === 'roster_19')
        @include('reports.partials.roster_19')
    @elseif($reportType === 'custom_builder')
        @include('reports.partials.custom_builder')
    @elseif($reportType === 'matrices')
        @include('reports.partials.matrices')
    @endif

</div>

<!-- أنماط الطباعة الدقيقة لتعبئة ورقة A4 كاملة بالضبط دون زيادة أو نقصان -->
<style>
    @media print {
        @if($reportType === 'matrices')
            @page {
                size: A4 landscape;
                margin: 6mm 8mm; /* هوامش رسمية أفقية مريحة لتعبئة الصفحة كاملة */
            }
            /* فصل صفحة الجدول عن صفحة الرسم البياني تماماً */
            .print-table-sheet {
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                display: block !important;
                width: 100% !important;
            }
            .print-chart-sheet {
                page-break-before: always !important;
                break-before: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                display: block !important;
                width: 100% !important;
            }
            .hidden.print-table-sheet, .hidden.print-chart-sheet {
                display: none !important;
            }
            /* ضبط مقاسات الجدول لتملأ الصفحة الأفقية كاملة بجمالية متناسقة */
            .matrix-table {
                width: 100% !important;
                border: 1.5px solid #000000 !important;
            }
            .matrix-table th, .matrix-table td {
                padding-top: 1.5px !important;
                padding-bottom: 1.5px !important;
                padding-left: 2.5px !important;
                padding-right: 2.5px !important;
                font-size: 8.5pt !important;
                line-height: 1.25 !important;
                border: 1px solid #000000 !important;
            }
            /* ضبط الرسم البياني ليمتد بكامل عرض صفحة A4 الأفقية الثانية */
            #chart-matrix-clustered {
                width: 100% !important;
                max-height: 135mm !important;
            }
        @else
            @page {
                size: A4 portrait;
                margin: 6mm 8mm; /* هوامش رسمية قياسية متوازنة لطابعات الليزر */
            }
        @endif
        html, body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }
        header, footer, nav, aside, .print\:hidden, #globalQuickAddModal {
            display: none !important;
        }
        main, #report-preview-container {
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            overflow: visible !important;
            height: auto !important;
            display: block !important;
        }
        #a4-paper {
            zoom: 1 !important;
            transform: none !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        #a4-paper img {
            height: 20mm !important;
            max-height: 20mm !important;
        }
        /* المسافات بين الجداول بالمليمتر لتوزيع الصفحة بالتساوي على كامل الورقة */
        #a4-paper > * + * {
            margin-top: 3.2mm !important;
        }
        #a4-paper table {
            width: 100% !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        #a4-paper tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        /* ضبط حشوات الخلايا رأسياً لتملأ صفحة A4 بالكامل بتناسق رصين */
        #a4-paper td, #a4-paper th {
            padding-top: 2.8px !important;
            padding-bottom: 2.8px !important;
            padding-left: 4px !important;
            padding-right: 4px !important;
            font-size: 10pt !important;
            line-height: 1.35 !important;
        }
    }
</style>

<!-- نظام التحجيم الأصلي الذكي والتحكم باللمس والفأرة لبيان الحالة A4 -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const previewContainer = document.getElementById('report-preview-container');
        const paper = document.getElementById('a4-paper');
        const zoomBadge = document.getElementById('zoom-percentage-badge');

        if (!previewContainer || !paper) return;

        let currentZoom = 0.90; // نسبة الزوم الافتراضية 90% بناء على طلب المستخدم

        window.applyZoom = function (zoom) {
            currentZoom = Math.max(0.35, Math.min(zoom, 2.0));
            if (paper) {
                // التحجيم المكتبي الحقيقي عبر خاصية zoom لمنع أي انزياح أو انفصال في الهوامش والظلال
                if ('zoom' in paper.style) {
                    paper.style.zoom = currentZoom;
                    paper.style.transform = '';
                } else {
                    paper.style.transform = `scale(${currentZoom})`;
                    paper.style.transformOrigin = 'top center';
                }
            }
            if (zoomBadge) {
                zoomBadge.textContent = `${Math.round(currentZoom * 100)}%`;
            }
        };

        window.zoomStep = function (delta) {
            applyZoom(currentZoom + delta);
        };

        window.setZoom = function (val) {
            applyZoom(val);
        };

        // توسيط الشاشة وضبط أبعاد الوثيقة على 90%
        window.fitToPage = function () {
            applyZoom(0.90);
            if (previewContainer) {
                previewContainer.scrollTo({
                    top: 0,
                    left: Math.max(0, (previewContainer.scrollWidth - previewContainer.clientWidth) / 2),
                    behavior: 'smooth'
                });
            }
        };

        // 1. التطبيق الافتراضي لنسبة التقريب 90% فور اكتمال تحميل الصفحة
        setTimeout(function() { applyZoom(0.90); }, 50);

        // 2. ميزة القرص باللمس على شاشات اللمس (Pinch-to-Zoom Gesture)
        // القرص للداخل = تبعيد (Zoom Out) / التباعد للخارج = تقريب (Zoom In)
        let initialTouchDist = null;
        let pinchStartZoom = currentZoom;

        previewContainer.addEventListener('touchstart', function (e) {
            if (e.touches.length === 2) {
                initialTouchDist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                pinchStartZoom = currentZoom;
            }
        }, { passive: true });

        previewContainer.addEventListener('touchmove', function (e) {
            if (e.touches.length === 2 && initialTouchDist) {
                e.preventDefault(); // منع تكبير متصفح الهاتف العام
                e.stopPropagation();
                const currentDist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                const factor = currentDist / initialTouchDist;
                applyZoom(pinchStartZoom * factor);
            }
        }, { passive: false });

        previewContainer.addEventListener('touchend', function (e) {
            if (e.touches.length < 2) {
                initialTouchDist = null;
            }
        }, { passive: true });

        // 3. التقريب والتبعيد بعجلة الماوس مع زر Ctrl أو لوحة اللمس (Trackpad Pinch)
        previewContainer.addEventListener('wheel', function (e) {
            if (e.ctrlKey) {
                e.preventDefault();
                e.stopPropagation();
                const delta = e.deltaY < 0 ? 0.05 : -0.05;
                applyZoom(currentZoom + delta);
            }
        }, { passive: false });
    });

    // دالة إظهار وإخفاء نافذة ترحيل الأعمدة ReportForm
    function toggleReportForm() {
        const modal = document.getElementById('reportform-window');
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }
</script>

@endsection
