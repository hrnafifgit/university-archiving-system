<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureDesktopAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::has('logged_user_id')) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً للوصول إلى نظام إدارة وأرشفة الكادر.');
        }

        // تحديث الصلاحيات الفعلية والدور لحظياً من قاعدة البيانات لضمان تطبيق أي تعديل دون الحاجة لإعادة تسجيل الدخول
        $userId = Session::get('logged_user_id');
        $user = \Illuminate\Support\Facades\DB::table('app_users')->where('user_id', $userId)->first();

        if (!$user || !$user->is_active) {
            Session::flush();
            return redirect()->route('login')->with('error', 'تم تعطيل هذا الحساب أو حذفه من قبل مدير النظام.');
        }

        $role = \Illuminate\Support\Facades\DB::table('user_roles')
            ->join('app_roles', 'user_roles.role_id', '=', 'app_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->select('app_roles.role_name')
            ->first();

        if ($role) {
            Session::put('logged_role_name', $role->role_name);
        }

        Session::put('logged_permissions', \App\Services\PermissionService::getEffectivePermissions($userId));

        return $next($request);
    }
}
