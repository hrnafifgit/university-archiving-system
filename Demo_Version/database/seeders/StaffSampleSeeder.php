<?php

namespace Database\Seeders;

use App\Models\AcademicDetail;
use App\Models\AcademicRank;
use App\Models\AdministrativeDecision;
use App\Models\AdministrativeDetail;
use App\Models\ContractDetail;
use App\Models\DepartmentUnit;
use App\Models\DigitalArchive;
use App\Models\JobGrade;
use App\Models\Qualification;
use App\Models\StaffBase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StaffSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (StaffBase::count() > 0) {
            return;
        }

        $csDept = DepartmentUnit::where('name', 'like', '%علوم الحاسوب%')->first() ?? DepartmentUnit::first();
        $dentDept = DepartmentUnit::where('name', 'like', '%تقويم الاسنان%')->orWhere('name', 'like', '%الاسنان%')->first() ?? DepartmentUnit::skip(1)->first();
        $adminDept = DepartmentUnit::where('name', 'like', '%إدارة%')->orWhere('name', 'like', '%ادارة الأعمال%')->first() ?? DepartmentUnit::skip(2)->first();

        $profRank = AcademicRank::where('name', 'like', '%أستاذ دكتور%')->first() ?? AcademicRank::first();
        $assocRank = AcademicRank::where('name', 'like', '%مشارك%')->first() ?? AcademicRank::skip(1)->first();
        $assistRank = AcademicRank::where('name', 'like', '%مساعد%')->first() ?? AcademicRank::skip(2)->first();

        $grade1 = JobGrade::where('grade_name', 'like', '%الأولى%')->first() ?? JobGrade::first();
        $grade2 = JobGrade::where('grade_name', 'like', '%الثانية%')->first() ?? JobGrade::skip(1)->first();

        // 1. أ.د. محمد علي الإرياني (أكاديمي)
        $s1 = StaffBase::create([
            'department_id' => $csDept->id,
            'job_number' => '10201',
            'national_id' => '03010045812',
            'full_name' => 'أ.د. محمد علي أحمد الإرياني',
            'gender' => 'ذكر',
            'birth_date' => '1975-04-12',
            'phone' => '771234567',
            'email' => 'm.eryani@ibbuniv.edu.ye',
            'hire_date' => '2004-09-01',
            'staff_type' => 'academic',
            'job_status' => 'مثبت',
        ]);

        AcademicDetail::create([
            'staff_id' => $s1->id,
            'rank_id' => $profRank->id,
            'general_specialization' => 'علوم الحاسوب وتكنولوجيا المعلومات',
            'exact_specialization' => 'هندسة البرمجيات والذكاء الاصطناعي',
            'academic_status' => 'مستمر بالعمل',
            'teaching_load' => 10,
            'last_promotion_date' => '2019-05-15',
        ]);

        Qualification::create([
            'staff_id' => $s1->id,
            'degree_type' => 'دكتوراه',
            'specialization' => 'هندسة برمجيات متقدمة',
            'granting_institution' => 'جامعة القاهرة',
            'graduation_year' => 2004,
            'country' => 'مصر',
            'thesis_title' => 'معمارية النظم الموزعة عالية الأداء',
        ]);

        $d1 = AdministrativeDecision::create([
            'staff_id' => $s1->id,
            'decision_number' => 'ق/2019/88',
            'decision_type' => 'قرار ترقية علمية',
            'issued_date' => '2019-05-15',
            'effective_date' => '2019-06-01',
            'status' => 'ساري',
            'notes' => 'ترقية إلى رتبة أستاذ دكتور (بروفيسور) بقرار مجلس الجامعة الموقر.',
        ]);

        DigitalArchive::create([
            'uuid' => (string) Str::uuid(),
            'staff_id' => $s1->id,
            'decision_id' => $d1->id,
            'qualification_id' => null,
            'document_title' => 'قرار الترقية العلمية لرتبة أستاذ دكتور',
            'tree_category' => 'القرارات الإدارية',
            'file_path' => 'archives/sample_decision.pdf',
            'file_size_bytes' => 1024,
            'page_count' => 1,
            'mime_type' => 'application/pdf',
        ]);

        // 2. د. فاطمة أحمد الحبيشي (أكاديمي)
        $s2 = StaffBase::create([
            'department_id' => $dentDept->id,
            'job_number' => '10202',
            'national_id' => '03010078945',
            'full_name' => 'د. فاطمة أحمد قاسم الحبيشي',
            'gender' => 'أنثى',
            'birth_date' => '1982-11-20',
            'phone' => '777894561',
            'email' => 'f.hubaishi@ibbuniv.edu.ye',
            'hire_date' => '2010-02-15',
            'staff_type' => 'academic',
            'job_status' => 'مثبت',
        ]);

        AcademicDetail::create([
            'staff_id' => $s2->id,
            'rank_id' => $assocRank->id,
            'general_specialization' => 'طب وجراحة الفم والأسنان',
            'exact_specialization' => 'تقويم الأسنان والفكين',
            'academic_status' => 'مستمر بالعمل',
            'teaching_load' => 12,
            'last_promotion_date' => '2021-09-01',
        ]);

        // 3. أ. بشير عبدالله الحداء (إداري)
        $s3 = StaffBase::create([
            'department_id' => $adminDept->id,
            'job_number' => '20301',
            'national_id' => '03010012399',
            'full_name' => 'أ. بشير عبدالله محمد الحداء',
            'gender' => 'ذكر',
            'birth_date' => '1986-07-08',
            'phone' => '773344556',
            'email' => 'b.haddaa@ibbuniv.edu.ye',
            'hire_date' => '2012-04-01',
            'staff_type' => 'administrative',
            'job_status' => 'مثبت',
        ]);

        AdministrativeDetail::create([
            'staff_id' => $s3->id,
            'job_grade_id' => $grade2->id ?? 2,
            'job_title' => 'مدير إدارة الشؤون الإدارية والمالية',
            'work_nature' => 'إشرافي وتنسيقي',
            'admin_confirmation_date' => '2015-01-10',
            'performance_rating' => 'ممتاز',
        ]);

        // 4. م. طارق خالد القاضي (متعاقد)
        $s4 = StaffBase::create([
            'department_id' => $csDept->id,
            'job_number' => '30401',
            'national_id' => '03010099887',
            'full_name' => 'م. طارق خالد أحمد القاضي',
            'gender' => 'ذكر',
            'birth_date' => '1995-03-25',
            'phone' => '775566778',
            'email' => 't.qadhi@ibbuniv.edu.ye',
            'hire_date' => '2022-10-01',
            'staff_type' => 'contract',
            'job_status' => 'متعاقد',
        ]);

        ContractDetail::create([
            'staff_id' => $s4->id,
            'contract_type' => 'ساعات فصلي',
            'approved_hours' => 14,
            'hourly_rate' => 3500.00,
            'contract_start_date' => '2026-09-01',
            'contract_end_date' => '2027-06-30',
        ]);
    }
}
