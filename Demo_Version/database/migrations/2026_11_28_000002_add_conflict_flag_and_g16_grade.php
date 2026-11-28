<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'has_academic_conflict')) {
                $table->boolean('has_academic_conflict')->default(false)->after('approval_status');
            }
        });

        DB::table('job_grades')->insertOrIgnore([
            'job_grade_id' => Str::uuid()->toString(),
            'grade_code' => 'G16',
            'grade_name_ar' => 'الدرجة السادسة عشرة',
            'grade_name_en' => 'Grade 16',
            'description' => 'الدرجة السادسة عشرة الإدارية',
        ]);
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'has_academic_conflict')) {
                $table->dropColumn('has_academic_conflict');
            }
        });
    }
};
