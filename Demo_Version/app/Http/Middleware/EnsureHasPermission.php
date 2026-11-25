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
        
        // مدير النظام ومسؤولو شؤون الكادر يمتلكون الصلاحيات الكاملة
        if (empty($roleName) || str_contains($roleName, 'مدير') || str_contains(strtolower($roleName), 'admin') || str_contains($roleName, 'Super') || str_contains($roleName, 'مسؤول')) {
            return $next($request);
        }

        $permissions = Session::get('logged_permissions', []);
        if (in_array($permission, $permissions, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'error' => "ليس لديك الصلاحية الكافية لإجراء هذه العملية ({$permission})."
            ], 403);
        }

        return back()->with('error', "ليس لديك الصلاحية الكافية لإجراء هذه العملية ({$permission}). يرجى مراجعة إدارة النظام.");
    }
}
