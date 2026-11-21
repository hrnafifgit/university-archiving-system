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

        return $next($request);
    }
}
