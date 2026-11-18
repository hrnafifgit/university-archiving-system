@extends('layouts.app')

@section('title', 'دليل واستعلام كادر الجامعة | نظام الأرشفة الجامعي')
@section('page_title', 'دليل واستعلام كادر الجامعة (تصفية متعددة المعايير)')
@section('page_subtitle', 'محرك البحث والفلترة الذكي لكافة منتسبي الجامعة مع عدّادات إحصائية فورية')

@section('content')

<!-- رسالة نجاح الحفظ إن وجدت -->
@if(session('success'))
<div class="p-4 bg-emerald-50 border-r-4 border-univ-600 rounded-xl text-univ-800 text-sm flex items-center justify-between shadow-sm">
    <div class="flex items-center">
        <i data-lucide="check-circle-2" class="w-5 h-5 ml-2 text-univ-600"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>
</div>
@endif

<!-- شريط العدّادات الإحصائية الفورية (المطابق لعدّادات واجهة bisho: TxtCount1 to TxtCount8) -->
<div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-center">
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-xs text-slate-500 block">إجمالي المطابق للبحث</span>
        <span class="text-xl font-black text-slate-800 font-mono">{{ $totalMatched }}</span>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-xs text-emerald-600 font-bold block">كادر أكاديمي</span>
        <span class="text-xl font-black text-univ-700 font-mono">{{ $academicCount }}</span>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-xs text-indigo-600 font-bold block">كادر إداري</span>
        <span class="text-xl font-black text-indigo-700 font-mono">{{ $adminCount }}</span>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-xs text-amber-600 font-bold block">متعاقد بالساعات</span>
        <span class="text-xl font-black text-amber-700 font-mono">{{ $contractCount }}</span>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-xs text-purple-600 font-bold block">حملة الدكتوراه</span>
        <span class="text-xl font-black text-purple-700 font-mono">{{ $phdCount }}</span>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-xs text-blue-600 font-bold block">على رأس العمل</span>
        <span class="text-xl font-black text-blue-700 font-mono">{{ $activeCount }}</span>
    </div>
</div>

