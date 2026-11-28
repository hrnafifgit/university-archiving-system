@extends('layouts.app')

@section('title', 'جامعة إب — مسار الاعتماد والتأكيد المتسلسل')

@section('content')
@php
    $currentUserId = session('logged_user_id');
    $currentRole = session('logged_role_name', 'مدير نظام');
    $isSuperAdmin = ($currentRole === 'مدير نظام' || $currentRole === 'مدير النظام');
    $canCreateTx = \App\Services\PermissionService::hasPerm('hourly_contracts.create') || \App\Services\PermissionService::hasPerm('budget.create') || $isSuperAdmin;
@endphp

<div class="flex-1 flex flex-col gap-3 min-h-0">

    <!-- 1. شريط التحكم ومبدل الأدوار القياسي -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3 px-5 flex flex-wrap items-center justify-between gap-3 shadow-xs shrink-0 select-none">
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#14532d] border border-emerald-200 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <h1 class="text-sm font-black text-slate-900">الاعتمادات</h1>
            </div>

            <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

            <!-- قائمة تبديل الدور الفوري -->
            <form method="POST" action="{{ route('security.switch_role') }}" class="flex items-center gap-2">
                @csrf
                <label class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-repeat text-[#14532d]"></i>
                    <span>الحساب النشط:</span>
                </label>
                <select name="user_id" onchange="this.form.submit()" class="w-60 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-800 focus:border-[#14532d] focus:bg-white focus:outline-none cursor-pointer">
                    @foreach($demoUsers as $du)
                        @php $duRole = $du->roles->first()?->role_name ?? 'مستخدم'; @endphp
                        <option value="{{ $du->user_id }}" {{ ($currentUserId === $du->user_id) ? 'selected' : '' }}>
                            {{ $duRole }} — {{ $du->display_name_ar ?? $du->username }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="flex items-center gap-2">
            @if(!$canCreateTx)
                <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-[11px]"></i> وضع القراءة فقط
                </span>
            @else
                <button type="button" onclick="document.getElementById('newWorkflowModal').classList.remove('hidden')"
                        class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-4 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>معاملة جديدة</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 2. مساحة العمل: جدول المعاملات (يمين) + تفاصيل المعاملة والخط الزمني (يسار) -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-3 min-h-0">

        <!-- العمود الأيمن: جدول المعاملات وحالات الاعتماد -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl shadow-xs flex flex-col min-h-0 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between shrink-0">
                <h2 class="text-xs font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#14532d]"></i>
                    <span>المعاملات وطلبات الاعتماد</span>
                </h2>
                <span class="text-[11px] font-mono font-bold bg-slate-200/70 text-slate-700 px-2 py-0.5 rounded-lg">{{ $transactions->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                @forelse($transactions as $tx)
                    @php
                        $badge = $tx->status_badge;
                        $isSel = $activeTx && $activeTx->transaction_id === $tx->transaction_id;
                    @endphp
                    <a href="{{ route('workflow.index', ['tx' => $tx->transaction_id]) }}"
                       class="block p-3.5 px-4 transition hover:bg-slate-50 {{ $isSel ? 'bg-emerald-50/60 border-r-4 border-[#14532d]' : '' }}">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="font-mono text-[11px] font-bold text-slate-500">{{ $tx->transaction_code }} • {{ $tx->type_label }}</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                                <i class="fa-solid {{ $badge['icon'] }} text-[9px]"></i>
                                <span>{{ $badge['label'] }}</span>
                            </span>
                        </div>
                        <h3 class="text-xs font-bold text-slate-900 leading-snug mb-1">{{ $tx->title }}</h3>
                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                            <span><i class="fa-solid fa-building-columns text-slate-400 ml-1"></i>{{ $tx->department_name ?? '—' }}</span>
                            @if($tx->amount)
                                <span class="font-mono font-bold text-slate-700">{{ number_format($tx->amount, 0) }} ر.ي</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">لا توجد معاملات مسجلة</div>
                @endforelse
            </div>
        </div>

        <!-- العمود الأيسر: تفاصيل المعاملة + الخط الزمني المتسلسل + أزرار الاعتماد -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-xs flex flex-col min-h-0 overflow-hidden">
            @if($activeTx)
                @php
                    $txBadge = $activeTx->status_badge;
                    $activeStepObj = $activeTx->steps->firstWhere('step_number', $activeTx->current_step);
                    $permModule = match ($activeTx->transaction_type) {
                        'budget' => 'budget',
                        'hourly_contract' => 'hourly_contracts',
                        default => 'employees',
                    };
                    $hasConfirmPerm = $isSuperAdmin || \App\Services\PermissionService::hasPerm("{$permModule}.confirm");
                    $canActCurrentStep = $activeStepObj
                        && in_array($activeTx->status, ['pending_dept_head', 'pending_admin_manager', 'pending_sec_general'], true)
                        && $hasConfirmPerm
                        && ($isSuperAdmin || $currentRole === $activeStepObj->required_role);
                @endphp

                <!-- ترويسة المعاملة -->
                <div class="bg-slate-50 border-b border-slate-200 p-4 px-5 flex flex-wrap items-center justify-between gap-2 shrink-0">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-xs font-bold text-[#14532d] bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">{{ $activeTx->transaction_code }}</span>
                            <span class="text-xs font-bold text-slate-500">{{ $activeTx->type_label }}</span>
                        </div>
                        <h2 class="text-sm font-black text-slate-900">{{ $activeTx->title }}</h2>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $txBadge['bg'] }} {{ $txBadge['text'] }} {{ $txBadge['border'] }}">
                        <i class="fa-solid {{ $txBadge['icon'] }}"></i>
                        <span>{{ $txBadge['label'] }}</span>
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto p-5 space-y-5 custom-scrollbar">
                    <!-- بطاقة بيانات المعاملة -->
                    <div class="p-3.5 bg-slate-50/70 border border-slate-200 rounded-2xl grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">الجهة</span>
                            <strong class="text-slate-800">{{ $activeTx->department_name ?? '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">مقدم الطلب</span>
                            <strong class="text-slate-800">{{ $activeTx->created_by_name ?? '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">المبلغ المقدر</span>
                            <strong class="font-mono text-slate-900">{{ $activeTx->amount ? number_format($activeTx->amount, 0) . ' ر.ي' : '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">تاريخ الإنشاء</span>
                            <strong class="font-mono text-slate-700">{{ $activeTx->created_at?->format('Y-m-d') }}</strong>
                        </div>
                        @if($activeTx->description)
                        <div class="col-span-2 sm:col-span-4 border-t border-slate-200/70 pt-2 text-slate-700">
                            {{ $activeTx->description }}
                        </div>
                        @endif
                    </div>

                    @if($activeTx->status === 'returned' && $activeTx->return_reason)
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 flex items-start gap-2">
                        <i class="fa-solid fa-rotate-left text-rose-600 mt-0.5"></i>
                        <div>
                            <strong class="font-black">سبب الإرجاع للتعديل:</strong>
                            <span>{{ $activeTx->return_reason }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- الخط الزمني البصري لمراحل الاعتماد (Sequential Stepper) -->
                    <div>
                        <h3 class="text-xs font-black text-slate-800 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-timeline text-[#14532d]"></i>
                            <span>مسار الاعتماد المتسلسل وسجل التوقيعات</span>
                        </h3>

                        <div class="space-y-3 relative before:content-[''] before:absolute before:top-4 before:bottom-4 before:right-4 before:w-0.5 before:bg-slate-200">
                            @foreach($activeTx->steps as $step)
                                @php
                                    $isDone = in_array($step->action, ['submitted', 'confirmed'], true);
                                    $isReturned = ($step->action === 'returned');
                                    $isCurrent = ($activeTx->current_step === $step->step_number) && in_array($activeTx->status, ['pending_dept_head', 'pending_admin_manager', 'pending_sec_general'], true);
                                @endphp
                                <div class="relative pr-11">
                                    <div class="absolute right-0.5 top-1 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10
                                        {{ $isDone ? 'bg-[#14532d] text-white border-[#14532d]' : ($isReturned ? 'bg-rose-600 text-white border-rose-600' : ($isCurrent ? 'bg-amber-100 text-amber-800 border-amber-500 ring-4 ring-amber-50' : 'bg-white text-slate-400 border-slate-300')) }}">
                                        @if($isDone)
                                            <i class="fa-solid fa-check"></i>
                                        @elseif($isReturned)
                                            <i class="fa-solid fa-xmark"></i>
                                        @else
                                            {{ $step->step_number }}
                                        @endif
                                    </div>

                                    <div class="p-3 rounded-xl border {{ $isCurrent ? 'bg-amber-50/40 border-amber-300' : ($isDone ? 'bg-white border-slate-200' : 'bg-slate-50/50 border-slate-200/70') }}">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-black text-slate-900">{{ $step->step_title }}</span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200">{{ $step->required_role }}</span>
                                            </div>
                                            @if($step->acted_at)
                                                <span class="text-[10px] font-mono text-slate-500">{{ $step->acted_at->format('Y-m-d H:i') }}</span>
                                            @endif
                                        </div>

                                        @if($step->actor_name)
                                            <div class="text-[11px] text-slate-700 mt-1 font-semibold">
                                                <i class="fa-solid fa-user-check text-emerald-700 ml-1"></i>
                                                {{ $step->actor_name }}
                                                @if($step->notes)
                                                    <span class="text-slate-500 font-normal">— "{{ $step->notes }}"</span>
                                                @endif
                                            </div>
                                        @elseif($isCurrent)
                                            <div class="text-[11px] text-amber-800 mt-1 font-bold">بانتظار إجراء ({{ $step->required_role }})...</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- منطقة الإجراءات حسب دور وصلاحيات المستخدم الحالي -->
                    @if($canActCurrentStep)
                        <form method="POST" action="{{ route('workflow.act', $activeTx->transaction_id) }}" class="p-4 bg-emerald-50/40 border border-emerald-200 rounded-2xl space-y-3">
                            @csrf
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-[#14532d] flex items-center gap-1.5">
                                    <i class="fa-solid fa-signature"></i>
                                    إجراء المرحلة الحالية ({{ $activeStepObj->step_title }})
                                </span>
                            </div>

                            <textarea name="notes" id="wfActionNotes" rows="2" placeholder="ملاحظات الاعتماد (اختياري) أو سبب الإرجاع (إلزامي عند الإرجاع)..." class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:border-[#14532d] focus:outline-none"></textarea>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit" name="action_type" value="confirm" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>تأكيد واعتماد (Confirm)</span>
                                </button>
                                <button type="submit" name="action_type" value="return" onclick="if(!document.getElementById('wfActionNotes').value.trim()){alert('يرجى كتابة سبب الإرجاع للتعديل'); return false;}" class="bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>إرجاع للتعديل</span>
                                </button>
                            </div>
                        </form>
                    @elseif(in_array($activeTx->status, ['draft', 'returned'], true) && $canCreateTx)
                        <form method="POST" action="{{ route('workflow.act', $activeTx->transaction_id) }}" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between">
                            @csrf
                            <span class="text-xs font-bold text-slate-700">المعاملة جاهزة للإرسال إلى رئيس القسم</span>
                            <button type="submit" name="action_type" value="submit" class="bg-[#14532d] hover:bg-[#166534] text-white font-bold px-5 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>إرسال للاعتماد</span>
                            </button>
                        </form>
                    @elseif($activeTx->status !== 'approved')
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-center gap-2">
                            <i class="fa-solid fa-eye text-slate-400"></i>
                            <span>أنت في <strong>وضع القراءة فقط</strong> لهذه المعاملة. الإجراء المطلوب حالياً من: <strong class="text-slate-900">{{ $activeStepObj?->required_role }}</strong></span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- نافذة إضافة معاملة جديدة -->
<div id="newWorkflowModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="bg-[#14532d] text-white p-4 px-5 flex items-center justify-between">
            <h3 class="text-sm font-black flex items-center gap-2">
                <i class="fa-solid fa-file-circle-plus"></i> معاملة اعتماد جديدة
            </h3>
            <button type="button" onclick="document.getElementById('newWorkflowModal').classList.add('hidden')" class="text-white/70 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('workflow.store') }}" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-800 mb-1">نوع المعاملة</label>
                <select name="transaction_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-bold">
                    <option value="missing_document">وثيقة ناقصة / أرشيف (تأكيد الموارد البشرية ثم إدارة النظام)</option>
                    <option value="academic_decision">قرار أكاديمي (تأكيد مدير عام الشؤون الأكاديمية)</option>
                    <option value="legal_decision">قرار شؤون قانونية (تأكيد الشؤون الإدارية / الأكاديمية)</option>
                    <option value="hourly_contract">عقد ساعات</option>
                    <option value="budget">بند موازنة</option>
                    <option value="decision">قرار إداري</option>
                    <option value="employee_update">تعديل بيانات كادر</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1">عنوان المعاملة *</label>
                <input type="text" name="title" required placeholder="أدخل عنوان المعاملة..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-bold">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">الجهة / القسم</label>
                    <input type="text" name="department_name" placeholder="مثال: كلية الهندسة" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">المبلغ (إن وجد)</label>
                    <input type="number" name="amount" placeholder="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-mono">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1">التفاصيل</label>
                <textarea name="description" rows="2" placeholder="وصف مختصر..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="submit" name="submit_mode" value="draft" class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold cursor-pointer">حفظ كمسودة</button>
                <button type="submit" name="submit_mode" value="submit" class="px-5 py-2 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white font-bold cursor-pointer">إرسال للاعتماد</button>
            </div>
        </form>
    </div>
</div>
@endsection
