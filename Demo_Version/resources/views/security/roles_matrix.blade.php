@extends('layouts.app')

@section('title', 'جامعة إب — الأدوار والصلاحيات')

@section('content')
<div class="flex-1 flex flex-col gap-3 min-h-0 overflow-y-auto pr-0.5 pb-6 custom-scrollbar">

    @if(isset($errors) && $errors->any())
        <div class="p-3.5 px-5 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl text-xs font-bold space-y-1 shadow-xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span>تعذر تنفيذ العملية للأسباب التالية:</span>
            </div>
            <ul class="list-disc list-inside text-[11px] font-medium pr-5 text-rose-800">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 1. شريط التحكم القياسي الموحد -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 px-5 flex flex-wrap items-center justify-between gap-3 shadow-xs shrink-0 select-none">
        
        <div class="flex items-center gap-3 flex-wrap">
            <!-- العنوان -->
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#14532d] border border-emerald-200 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h1 class="text-sm font-black text-slate-900">الأدوار والصلاحيات</h1>
            </div>

            <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

            <!-- تبديل الوضع: الأدوار | المستخدمون -->
            <div class="inline-flex items-center bg-slate-100 border border-slate-200 rounded-xl p-1 text-xs font-bold">
                <a href="{{ route('security.roles.index', ['mode' => 'role']) }}"
                   class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $mode === 'role' ? 'bg-[#14532d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-id-badge text-[11px]"></i>
                    <span>الأدوار</span>
                </a>
                <a href="{{ route('security.roles.index', ['mode' => 'user']) }}"
                   class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $mode === 'user' ? 'bg-[#14532d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-users text-[11px]"></i>
                    <span>المستخدمون</span>
                </a>
            </div>

            <!-- قائمة منسدلة لاختيار الدور أو المستخدم -->
            @if($mode === 'role')
                <form method="GET" action="{{ route('security.roles.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="mode" value="role">
                    <select name="role_id" onchange="this.form.submit()" class="w-52 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-800 focus:border-[#14532d] focus:bg-white focus:outline-none">
                        @foreach($roles as $r)
                            <option value="{{ $r->role_id }}" {{ ($selectedRole && $selectedRole->role_id === $r->role_id) ? 'selected' : '' }}>
                                {{ $r->role_name }} ({{ $r->permissions->count() }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @else
                <form method="GET" action="{{ route('security.roles.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="mode" value="user">
                    <select name="user_id" onchange="this.form.submit()" class="w-64 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-800 focus:border-[#14532d] focus:bg-white focus:outline-none">
                        @foreach($users as $u)
                            <option value="{{ $u->user_id }}" {{ ($selectedUser && $selectedUser->user_id === $u->user_id) ? 'selected' : '' }}>
                                {{ $u->display_name_ar ?? $u->username }} — {{ $u->roles->first()?->role_name ?? 'بدون دور' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        <!-- أزرار الإجراءات السريعة -->
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addRoleModal').classList.remove('hidden')"
                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs cursor-pointer">
                <i class="fa-solid fa-plus text-[#14532d] text-[10px]"></i>
                <span>إضافة دور</span>
            </button>

            <button type="button" onclick="document.getElementById('assignCadreModal').classList.remove('hidden')"
                    class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#14532d] border border-emerald-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs cursor-pointer">
                <i class="fa-solid fa-user-plus text-[10px]"></i>
                <span>تعيين كادر كمستخدم</span>
            </button>

            <button type="submit" form="permissionsMatrixForm"
                    class="px-4 py-1.5 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white text-xs font-bold flex items-center gap-1.5 transition shadow-xs cursor-pointer">
                <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                <span>حفظ</span>
            </button>
        </div>

    </div>

    <!-- 2. نموذج مصفوفة الصلاحيات -->
    <form id="permissionsMatrixForm" method="POST" action="{{ route('security.roles.update') }}" class="flex flex-col gap-3">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode }}">
        @if($mode === 'role' && $selectedRole)
            <input type="hidden" name="role_id" value="{{ $selectedRole->role_id }}">
        @elseif($mode === 'user' && $selectedUser)
            <input type="hidden" name="user_id" value="{{ $selectedUser->user_id }}">
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <div class="bg-slate-50/80 border-b border-slate-200 px-5 py-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                    <i class="fa-solid fa-table-cells text-[#14532d]"></i>
                    @if($mode === 'role')
                        <span>صلاحيات الدور:</span>
                        <span class="text-[#14532d] font-black">{{ $selectedRole?->role_name }}</span>
                    @else
                        <span>صلاحيات المستخدم:</span>
                        <span class="text-[#14532d] font-black">{{ $selectedUser?->display_name_ar ?? $selectedUser?->username }}</span>
                        @if($selectedUser?->employee)
                            <span class="text-[11px] font-mono text-slate-400">({{ $selectedUser->employee->employee_number }})</span>
                        @endif
                    @endif
                </div>

                <button type="button" onclick="toggleAllMatrix()" class="text-xs font-bold text-[#14532d] hover:underline flex items-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-check-double text-[10px]"></i>
                    <span>تحديد الكل</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold select-none">
                        <tr>
                            <th class="py-3 px-4 w-56">الشاشة</th>
                            @foreach($actions as $actKey => $actInfo)
                                <th class="py-2.5 px-3 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="flex items-center gap-1 {{ $actKey === 'confirm' ? 'text-[#14532d] font-black' : '' }}">
                                            <i class="fa-solid {{ $actInfo['icon'] }} text-[11px]"></i>
                                            <span>{{ $actInfo['label'] }}</span>
                                        </span>
                                        <input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 text-[#14532d] focus:ring-[#14532d] cursor-pointer" onchange="toggleColumn('{{ $actKey }}', this.checked)" title="تحديد العمود">
                                    </div>
                                </th>
                            @endforeach
                            <th class="py-2.5 px-3 text-center w-20">الكل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($modules as $modKey => $modInfo)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-[#14532d] flex items-center justify-center text-xs shrink-0">
                                            <i class="fa-solid {{ $modInfo['icon'] }}"></i>
                                        </div>
                                        <span>{{ $modInfo['label'] }}</span>
                                    </div>
                                </td>

                                @foreach($actions as $actKey => $actInfo)
                                    @php
                                        $supported = in_array($actKey, $modInfo['actions'] ?? [], true);
                                        $code = "{$modKey}.{$actKey}";
                                        $checked = in_array($code, $grantedCodes, true);
                                    @endphp
                                    <td class="py-2.5 px-3 text-center">
                                        @if($supported)
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $code }}"
                                                   data-module="{{ $modKey }}"
                                                   data-action="{{ $actKey }}"
                                                   {{ $checked ? 'checked' : '' }}
                                                   class="perm-checkbox w-4 h-4 rounded border-slate-300 text-[#14532d] focus:ring-[#14532d] cursor-pointer">
                                        @else
                                            <span class="text-slate-300 font-bold select-none" title="غير منطبق على هذه الشاشة">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="py-2.5 px-3 text-center">
                                    <input type="checkbox"
                                           onchange="toggleRow('{{ $modKey }}', this.checked)"
                                           class="w-4 h-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-700 cursor-pointer"
                                           title="تحديد الصف">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. إعدادات أمان الحساب (في وضع المستخدم) -->
        @if($mode === 'user' && $selectedUser)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-4 px-5">
            <h3 class="text-xs font-black text-slate-800 flex items-center gap-2 mb-3">
                <i class="fa-solid fa-shield-halved text-[#14532d]"></i>
                <span>أمان الحساب</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-100/70 transition">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-key text-amber-600 text-xs"></i>
                        <span class="text-xs font-bold text-slate-800">تغيير كلمة المرور مباشرة</span>
                    </div>
                    <input type="checkbox" name="allow_direct_password_change" value="1"
                           {{ ($selectedUser->allow_direct_password_change ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-[#14532d] focus:ring-[#14532d]">
                </label>

                <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-100/70 transition">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope-circle-check text-[#14532d] text-xs"></i>
                        <span class="text-xs font-bold text-slate-800">اشتراط التأكيد (Confirm) للبيانات الحساسة</span>
                    </div>
                    <input type="checkbox" name="require_confirm_sensitive_edit" value="1"
                           {{ ($selectedUser->require_confirm_sensitive_edit ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-[#14532d] focus:ring-[#14532d]">
                </label>
            </div>
        </div>
        @endif
    </form>

</div>

<!-- نافذة إضافة دور جديد ومنحه صلاحيات -->
<div id="addRoleModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="bg-[#14532d] text-white p-3.5 px-5 flex items-center justify-between">
            <h3 class="text-sm font-black flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> إضافة دور جديد
            </h3>
            <button type="button" onclick="document.getElementById('addRoleModal').classList.add('hidden')" class="text-white/70 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('security.roles.store') }}" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-800 mb-1">اسم الدور *</label>
                <input type="text" name="role_name" required placeholder="مثال: مراقب مالي" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1">الوصف</label>
                <input type="text" name="description" placeholder="وصف مختصر..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2">
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1.5">الصلاحيات المبدئية</label>
                <div class="max-h-44 overflow-y-auto border border-slate-200 rounded-xl p-2.5 space-y-1.5 bg-slate-50/50">
                    @foreach($modules as $modKey => $modInfo)
                        <div class="flex items-center justify-between py-1 border-b border-slate-100 last:border-0">
                            <span class="font-bold text-slate-800">{{ $modInfo['label'] }}</span>
                            <div class="flex items-center flex-wrap gap-2">
                                @foreach($actions as $actKey => $actInfo)
                                    @if(in_array($actKey, $modInfo['actions'] ?? [], true))
                                        <label class="inline-flex items-center gap-1 text-[11px] text-slate-600 cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $modKey }}.{{ $actKey }}" {{ $actKey === 'view' ? 'checked' : '' }} class="rounded text-[#14532d]">
                                            <span>{{ $actInfo['label'] }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('addRoleModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold cursor-pointer">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white font-bold cursor-pointer">إنشاء الدور</button>
            </div>
        </form>
    </div>
</div>

<!-- نافذة تعيين كادر كمستخدم للنظام -->
<div id="assignCadreModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-[#14532d] text-white p-3.5 px-5 flex items-center justify-between">
            <h3 class="text-sm font-black flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> تعيين كادر كمستخدم
            </h3>
            <button type="button" onclick="document.getElementById('assignCadreModal').classList.add('hidden')" class="text-white/70 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('security.users.assign') }}" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-800 mb-1">اختر الكادر *</label>
                <select name="employee_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-bold">
                    <option value="">-- اختر من الكادر --</option>
                    @foreach($availableEmployees as $emp)
                        <option value="{{ $emp->employee_id }}" {{ old('employee_id') == $emp->employee_id ? 'selected' : '' }}>{{ $emp->full_name_ar }} ({{ $emp->employee_number }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1">الدور الوظيفي *</label>
                <select name="role_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-bold">
                    @foreach($roles as $r)
                        <option value="{{ $r->role_id }}" {{ old('role_id') == $r->role_id ? 'selected' : '' }}>{{ $r->role_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">اسم المستخدم *</label>
                    <input type="text" name="username" id="assign_username_input" required 
                           value="{{ old('username') }}"
                           oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9_.\-]/g, ''); checkUsernameLive(this.value);"
                           placeholder="ahmed_ali" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-mono">
                    <div id="assign_username_feedback" class="hidden text-[10px] font-bold mt-1"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">كلمة المرور *</label>
                    <input type="password" name="password" required minlength="4" placeholder="••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-mono">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('assignCadreModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold cursor-pointer">إلغاء</button>
                <button type="submit" id="assignUserSubmitBtn" class="px-5 py-2 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white font-bold cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">تعيين وتخصيص الصلاحيات</button>
            </div>
        </form>
    </div>
</div>

<script>
let usernameCheckTimer = null;
function checkUsernameLive(val) {
    clearTimeout(usernameCheckTimer);
    const fb = document.getElementById('assign_username_feedback');
    const btn = document.getElementById('assignUserSubmitBtn');
    const inp = document.getElementById('assign_username_input');
    if (!fb || !btn || !inp) return;

    const clean = (val || '').trim();
    if (!clean) {
        fb.classList.add('hidden');
        btn.disabled = false;
        inp.classList.remove('border-rose-500', 'border-emerald-500');
        return;
    }

    usernameCheckTimer = setTimeout(() => {
        fetch(`{{ route('security.users.check_username') }}?username=${encodeURIComponent(clean)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            fb.classList.remove('hidden', 'text-rose-600', 'text-emerald-700');
            inp.classList.remove('border-rose-500', 'border-emerald-500');
            if (data.available) {
                fb.classList.add('text-emerald-700');
                fb.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${data.message}`;
                inp.classList.add('border-emerald-500');
                btn.disabled = false;
            } else {
                fb.classList.add('text-rose-600');
                fb.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${data.message}`;
                inp.classList.add('border-rose-500');
                btn.disabled = true;
            }
        })
        .catch(() => {});
    }, 250);
}

function toggleRow(moduleKey, state) {
    document.querySelectorAll(`.perm-checkbox[data-module="${moduleKey}"]`).forEach(cb => cb.checked = state);
}
function toggleColumn(actionKey, state) {
    document.querySelectorAll(`.perm-checkbox[data-action="${actionKey}"]`).forEach(cb => cb.checked = state);
}
let allSelectedState = false;
function toggleAllMatrix() {
    allSelectedState = !allSelectedState;
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = allSelectedState);
}
</script>
@endsection
