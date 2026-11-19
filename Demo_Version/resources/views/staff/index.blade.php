@extends('layouts.app')

@section('title', 'دليل واستعلام الكادر | نظام الأرشفة الجامعي')
@section('page_title', 'دليل واستعلام الكادر')

@section('content')
<div class="space-y-5">

    <!-- تنبيه النجاح إن وجد -->
    @if(session('success'))
    <div class="p-3 bg-brand-50 border border-brand-200 rounded-xl text-brand-900 text-xs flex items-center justify-between">
        <div class="flex items-center">
            <i data-lucide="check" class="w-4 h-4 ml-2"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>
    @endif

    <!-- شريط الإحصاءات السريعة - نمط أبيض وأخضر داكن -->
    <div class="grid grid-cols-3 md:grid-cols-6 gap-2 text-center text-xs">
        <div class="bg-white p-2.5 rounded-xl border border-slate-200">
            <span class="text-slate-400 block text-[11px]">المطابق</span>
            <span class="text-base font-bold text-slate-800 font-mono">{{ $totalMatched }}</span>
        </div>
        <div class="bg-white p-2.5 rounded-xl border border-slate-200">
            <span class="text-slate-400 block text-[11px]">أكاديمي</span>
            <span class="text-base font-bold text-brand-900 font-mono">{{ $academicCount }}</span>
        </div>
        <div class="bg-white p-2.5 rounded-xl border border-slate-200">
            <span class="text-slate-400 block text-[11px]">إداري</span>
            <span class="text-base font-bold text-slate-700 font-mono">{{ $adminCount }}</span>
        </div>
        <div class="bg-white p-2.5 rounded-xl border border-slate-200">
            <span class="text-slate-400 block text-[11px]">متعاقد</span>
            <span class="text-base font-bold text-slate-700 font-mono">{{ $contractCount }}</span>
        </div>
        <div class="bg-white p-2.5 rounded-xl border border-slate-200">
            <span class="text-slate-400 block text-[11px]">دكتوراه</span>
            <span class="text-base font-bold text-brand-900 font-mono">{{ $phdCount }}</span>
        </div>
        <div class="bg-white p-2.5 rounded-xl border border-slate-200">
            <span class="text-slate-400 block text-[11px]">على رأس العمل</span>
            <span class="text-base font-bold text-brand-900 font-mono">{{ $activeCount }}</span>
        </div>
    </div>

    <!-- لوحة البحث والفلترة النظيفة -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('staff.index') }}" method="GET" class="space-y-3">
            
            <!-- حقل البحث الرئيسي المباشر -->
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <input name="search" value="{{ request('search') }}" type="text" 
                        placeholder="ابحث بالاسم، الرقم الوظيفي، أو الرقم الوطني..." 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pr-9 pl-3 py-2 text-xs focus:bg-white focus:outline-none focus:border-brand-900 focus:ring-1 focus:ring-brand-900 transition">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute right-3 top-2.5"></i>
                </div>
                <div class="flex items-center space-x-2 space-x-reverse">
                    <button type="submit" class="bg-brand-900 hover:bg-brand-800 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center transition shadow-sm">
                        <i data-lucide="filter" class="w-3.5 h-3.5 ml-1.5"></i>
                        <span>تصفية</span>
                    </button>
                    <a href="{{ route('staff.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl text-xs font-semibold transition" title="إعادة الضبط">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- القوائم المنسدلة بدون تعقيد -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-2 pt-2 border-t border-slate-100 text-xs">
                <div>
                    <select name="entity_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 focus:border-brand-900">
                        <option value="">كافة الكليات والمراكز</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="department_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 focus:border-brand-900">
                        <option value="">كافة الأقسام</option>
                        @foreach($departments as $dept)
                            @if(!request('entity_id') || $dept->entity_id == request('entity_id'))
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="staff_type" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 focus:border-brand-900">
                        <option value="">كافة التصنيفات</option>
                        <option value="academic" {{ request('staff_type') == 'academic' ? 'selected' : '' }}>أكاديمي</option>
                        <option value="administrative" {{ request('staff_type') == 'administrative' ? 'selected' : '' }}>إداري</option>
                        <option value="contract" {{ request('staff_type') == 'contract' ? 'selected' : '' }}>متعاقد</option>
                    </select>
                </div>

                <div>
                    <select name="rank_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 focus:border-brand-900">
                        <option value="">كافة الرتب</option>
                        @foreach($ranks as $rank)
                            <option value="{{ $rank->id }}" {{ request('rank_id') == $rank->id ? 'selected' : '' }}>{{ $rank->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="job_status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-1.5 focus:border-brand-900">
                        <option value="">كافة الحالات</option>
                        @foreach($lookups['jobStatuses'] as $status)
                            <option value="{{ $status }}" {{ request('job_status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </form>
    </div>

    <!-- جدول البيانات -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">الرقم</th>
                        <th class="py-3 px-4">الاسم الكامل</th>
                        <th class="py-3 px-4">التصنيف</th>
                        <th class="py-3 px-4">الكلية / القسم</th>
                        <th class="py-3 px-4">الرتبة</th>
                        <th class="py-3 px-4">الأرشيف</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staffList as $staff)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-4 font-mono font-bold text-brand-900">{{ $staff->job_number }}</td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $staff->full_name }}
                            <span class="block text-[10px] text-slate-400 font-mono font-normal">ر.و: {{ $staff->national_id }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @if($staff->staff_type == 'academic')
                                <span class="text-brand-900 font-semibold bg-brand-50 px-2 py-0.5 rounded text-[11px]">أكاديمي</span>
                            @elseif($staff->staff_type == 'administrative')
                                <span class="text-slate-700 font-semibold bg-slate-100 px-2 py-0.5 rounded text-[11px]">إداري</span>
                            @else
                                <span class="text-slate-600 font-semibold bg-slate-100 px-2 py-0.5 rounded text-[11px]">متعاقد</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            <span class="font-semibold block text-slate-800">{{ $staff->department->entity->name }}</span>
                            <span class="text-slate-400 text-[10px]">{{ $staff->department->name }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            @if($staff->academicDetails)
                                <span class="font-bold text-brand-900">{{ $staff->academicDetails->rank->name ?? 'عضو هيئة تدريس' }}</span>
                            @elseif($staff->administrativeDetails)
                                <span>{{ $staff->administrativeDetails->job_title }}</span>
                            @else
                                <span>متعاقد</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center text-slate-500 font-mono text-[11px]">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 ml-1 text-brand-900"></i>
                                {{ $staff->archives->count() }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center space-x-1 space-x-reverse">
                                <a href="{{ route('staff.show', $staff->id) }}" title="معاينة الملف والأرشيف" class="p-1.5 rounded-lg text-slate-500 hover:text-brand-900 hover:bg-brand-50 transition">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('reports.index', ['staff_id' => $staff->id]) }}" title="بيان الحالة" class="p-1.5 rounded-lg text-slate-500 hover:text-brand-900 hover:bg-brand-50 transition">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('تأكيد حذف الكادر؟');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="حذف" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-1 text-slate-300"></i>
                            <p>لا توجد نتائج مطابقة للبحث.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-slate-100 bg-white">
            {{ $staffList->links() }}
        </div>
    </div>

</div>
@endsection
