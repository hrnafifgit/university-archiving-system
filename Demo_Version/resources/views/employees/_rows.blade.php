@forelse($employees as $index => $emp)
    @php
        $rowNum = isset($startIndex) ? ($startIndex + $loop->iteration) : ($loop->iteration);
    @endphp
    <tr class="hover:bg-emerald-50/40 transition-colors cursor-pointer {{ $rowNum % 2 == 0 ? 'bg-slate-50/50' : 'bg-white' }}"
        onclick="window.location='{{ route('employees.show', $emp->employee_id) }}'">
        <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-sm">{{ $rowNum }}</td>
        <td class="py-3.5 px-4 font-mono font-bold text-brand-900 text-center text-sm whitespace-nowrap">
            {{ $emp->employee_number ?? '—' }}
        </td>
        <td class="py-3.5 px-4 font-bold text-slate-900 hover:text-brand-900 transition">
            <div class="flex items-center gap-2.5">
                @if($emp->photo_document_id && $emp->photoDocument?->currentVersion)
                    <img src="{{ route('archive.preview', $emp->photoDocument->currentVersion->document_version_id) }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200 shadow-2xs" alt="">
                @else
                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs shrink-0">
                        <i class="fa-solid fa-user text-slate-400"></i>
                    </div>
                @endif
                <span class="text-sm font-bold text-slate-900">{{ $emp->full_name_ar }}</span>
            </div>
        </td>
        <td class="py-3.5 px-4 text-slate-800 font-medium text-sm truncate max-w-[200px]">
            {{ $emp->currentEmployment?->organizationalUnit?->parent?->unit_name_ar ?? $emp->currentEmployment?->organizationalUnit?->unit_name_ar ?? '—' }}
        </td>
        <td class="py-3.5 px-4 text-slate-600 text-sm truncate max-w-[180px]">
            {{ $emp->currentEmployment?->organizationalUnit?->unit_name_ar ?? '—' }}
        </td>
        <td class="py-3.5 px-4 text-slate-800 font-semibold text-sm whitespace-nowrap">
            {{ $emp->currentEmployment?->academicRank?->rank_name_ar ?? ($emp->currentEmployment?->jobGrade?->grade_name_ar ?? 'إداري') }}
        </td>
        <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $emp->currentEmployment?->employmentStatus?->is_active_status ? 'bg-brand-50 text-brand-900 border-brand-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                {{ $emp->currentEmployment?->employmentStatus?->status_name_ar ?? 'مثبت' }}
            </span>
        </td>
        <td class="py-3.5 px-4 text-center whitespace-nowrap">
            @if(($emp->documents_count ?? $emp->documents->count()) > 0)
                <span class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">
                    <i class="fa-solid fa-file-pdf text-[#14532d]"></i> {{ $emp->documents_count ?? $emp->documents->count() }}
                </span>
            @else
                <span class="text-slate-300 font-mono text-sm">—</span>
            @endif
        </td>
        <td class="py-3.5 px-4 text-center whitespace-nowrap" onclick="event.stopPropagation()">
            <div class="flex items-center justify-center gap-1.5">
                <a href="{{ route('employees.show', $emp->employee_id) }}" class="w-8 h-8 rounded-lg bg-brand-900 hover:bg-brand-800 text-white flex items-center justify-center transition shadow-2xs" title="فتح ملف الكادر الموحد">
                    <i class="fa-solid fa-folder-open text-xs"></i>
                </a>
                <a href="{{ route('reports.index', ['employee_id' => $emp->employee_id]) }}" class="w-8 h-8 rounded-lg bg-white text-slate-600 hover:text-brand-900 hover:bg-slate-50 border border-slate-200 flex items-center justify-center transition shadow-2xs" title="طباعة بيان حالة رسمي">
                    <i class="fa-solid fa-print text-xs"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    @if(!isset($isAppend) || !$isAppend)
        <tr id="empty-records-row">
            <td colspan="9" class="py-16 text-center text-slate-500 font-medium bg-white">
                <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3 block"></i>
                <span class="text-base">لا توجد نتائج مطابقة للبحث.</span>
            </td>
        </tr>
    @endif
@endforelse
