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
        // 1. الكيان التنظيمي الأعلى (Level 1: كلية / مركز / دائرة / عمادة)
        Schema::create('organizational_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الكيان (مثال: كلية الهندسة وتكنولوجيا المعلومات)
            $table->string('entity_type')->default('كلية'); // كلية / مركز / دائرة / عمادة
            $table->string('nature')->default('أكاديمي'); // أكاديمي / إداري / خدمي
            $table->string('code')->unique(); // الرمز المختصر (مثال: ENG)
            $table->timestamps();
        });

        // 2. القسم أو الوحدة التابعة (Level 2: قسم علمي / وحدة إدارية)
        Schema::create('departments_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('organizational_entities')->cascadeOnDelete();
            $table->string('name'); // اسم القسم (مثال: قسم علوم الحاسوب)
            $table->string('unit_type')->default('قسم علمي'); // قسم علمي / وحدة إدارية
            $table->string('code')->nullable(); // الرمز الكودي
            $table->date('established_at')->nullable();
            $table->timestamps();
        });

        // 3. الرتب العلمية (Academic Ranks)
        Schema::create('academic_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // أستاذ دكتور / أستاذ مشارك / أستاذ مساعد / مدرس / معيد
            $table->unsignedInteger('seniority_order')->default(1); // ترتيب الأقدمية
            $table->timestamps();
        });

        // 4. الدرجات الوظيفية للخدمة المدنية (Job Grades)
        Schema::create('job_grades', function (Blueprint $table) {
            $table->id();
            $table->string('grade_name'); // الدرجة الأولى، الثانية، العاشرة...
            $table->string('executive_level')->nullable(); // قيادي / إشرافي / تنفيذي
            $table->timestamps();
        });

        // 5. سلم الرواتب والبدلات المعتمد (Salary Scale)
        Schema::create('salary_scale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_rank_id')->nullable()->constrained('academic_ranks')->nullOnDelete();
            $table->foreignId('job_grade_id')->nullable()->constrained('job_grades')->nullOnDelete();
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('specialization_allowance', 12, 2)->default(0);
            $table->decimal('total_entitlement', 12, 2)->default(0);
            $table->timestamps();
        });

        // 6. كادر الجامعة - الكيان الأب الموحد (Level 3: staff_base)
        Schema::create('staff_base', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments_units')->cascadeOnDelete();
            $table->string('job_number', 50)->unique(); // الرقم الوظيفي
            $table->string('national_id', 50)->unique(); // الرقم الوطني
            $table->string('full_name'); // الاسم الرباعي
            $table->enum('gender', ['ذكر', 'أنثى'])->default('ذكر');
            $table->date('birth_date')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->date('hire_date')->nullable(); // تاريخ التعيين بالجامعة
            $table->enum('staff_type', ['academic', 'administrative', 'contract']); // نوع الكادر (Subtype)
            $table->string('job_status')->default('مثبت'); // مثبت / مفرغ / منتدب / إجازة / بدون راتب
            $table->timestamps();
        });

        // 7. تفاصيل الكادر الأكاديمي (Level 4 - A: 1:1 Subtype)
        Schema::create('academic_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->unique()->constrained('staff_base')->cascadeOnDelete();
            $table->foreignId('rank_id')->constrained('academic_ranks')->restrictOnDelete();
            $table->string('general_specialization')->nullable(); // التخصص العام
            $table->string('exact_specialization')->nullable(); // التخصص الدقيق
            $table->string('academic_status')->default('مستمر'); // مستمر / مفرغ للدراسة / تفرغ علمي
            $table->unsignedInteger('teaching_load')->default(12); // العبء التدريسي
            $table->date('last_promotion_date')->nullable();
            $table->timestamps();
        });

        // 8. تفاصيل الكادر الإداري (Level 4 - B: 1:1 Subtype)
        Schema::create('administrative_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->unique()->constrained('staff_base')->cascadeOnDelete();
            $table->foreignId('job_grade_id')->constrained('job_grades')->restrictOnDelete();
            $table->string('job_title'); // المسمى الوظيفي
            $table->string('work_nature')->default('مكتبي'); // مكتبي / ميداني / فني
            $table->date('admin_confirmation_date')->nullable(); // تاريخ التثبيت
            $table->string('performance_rating')->nullable();
            $table->timestamps();
        });

        // 9. تفاصيل المتعاقدين بالساعات (Level 4 - C: 1:1 Subtype)
        Schema::create('contract_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->unique()->constrained('staff_base')->cascadeOnDelete();
            $table->string('contract_type')->default('ساعات فصلي');
            $table->unsignedInteger('approved_hours')->default(8);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->timestamps();
        });

        // 10. المؤهلات العلمية التراكمية (Level 5: 1:N qualifications)
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff_base')->cascadeOnDelete();
            $table->string('degree_type'); // بكالوريوس / ماجستير / دكتوراه
            $table->string('specialization'); // التخصص العلمي
            $table->string('granting_institution'); // الجامعة المانحة
            $table->unsignedSmallInteger('graduation_year')->nullable(); // سنة التخرج
            $table->string('country')->nullable(); // دولة التخرج
            $table->text('thesis_title')->nullable(); // عنوان الأطروحة
            $table->timestamps();
        });

        // 11. سجل القرارات الإدارية (Level 5: 1:N administrative_decisions)
        Schema::create('administrative_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff_base')->cascadeOnDelete();
            $table->string('decision_number'); // رقم القرار الرسمي
            $table->string('decision_type'); // تعيين / ترقية / إيفاد / تفرغ / تثبيت
            $table->date('issued_date'); // تاريخ صدور القرار
            $table->date('effective_date')->nullable(); // تاريخ سريان ونفاذ القرار
            $table->string('status')->default('ساري'); // ساري / ملغي / منتهي
            $table->text('notes')->nullable(); // حيثيات وملاحظات
            $table->timestamps();
        });

        // 12. الأرشيف الرقمي الشجري (Level 5: 1:N digital_archives)
        Schema::create('digital_archives', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // معرّف فريد للوثيقة لمنع التداخل
            $table->foreignId('staff_id')->constrained('staff_base')->cascadeOnDelete();
            $table->foreignId('decision_id')->nullable()->constrained('administrative_decisions')->nullOnDelete();
            $table->foreignId('qualification_id')->nullable()->constrained('qualifications')->nullOnDelete();
            $table->string('document_title'); // اسم وعنوان الوثيقة
            $table->string('tree_category'); // تصنيف الشجرة: مؤهلات / قرارات / وثائق ثبوتية
            $table->string('file_path'); // مسار التخزين المحمي بالسيرفر
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->unsignedInteger('page_count')->default(1);
            $table->string('mime_type')->default('application/pdf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_archives');
        Schema::dropIfExists('administrative_decisions');
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('contract_details');
        Schema::dropIfExists('administrative_details');
        Schema::dropIfExists('academic_details');
        Schema::dropIfExists('staff_base');
        Schema::dropIfExists('salary_scale');
        Schema::dropIfExists('job_grades');
        Schema::dropIfExists('academic_ranks');
        Schema::dropIfExists('departments_units');
        Schema::dropIfExists('organizational_entities');
    }
};
