<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\OrganizationalUnit;
use App\Models\AcademicRank;
use App\Models\JobGrade;
use App\Models\EmploymentStatus;
use App\Models\DocumentType;
use App\Models\QualificationLevel;

class PersonnelSystemTest extends TestCase
{
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('جامعة إب');
        $response->assertSee('نظام إدارة وأرشفة الكادر');
        $response->assertDontSee('UAS v2.0 Enterprise Desktop');
        $response->assertDontSee('اتصال مشفر');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);
        $response->assertRedirect('/');
        $this->assertEquals('admin', session('logged_username'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHas('error');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_all_views(): void
    {
        $views = [
            '/',
            '/employees',
            '/employees/create',
            '/decisions',
            '/archive',
            '/reports',
            '/master-data',
            '/master-data?tab=org_units',
            '/master-data?tab=ranks',
            '/master-data?tab=grades',
            '/master-data?tab=doc_types',
            '/master-data?tab=statuses',
        ];

        foreach ($views as $view) {
            $response = $this->withSession([
                'logged_user_id' => '00000000-0000-0000-0000-000000000001',
                'logged_username' => 'admin',
                'logged_role_name' => 'مدير النظام'
            ])->get($view);

            $response->assertStatus(200);
        }
    }

    public function test_quick_add_api_endpoints(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ];

        // 1. Quick add unit
        $unitRes = $this->withSession($session)->postJson('/quick-add/unit', [
            'name' => 'قسم اختبار الأنظمة الذكية',
            'type' => 'department'
        ]);
        $unitRes->assertStatus(200)->assertJson(['success' => true]);

        // 2. Quick add rank
        $rankRes = $this->withSession($session)->postJson('/quick-add/rank', [
            'name' => 'باحث ما بعد الدكتوراه'
        ]);
        $rankRes->assertStatus(200)->assertJson(['success' => true]);

        // 3. Quick add grade
        $gradeRes = $this->withSession($session)->postJson('/quick-add/grade', [
            'name' => 'الدرجة الخامسة المتميزة'
        ]);
        $gradeRes->assertStatus(200)->assertJson(['success' => true]);

        // 4. Quick add status
        $statusRes = $this->withSession($session)->postJson('/quick-add/status', [
            'name' => 'انتداب خارجي مؤقت'
        ]);
        $statusRes->assertStatus(200)->assertJson(['success' => true]);

        // 5. Quick add doc type
        $docRes = $this->withSession($session)->postJson('/quick-add/doc-type', [
            'name' => 'شهادة تكريم وبحث علمي'
        ]);
        $docRes->assertStatus(200)->assertJson(['success' => true]);

        // 6. Quick add qual level
        $qualRes = $this->withSession($session)->postJson('/quick-add/qual-level', [
            'name' => 'زمالة بحثية عليا'
        ]);
        $qualRes->assertStatus(200)->assertJson(['success' => true]);

        // 7. Infinite scroll test on /employees (10 records per scroll batch)
        $scrollRes = $this->withSession($session)->getJson('/employees?page=2&ajax=1');
        $scrollRes->assertStatus(200)
            ->assertJsonStructure([
                'html',
                'hasMore',
                'nextPage',
                'loadedCount',
                'total'
            ]);
        $this->assertEquals(20, $scrollRes->json('loadedCount'));
        $this->assertTrue($scrollRes->json('hasMore'));
        $this->assertEquals(3, $scrollRes->json('nextPage'));

        // Scroll to last page
        $total = $scrollRes->json('total');
        $lastPageNum = (int)ceil($total / 10);
        $lastPageRes = $this->withSession($session)->getJson('/employees?page=' . $lastPageNum . '&ajax=1');
        $lastPageRes->assertStatus(200);
        $this->assertFalse($lastPageRes->json('hasMore'));
        $this->assertEquals($total, $lastPageRes->json('loadedCount'));
    }

    public function test_qualification_and_assignment_crud_operations(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ];

        $employee = \App\Models\Employee::first();
        $this->assertNotNull($employee);

        // 1. Update basic info with address
        $res = $this->withSession($session)->put('/employees/' . $employee->employee_id . '/basic', [
            'full_name_ar' => $employee->full_name_ar,
            'gender' => $employee->gender ?? 'male',
            'employee_number' => $employee->employee_number,
            'address' => 'مدينة إب - شارع الجامعة الرئيسي',
            'notes' => 'تحديث تجريبي ناجح'
        ]);
        $res->assertSessionHas('success');
        $employee->refresh();
        $this->assertEquals('مدينة إب - شارع الجامعة الرئيسي', $employee->address);

        // 2. Add and Update Qualification
        $qualLevel = \App\Models\QualificationLevel::first();
        $storeQualRes = $this->withSession($session)->post('/employees/' . $employee->employee_id . '/qualifications', [
            'qualification_level_id' => $qualLevel->qualification_level_id,
            'degree_title' => 'ماجستير هندسة برمجيات متقدمة',
            'university_name' => 'جامعة إب',
            'grade_or_gpa' => 'امتياز',
            'is_highest' => '1',
        ]);
        $storeQualRes->assertSessionHas('success');

        $qual = \App\Models\EmployeeQualification::where('employee_id', $employee->employee_id)->latest('created_at')->first();
        $this->assertNotNull($qual);
        $this->assertEquals('ماجستير هندسة برمجيات متقدمة', $qual->degree_title);

        $updateQualRes = $this->withSession($session)->put('/employees/' . $employee->employee_id . '/qualifications/' . $qual->qualification_id, [
            'qualification_level_id' => $qualLevel->qualification_level_id,
            'degree_title' => 'دكتوراه هندسة برمجيات ونظم ذكية',
            'university_name' => 'جامعة القاهرة',
            'grade_or_gpa' => 'امتياز مع مرتبة الشرف',
            'is_highest' => '1',
        ]);
        $updateQualRes->assertSessionHas('success');
        $qual->refresh();
        $this->assertEquals('دكتوراه هندسة برمجيات ونظم ذكية', $qual->degree_title);

        // 3. Update Assignment
        $currentAssignment = $employee->currentEmployment;
        if ($currentAssignment) {
            $updateAssignRes = $this->withSession($session)->put('/employees/' . $employee->employee_id . '/assignments/' . $currentAssignment->employment_record_id, [
                'employee_category_id' => $currentAssignment->employee_category_id,
                'organizational_unit_id' => $currentAssignment->organizational_unit_id,
                'employment_status_id' => $currentAssignment->employment_status_id,
                'start_date' => $currentAssignment->start_date ? $currentAssignment->start_date->format('Y-m-d') : date('Y-m-d'),
                'payroll_status' => 'على رأس العمل - مؤكد',
                'payroll_job_title' => 'أستاذ دكتور رسمي',
            ]);
            $updateAssignRes->assertSessionHas('success');
            $currentAssignment->refresh();
            $this->assertEquals('على رأس العمل - مؤكد', $currentAssignment->payroll_status);
        }
    }

    public function test_decision_and_absence_crud_operations(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ];

        $employee = \App\Models\Employee::first();
        $decType = \App\Models\DecisionType::first();
        $decStatus = \App\Models\DecisionStatus::first();

        // 1. Create decision
        $storeDecRes = $this->withSession($session)->post('/decisions', [
            'employee_id' => $employee->employee_id,
            'decision_type_id' => $decType->decision_type_id,
            'decision_number' => 'TEST/DEC/2026/999',
            'issue_date' => date('Y-m-d'),
            'effective_start_date' => date('Y-m-d'),
            'decision_status_id' => $decStatus->decision_status_id,
            'summary' => 'قرار تجريبي لاختبار التحديث',
        ]);
        $storeDecRes->assertRedirect('/decisions?tab=decisions');

        $decision = \App\Models\EmployeeDecision::where('decision_number', 'TEST/DEC/2026/999')->first();
        $this->assertNotNull($decision);

        // 2. Update decision
        $updateDecRes = $this->withSession($session)->put('/decisions/' . $decision->decision_id, [
            'decision_type_id' => $decType->decision_type_id,
            'decision_number' => 'TEST/DEC/2026/999-UPDATED',
            'issue_date' => date('Y-m-d'),
            'effective_start_date' => date('Y-m-d'),
            'decision_status_id' => $decStatus->decision_status_id,
            'summary' => 'ملخص محدث للقرار التجريبي',
        ]);
        $updateDecRes->assertRedirect('/decisions?tab=decisions');
        $decision->refresh();
        $this->assertEquals('TEST/DEC/2026/999-UPDATED', $decision->decision_number);

        // 3. Create, update, delete absence
        $storeAbsRes = $this->withSession($session)->post('/decisions/absences', [
            'employee_id' => $employee->employee_id,
            'absence_type' => 'sabbatical',
            'start_date' => date('Y-m-d'),
            'status' => 'active',
            'destination_or_host' => 'جامعة أكسفورد - بريطانيا',
        ]);
        $storeAbsRes->assertRedirect('/decisions?tab=absences');

        $absence = \App\Models\EmployeeAbsence::where('employee_id', $employee->employee_id)->latest('start_date')->first();
        $this->assertNotNull($absence);

        $updateAbsRes = $this->withSession($session)->put('/decisions/absences/' . $absence->absence_id, [
            'absence_type' => 'sabbatical',
            'start_date' => date('Y-m-d'),
            'status' => 'completed',
            'destination_or_host' => 'جامعة كمبريدج - بريطانيا',
        ]);
        $updateAbsRes->assertRedirect('/decisions?tab=absences');
        $absence->refresh();
        $this->assertEquals('completed', $absence->status);

        // Delete absence
        $delAbsRes = $this->withSession($session)->delete('/decisions/absences/' . $absence->absence_id);
        $delAbsRes->assertRedirect('/decisions?tab=absences');
        $this->assertNull(\App\Models\EmployeeAbsence::find($absence->absence_id));

        // Delete decision
        $delDecRes = $this->withSession($session)->delete('/decisions/' . $decision->decision_id);
        $delDecRes->assertRedirect('/decisions');
        $this->assertNull(\App\Models\EmployeeDecision::find($decision->decision_id));
    }

    public function test_document_upload_storage_and_preview_workflow(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ];

        $employee = \App\Models\Employee::first();
        $docType = \App\Models\DocumentType::first();

        // 1. Direct upload from archive
        $docTitle = 'شهادة موثقة تجريبية ' . uniqid();
        $pdfContent = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('شهادة_جامعية.pdf', $pdfContent);

        $res = $this->withSession($session)->post('/archive/upload', [
            'employee_id' => $employee->employee_id,
            'document_type_id' => $docType->document_type_id,
            'document_title' => $docTitle,
            'document_number' => 'CERT-2026-001',
            'document_file' => $file,
            'description' => 'وثيقة اختبار للرفع والأرشفة',
        ]);

        $res->assertRedirect('/archive');
        $res->assertSessionHas('success');

        $doc = \App\Models\Document::where('document_title', $docTitle)->first();
        $this->assertNotNull($doc);
        $this->assertNotNull($doc->currentVersion);

        try {
            // Verify physical file was stored on secure local private disk
            \Illuminate\Support\Facades\Storage::disk('local')->assertExists($doc->currentVersion->storage_key);

            // 2. Preview the document
            $previewRes = $this->withSession($session)->get('/archive/preview/' . $doc->currentVersion->document_version_id);
            $previewRes->assertStatus(200);

            // 3. Download the document
            $downloadRes = $this->withSession($session)->get('/archive/download/' . $doc->currentVersion->document_version_id);
            $downloadRes->assertStatus(200);

            // 4. Upgrade version
            $newPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent('شهادة_محدثة_v2.pdf', $pdfContent . "\n%v2");
            $upgradeRes = $this->withSession($session)->post('/archive/' . $doc->document_id . '/versions', [
                'document_file' => $newPdf,
            ]);
            $upgradeRes->assertSessionHas('success');

            $doc->refresh();
            $this->assertEquals(2, $doc->versions()->count());
            $currentV = $doc->currentVersion;
            $this->assertNotNull($currentV);
            $this->assertEquals(2, $currentV->version_number);
            \Illuminate\Support\Facades\Storage::disk('local')->assertExists($currentV->storage_key);
        } finally {
            $doc->versions()->delete();
            $doc->delete();
        }
    }

    public function test_gender_classification_and_export_accuracy(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ];

        // 1. Cleanup any stale test records and create a test female employee
        \App\Models\Employee::where('employee_number', 'like', 'FEM-TEST%')->orWhere('national_id', '9988776655')->delete();
        $empCode = 'FEM-TEST-' . substr(uniqid(), -6);

        $femaleEmp = \App\Models\Employee::create([
            'full_name_ar' => 'د. سميرة أحمد يحيى الشرفي',
            'employee_number' => $empCode,
            'national_id' => '9988776655',
            'gender' => 'female',
            'is_active_record' => true,
        ]);

        $ou = \App\Models\OrganizationalUnit::whereIn('unit_type', ['faculty', 'college'])->first();
        $cat = \App\Models\EmployeeCategory::first();

        $rec = \App\Models\EmploymentRecord::create([
            'employee_id' => $femaleEmp->employee_id,
            'employee_category_id' => $cat->employee_category_id,
            'organizational_unit_id' => $ou->organizational_unit_id,
            'start_date' => now()->toDateString(),
            'is_primary' => true,
            'end_date' => null,
            'payroll_status' => 'مثبت',
        ]);

        try {
            // Check matrix eh8 gender
            $matrixService = app(\App\Services\ReportMatrixService::class);
            $eh8 = $matrixService->getMatrixEh8();
            $this->assertGreaterThan(0, $eh8['colTotals']['انثى']);

            // Check CSV export
            $response = $this->withSession($session)->get('/reports/export?search=' . urlencode('سميرة'));
            $response->assertStatus(200);
            $content = $response->streamedContent();
            $this->assertStringContainsString('أنثى', $content);
            $this->assertStringContainsString('سميرة أحمد يحيى الشرفي', $content);
        } finally {
            $rec->delete();
            $femaleEmp->delete();
        }
    }

    public function test_safe_document_deletion_without_foreign_key_violation(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ];

        $employee = \App\Models\Employee::first();
        $docType = \App\Models\DocumentType::first();

        $pdfContent = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('وثيقة_للحذف.pdf', $pdfContent);

        // Upload doc via employee operations
        $uploadRes = $this->withSession($session)->post('/employees/' . $employee->employee_id . '/documents', [
            'document_type_id' => $docType->document_type_id,
            'document_title' => 'وثيقة اختبار لحذفها بأمان',
            'document_file' => $file,
        ]);
        $uploadRes->assertSessionHas('success');

        $doc = \App\Models\Document::where('employee_id', $employee->employee_id)
            ->where('document_title', 'وثيقة اختبار لحذفها بأمان')
            ->first();
        $this->assertNotNull($doc);
        $this->assertEquals(1, $doc->versions()->count());

        // Now test deleteDocument - should delete cleanly without 500 foreign key error
        $deleteRes = $this->withSession($session)->delete('/employees/' . $employee->employee_id . '/documents/' . $doc->document_id);
        $deleteRes->assertSessionHas('success');

        $this->assertNull(\App\Models\Document::find($doc->document_id));
        $this->assertEquals(0, \App\Models\DocumentVersion::where('document_id', $doc->document_id)->count());
    }

    public function test_highest_qualification_exclusivity(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ];

        $employee = \App\Models\Employee::first();
        $qualLevel = \App\Models\QualificationLevel::first();

        // Add 1st qualification with is_highest
        $res1 = $this->withSession($session)->post('/employees/' . $employee->employee_id . '/qualifications', [
            'qualification_level_id' => $qualLevel->qualification_level_id,
            'degree_title' => 'مؤهل تجريبي أول',
            'is_highest' => '1',
        ]);
        $res1->assertSessionHas('success');

        $q1 = \App\Models\EmployeeQualification::where('employee_id', $employee->employee_id)
            ->where('degree_title', 'مؤهل تجريبي أول')
            ->first();
        $this->assertTrue((bool)$q1->is_highest_qualification);

        // Add 2nd qualification with is_highest
        $res2 = $this->withSession($session)->post('/employees/' . $employee->employee_id . '/qualifications', [
            'qualification_level_id' => $qualLevel->qualification_level_id,
            'degree_title' => 'مؤهل تجريبي ثان',
            'is_highest' => '1',
        ]);
        $res2->assertSessionHas('success');

        $q1->refresh();
        $q2 = \App\Models\EmployeeQualification::where('employee_id', $employee->employee_id)
            ->where('degree_title', 'مؤهل تجريبي ثان')
            ->first();

        // 1st must now be false, 2nd must be true
        $this->assertFalse((bool)$q1->is_highest_qualification);
        $this->assertTrue((bool)$q2->is_highest_qualification);

        // Cleanup
        $q1->delete();
        $q2->delete();
    }

    public function test_audit_logging_and_permission_enforcement(): void
    {
        // 1. Verify audit log creation
        $log = \App\Services\AuditLogService::log('TEST_ACTION', 'test_entity', null, ['old' => 'val1'], ['new' => 'val2']);
        $this->assertNotNull($log);
        $this->assertEquals('TEST_ACTION', $log->action_code);
        $this->assertEquals('test_entity', $log->entity_name);

        // 2. Verify permission enforcement for user WITHOUT delete_employees permission
        $restrictedSession = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000002',
            'logged_username' => 'viewer_user',
            'logged_role_name' => 'مستعرض تقارير (Viewer)',
            'logged_permissions' => ['view_reports'] // Only view_reports, lacks delete_employees!
        ];

        $employee = \App\Models\Employee::where('is_active_record', true)->first();

        // Attempt delete as restricted user
        $deleteRes = $this->withSession($restrictedSession)->delete('/employees/' . $employee->employee_id);
        
        // Must be redirected with error or forbidden
        $deleteRes->assertSessionHas('error');

        // Employee must still be active!
        $employee->refresh();
        $this->assertTrue((bool)$employee->is_active_record);
    }

    public function test_session_security_and_login_audit(): void
    {
        // Test login logs to audit_logs
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);
        $response->assertRedirect('/');

        $loginLog = \App\Models\AuditLog::where('action_code', 'LOGIN')
            ->orderByDesc('occurred_at')
            ->first();
        $this->assertNotNull($loginLog);
        $this->assertEquals('app_users', $loginLog->entity_name);

        // Test logout invalidates session and logs logout
        $logoutRes = $this->withSession([
            'logged_user_id' => $loginLog->user_id,
            'logged_username' => 'admin',
        ])->post('/logout');

        $logoutRes->assertRedirect('/login');

        $logoutLog = \App\Models\AuditLog::where('action_code', 'LOGOUT')
            ->orderByDesc('occurred_at')
            ->first();
        $this->assertNotNull($logoutLog);
    }

    public function test_custom_builder_report_without_legacy_json_dependency(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
        ];

        // Access custom builder report
        $response = $this->withSession($session)->get('/reports?type=custom_builder');
        $response->assertStatus(200);
        $response->assertViewHas('customReportData');

        $data = $response->viewData('customReportData');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        // Verify structure of the database-driven rows
        $firstRow = $data[0];
        $this->assertArrayHasKey('full_name_ar', $firstRow);
        $this->assertArrayHasKey('gender', $firstRow);
        $this->assertArrayHasKey('payroll_status', $firstRow);
        $this->assertArrayHasKey('faculty_name', $firstRow);
        $this->assertArrayHasKey('academic_rank', $firstRow);
    }

    public function test_dashboard_caching_and_ui_ergonomics(): void
    {
        $session = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
        ];

        // 1. Dashboard cache test
        \Illuminate\Support\Facades\Cache::forget('dashboard_core_metrics');
        $response1 = $this->withSession($session)->get('/');
        $response1->assertStatus(200);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('dashboard_core_metrics'));

        // Successive call should use cached payload
        $response2 = $this->withSession($session)->get('/');
        $response2->assertStatus(200);

        // 2. UI ergonomics: Body must not have user-select: none
        $response1->assertDontSee('user-select: none');
    }

    public function test_document_storage_service_and_safe_lifecycle(): void
    {
        $employee = \App\Models\Employee::where('is_active_record', true)->first();
        $this->assertNotNull($employee);

        // 1. Test storing document via unified service
        $file = \Illuminate\Http\UploadedFile::fake()->create('contract_official.pdf', 300, 'application/pdf');
        $doc = \App\Services\DocumentStorageService::storeEmployeeDocument($employee, $file, [
            'document_title' => 'عقد عمل تجريبي موحد',
            'document_number' => 'DOC-TEST-999',
            'is_confidential' => true,
        ]);

        $this->assertNotNull($doc);
        $this->assertEquals('عقد عمل تجريبي موحد', $doc->document_title);
        $this->assertNotNull($doc->currentVersion);
        $this->assertNotEmpty($doc->currentVersion->checksum_sha256);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists($doc->currentVersion->storage_key));

        // 2. Test deleting document via unified service
        \App\Services\DocumentStorageService::deleteDocument($doc);
        $this->assertNull(\App\Models\Document::find($doc->document_id));
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('local')->exists($doc->currentVersion->storage_key));
    }

    public function test_comprehensive_rbac_and_cache_invalidation_workflow(): void
    {
        // 1. User lacking manage_master_data attempting quick-add must receive 403
        $viewerSession = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000002',
            'logged_username' => 'viewer_user',
            'logged_role_name' => 'مستعرض تقارير (Viewer)',
            'logged_permissions' => ['view_reports'],
        ];

        $resBlocked = $this->withSession($viewerSession)->postJson('/quick-add/rank', [
            'name' => 'رتبة غير مصرح بها',
        ]);
        $resBlocked->assertStatus(403);

        // 2. Authorized admin user succeeds and creates audit log
        $adminSession = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام (Super Admin)',
            'logged_permissions' => ['manage_master_data', 'manage_employees', 'manage_archive'],
        ];

        $rankName = 'رتبة تجريبية موثقة ' . rand(100, 999);
        $resSuccess = $this->withSession($adminSession)->postJson('/quick-add/rank', [
            'name' => $rankName,
        ]);
        $resSuccess->assertStatus(200);
        $resSuccess->assertJson(['success' => true]);

        // Verify audit log created
        $audit = \App\Models\AuditLog::where('action_code', 'RANK_QUICK_CREATE')
            ->orderByDesc('occurred_at')
            ->first();
        $this->assertNotNull($audit);
        $this->assertStringContainsString($rankName, json_encode($audit->new_values, JSON_UNESCAPED_UNICODE));

        // Clean up test rank immediately to keep database clean
        \App\Models\AcademicRank::where('rank_name_ar', $rankName)->delete();
        $audit->delete();

        // 3. Test Dashboard cache invalidation on operations
        \Illuminate\Support\Facades\Cache::put('dashboard_core_metrics', ['dummy' => 'value'], 180);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('dashboard_core_metrics'));

        $employee = \App\Models\Employee::where('is_active_record', true)->first();
        $file = \Illuminate\Http\UploadedFile::fake()->create('memo.pdf', 100, 'application/pdf');

        $docType = \App\Models\DocumentType::first();
        $this->withSession($adminSession)->post('/employees/' . $employee->employee_id . '/documents', [
            'document_type_id' => $docType->document_type_id,
            'document_title' => 'مذكرة اختبار الكاش',
            'document_file' => $file,
        ]);

        // Dashboard cache must now be forgotten/invalidated!
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('dashboard_core_metrics'));

        // Clean up test document and its versions to prevent database pollution
        $createdDoc = \App\Models\Document::where('document_title', 'مذكرة اختبار الكاش')->first();
        if ($createdDoc) {
            \App\Models\DocumentVersion::where('document_id', $createdDoc->document_id)->delete();
            $createdDoc->delete();
        }
    }

    public function test_master_data_update_and_delete_operations(): void
    {
        $adminSession = [
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام (Super Admin)',
            'logged_permissions' => ['manage_master_data', 'manage_employees'],
        ];

        // 1. Create, Update, and Delete Unit
        $code = 'TEST_U_' . rand(1000, 9999);
        $resStoreUnit = $this->withSession($adminSession)->post('/master-data/units', [
            'unit_name_ar' => 'وحدة تجريبية لاختبار الحذف',
            'unit_code' => $code,
            'unit_type' => 'department',
        ]);
        $resStoreUnit->assertRedirect('/master-data?tab=org_units');
        $unit = \App\Models\OrganizationalUnit::where('unit_code', $code)->first();
        $this->assertNotNull($unit);

        // Update
        $resUpdateUnit = $this->withSession($adminSession)->put('/master-data/units/' . $unit->organizational_unit_id, [
            'unit_name_ar' => 'وحدة تجريبية معدلة بالكامل',
            'unit_code' => $code . '_UPD',
            'unit_type' => 'department',
        ]);
        $resUpdateUnit->assertRedirect('/master-data?tab=org_units');
        $unit->refresh();
        $this->assertEquals('وحدة تجريبية معدلة بالكامل', $unit->unit_name_ar);

        // Delete
        $resDelUnit = $this->withSession($adminSession)->delete('/master-data/units/' . $unit->organizational_unit_id);
        $resDelUnit->assertRedirect('/master-data?tab=org_units');
        $this->assertNull(\App\Models\OrganizationalUnit::find($unit->organizational_unit_id));

        // 2. Prevent Delete when Unit has related records
        $linkedUnit = \App\Models\EmploymentRecord::first()?->organizationalUnit;
        if ($linkedUnit) {
            $resDelLinked = $this->withSession($adminSession)->delete('/master-data/units/' . $linkedUnit->organizational_unit_id);
            $resDelLinked->assertSessionHas('error');
            $this->assertNotNull(\App\Models\OrganizationalUnit::find($linkedUnit->organizational_unit_id));
        }

        // 3. Create, Update, Delete Job Title
        $titleRes = $this->withSession($adminSession)->post('/master-data/job-titles', [
            'title_name_ar' => 'مسمى اختبار ' . rand(100, 999),
        ]);
        $titleRes->assertRedirect('/master-data?tab=job_titles');
        $jt = \App\Models\JobTitle::latest('job_title_id')->first();
        $this->assertNotNull($jt);

        $updateJtRes = $this->withSession($adminSession)->put('/master-data/job-titles/' . $jt->job_title_id, [
            'title_name_ar' => 'مسمى معدل ' . rand(100, 999),
            'title_name_en' => 'Updated Title',
        ]);
        $updateJtRes->assertRedirect('/master-data?tab=job_titles');

        $delJtRes = $this->withSession($adminSession)->delete('/master-data/job-titles/' . $jt->job_title_id);
        $delJtRes->assertRedirect('/master-data?tab=job_titles');
        $this->assertNull(\App\Models\JobTitle::find($jt->job_title_id));
    }
}
