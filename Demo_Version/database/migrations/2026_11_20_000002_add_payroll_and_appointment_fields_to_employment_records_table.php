<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employment_records', function (Blueprint $table) {
            $table->string('payroll_status', 100)->nullable()->comment('الحالة بحسب كشف الراتب');
            $table->string('payroll_job_title', 150)->nullable()->comment('الوظيفة الحالية بكشف الراتب');
            $table->string('payroll_job_grade', 100)->nullable()->comment('الدرجة بحسب كشف الراتب');
            $table->string('qualification_at_appointment', 150)->nullable()->comment('المؤهل عند التعيين بالجامعة أكاديمي');
            $table->date('first_appointment_date')->nullable()->comment('تاريخ التعيين بالوظيفة لأول مرة');
            $table->date('academic_appointment_date')->nullable()->comment('تاريخ التعيين بالجامعة أكاديمي');
            $table->string('initial_rank', 100)->nullable()->comment('الدرجة المعين فيها أول مرة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_records', function (Blueprint $table) {
            $table->dropColumn([
                'payroll_status',
                'payroll_job_title',
                'payroll_job_grade',
                'qualification_at_appointment',
                'first_appointment_date',
                'academic_appointment_date',
                'initial_rank',
            ]);
        });
    }
};
