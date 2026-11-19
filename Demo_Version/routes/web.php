<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportBuilderController;
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
    Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // مركز التقارير وبيان الحالة الرسمية
    Route::get('/reports', function () {
        $staffId = request('staff_id');
        $staff = $staffId 
            ? \App\Models\StaffBase::with(['department.entity', 'academicDetails.rank', 'qualifications', 'decisions'])->find($staffId)
            : \App\Models\StaffBase::with(['department.entity', 'academicDetails.rank', 'qualifications', 'decisions'])->first();

        return view('reports.index', compact('staff'));
    })->name('reports.index');

    // مركز المؤشرات والإحصائيات التفاعلي (مكافئ شاشات eh1 إلى eh8 في الإكسل)
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // منشئ التقارير المخصصة واختيار الأعمدة (مكافئ واجهة ReportForm وشيت Repj1)
    Route::get('/reports/builder', [ReportBuilderController::class, 'index'])->name('reports.builder');
    Route::get('/reports/builder/export', [ReportBuilderController::class, 'exportCsv'])->name('reports.builder.export');

});
