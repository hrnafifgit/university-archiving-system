<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// لوحة التحكم الرئيسية
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// إدارة ودليل الكادر (CRUD)
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
