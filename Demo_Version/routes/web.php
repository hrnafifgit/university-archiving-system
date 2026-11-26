<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\EnsureDesktopAuthenticated;
use App\Http\Middleware\EnsureHasPermission;
use Illuminate\Support\Facades\Route;

// مسارات المصادقة وتسجيل الدخول مع حماية Throttling من هجمات التخمين
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:60,1')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// كافة مسارات النظام محمية بواسطة جلسة المصادقة المباشرة
Route::middleware([EnsureDesktopAuthenticated::class])->group(function () {
    
    // لوحة العمليات المركزية والبحث الشامل
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/global-search', [DashboardController::class, 'globalSearch'])->name('global_search');

    // دليل وسجلات الكادر (CRUD)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    Route::get('/employees/import/template', [EmployeeController::class, 'downloadImportTemplate'])->name('employees.import.template');
    Route::post('/employees/import/excel', [EmployeeController::class, 'importExcel'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.import.excel');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.store');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->middleware(EnsureHasPermission::class . ':delete_employees')->name('employees.destroy');

    // عمليات الملف الموحد التكميلية (Modals & Actions)
    Route::put('/employees/{id}/basic', [\App\Http\Controllers\EmployeeOperationsController::class, 'updateBasic'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.update.basic');
    Route::post('/employees/{id}/assignments', [\App\Http\Controllers\EmployeeOperationsController::class, 'storeAssignment'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.assignments.store');
    Route::put('/employees/{id}/assignments/{assignmentId}', [\App\Http\Controllers\EmployeeOperationsController::class, 'updateAssignment'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.assignments.update');
    Route::post('/employees/{id}/qualifications', [\App\Http\Controllers\EmployeeOperationsController::class, 'storeQualification'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.qualifications.store');
    Route::put('/employees/{employeeId}/qualifications/{qualId}', [\App\Http\Controllers\EmployeeOperationsController::class, 'updateQualification'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.qualifications.update');
    Route::delete('/employees/{employeeId}/qualifications/{qualId}', [\App\Http\Controllers\EmployeeOperationsController::class, 'deleteQualification'])->middleware(EnsureHasPermission::class . ':manage_employees')->name('employees.qualifications.destroy');
    Route::post('/employees/{id}/documents', [\App\Http\Controllers\EmployeeOperationsController::class, 'storeDocument'])->middleware(EnsureHasPermission::class . ':manage_archive')->name('employees.documents.store');
    Route::delete('/employees/{employeeId}/documents/{docId}', [\App\Http\Controllers\EmployeeOperationsController::class, 'deleteDocument'])->middleware(EnsureHasPermission::class . ':delete_archive')->name('employees.documents.destroy');

    // مركز التقارير وبيان الحالة والمصفوفات الإحصائية
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/matrix-export/{matrix}', [\App\Http\Controllers\ReportController::class, 'exportMatrix'])->name('reports.matrix.export');

    // إدارة الهيكل والبيانات المرجعية (Master Data Management)
    Route::get('/master-data', [\App\Http\Controllers\MasterDataController::class, 'index'])->name('master_data.index');
    Route::get('/master-data/unit-stats/{id}', [\App\Http\Controllers\MasterDataController::class, 'getUnitStats'])->name('master_data.unit_stats');
    Route::middleware([EnsureHasPermission::class . ':manage_master_data'])->group(function () {
        // 1. الكليات والأقسام
        Route::post('/master-data/units', [\App\Http\Controllers\MasterDataController::class, 'storeUnit'])->name('master_data.units.store');
        Route::put('/master-data/units/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateUnit'])->name('master_data.units.update');
        Route::delete('/master-data/units/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyUnit'])->name('master_data.units.destroy');

        // 2. الرتب العلمية
        Route::post('/master-data/ranks', [\App\Http\Controllers\MasterDataController::class, 'storeRank'])->name('master_data.ranks.store');
        Route::put('/master-data/ranks/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateRank'])->name('master_data.ranks.update');
        Route::delete('/master-data/ranks/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyRank'])->name('master_data.ranks.destroy');

        // 3. أنواع الوثائق
        Route::post('/master-data/doc-types', [\App\Http\Controllers\MasterDataController::class, 'storeDocType'])->name('master_data.doctypes.store');
        Route::put('/master-data/doc-types/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateDocType'])->name('master_data.doctypes.update');
        Route::delete('/master-data/doc-types/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyDocType'])->name('master_data.doctypes.destroy');

        // 4. الدرجات الوظيفية
        Route::post('/master-data/grades', [\App\Http\Controllers\MasterDataController::class, 'storeGrade'])->name('master_data.grades.store');
        Route::put('/master-data/grades/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateGrade'])->name('master_data.grades.update');
        Route::delete('/master-data/grades/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyGrade'])->name('master_data.grades.destroy');

        // 5. الحالات الوظيفية
        Route::post('/master-data/statuses', [\App\Http\Controllers\MasterDataController::class, 'storeStatus'])->name('master_data.statuses.store');
        Route::put('/master-data/statuses/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateStatus'])->name('master_data.statuses.update');
        Route::delete('/master-data/statuses/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyStatus'])->name('master_data.statuses.destroy');

        // 6. المسميات الوظيفية
        Route::post('/master-data/job-titles', [\App\Http\Controllers\MasterDataController::class, 'storeJobTitle'])->name('master_data.jobtitles.store');
        Route::put('/master-data/job-titles/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateJobTitle'])->name('master_data.jobtitles.update');
        Route::delete('/master-data/job-titles/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyJobTitle'])->name('master_data.jobtitles.destroy');

        // 7. أنواع القرارات
        Route::post('/master-data/decision-types', [\App\Http\Controllers\MasterDataController::class, 'storeDecisionType'])->name('master_data.decisiontypes.store');
        Route::put('/master-data/decision-types/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateDecisionType'])->name('master_data.decisiontypes.update');
        Route::delete('/master-data/decision-types/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyDecisionType'])->name('master_data.decisiontypes.destroy');

        // 8. الجامعات
        Route::post('/master-data/universities', [\App\Http\Controllers\MasterDataController::class, 'storeUniversity'])->name('master_data.universities.store');
        Route::put('/master-data/universities/{id}', [\App\Http\Controllers\MasterDataController::class, 'updateUniversity'])->name('master_data.universities.update');
        Route::delete('/master-data/universities/{id}', [\App\Http\Controllers\MasterDataController::class, 'destroyUniversity'])->name('master_data.universities.destroy');
    });

    // القرارات الإدارية وحركات التفرغ والإجازات (Decisions & Movements)
    Route::get('/decisions', [\App\Http\Controllers\DecisionController::class, 'index'])->name('decisions.index');
    Route::post('/decisions', [\App\Http\Controllers\DecisionController::class, 'store'])->middleware(EnsureHasPermission::class . ':manage_decisions')->name('decisions.store');
    Route::put('/decisions/{id}', [\App\Http\Controllers\DecisionController::class, 'update'])->middleware(EnsureHasPermission::class . ':manage_decisions')->name('decisions.update');
    Route::delete('/decisions/{id}', [\App\Http\Controllers\DecisionController::class, 'destroy'])->middleware(EnsureHasPermission::class . ':delete_decisions')->name('decisions.destroy');
    Route::post('/decisions/absences', [\App\Http\Controllers\DecisionController::class, 'storeAbsence'])->middleware(EnsureHasPermission::class . ':manage_decisions')->name('decisions.absences.store');
    Route::put('/decisions/absences/{id}', [\App\Http\Controllers\DecisionController::class, 'updateAbsence'])->middleware(EnsureHasPermission::class . ':manage_decisions')->name('decisions.absences.update');
    Route::delete('/decisions/absences/{id}', [\App\Http\Controllers\DecisionController::class, 'destroyAbsence'])->middleware(EnsureHasPermission::class . ':delete_decisions')->name('decisions.absences.destroy');

    // المستودع الرقمي ومحرك بحث الأرشيف الإلكتروني (Digital Archive Explorer)
    Route::get('/archive', [\App\Http\Controllers\ArchiveController::class, 'index'])->name('archive.index');
    Route::get('/archive/export/excel', [\App\Http\Controllers\ArchiveController::class, 'exportExcel'])->name('archive.export.excel');
    Route::post('/archive/upload', [\App\Http\Controllers\ArchiveController::class, 'storeDirect'])->middleware(EnsureHasPermission::class . ':manage_archive')->name('archive.store');
    Route::post('/archive/bulk-upload', [\App\Http\Controllers\ArchiveController::class, 'bulkUpload'])->middleware(EnsureHasPermission::class . ':manage_archive')->name('archive.bulk_upload');
    Route::post('/archive/{docId}/versions', [\App\Http\Controllers\ArchiveController::class, 'uploadNewVersion'])->middleware(EnsureHasPermission::class . ':manage_archive')->name('archive.versions.store');
    Route::get('/archive/preview/{versionId}', [\App\Http\Controllers\ArchiveController::class, 'preview'])->name('archive.preview');
    Route::get('/archive/download/{versionId}', [\App\Http\Controllers\ArchiveController::class, 'download'])->name('archive.download');
    Route::delete('/archive/{id}', [\App\Http\Controllers\ArchiveController::class, 'destroy'])->middleware(EnsureHasPermission::class . ':manage_archive')->name('archive.destroy');
    Route::post('/archive/{id}/restore', [\App\Http\Controllers\ArchiveController::class, 'restore'])->middleware(EnsureHasPermission::class . ':manage_archive')->name('archive.restore');
    Route::delete('/archive/{id}/force', [\App\Http\Controllers\ArchiveController::class, 'forceDelete'])->middleware(EnsureHasPermission::class . ':manage_archive')->name('archive.force_delete');
    Route::get('/archive/{id}/audit-trail', [\App\Http\Controllers\ArchiveController::class, 'auditTrail'])->name('archive.audit_trail');

    // الإضافة السريعة للتصنيفات من القوائم المنسدلة (Quick Add Inline Modal Actions - مقيدة بصلاحية الهيكل)
    Route::middleware([EnsureHasPermission::class . ':manage_master_data'])->group(function () {
        Route::post('/quick-add/unit', [\App\Http\Controllers\QuickAddController::class, 'storeUnit'])->name('quick.add.unit');
        Route::post('/quick-add/rank', [\App\Http\Controllers\QuickAddController::class, 'storeRank'])->name('quick.add.rank');
        Route::post('/quick-add/grade', [\App\Http\Controllers\QuickAddController::class, 'storeGrade'])->name('quick.add.grade');
        Route::post('/quick-add/status', [\App\Http\Controllers\QuickAddController::class, 'storeStatus'])->name('quick.add.status');
        Route::post('/quick-add/doc-type', [\App\Http\Controllers\QuickAddController::class, 'storeDocType'])->name('quick.add.doctype');
        Route::post('/quick-add/qual-level', [\App\Http\Controllers\QuickAddController::class, 'storeQualLevel'])->name('quick.add.quallevel');
        Route::post('/quick-add/job-title', [\App\Http\Controllers\QuickAddController::class, 'storeJobTitle'])->name('quick.add.jobtitle');
        Route::post('/quick-add/university', [\App\Http\Controllers\QuickAddController::class, 'storeUniversity'])->name('quick.add.university');
    });

    // ==========================================================
    // إدارة الأدوار والصلاحيات التفصيلية + مسار الاعتماد المتسلسل
    // ==========================================================
    Route::get('/security/roles', [\App\Http\Controllers\RolePermissionController::class, 'index'])->name('security.roles.index');
    Route::post('/security/roles', [\App\Http\Controllers\RolePermissionController::class, 'updateMatrix'])->name('security.roles.update');
    Route::post('/security/roles/create', [\App\Http\Controllers\RolePermissionController::class, 'storeRole'])->name('security.roles.store');
    Route::post('/security/users/assign', [\App\Http\Controllers\RolePermissionController::class, 'assignEmployeeUser'])->name('security.users.assign');
    Route::post('/security/switch-role', [\App\Http\Controllers\RolePermissionController::class, 'switchRole'])->name('security.switch_role');

    Route::get('/workflow', [\App\Http\Controllers\ApprovalWorkflowController::class, 'index'])->name('workflow.index');
    Route::post('/workflow', [\App\Http\Controllers\ApprovalWorkflowController::class, 'store'])->name('workflow.store');
    Route::post('/workflow/{id}/act', [\App\Http\Controllers\ApprovalWorkflowController::class, 'act'])->name('workflow.act');

});
