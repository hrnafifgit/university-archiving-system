<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. حقول أمان الحساب في جدول المستخدمين
        Schema::table('app_users', function (Blueprint $table) {
            if (!Schema::hasColumn('app_users', 'allow_direct_password_change')) {
                $table->boolean('allow_direct_password_change')->default(true);
            }
            if (!Schema::hasColumn('app_users', 'require_confirm_sensitive_edit')) {
                $table->boolean('require_confirm_sensitive_edit')->default(true);
            }
            if (!Schema::hasColumn('app_users', 'display_name_ar')) {
                $table->string('display_name_ar', 150)->nullable();
            }
        });

        // 2. جدول الصلاحيات الاستثنائية لمستخدم معين
        if (!Schema::hasTable('user_custom_permissions')) {
            Schema::create('user_custom_permissions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('user_id');
                $table->uuid('permission_id');
                $table->boolean('is_granted')->default(true);
                $table->timestamps();

                $table->foreign('user_id')->references('user_id')->on('app_users')->onDelete('cascade');
                $table->foreign('permission_id')->references('permission_id')->on('app_permissions')->onDelete('cascade');
                $table->unique(['user_id', 'permission_id']);
            });
        }

        // 3. جدول معاملات مسار الاعتماد المتسلسل (Confirm / Approval Workflow)
        if (!Schema::hasTable('workflow_transactions')) {
            Schema::create('workflow_transactions', function (Blueprint $table) {
                $table->uuid('transaction_id')->primary();
                $table->string('transaction_code', 50)->unique();
                $table->string('transaction_type', 60); // hourly_contract | budget | decision | employee_update
                $table->string('title', 250);
                $table->text('description')->nullable();
                $table->decimal('amount', 14, 2)->nullable();
                $table->string('department_name', 150)->nullable();
                $table->uuid('created_by')->nullable();
                $table->string('created_by_name', 150)->nullable();
                $table->integer('current_step')->default(1); // 1: إدخال، 2: رئيس قسم، 3: مدير إدارة، 4: أمين عام، 5: معتمد
                $table->string('status', 50)->default('draft'); // draft | pending_dept_head | pending_admin_manager | pending_sec_general | approved | returned
                $table->text('return_reason')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('user_id')->on('app_users')->nullOnDelete();
            });
        }

        // 4. جدول خطوات وسجل التوقيعات المتسلسل
        if (!Schema::hasTable('workflow_steps')) {
            Schema::create('workflow_steps', function (Blueprint $table) {
                $table->uuid('step_id')->primary();
                $table->uuid('transaction_id');
                $table->integer('step_number'); // 1, 2, 3, 4
                $table->string('step_title', 150);
                $table->string('required_role', 100);
                $table->uuid('actor_id')->nullable();
                $table->string('actor_name', 150)->nullable();
                $table->string('action', 40)->default('pending'); // submitted | confirmed | returned | pending
                $table->text('notes')->nullable();
                $table->timestamp('acted_at')->nullable();
                $table->timestamps();

                $table->foreign('transaction_id')->references('transaction_id')->on('workflow_transactions')->onDelete('cascade');
                $table->foreign('actor_id')->references('user_id')->on('app_users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_transactions');
        Schema::dropIfExists('user_custom_permissions');
    }
};
