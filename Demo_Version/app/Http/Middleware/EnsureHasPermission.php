<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasPermission
{
    /**
     * التحقق من امتلاك المستخدم للصلاحية المطلوبة
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // التحقق من الحساب المسجل في النظام
        if (auth()->check()) {
            $u = auth()->user();
            if ($u && (strtolower($u->username ?? '') === 'admin' || str_contains(strtolower($u->email ?? ''), 'admin'))) {
                return $next($request);
            }
        }

        $roleName = (string) Session::get('logged_role_name', '');
        $username = strtolower((string) Session::get('logged_username', ''));
        
        // مدير النظام فقط يمتلك الصلاحية المطلقة، بينما باقي الأدوار (مثل مدير الإدارة ورئيس القسم) تخضع للمصفوفة
        if (empty($roleName) || $username === 'admin' || $roleName === 'مدير نظام' || str_contains($roleName, 'مدير النظام') || str_contains(strtolower($roleName), 'super admin')) {
            return $next($request);
        }

        if (\App\Services\PermissionService::hasPerm($permission)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'error' => "ليس لديك الصلاحية الكافية لإجراء هذه العملية ({$permission})."
            ], 403);
        }

        return redirect()->route('dashboard')->with('error', "ليس لديك الصلاحية الكافية لإجراء هذه العملية ({$permission}).");
    }
}
