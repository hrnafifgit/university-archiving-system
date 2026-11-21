<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // تمكين الامتداد في حال لم يكن ممكّناً
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto;');

        // 1. data_sources
        Schema::create('data_sources', function (Blueprint $table) {
            $table->uuid('data_source_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('source_name', 150)->unique();
            $table->text('description')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
        });

        // 2. countries
        Schema::create('countries', function (Blueprint $table) {
            $table->id('country_id');
            $table->string('country_name_ar', 150);
            $table->string('country_name_en', 150)->nullable();
            $table->char('iso_code', 2)->nullable()->unique();
        });

        // 3. organizational_units
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->uuid('organizational_unit_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('parent_unit_id')->nullable();
            $table->string('unit_code', 50)->nullable()->unique();
            $table->string('unit_name_ar', 200);
            $table->string('unit_name_en', 200)->nullable();
            $table->string('unit_type', 30);
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->default(DB::raw('now()'));
        });
        DB::statement('ALTER TABLE organizational_units ADD CONSTRAINT fk_parent_unit FOREIGN KEY (parent_unit_id) REFERENCES organizational_units(organizational_unit_id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE organizational_units ADD CONSTRAINT chk_parent_not_self CHECK (parent_unit_id IS NULL OR parent_unit_id <> organizational_unit_id);');

        // 4. employee_categories
        Schema::create('employee_categories', function (Blueprint $table) {
            $table->smallIncrements('employee_category_id');
            $table->string('category_code', 40)->unique();
            $table->string('category_name_ar', 120)->unique();
            $table->string('category_name_en', 120)->nullable();
            $table->text('description')->nullable();
        });

        // 5. employment_statuses
        Schema::create('employment_statuses', function (Blueprint $table) {
            $table->smallIncrements('employment_status_id');
            $table->string('status_code', 40)->unique();
            $table->string('status_name_ar', 120)->unique();
            $table->string('status_name_en', 120)->nullable();
            $table->boolean('is_active_status')->default(true);
        });

        // 6. job_titles
        Schema::create('job_titles', function (Blueprint $table) {
            $table->uuid('job_title_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('title_name_ar', 180)->unique();
            $table->string('title_name_en', 180)->nullable();
            $table->unsignedSmallInteger('employee_category_id')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('employee_category_id')->references('employee_category_id')->on('employee_categories')->nullOnDelete();
        });

        // 7. job_grades
        Schema::create('job_grades', function (Blueprint $table) {
            $table->uuid('job_grade_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('grade_code', 50)->unique();
            $table->string('grade_name_ar', 120);
            $table->string('grade_name_en', 120)->nullable();
            $table->text('description')->nullable();
        });

        // 8. academic_ranks
        Schema::create('academic_ranks', function (Blueprint $table) {
            $table->uuid('academic_rank_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('rank_code', 50)->unique();
            $table->string('rank_name_ar', 120)->unique();
            $table->string('rank_name_en', 120)->nullable();
            $table->smallInteger('rank_order')->nullable();
        });

        // 9. employees
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('employee_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('employee_number', 50)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->string('full_name_ar', 250);
            $table->string('full_name_en', 250)->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 180)->nullable();
            $table->string('phone_number', 40)->nullable();
            $table->string('email', 254)->nullable();
            $table->text('address')->nullable();
            $table->uuid('photo_document_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active_record')->default(true);
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->timestampTz('updated_at')->default(DB::raw('now()'));
            $table->uuid('created_from_source_id')->nullable();

            $table->foreign('created_from_source_id')->references('data_source_id')->on('data_sources')->nullOnDelete();
        });

        DB::statement("CREATE UNIQUE INDEX uq_employees_employee_number ON employees(employee_number) WHERE employee_number IS NOT NULL AND btrim(employee_number) <> '';");
        DB::statement("CREATE UNIQUE INDEX uq_employees_national_id ON employees(national_id) WHERE national_id IS NOT NULL AND btrim(national_id) <> '';");
        DB::statement("CREATE INDEX ix_employees_name_ar ON employees (full_name_ar);");
        DB::statement("CREATE INDEX ix_employees_phone ON employees (phone_number);");

        // 10. decision_types
        Schema::create('decision_types', function (Blueprint $table) {
            $table->smallIncrements('decision_type_id');
            $table->string('type_code', 50)->unique();
            $table->string('type_name_ar', 150)->unique();
            $table->string('type_name_en', 150)->nullable();
            $table->boolean('requires_end_date')->default(false);
        });

        // 11. decision_statuses
        Schema::create('decision_statuses', function (Blueprint $table) {
            $table->smallIncrements('decision_status_id');
            $table->string('status_code', 40)->unique();
            $table->string('status_name_ar', 100)->unique();
            $table->string('status_name_en', 100)->nullable();
        });

        // 12. employee_decisions
        Schema::create('employee_decisions', function (Blueprint $table) {
            $table->uuid('decision_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->unsignedSmallInteger('decision_type_id');
            $table->string('decision_number', 100)->nullable();
            $table->string('issuing_authority', 200)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('effective_start_date')->nullable();
            $table->date('effective_end_date')->nullable();
            $table->unsignedSmallInteger('decision_status_id')->nullable();
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('data_source_id')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));

            $table->foreign('employee_id')->references('employee_id')->on('employees')->restrictOnDelete();
            $table->foreign('decision_type_id')->references('decision_type_id')->on('decision_types');
            $table->foreign('decision_status_id')->references('decision_status_id')->on('decision_statuses')->nullOnDelete();
            $table->foreign('data_source_id')->references('data_source_id')->on('data_sources')->nullOnDelete();
        });
        DB::statement("CREATE INDEX ix_decisions_employee ON employee_decisions(employee_id);");
        DB::statement("CREATE INDEX ix_decisions_number ON employee_decisions(decision_number);");

        // 13. employment_records
        Schema::create('employment_records', function (Blueprint $table) {
            $table->uuid('employment_record_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->unsignedSmallInteger('employee_category_id');
            $table->uuid('organizational_unit_id')->nullable();
            $table->uuid('job_title_id')->nullable();
            $table->uuid('job_grade_id')->nullable();
            $table->uuid('academic_rank_id')->nullable();
            $table->unsignedSmallInteger('employment_status_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->string('appointment_basis', 100)->nullable();
            $table->uuid('decision_id')->nullable();
            $table->uuid('data_source_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));

            $table->foreign('employee_id')->references('employee_id')->on('employees')->restrictOnDelete();
            $table->foreign('employee_category_id')->references('employee_category_id')->on('employee_categories');
            $table->foreign('organizational_unit_id')->references('organizational_unit_id')->on('organizational_units')->nullOnDelete();
            $table->foreign('job_title_id')->references('job_title_id')->on('job_titles')->nullOnDelete();
            $table->foreign('job_grade_id')->references('job_grade_id')->on('job_grades')->nullOnDelete();
            $table->foreign('academic_rank_id')->references('academic_rank_id')->on('academic_ranks')->nullOnDelete();
            $table->foreign('employment_status_id')->references('employment_status_id')->on('employment_statuses')->nullOnDelete();
            $table->foreign('decision_id')->references('decision_id')->on('employee_decisions')->nullOnDelete();
            $table->foreign('data_source_id')->references('data_source_id')->on('data_sources')->nullOnDelete();
        });
        DB::statement("CREATE INDEX ix_employment_employee_dates ON employment_records(employee_id, start_date DESC);");
        DB::statement("CREATE INDEX ix_employment_unit ON employment_records(organizational_unit_id);");
        DB::statement("CREATE UNIQUE INDEX uq_one_current_primary_employment ON employment_records(employee_id) WHERE is_primary = TRUE AND end_date IS NULL;");

        // 14. universities
        Schema::create('universities', function (Blueprint $table) {
            $table->uuid('university_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('university_name_ar', 220);
            $table->string('university_name_en', 220)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();

            $table->foreign('country_id')->references('country_id')->on('countries')->nullOnDelete();
            $table->unique(['university_name_ar', 'country_id']);
        });

        // 15. qualification_levels
        Schema::create('qualification_levels', function (Blueprint $table) {
            $table->smallIncrements('qualification_level_id');
            $table->string('level_code', 40)->unique();
            $table->string('level_name_ar', 120)->unique();
            $table->string('level_name_en', 120)->nullable();
            $table->smallInteger('level_order')->nullable();
        });

        // 16. specializations
        Schema::create('specializations', function (Blueprint $table) {
            $table->uuid('specialization_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('parent_specialization_id')->nullable();
            $table->string('specialization_name_ar', 200);
            $table->string('specialization_name_en', 200)->nullable();
            $table->string('specialization_type', 20);
            $table->unique(['specialization_name_ar', 'specialization_type']);
        });
        DB::statement('ALTER TABLE specializations ADD CONSTRAINT fk_parent_specialization FOREIGN KEY (parent_specialization_id) REFERENCES specializations(specialization_id) ON DELETE SET NULL;');

        // 17. employee_qualifications
        Schema::create('employee_qualifications', function (Blueprint $table) {
            $table->uuid('qualification_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->unsignedSmallInteger('qualification_level_id');
            $table->uuid('specialization_id')->nullable();
            $table->uuid('university_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('degree_title', 200)->nullable();
            $table->string('grade_or_gpa', 100)->nullable();
            $table->date('graduation_date')->nullable();
            $table->date('award_date')->nullable();
            $table->boolean('is_highest_qualification')->default(false);
            $table->string('verification_status', 30)->default('unknown');
            $table->date('verification_date')->nullable();
            $table->uuid('data_source_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));

            $table->foreign('employee_id')->references('employee_id')->on('employees')->restrictOnDelete();
            $table->foreign('qualification_level_id')->references('qualification_level_id')->on('qualification_levels');
            $table->foreign('specialization_id')->references('specialization_id')->on('specializations')->nullOnDelete();
            $table->foreign('university_id')->references('university_id')->on('universities')->nullOnDelete();
            $table->foreign('country_id')->references('country_id')->on('countries')->nullOnDelete();
            $table->foreign('data_source_id')->references('data_source_id')->on('data_sources')->nullOnDelete();
        });
        DB::statement("CREATE INDEX ix_qualifications_employee ON employee_qualifications(employee_id);");
        DB::statement("CREATE INDEX ix_qualifications_level ON employee_qualifications(qualification_level_id);");

        // 18. document_types
        Schema::create('document_types', function (Blueprint $table) {
            $table->uuid('document_type_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('type_code', 60)->unique();
            $table->string('type_name_ar', 160)->unique();
            $table->string('type_name_en', 160)->nullable();
            $table->string('applies_to', 30)->default('employee');
            $table->boolean('is_required_by_default')->default(false);
            $table->boolean('is_active')->default(true);
        });

        // 19. documents
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('document_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->uuid('document_type_id');
            $table->string('document_title', 250);
            $table->string('document_number', 100)->nullable();
            $table->date('document_date')->nullable();
            $table->string('issuing_authority', 200)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_confidential')->default(true);
            $table->date('retention_until')->nullable();
            $table->uuid('uploaded_by_user_id')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->timestampTz('updated_at')->default(DB::raw('now()'));

            $table->foreign('employee_id')->references('employee_id')->on('employees')->restrictOnDelete();
            $table->foreign('document_type_id')->references('document_type_id')->on('document_types');
        });
        DB::statement("CREATE INDEX ix_documents_employee ON documents(employee_id);");
        DB::statement("CREATE INDEX ix_documents_type ON documents(document_type_id);");

        // 20. document_versions
        Schema::create('document_versions', function (Blueprint $table) {
            $table->uuid('document_version_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('document_id');
            $table->integer('version_number');
            $table->text('storage_key')->unique();
            $table->string('original_file_name', 255);
            $table->string('mime_type', 120)->nullable();
            $table->bigInteger('file_size_bytes')->nullable();
            $table->char('checksum_sha256', 64)->nullable();
            $table->timestampTz('uploaded_at')->default(DB::raw('now()'));
            $table->boolean('is_current')->default(false);

            $table->foreign('document_id')->references('document_id')->on('documents')->restrictOnDelete();
            $table->unique(['document_id', 'version_number']);
        });
        DB::statement("CREATE UNIQUE INDEX uq_document_one_current_version ON document_versions(document_id) WHERE is_current = TRUE;");

        // ربط صورة الموظف بعد إنشاء جدول documents
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('photo_document_id')->references('document_id')->on('documents')->nullOnDelete();
        });

        // 21. qualification_documents
        Schema::create('qualification_documents', function (Blueprint $table) {
            $table->uuid('qualification_id');
            $table->uuid('document_id');
            $table->primary(['qualification_id', 'document_id']);

            $table->foreign('qualification_id')->references('qualification_id')->on('employee_qualifications')->cascadeOnDelete();
            $table->foreign('document_id')->references('document_id')->on('documents')->restrictOnDelete();
        });

        // 22. decision_documents
        Schema::create('decision_documents', function (Blueprint $table) {
            $table->uuid('decision_id');
            $table->uuid('document_id');
            $table->primary(['decision_id', 'document_id']);

            $table->foreign('decision_id')->references('decision_id')->on('employee_decisions')->cascadeOnDelete();
            $table->foreign('document_id')->references('document_id')->on('documents')->restrictOnDelete();
        });

        // 23. employment_documents
        Schema::create('employment_documents', function (Blueprint $table) {
            $table->uuid('employment_record_id');
            $table->uuid('document_id');
            $table->primary(['employment_record_id', 'document_id']);

            $table->foreign('employment_record_id')->references('employment_record_id')->on('employment_records')->cascadeOnDelete();
            $table->foreign('document_id')->references('document_id')->on('documents')->restrictOnDelete();
        });

        // 24. employee_absences
        Schema::create('employee_absences', function (Blueprint $table) {
            $table->uuid('absence_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->string('absence_type', 40);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('destination_or_host', 250)->nullable();
            $table->string('status', 30)->default('active');
            $table->uuid('decision_id')->nullable();
            $table->text('notes')->nullable();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->restrictOnDelete();
            $table->foreign('decision_id')->references('decision_id')->on('employee_decisions')->nullOnDelete();
        });
        DB::statement("CREATE INDEX ix_absences_employee_dates ON employee_absences(employee_id, start_date DESC);");

        // 25. app_users
        Schema::create('app_users', function (Blueprint $table) {
            $table->uuid('user_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id')->nullable();
            $table->string('username', 100)->unique();
            $table->text('password_hash');
            $table->boolean('is_active')->default(true);
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));

            $table->foreign('employee_id')->references('employee_id')->on('employees')->nullOnDelete();
        });

        // 26. app_roles & permissions
        Schema::create('app_roles', function (Blueprint $table) {
            $table->uuid('role_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('role_name', 100)->unique();
            $table->text('description')->nullable();
        });

        Schema::create('app_permissions', function (Blueprint $table) {
            $table->uuid('permission_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('permission_code', 120)->unique();
            $table->text('description')->nullable();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->primary(['user_id', 'role_id']);

            $table->foreign('user_id')->references('user_id')->on('app_users')->cascadeOnDelete();
            $table->foreign('role_id')->references('role_id')->on('app_roles')->cascadeOnDelete();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->primary(['role_id', 'permission_id']);

            $table->foreign('role_id')->references('role_id')->on('app_roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('permission_id')->on('app_permissions')->cascadeOnDelete();
        });

        // 27. audit_logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('audit_log_id');
            $table->uuid('user_id')->nullable();
            $table->string('action_code', 80);
            $table->string('entity_name', 120);
            $table->uuid('entity_id')->nullable();
            $table->timestampTz('occurred_at')->default(DB::raw('now()'));
            $table->ipAddress('ip_address')->nullable();
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();

            $table->foreign('user_id')->references('user_id')->on('app_users')->nullOnDelete();
        });
        DB::statement("CREATE INDEX ix_audit_logs_entity ON audit_logs(entity_name, entity_id);");
        DB::statement("CREATE INDEX ix_audit_logs_date ON audit_logs(occurred_at DESC);");

        // 28. import_batches & import_row_errors
        Schema::create('import_batches', function (Blueprint $table) {
            $table->uuid('import_batch_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('source_file_name', 255);
            $table->uuid('data_source_id')->nullable();
            $table->uuid('imported_by_user_id')->nullable();
            $table->timestampTz('imported_at')->default(DB::raw('now()'));
            $table->integer('total_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('rejected_rows')->default(0);
            $table->text('notes')->nullable();

            $table->foreign('data_source_id')->references('data_source_id')->on('data_sources')->nullOnDelete();
            $table->foreign('imported_by_user_id')->references('user_id')->on('app_users')->nullOnDelete();
        });

        Schema::create('import_row_errors', function (Blueprint $table) {
            $table->id('import_row_error_id');
            $table->uuid('import_batch_id');
            $table->integer('source_row_number');
            $table->jsonb('row_data')->nullable();
            $table->text('error_details');

            $table->foreign('import_batch_id')->references('import_batch_id')->on('import_batches')->cascadeOnDelete();
        });

        // 29. employee_source_snapshots
        Schema::create('employee_source_snapshots', function (Blueprint $table) {
            $table->uuid('employee_source_snapshot_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->uuid('data_source_id');
            $table->date('snapshot_as_of_date');
            $table->string('reported_employment_status', 150)->nullable();
            $table->string('reported_job_title', 180)->nullable();
            $table->string('reported_job_grade', 120)->nullable();
            $table->string('reported_organizational_unit', 220)->nullable();
            $table->date('reported_first_appointment_date')->nullable();
            $table->date('reported_academic_appointment_date')->nullable();
            $table->string('reported_qualification_at_appointment', 180)->nullable();
            $table->jsonb('raw_source_data')->nullable();
            $table->timestampTz('imported_at')->default(DB::raw('now()'));
            $table->text('notes')->nullable();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->restrictOnDelete();
            $table->foreign('data_source_id')->references('data_source_id')->on('data_sources')->restrictOnDelete();
            $table->unique(['employee_id', 'data_source_id', 'snapshot_as_of_date']);
        });
        DB::statement("CREATE INDEX ix_employee_source_snapshots_employee_date ON employee_source_snapshots(employee_id, snapshot_as_of_date DESC);");

        // 30. v_employee_current_primary_employment VIEW
        DB::statement("
            CREATE OR REPLACE VIEW v_employee_current_primary_employment AS
            SELECT
                e.employee_id,
                e.employee_number,
                e.full_name_ar,
                er.employment_record_id,
                er.start_date,
                er.organizational_unit_id,
                ou.unit_name_ar,
                ec.category_name_ar,
                jt.title_name_ar,
                jg.grade_name_ar,
                ar.rank_name_ar,
                es.status_name_ar
            FROM employees e
            LEFT JOIN employment_records er
              ON er.employee_id = e.employee_id
             AND er.is_primary = TRUE
             AND er.end_date IS NULL
            LEFT JOIN organizational_units ou ON ou.organizational_unit_id = er.organizational_unit_id
            LEFT JOIN employee_categories ec ON ec.employee_category_id = er.employee_category_id
            LEFT JOIN job_titles jt ON jt.job_title_id = er.job_title_id
            LEFT JOIN job_grades jg ON jg.job_grade_id = er.job_grade_id
            LEFT JOIN academic_ranks ar ON ar.academic_rank_id = er.academic_rank_id
            LEFT JOIN employment_statuses es ON es.employment_status_id = er.employment_status_id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_employee_current_primary_employment CASCADE;");
        Schema::dropIfExists('employee_source_snapshots');
        Schema::dropIfExists('import_row_errors');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('app_permissions');
        Schema::dropIfExists('app_roles');
        Schema::dropIfExists('app_users');
        Schema::dropIfExists('employee_absences');
        Schema::dropIfExists('employment_documents');
        Schema::dropIfExists('decision_documents');
        Schema::dropIfExists('qualification_documents');
        Schema::dropIfExists('document_versions');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['photo_document_id']);
        });
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('employee_qualifications');
        Schema::dropIfExists('specializations');
        Schema::dropIfExists('qualification_levels');
        Schema::dropIfExists('universities');
        Schema::dropIfExists('employment_records');
        Schema::dropIfExists('employee_decisions');
        Schema::dropIfExists('decision_statuses');
        Schema::dropIfExists('decision_types');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('academic_ranks');
        Schema::dropIfExists('job_grades');
        Schema::dropIfExists('job_titles');
        Schema::dropIfExists('employment_statuses');
        Schema::dropIfExists('employee_categories');
        Schema::dropIfExists('organizational_units');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('data_sources');
    }
};
