@extends('layouts.app')

@section('title', 'جامعة إب — صلاحيات غير مفعلة')

@section('content')
<div class="flex-1 flex items-center justify-center p-6">
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm max-w-md w-full p-8 text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-2xl mx-auto">
            <i class="fa-solid fa-user-lock"></i>
        </div>
        <h1 class="text-lg font-black text-slate-900">لا توجد صلاحيات عرض مفعلة لحسابك</h1>
        <p class="text-xs text-slate-600 leading-relaxed">
            مرحباً بك (<span class="font-bold text-slate-800">{{ session('logged_display_name', session('logged_username')) }}</span>).<br>
            دورك الحالي (<span class="font-bold text-[#14532d]">{{ session('logged_role_name', 'مستخدم') }}</span>) لا يملك حالياً صلاحية عرض لأي شاشة من شاشات النظام.
            يرجى مراجعة مدير النظام لمنح الصلاحيات المطلوبة.
        </p>
        <div class="pt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 px-5 rounded-xl bg-[#14532d] hover:bg-[#166534] text-white text-xs font-bold transition cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