<!-- لوحة الفلترة والبحث المتقدم المتعدد المعايير -->
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
    <form action="{{ route('staff.index') }}" method="GET" class="space-y-4">
        
        <!-- صف البحث المباشر -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-8 relative">
                <label class="block text-xs font-bold text-slate-600 mb-1">البحث بالاسم الرباعي، الرقم الوظيفي، أو الرقم الوطني (بحث لحظي)</label>
                <div class="relative">
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="ابحث باسم الموظف، الرقم الوظيفي، أو الهاتف..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pr-10 pl-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-univ-500">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute right-3.5 top-3.5"></i>
                </div>
            </div>
            <div class="md:col-span-4 flex items-end space-x-2 space-x-reverse">
                <button type="submit" class="flex-1 bg-univ-600 hover:bg-univ-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm flex items-center justify-center shadow-md shadow-univ-600/20 transition">
                    <i data-lucide="filter" class="w-4 h-4 ml-1.5"></i>
                    <span>تطبيق التصفية</span>
                </button>
                <a href="{{ route('staff.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2.5 px-4 rounded-xl text-sm transition">
                    إعادة ضبط
                </a>
            </div>
        </div>

        <!-- صف القوائم المنسدلة التفصيلية (كليات، أقسام، رتب، حالات مستخرجة من الإكسل) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 pt-3 border-t border-slate-100 text-xs">
            <div>
                <label class="block font-bold text-slate-500 mb-1">الجهة العليا (كلية / مركز)</label>
                <select name="entity_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-univ-500">
                    <option value="">كافة الكليات والمراكز</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 mb-1">القسم / الإدارة التابعة</label>
                <select name="department_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-univ-500">
                    <option value="">كافة الأقسام والإدارات</option>
                    @foreach($departments as $dept)
                        @if(!request('entity_id') || $dept->entity_id == request('entity_id'))
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 mb-1">تصنيف الكادر (Subtype)</label>
                <select name="staff_type" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-univ-500">
                    <option value="">كافة التصنيفات</option>
                    <option value="academic" {{ request('staff_type') == 'academic' ? 'selected' : '' }}>كادر أكاديمي</option>
                    <option value="administrative" {{ request('staff_type') == 'administrative' ? 'selected' : '' }}>كادر إداري</option>
                    <option value="contract" {{ request('staff_type') == 'contract' ? 'selected' : '' }}>متعاقد بالساعات</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 mb-1">الرتبة العلمية الأكاديمية</label>
                <select name="rank_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-univ-500">
                    <option value="">كافة الرتب العلمية</option>
                    @foreach($ranks as $rank)
                        <option value="{{ $rank->id }}" {{ request('rank_id') == $rank->id ? 'selected' : '' }}>{{ $rank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 mb-1">الموقف / الحالة الوظيفية</label>
                <select name="job_status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-univ-500">
                    <option value="">كافة الحالات</option>
                    @foreach($lookups['jobStatuses'] as $status)
                        <option value="{{ $status }}" {{ request('job_status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 mb-1">النوع الاجتماعي (الجنس)</label>
                <select name="gender" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-univ-500">
                    <option value="">كلا الجنسين</option>
                    <option value="ذكر" {{ request('gender') == 'ذكر' ? 'selected' : '' }}>ذكور فقط</option>
                    <option value="أنثى" {{ request('gender') == 'أنثى' ? 'selected' : '' }}>إناث فقط</option>
                </select>
            </div>
        </div>
                    <option value="">كلا الجنسين</option>
                    <option value="ذكر" {{ request('gender') == 'ذكر' ? 'selected' : '' }}>ذكور فقط</option>
                    <option value="أنثى" {{ request('gender') == 'أنثى' ? 'selected' : '' }}>إناث فقط</option>
                </select>
            </div>
        </div>
    </form>
</div>

<!-- جدول البيانات التفاعلي -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs font-bold border-b border-slate-200">
                <tr>
                    <th class="py-4 px-6">الرقم الوظيفي</th>
                    <th class="py-4 px-6">الاسم الكامل</th>
                    <th class="py-4 px-6">نوع الكادر</th>
                    <th class="py-4 px-6">الكلية والقسم</th>
                    <th class="py-4 px-6">الرتبة / المسمى</th>
                    <th class="py-4 px-6">حالة الوثائق</th>
                    <th class="py-4 px-6 text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($staffList as $staff)
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="py-4 px-6 font-mono font-bold text-univ-700">{{ $staff->job_number }}</td>
                    <td class="py-4 px-6 font-bold text-slate-800">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-univ-100 text-univ-800 flex items-center justify-center font-bold text-xs ml-2.5">
                                {{ mb_substr($staff->full_name, 0, 2) }}
                            </div>
                            <div>
                                <span>{{ $staff->full_name }}</span>
                                <span class="block text-xs text-slate-400 font-normal">الرقم الوطني: {{ $staff->national_id }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        @if($staff->staff_type == 'academic')
                            <span class="bg-emerald-50 text-univ-700 text-xs px-2.5 py-1 rounded-full font-bold">أكاديمي</span>
                        @elseif($staff->staff_type == 'administrative')
                            <span class="bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-full font-bold">إداري</span>
                        @else
                            <span class="bg-amber-50 text-amber-700 text-xs px-2.5 py-1 rounded-full font-bold">متعاقد</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-slate-600 text-xs leading-relaxed">
                        <span class="font-bold text-slate-800 block">{{ $staff->department->entity->name }}</span>
                        <span class="text-slate-500">{{ $staff->department->name }}</span>
                    </td>
                    <td class="py-4 px-6 font-semibold text-slate-700 text-xs">
                        @if($staff->academicDetails)
                            <span class="text-emerald-800 font-bold block">{{ $staff->academicDetails->rank->name ?? 'عضو هيئة تدريس' }}</span>
                            <span class="text-slate-400 font-normal">{{ $staff->academicDetails->exact_specialization ?? '' }}</span>
                        @elseif($staff->administrativeDetails)
                            <span class="text-indigo-800 font-bold block">{{ $staff->administrativeDetails->job_title }}</span>
                            <span class="text-slate-400 font-normal">{{ $staff->administrativeDetails->jobGrade->grade_name ?? '' }}</span>
                        @elseif($staff->contractDetails)
                            <span class="text-amber-800 font-bold block">متعاقد بالساعات</span>
                            <span class="text-slate-400 font-normal">({{ $staff->contractDetails->approved_hours }} س/أسبوعياً)</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                            <i data-lucide="file-check" class="w-3.5 h-3.5 ml-1"></i>
                            {{ $staff->archives->count() }} وثائق مؤرشفة
                        </span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex items-center justify-center space-x-1.5 space-x-reverse">
                            <a href="{{ route('staff.show', $staff->id) }}" title="فتح الملف الشامل وعارض PDF" class="p-2 rounded-lg bg-slate-100 hover:bg-univ-50 text-slate-600 hover:text-univ-700 transition">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('reports.index', ['staff_id' => $staff->id]) }}" title="طباعة بيان الحالة الرسمية" class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-700 transition">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الكادر نهائياً من قاعدة البيانات؟');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="حذف السجل" class="p-2 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-700 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                        <p class="font-bold">لا توجد سجلات مطابقة لمعايير البحث الحالية.</p>
                        <a href="{{ route('staff.create') }}" class="text-xs text-univ-600 font-bold hover:underline mt-1 inline-block">انقر هنا لإضافة أول كادر جديد</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- الترقيم والتنقل -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        {{ $staffList->links() }}
    </div>
</div>
@endsection
