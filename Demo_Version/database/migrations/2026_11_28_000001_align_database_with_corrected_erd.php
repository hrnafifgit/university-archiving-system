<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تعديل قاعدة البيانات لتتطابق مع مخطط ERD (Chen) المصحح النهائي.
     * يتم التنفيذ بشكل غير هدام (Non-destructive) مع ترحيل البيانات الحالية للجداول الجديدة.
     */
    public function up(): void
    {
        // =====================================================================
        // 1. الجداول المرجعية الجديدة: الموقف الوظيفي وحالة كشف الراتب
        // =====================================================================
        if (!Schema::hasTable('job_positions')) {
            Schema::create('job_positions', function (Blueprint $table) {
                $table->smallIncrements('job_position_id');
                $table->string('position_code', 50)->unique();
                $table->string('position_name_ar', 150)->unique();
                $table->string('position_name_en', 150)->nullable();
            });
        }

        if (!Schema::hasTable('payroll_statuses')) {
            Schema::create('payroll_statuses', function (Blueprint $table) {
                $table->smallIncrements('payroll_status_id');
                $table->string('status_name_ar', 150)->unique();
                $table->string('status_name_en', 150)->nullable();
                $table->string('scope', 100)->nullable();
            });
        }

        // =====================================================================
        // 2. تحديث كيان «كادر» (employees) بالصفات والعلاقات الجديدة
        // =====================================================================
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'first_appointment_date')) {
                $table->date('first_appointment_date')->nullable()->after('address');
            }
            if (!Schema::hasColumn('employees', 'appointment_authority')) {
                $table->string('appointment_authority', 220)->nullable()->after('first_appointment_date');
            }
            if (!Schema::hasColumn('employees', 'approval_status')) {
                $table->string('approval_status', 40)->default('approved')->after('notes');
            }
            if (!Schema::hasColumn('employees', 'fine_specialization_id')) {
                $table->uuid('fine_specialization_id')->nullable()->after('approval_status');
                $table->foreign('fine_specialization_id', 'fk_emp_fine_spec')
                    ->references('specialization_id')->on('specializations')->nullOnDelete();
            }
            if (!Schema::hasColumn('employees', 'appointment_qualification_level_id')) {
                $table->unsignedSmallInteger('appointment_qualification_level_id')->nullable()->after('fine_specialization_id');
                $table->foreign('appointment_qualification_level_id', 'fk_emp_appt_qual_level')
                    ->references('qualification_level_id')->on('qualification_levels')->nullOnDelete();
            }
        });

        // =====================================================================
        // 3. تفعيل التخصص المتداخل الجزئي ISA: أكاديمي، إداري، متعاقد
        // =====================================================================
        if (!Schema::hasTable('academic_staff')) {
            Schema::create('academic_staff', function (Blueprint $table) {
                $table->uuid('employee_id')->primary();
                $table->date('academic_appointment_date')->nullable();
                $table->boolean('is_department_head')->default(false);

                $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('administrative_staff')) {
            Schema::create('administrative_staff', function (Blueprint $table) {
                $table->uuid('employee_id')->primary();
                $table->date('admin_appointment_date')->nullable();
                $table->string('contract_type', 100)->nullable();

                $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('contractors')) {
            Schema::create('contractors', function (Blueprint $table) {
                $table->uuid('employee_id')->primary();
                $table->date('first_contract_date')->nullable();
                $table->string('job_classification', 60)->nullable(); // إدارية | فنية | أكاديمية

                $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
            });
        }

        // الكيان الضعيف التابع للمتعاقد: عقد متعاقد (يُبرم_عقداً 1:N!)
        if (!Schema::hasTable('contractor_contracts')) {
            Schema::create('contractor_contracts', function (Blueprint $table) {
                $table->uuid('contract_id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('employee_id');
                $table->string('contract_number', 100)->nullable();
                $table->string('wage_system', 40)->nullable(); // شهري | بالساعة
                $table->decimal('approved_wage', 12, 2)->nullable();
                $table->smallInteger('weekly_hours')->nullable();
                $table->date('contract_start_date');
                $table->date('contract_end_date')->nullable();
                $table->string('contract_status', 30)->nullable(); // ساري | منتهٍ
                $table->timestampTz('created_at')->default(DB::raw('now()'));

                $table->foreign('employee_id')->references('employee_id')->on('contractors')->cascadeOnDelete();
            });
            DB::statement("CREATE INDEX ix_contractor_contracts_emp ON contractor_contracts(employee_id, contract_start_date DESC);");
        }

        // ربط الوثائق بالعقود: تثبت عقداً (docs N : 1 contract)
        if (!Schema::hasTable('contract_documents')) {
            Schema::create('contract_documents', function (Blueprint $table) {
                $table->uuid('contract_id');
                $table->uuid('document_id');
                $table->primary(['contract_id', 'document_id']);

                $table->foreign('contract_id')->references('contract_id')->on('contractor_contracts')->cascadeOnDelete();
                $table->foreign('document_id')->references('document_id')->on('documents')->restrictOnDelete();
            });
        }

        // =====================================================================
        // 4. العلاقات التاريخية (M:N) حسب المخطط
        // =====================================================================
        // يعمل_في: كادر (N) <-> وحدة تنظيمية (M)
        if (!Schema::hasTable('employee_organizational_units')) {
            Schema::create('employee_organizational_units', function (Blueprint $table) {
                $table->uuid('employee_unit_id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('employee_id');
                $table->uuid('organizational_unit_id');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_primary')->default(true);

                $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
                $table->foreign('organizational_unit_id')->references('organizational_unit_id')->on('organizational_units')->restrictOnDelete();
            });
            DB::statement("CREATE INDEX ix_emp_org_units_emp ON employee_organizational_units(employee_id);");
        }

        // يتدرج_برتبة: أكاديمي (N) <-> رتبة علمية (M)
        if (!Schema::hasTable('academic_rank_history')) {
            Schema::create('academic_rank_history', function (Blueprint $table) {
                $table->uuid('academic_rank_history_id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('employee_id');
                $table->uuid('academic_rank_id');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_current')->default(true);

                $table->foreign('employee_id')->references('employee_id')->on('academic_staff')->cascadeOnDelete();
                $table->foreign('academic_rank_id')->references('academic_rank_id')->on('academic_ranks')->restrictOnDelete();
            });
            DB::statement("CREATE INDEX ix_academic_rank_hist_emp ON academic_rank_history(employee_id);");
        }

        // ينال_درجة: إداري (N) <-> درجة وظيفية (M)
        if (!Schema::hasTable('administrative_job_grades')) {
            Schema::create('administrative_job_grades', function (Blueprint $table) {
                $table->uuid('admin_grade_id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('employee_id');
                $table->uuid('job_grade_id');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_current')->default(true);

                $table->foreign('employee_id')->references('employee_id')->on('administrative_staff')->cascadeOnDelete();
                $table->foreign('job_grade_id')->references('job_grade_id')->on('job_grades')->restrictOnDelete();
            });
            DB::statement("CREATE INDEX ix_admin_job_grades_emp ON administrative_job_grades(employee_id);");
        }

        // =====================================================================
        // 5. تحديث «سجل وظيفي» (employment_records) وإنشاء «كشف راتب» (payroll_records)
        // =====================================================================
        Schema::table('employment_records', function (Blueprint $table) {
            if (!Schema::hasColumn('employment_records', 'job_position_id')) {
                $table->unsignedSmallInteger('job_position_id')->nullable()->after('job_title_id');
                $table->foreign('job_position_id', 'fk_emp_rec_job_pos')
                    ->references('job_position_id')->on('job_positions')->nullOnDelete();
            }
        });

        if (!Schema::hasTable('payroll_records')) {
            Schema::create('payroll_records', function (Blueprint $table) {
                $table->uuid('payroll_record_id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('employee_id');
                $table->string('payroll_month', 20)->nullable();
                $table->unsignedSmallInteger('payroll_status_id')->nullable();
                $table->uuid('job_title_id')->nullable();
                $table->uuid('job_grade_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestampTz('created_at')->default(DB::raw('now()'));

                $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
                $table->foreign('payroll_status_id')->references('payroll_status_id')->on('payroll_statuses')->nullOnDelete();
                $table->foreign('job_title_id')->references('job_title_id')->on('job_titles')->nullOnDelete();
                $table->foreign('job_grade_id')->references('job_grade_id')->on('job_grades')->nullOnDelete();
            });
            DB::statement("CREATE INDEX ix_payroll_records_emp ON payroll_records(employee_id);");
        }

        // =====================================================================
        // 6. تحديث «قرار إداري» (employee_decisions) وعلاقة «يشمل» (M:N)
        // =====================================================================
        Schema::table('employee_decisions', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_decisions', 'annual_number')) {
                $table->string('annual_number', 50)->nullable()->after('decision_number');
            }
            if (!Schema::hasColumn('employee_decisions', 'decision_year')) {
                $table->smallInteger('decision_year')->nullable()->after('annual_number');
            }
            if (!Schema::hasColumn('employee_decisions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('summary');
            }
        });

        // جعل employee_id اختيارياً في employee_decisions لدعم القرارات الجماعية عبر decision_employees
        DB::statement('ALTER TABLE employee_decisions ALTER COLUMN employee_id DROP NOT NULL;');

        if (!Schema::hasTable('decision_employees')) {
            Schema::create('decision_employees', function (Blueprint $table) {
                $table->uuid('decision_id');
                $table->uuid('employee_id');
                $table->primary(['decision_id', 'employee_id']);

                $table->foreign('decision_id')->references('decision_id')->on('employee_decisions')->cascadeOnDelete();
                $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
            });
        }

        // =====================================================================
        // 7. تحديث «مؤهل علمي» (employee_qualifications) بنمط الدراسة وقرار الإيفاد
        // =====================================================================
        Schema::table('employee_qualifications', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_qualifications', 'study_mode')) {
                $table->string('study_mode', 30)->nullable()->after('grade_or_gpa'); // داخلي | خارجي
            }
            if (!Schema::hasColumn('employee_qualifications', 'dispatch_decision_id')) {
                $table->uuid('dispatch_decision_id')->nullable()->after('country_id');
                $table->foreign('dispatch_decision_id', 'fk_qual_dispatch_dec')
                    ->references('decision_id')->on('employee_decisions')->nullOnDelete();
            }
        });

        // =====================================================================
        // 8. تحديث «مستخدم النظام» (app_users) و«صلاحية» (app_permissions)
        // =====================================================================
        Schema::table('app_users', function (Blueprint $table) {
            if (!Schema::hasColumn('app_users', 'permission_level')) {
                $table->string('permission_level', 50)->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('app_users', 'data_scope')) {
                $table->string('data_scope', 60)->nullable()->after('permission_level'); // ذاتي | أكاديمي | إداري | موفدين | قانونية
            }
            if (!Schema::hasColumn('app_users', 'organizational_unit_id')) {
                $table->uuid('organizational_unit_id')->nullable()->after('data_scope');
                $table->foreign('organizational_unit_id', 'fk_user_org_unit')
                    ->references('organizational_unit_id')->on('organizational_units')->nullOnDelete();
            }
        });

        Schema::table('app_permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('app_permissions', 'screen_name')) {
                $table->string('screen_name', 100)->nullable()->after('permission_code');
            }
            if (!Schema::hasColumn('app_permissions', 'action_name')) {
                $table->string('action_name', 60)->nullable()->after('screen_name');
            }
        });

        // =====================================================================
        // 9. إنشاء «طلب اعتماد وتعديل» (approval_change_requests) و«موافقة» (request_approvals)
        // =====================================================================
        if (!Schema::hasTable('approval_change_requests')) {
            Schema::create('approval_change_requests', function (Blueprint $table) {
                $table->uuid('request_id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('employee_id');
                $table->uuid('requested_by_user_id');
                $table->string('request_type', 60);
                $table->string('change_category', 80)->nullable();
                $table->string('competent_authority', 100)->nullable(); // الموارد البشرية | الشؤون الأكاديمية
                $table->string('request_channel', 60)->nullable();
                $table->jsonb('requested_values')->nullable();
                $table->string('status', 40)->default('pending');
                $table->timestampTz('submitted_at')->default(DB::raw('now()'));

                $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
                $table->foreign('requested_by_user_id')->references('user_id')->on('app_users')->restrictOnDelete();
            });
            DB::statement("CREATE INDEX ix_acr_employee ON approval_change_requests(employee_id);");
            DB::statement("CREATE INDEX ix_acr_user ON approval_change_requests(requested_by_user_id);");
        }

        if (!Schema::hasTable('request_approvals')) {
            Schema::create('request_approvals', function (Blueprint $table) {
                $table->uuid('approval_id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('request_id');
                $table->uuid('approved_by_user_id');
                $table->string('approval_level', 50);
                $table->string('action', 40);
                $table->text('signature')->nullable();
                $table->timestampTz('acted_at')->default(DB::raw('now()'));

                $table->foreign('request_id')->references('request_id')->on('approval_change_requests')->cascadeOnDelete();
                $table->foreign('approved_by_user_id')->references('user_id')->on('app_users')->restrictOnDelete();
            });
            DB::statement("CREATE INDEX ix_req_approvals_req ON request_approvals(request_id);");
        }

        // =====================================================================
        // 10. ترحيل البيانات الحالية إلى الجداول الجديدة (Data Backfill)
        // =====================================================================
        // أ. ترحيل القرارات الحالية إلى جدول يشمل (decision_employees)
        DB::statement("
            INSERT INTO decision_employees (decision_id, employee_id)
            SELECT decision_id, employee_id
            FROM employee_decisions
            WHERE employee_id IS NOT NULL
            ON CONFLICT DO NOTHING;
        ");

        // ب. ترحيل بيانات التعيين الأول من employment_records إلى employees
        DB::statement("
            UPDATE employees e
            SET first_appointment_date = er.first_appointment_date
            FROM (
                SELECT DISTINCT ON (employee_id) employee_id, first_appointment_date
                FROM employment_records
                WHERE first_appointment_date IS NOT NULL
                ORDER BY employee_id, start_date ASC
            ) er
            WHERE e.employee_id = er.employee_id
              AND e.first_appointment_date IS NULL;
        ");

        // ج. ترحيل ارتباط الوحدات التنظيمية إلى جدول يعمل_في (employee_organizational_units)
        DB::statement("
            INSERT INTO employee_organizational_units (employee_id, organizational_unit_id, start_date, end_date, is_primary)
            SELECT DISTINCT ON (employee_id, organizational_unit_id, start_date)
                employee_id, organizational_unit_id, start_date, end_date, is_primary
            FROM employment_records
            WHERE organizational_unit_id IS NOT NULL;
        ");

        // د. ترحيل الكادر إلى الجداول الفرعية (أكاديمي / إداري / متعاقد) بناءً على الفئة الحالية
        DB::statement("
            INSERT INTO academic_staff (employee_id, academic_appointment_date)
            SELECT DISTINCT er.employee_id, MIN(er.academic_appointment_date)
            FROM employment_records er
            JOIN employee_categories ec ON ec.employee_category_id = er.employee_category_id
            WHERE ec.category_code ILIKE '%academic%' OR ec.category_name_ar LIKE '%أكاديمي%'
            GROUP BY er.employee_id
            ON CONFLICT (employee_id) DO NOTHING;
        ");

        DB::statement("
            INSERT INTO administrative_staff (employee_id, admin_appointment_date)
            SELECT DISTINCT er.employee_id, MIN(er.start_date)
            FROM employment_records er
            JOIN employee_categories ec ON ec.employee_category_id = er.employee_category_id
            WHERE ec.category_code ILIKE '%admin%' OR ec.category_name_ar LIKE '%إداري%'
            GROUP BY er.employee_id
            ON CONFLICT (employee_id) DO NOTHING;
        ");

        DB::statement("
            INSERT INTO contractors (employee_id, first_contract_date)
            SELECT DISTINCT er.employee_id, MIN(er.start_date)
            FROM employment_records er
            JOIN employee_categories ec ON ec.employee_category_id = er.employee_category_id
            WHERE ec.category_code ILIKE '%contract%' OR ec.category_code ILIKE '%hourly%' OR ec.category_name_ar LIKE '%متعاقد%'
            GROUP BY er.employee_id
            ON CONFLICT (employee_id) DO NOTHING;
        ");

        // هـ. ترحيل سجل الرتب العلمية للأكاديميين والدرجات الوظيفية للإداريين
        DB::statement("
            INSERT INTO academic_rank_history (employee_id, academic_rank_id, start_date, end_date, is_current)
            SELECT er.employee_id, er.academic_rank_id, er.start_date, er.end_date, (er.end_date IS NULL)
            FROM employment_records er
            JOIN academic_staff a ON a.employee_id = er.employee_id
            WHERE er.academic_rank_id IS NOT NULL;
        ");

        DB::statement("
            INSERT INTO administrative_job_grades (employee_id, job_grade_id, start_date, end_date, is_current)
            SELECT er.employee_id, er.job_grade_id, er.start_date, er.end_date, (er.end_date IS NULL)
            FROM employment_records er
            JOIN administrative_staff adm ON adm.employee_id = er.employee_id
            WHERE er.job_grade_id IS NOT NULL;
        ");
    }

    /**
     * التراجع عن التعديلات.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_approvals');
        Schema::dropIfExists('approval_change_requests');

        Schema::table('app_permissions', function (Blueprint $table) {
            $table->dropColumn(['screen_name', 'action_name']);
        });

        Schema::table('app_users', function (Blueprint $table) {
            $table->dropForeign('fk_user_org_unit');
            $table->dropColumn(['permission_level', 'data_scope', 'organizational_unit_id']);
        });

        Schema::table('employee_qualifications', function (Blueprint $table) {
            $table->dropForeign('fk_qual_dispatch_dec');
            $table->dropColumn(['study_mode', 'dispatch_decision_id']);
        });

        Schema::dropIfExists('decision_employees');

        Schema::table('employee_decisions', function (Blueprint $table) {
            $table->dropColumn(['annual_number', 'decision_year', 'is_active']);
        });

        Schema::dropIfExists('payroll_records');

        Schema::table('employment_records', function (Blueprint $table) {
            $table->dropForeign('fk_emp_rec_job_pos');
            $table->dropColumn('job_position_id');
        });

        Schema::dropIfExists('administrative_job_grades');
        Schema::dropIfExists('academic_rank_history');
        Schema::dropIfExists('employee_organizational_units');
        Schema::dropIfExists('contract_documents');
        Schema::dropIfExists('contractor_contracts');
        Schema::dropIfExists('contractors');
        Schema::dropIfExists('administrative_staff');
        Schema::dropIfExists('academic_staff');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('fk_emp_fine_spec');
            $table->dropForeign('fk_emp_appt_qual_level');
            $table->dropColumn([
                'first_appointment_date',
                'appointment_authority',
                'approval_status',
                'fine_specialization_id',
                'appointment_qualification_level_id',
            ]);
        });

        Schema::dropIfExists('payroll_statuses');
        Schema::dropIfExists('job_positions');
    }
};
