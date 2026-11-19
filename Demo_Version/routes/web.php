<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// مسارات المصادقة وتسجيل الدخول الموحد لجامعة إب
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// الصفحة الأولى: التوجيه لتسجيل الدخول إذا لم يكن المستخدم مسجلاً
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// المسارات المحمية بصلاحيات الدخول (Protected Application Routes)
Route::middleware('auth')->group(function () {

    // 1. لوحة التحكم والعمليات الرئيسية (مركز الأزرار والخدمات التنفيذية)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. شاشة الإحصائيات والمؤشرات الجامعية الشاملة (شاشة منفصلة متكاملة)
    Route::get('/statistics', [DashboardController::class, 'statistics'])->name('statistics');

    // 3. إدارة ودليل واستعلام الكادر (CRUD & Smart Filters)
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // 4. مركز التقارير وبيان الحالة الرسمية
    Route::get('/reports', function () {
        $staffId = request('staff_id');
        $staff = $staffId 
            ? \App\Models\StaffBase::with(['department.entity', 'academicDetails.rank', 'qualifications', 'decisions'])->find($staffId)
            : \App\Models\StaffBase::with(['department.entity', 'academicDetails.rank', 'qualifications', 'decisions'])->first();

        $allStaff = \App\Models\StaffBase::orderBy('full_name')->get(['id', 'full_name', 'job_number']);

        return view('reports.index', compact('staff', 'allStaff'));
    })->name('reports.index');

});
