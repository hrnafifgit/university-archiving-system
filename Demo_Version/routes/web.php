<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// مسارات المصادقة وتسجيل الدخول الموحد لجامعة إب
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// المسارات المحمية بصلاحيات الدخول (Protected Application Routes)
Route::middleware('auth')->group(function () {

    // لوحة التحكم والقيادة الرئيسية
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // إدارة ودليل واستعلام الكادر (CRUD & Smart Filters)
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // مركز التقارير وبيان الحالة الرسمية
    Route::get('/reports', function () {
        $staffId = request('staff_id');
        $staff = $staffId 
            ? \App\Models\StaffBase::with(['department.entity', 'academicDetails.rank', 'qualifications', 'decisions'])->find($staffId)
            : \App\Models\StaffBase::with(['department.entity', 'academicDetails.rank', 'qualifications', 'decisions'])->first();

        return view('reports.index', compact('staff'));
    })->name('reports.index');

});
