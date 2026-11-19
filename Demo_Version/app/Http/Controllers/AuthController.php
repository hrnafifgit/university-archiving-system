<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * عرض شاشة تسجيل الدخول الموحدة
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * معالجة طلب تسجيل الدخول (يدعم الدخول المباشر بكلمة المرور)
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'يرجى إدخال كلمة المرور للمتابعة',
        ]);

        $password = trim($request->input('password'));
        $loginInput = trim($request->input('login', ''));
        $remember = $request->boolean('remember', true);

        // إذا لم يحدد اسم مستخدم، نعتمد حساب مسؤول النظام المعتمد
        if (empty($loginInput)) {
            $user = User::first();
        } else {
            $user = User::where('email', $loginInput)->first();
            if (!$user) {
                if ($loginInput === 'admin') {
                    $user = User::where('email', 'admin@ibbuniv.edu.ye')->first();
                } elseif ($loginInput === 'academic') {
                    $user = User::where('email', 'academic@ibbuniv.edu.ye')->first();
                } elseif ($loginInput === 'archivist') {
                    $user = User::where('email', 'archivist@ibbuniv.edu.ye')->first();
                }
            }
            if (!$user) {
                $user = User::first();
            }
        }

        // دعم كلمة المرور السريعة (123) أو (password) أو كلمة المرور المشفرة
        $isValidPassword = false;
        if ($user) {
            if (Hash::check($password, $user->password) || $password === '123' || $password === 'password' || $password === 'admin123') {
                $isValidPassword = true;
            }
        }

        if ($user && $isValidPassword) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'password' => 'كلمة المرور غير صحيحة، يرجى إدخال كلمة المرور المعتمدة.',
        ])->withInput();
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'تم تسجيل الخروج بنجاح من بوابة نظام الأرشفة الجامعي.');
    }
}
