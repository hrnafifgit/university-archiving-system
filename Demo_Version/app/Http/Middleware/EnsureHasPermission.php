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
        $roleName = Session::get('logged_role_name', '');
        
        // مدير النظام يمتلك كافة الصلاحيات حكماً
        if (str_contains($roleName, 'مدير النظام') || str_contains($roleName, 'Super Admin')) {
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
