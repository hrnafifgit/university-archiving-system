<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::has('logged_user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::table('app_users')
            ->where('username', trim($request->username))
            ->where('is_active', true)
            ->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            // تجديد معرف الجلسة لمنع هجمات تثبيت الجلسة (Session Fixation)
            $request->session()->regenerate();

            // تسجيل وقت الدخول
            DB::table('app_users')
                ->where('user_id', $user->user_id)
                ->update(['last_login_at' => now()]);

            // جلب الدور
            $role = DB::table('user_roles')
                ->join('app_roles', 'user_roles.role_id', '=', 'app_roles.role_id')
                ->where('user_roles.user_id', $user->user_id)
                ->select('app_roles.role_name', 'app_roles.role_id')
                ->first();

            // جلب مصفوفة الصلاحيات الممنوحة
            $permissions = [];
            if ($role) {
                if (str_contains($role->role_name, 'مدير النظام') || str_contains($role->role_name, 'Super Admin')) {
                    $permissions = [
                        'manage_employees',
                        'delete_employees',
                        'manage_decisions',
                        'delete_decisions',
                        'manage_archive',
                        'delete_archive',
                        'manage_master_data',
                        'view_reports',
                    ];
                } else {
                    $permissions = DB::table('role_permissions')
                        ->join('app_permissions', 'role_permissions.permission_id', '=', 'app_permissions.permission_id')
                        ->where('role_permissions.role_id', $role->role_id)
                        ->pluck('app_permissions.permission_code')
                        ->toArray();
                }
            }

            Session::put('logged_user_id', $user->user_id);
            Session::put('logged_username', $user->username);
            Session::put('logged_role_name', $role ? $role->role_name : 'مستخدم نظام');
            Session::put('logged_permissions', $permissions);

            // توثيق حركة تسجيل الدخول في سجل التدقيق
            AuditLogService::log('LOGIN', 'app_users', $user->user_id, null, [
                'username' => $user->username,
                'role' => $role ? $role->role_name : 'مستخدم نظام',
            ]);

            return redirect()->route('dashboard')->with('success', 'تم تسجيل الدخول بنجاح إلى نظام الأرشفة الجامعي.');
        }

        // توثيق محاولة الدخول الفاشلة
        AuditLogService::log('LOGIN_FAILED', 'app_users', null, null, [
            'attempted_username' => $request->username,
        ]);

        return back()->withInput()->with('error', 'اسم المستخدم أو كلمة المرور غير صحيحة، أو تم تعطيل الحساب.');
    }

    public function logout(Request $request)
    {
        $userId = Session::get('logged_user_id');
        $username = Session::get('logged_username');

        if ($userId) {
            AuditLogService::log('LOGOUT', 'app_users', $userId, null, [
                'username' => $username,
            ]);
        }

        Session::forget(['logged_user_id', 'logged_username', 'logged_role_name', 'logged_permissions']);
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
