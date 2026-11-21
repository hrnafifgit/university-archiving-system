<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PersonnelRecordsMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Sources
        $dsIbbManual = Str::uuid()->toString();
        $dsExcelOld = Str::uuid()->toString();

        DB::table('data_sources')->insertOrIgnore([
            [
                'data_source_id' => $dsIbbManual,
                'source_name' => 'الإدخال المباشر - شؤون أعضاء هيئة التدريس',
                'description' => 'البيانات الموثقة والمراجعة مباشرة من إدارة شؤون هيئة التدريس بجامعة إب',
                'created_at' => now(),
            ],
            [
                'data_source_id' => $dsExcelOld,
                'source_name' => 'نظام الإكسل السابق (ibbunvAkademy.xlsm)',
                'description' => 'البيانات المأخوذة من قاعدة بيانات الإكسل القديمة مع مسارات الأرشيف D:\archac26',
                'created_at' => now(),
            ],
        ]);

        // 2. Countries
        $countries = [
            ['country_name_ar' => 'الجمهورية اليمنية', 'country_name_en' => 'Yemen', 'iso_code' => 'YE'],
            ['country_name_ar' => 'جمهورية مصر العربية', 'country_name_en' => 'Egypt', 'iso_code' => 'EG'],
            ['country_name_ar' => 'المملكة العربية السعودية', 'country_name_en' => 'Saudi Arabia', 'iso_code' => 'SA'],
            ['country_name_ar' => 'جمهورية الهند', 'country_name_en' => 'India', 'iso_code' => 'IN'],
            ['country_name_ar' => 'ماليزيا', 'country_name_en' => 'Malaysia', 'iso_code' => 'MY'],
            ['country_name_ar' => 'المملكة الأردنية الهاشمية', 'country_name_en' => 'Jordan', 'iso_code' => 'JO'],
            ['country_name_ar' => 'جمهورية العراق', 'country_name_en' => 'Iraq', 'iso_code' => 'IQ'],
            ['country_name_ar' => 'الجمهورية العربية السورية', 'country_name_en' => 'Syria', 'iso_code' => 'SY'],
            ['country_name_ar' => 'روسيا الاتحادية', 'country_name_en' => 'Russia', 'iso_code' => 'RU'],
            ['country_name_ar' => 'ألمانيا الاتحادية', 'country_name_en' => 'Germany', 'iso_code' => 'DE'],
        ];
        foreach ($countries as $c) {
            DB::table('countries')->insertOrIgnore($c);
        }

        // 3. Employee Categories
        $categories = [
            ['category_code' => 'ACADEMIC', 'category_name_ar' => 'أكاديمي', 'category_name_en' => 'Academic', 'description' => 'أعضاء هيئة التدريس ومساعدوهم'],
            ['category_code' => 'ADMIN', 'category_name_ar' => 'إداري', 'category_name_en' => 'Administrative', 'description' => 'الكادر الإداري والمكتبي'],
            ['category_code' => 'TECHNICAL', 'category_name_ar' => 'فني / تقني', 'category_name_en' => 'Technical', 'description' => 'مهندسو المعامل والتقنيون'],
            ['category_code' => 'WORKER', 'category_name_ar' => 'عمال وخدمات', 'category_name_en' => 'Workers and Services', 'description' => 'كادر الخدمات المساندة'],
            ['category_code' => 'CONTRACTOR', 'category_name_ar' => 'متعاقد', 'category_name_en' => 'Contractor', 'description' => 'المتعاقدون بالساعات أو العقود السنوية'],
            ['category_code' => 'OTHER', 'category_name_ar' => 'أخرى', 'category_name_en' => 'Other', 'description' => 'فئات أخرى'],
        ];
        foreach ($categories as $cat) {
            DB::table('employee_categories')->insertOrIgnore($cat);
        }

        // 4. Employment Statuses (جامعة إب ومكتب الخدمة المدنية)
        $statuses = [
            ['status_code' => 'ACTIVE', 'status_name_ar' => 'على رأس العمل (مثبت)', 'status_name_en' => 'Active', 'is_active_status' => true],
            ['status_code' => 'SCHOLARSHIP_OUT', 'status_name_ar' => 'موفد بالخارج', 'status_name_en' => 'Scholarship Abroad', 'is_active_status' => true],
            ['status_code' => 'SCHOLARSHIP_IN', 'status_name_ar' => 'موفد بالداخل', 'status_name_en' => 'Scholarship Internal', 'is_active_status' => true],
            ['status_code' => 'SABBATICAL', 'status_name_ar' => 'إجازة تفرغ علمي', 'status_name_en' => 'Sabbatical Leave', 'is_active_status' => true],
            ['status_code' => 'PAID_LEAVE', 'status_name_ar' => 'تفرغ براتب', 'status_name_en' => 'Paid Leave', 'is_active_status' => true],
            ['status_code' => 'UNPAID_LEAVE', 'status_name_ar' => 'إجازة بدون راتب', 'status_name_en' => 'Unpaid Leave', 'is_active_status' => true],
            ['status_code' => 'SECONDMENT', 'status_name_ar' => 'منتدب', 'status_name_en' => 'Seconded', 'is_active_status' => true],
            ['status_code' => 'NATIONAL_MISSION', 'status_name_ar' => 'مهمة وطنية', 'status_name_en' => 'National Mission', 'is_active_status' => true],
            ['status_code' => 'STUDY_LEAVE', 'status_name_ar' => 'مفرغ للدراسة', 'status_name_en' => 'Study Leave', 'is_active_status' => true],
            ['status_code' => 'RETIRED', 'status_name_ar' => 'بلغ أجل التقاعد', 'status_name_en' => 'Retired', 'is_active_status' => false],
            ['status_code' => 'TRANSFERRED', 'status_name_ar' => 'منقول من الجامعة', 'status_name_en' => 'Transferred', 'is_active_status' => false],
            ['status_code' => 'SICK_LEAVE', 'status_name_ar' => 'إجازة مرضية', 'status_name_en' => 'Sick Leave', 'is_active_status' => true],
            ['status_code' => 'DISCONTINUED', 'status_name_ar' => 'منقطع عن العمل', 'status_name_en' => 'Discontinued', 'is_active_status' => false],
        ];
        foreach ($statuses as $st) {
            DB::table('employment_statuses')->insertOrIgnore($st);
        }

        // 5. Academic Ranks
        $ranks = [
            ['academic_rank_id' => Str::uuid()->toString(), 'rank_code' => 'PROF', 'rank_name_ar' => 'أستاذ دكتور (بروفيسور)', 'rank_name_en' => 'Professor', 'rank_order' => 1],
            ['academic_rank_id' => Str::uuid()->toString(), 'rank_code' => 'ASSOC_PROF', 'rank_name_ar' => 'أستاذ مشارك', 'rank_name_en' => 'Associate Professor', 'rank_order' => 2],
            ['academic_rank_id' => Str::uuid()->toString(), 'rank_code' => 'ASST_PROF', 'rank_name_ar' => 'أستاذ مساعد', 'rank_name_en' => 'Assistant Professor', 'rank_order' => 3],
            ['academic_rank_id' => Str::uuid()->toString(), 'rank_code' => 'LECTURER', 'rank_name_ar' => 'مدرس', 'rank_name_en' => 'Lecturer', 'rank_order' => 4],
            ['academic_rank_id' => Str::uuid()->toString(), 'rank_code' => 'TEACHING_ASST', 'rank_name_ar' => 'معيد', 'rank_name_en' => 'Teaching Assistant', 'rank_order' => 5],
            ['academic_rank_id' => Str::uuid()->toString(), 'rank_code' => 'RESEARCHER', 'rank_name_ar' => 'باحث علمي', 'rank_name_en' => 'Researcher', 'rank_order' => 6],
        ];
        foreach ($ranks as $rnk) {
            DB::table('academic_ranks')->insertOrIgnore($rnk);
        }

        // 6. Job Grades (الخدمة المدنية)
        $grades = [
            ['job_grade_id' => Str::uuid()->toString(), 'grade_code' => 'G1', 'grade_name_ar' => 'الدرجة الأولى', 'grade_name_en' => 'Grade 1', 'description' => 'مدير عام وما فوق'],
            ['job_grade_id' => Str::uuid()->toString(), 'grade_code' => 'G2', 'grade_name_ar' => 'الدرجة الثانية', 'grade_name_en' => 'Grade 2', 'description' => 'مدير إدارة'],
            ['job_grade_id' => Str::uuid()->toString(), 'grade_code' => 'G3', 'grade_name_ar' => 'الدرجة الثالثة', 'grade_name_en' => 'Grade 3', 'description' => 'رئيس قسم إداري'],
            ['job_grade_id' => Str::uuid()->toString(), 'grade_code' => 'G4', 'grade_name_ar' => 'الدرجة الرابعة', 'grade_name_en' => 'Grade 4', 'description' => 'أخصائي أول'],
            ['job_grade_id' => Str::uuid()->toString(), 'grade_code' => 'G5', 'grade_name_ar' => 'الدرجة الخامسة', 'grade_name_en' => 'Grade 5', 'description' => 'أخصائي'],
        ];
        foreach ($grades as $grd) {
            DB::table('job_grades')->insertOrIgnore($grd);
        }

        // 7. Decision Types
        $decisionTypes = [
            ['type_code' => 'APPOINTMENT', 'type_name_ar' => 'قرار تعيين رسمي', 'type_name_en' => 'Official Appointment', 'requires_end_date' => false],
            ['type_code' => 'PROMOTION', 'type_name_ar' => 'قرار ترقية علمية', 'type_name_en' => 'Academic Promotion', 'requires_end_date' => false],
            ['type_code' => 'CONFIRMATION', 'type_name_ar' => 'قرار تثبيت وظيفي', 'type_name_en' => 'Job Confirmation', 'requires_end_date' => false],
            ['type_code' => 'SCHOLARSHIP', 'type_name_ar' => 'قرار إيفاد للدراسة', 'type_name_en' => 'Scholarship Assignment', 'requires_end_date' => true],
            ['type_code' => 'SABBATICAL', 'type_name_ar' => 'قرار تفرغ علمي', 'type_name_en' => 'Sabbatical Leave', 'requires_end_date' => true],
            ['type_code' => 'ASSIGNMENT', 'type_name_ar' => 'قرار تكليف إداري', 'type_name_en' => 'Administrative Assignment', 'requires_end_date' => true],
            ['type_code' => 'SECONDMENT', 'type_name_ar' => 'قرار ندب أو إعارة', 'type_name_en' => 'Secondment Decision', 'requires_end_date' => true],
            ['type_code' => 'TRANSFER', 'type_name_ar' => 'قرار نقل وظيفي', 'type_name_en' => 'Transfer Decision', 'requires_end_date' => false],
            ['type_code' => 'CONTRACT', 'type_name_ar' => 'عقد عمل بالساعات', 'type_name_en' => 'Hourly Contract', 'requires_end_date' => true],
            ['type_code' => 'LEAVE', 'type_name_ar' => 'قرار إجازة بدون راتب', 'type_name_en' => 'Unpaid Leave Decision', 'requires_end_date' => true],
        ];
        foreach ($decisionTypes as $dt) {
            DB::table('decision_types')->insertOrIgnore($dt);
        }

        // 8. Decision Statuses
        $decisionStatuses = [
            ['status_code' => 'ACTIVE', 'status_name_ar' => 'ساري ونافذ', 'status_name_en' => 'Active'],
            ['status_code' => 'EXPIRED', 'status_name_ar' => 'منتهي المدة', 'status_name_en' => 'Expired'],
            ['status_code' => 'CANCELLED', 'status_name_ar' => 'ملغي بقرار لاحق', 'status_name_en' => 'Cancelled'],
            ['status_code' => 'SUSPENDED', 'status_name_ar' => 'موقوف مؤقتاً', 'status_name_en' => 'Suspended'],
            ['status_code' => 'DRAFT', 'status_name_ar' => 'مسودة قيد المراجعة', 'status_name_en' => 'Draft'],
        ];
        foreach ($decisionStatuses as $ds) {
            DB::table('decision_statuses')->insertOrIgnore($ds);
        }

        // 9. Qualification Levels
        $qualLevels = [
            ['level_code' => 'BACHELOR', 'level_name_ar' => 'بكالوريوس / ليسانس', 'level_name_en' => 'Bachelor', 'level_order' => 1],
            ['level_code' => 'HIGHER_DIPLOMA', 'level_name_ar' => 'دبلوم عالي', 'level_name_en' => 'Higher Diploma', 'level_order' => 2],
            ['level_code' => 'MASTER', 'level_name_ar' => 'ماجستير', 'level_name_en' => 'Master', 'level_order' => 3],
            ['level_code' => 'DOCTORATE', 'level_name_ar' => 'دكتوراه (PhD)', 'level_name_en' => 'Doctorate', 'level_order' => 4],
            ['level_code' => 'PROF_DOCTORATE', 'level_name_ar' => 'دكتوراه مهنية / بورد', 'level_name_en' => 'Professional Doctorate / Board', 'level_order' => 5],
            ['level_code' => 'POST_DOC', 'level_name_ar' => 'ما بعد الدكتوراه', 'level_name_en' => 'Postdoctoral', 'level_order' => 6],
        ];
        foreach ($qualLevels as $ql) {
            DB::table('qualification_levels')->insertOrIgnore($ql);
        }

        // 10. Document Types
        $docTypes = [
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'PERSONAL_ID', 'type_name_ar' => 'البطاقة الشخصية / جواز السفر', 'type_name_en' => 'Personal ID / Passport', 'applies_to' => 'employee'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'PHOTO', 'type_name_ar' => 'الصورة الشخصية الرسمية', 'type_name_en' => 'Official Photo', 'applies_to' => 'employee'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'CV', 'type_name_ar' => 'السيرة الذاتية (C.V)', 'type_name_en' => 'Curriculum Vitae', 'applies_to' => 'employee'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'BACHELOR_CERT', 'type_name_ar' => 'وثيقة شهادة البكالوريوس مع السجل', 'type_name_en' => 'Bachelor Certificate', 'applies_to' => 'qualification'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'MASTER_CERT', 'type_name_ar' => 'وثيقة شهادة الماجستير مع المعادلة', 'type_name_en' => 'Master Certificate', 'applies_to' => 'qualification'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'DOCTORATE_CERT', 'type_name_ar' => 'وثيقة شهادة الدكتوراه مع المعادلة', 'type_name_en' => 'Doctorate Certificate', 'applies_to' => 'qualification'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'APPOINTMENT_DECISION', 'type_name_ar' => 'أصل قرار التعيين بالجامعة', 'type_name_en' => 'Appointment Decision Doc', 'applies_to' => 'decision'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'PROMOTION_DECISION', 'type_name_ar' => 'أصل قرار الترقية العلمية', 'type_name_en' => 'Academic Promotion Doc', 'applies_to' => 'decision'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'SCHOLARSHIP_DECISION', 'type_name_ar' => 'قرار الإيفاد والتفرغ الدراسي', 'type_name_en' => 'Scholarship Decision Doc', 'applies_to' => 'decision'],
            ['document_type_id' => Str::uuid()->toString(), 'type_code' => 'SERVICE_STATEMENT', 'type_name_ar' => 'بيان الحالة والخدمة الوظيفية', 'type_name_en' => 'Service Statement Doc', 'applies_to' => 'employment'],
        ];
        foreach ($docTypes as $dtp) {
            DB::table('document_types')->insertOrIgnore($dtp);
        }

        // 11. Organizational Units (الهيكل الهرمي الكامل لجامعة إب)
        $univId = Str::uuid()->toString();
        DB::table('organizational_units')->insertOrIgnore([
            'organizational_unit_id' => $univId,
            'parent_unit_id' => null,
            'unit_code' => 'IBB_UNIV',
            'unit_name_ar' => 'جامعة إب',
            'unit_name_en' => 'Ibb University',
            'unit_type' => 'university',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $collegesAndCenters = [
            ['code' => 'ENG', 'name' => 'كلية الهندسة وتكنولوجيا المعلومات', 'type' => 'faculty', 'depts' => [
                'علوم الحاسوب وتقنية المعلومات', 'هندسة البرمجيات', 'هندسة الشبكات والاتصالات', 'الهندسة المعمارية', 'الهندسة المدنية', 'الهندسة الكهربائية'
            ]],
            ['code' => 'DENT', 'name' => 'كلية طب الأسنان', 'type' => 'college', 'depts' => [
                'جراحة الفم والفكين', 'تقويم الأسنان وطب أسنان الأطفال', 'طب الأسنان التحفظي والاستعاضة', 'علوم طب الأسنان الأساسية'
            ]],
            ['code' => 'MED', 'name' => 'كلية الطب والعلوم الصحية', 'type' => 'faculty', 'depts' => [
                'الطب البشري', 'المختبرات الطبية', 'التمريض والقبالة'
            ]],
            ['code' => 'SCI', 'name' => 'كلية العلوم التطبيقية', 'type' => 'faculty', 'depts' => [
                'الرياضيات والحاسوب', 'الفيزياء', 'الكيمياء', 'علوم الحياة (البيولوجي)', 'الميكروبيولوجي'
            ]],
            ['code' => 'ADMIN_SCI', 'name' => 'كلية العلوم الإدارية', 'type' => 'faculty', 'depts' => [
                'إدارة الأعمال', 'المحاسبة', 'العلوم المالية والمصرفية', 'التسويق ونظم المعلومات الإدارية'
            ]],
            ['code' => 'ARTS', 'name' => 'كلية الآداب', 'type' => 'faculty', 'depts' => [
                'الدراسات الإسلامية', 'اللغة العربية وآدابها', 'اللغة الإنجليزية والترجمة', 'الجغرافيا ونظم المعلومات الجغرافية', 'التاريخ والآثار', 'علم النفس'
            ]],
            ['code' => 'EDU', 'name' => 'كلية التربية - إب', 'type' => 'faculty', 'depts' => [
                'المناهج وطرق التدريس', 'الأصول والإدارة التربوية', 'العلوم التربوية والنفسية'
            ]],
            ['code' => 'AGRI', 'name' => 'كلية الزراعة والطب البيطري', 'type' => 'faculty', 'depts' => [
                'الإنتاج النباتي والوقاية', 'الإنتاج الحيواني والدواجن', 'علوم الأغذية والصناعات الغذائية'
            ]],
            ['code' => 'LAW', 'name' => 'كلية القانون', 'type' => 'faculty', 'depts' => [
                'القانون العام', 'القانون الخاص', 'الفقه المقارن'
            ]],
            ['code' => 'SADDAH', 'name' => 'الكلية النوعية بالسدة', 'type' => 'college', 'depts' => [
                'العلوم الإدارية والمالية', 'العلوم التربوية واللغات'
            ]],
            ['code' => 'COMP_CENTER', 'name' => 'مركز الحاسوب وتقنية المعلومات', 'type' => 'center', 'depts' => [
                'شعبة التدريب والتأهيل التقني', 'شعبة الأنظمة والبرمجيات الأكاديمية'
            ]],
            ['code' => 'CONT_EDU', 'name' => 'مركز التعليم المستمر وخدمة المجتمع', 'type' => 'center', 'depts' => [
                'برامج الدبلومات المهنية', 'وحدة التدريب والاستشارات'
            ]],
            ['code' => 'LANG_CENTER', 'name' => 'مركز اللغات والترجمة', 'type' => 'center', 'depts' => [
                'شعبة اللغة الإنجليزية والترجمة', 'شعبة تدريس اللغات الحية'
            ]],
            ['code' => 'PRESIDENCY', 'name' => 'رئاسة الجامعة والأمانة العامة', 'type' => 'administration', 'depts' => [
                'الإدارة العامة للشؤون الأكاديمية', 'الإدارة العامة للشؤون الإدارية', 'الإدارة العامة للدراسات العليا', 'شؤون هيئة التدريس'
            ]],
        ];

        foreach ($collegesAndCenters as $college) {
            $collegeId = Str::uuid()->toString();
            DB::table('organizational_units')->insertOrIgnore([
                'organizational_unit_id' => $collegeId,
                'parent_unit_id' => $univId,
                'unit_code' => $college['code'],
                'unit_name_ar' => $college['name'],
                'unit_name_en' => $college['name'],
                'unit_type' => $college['type'],
                'is_active' => true,
                'created_at' => now(),
            ]);

            foreach ($college['depts'] as $deptIndex => $deptName) {
                DB::table('organizational_units')->insertOrIgnore([
                    'organizational_unit_id' => Str::uuid()->toString(),
                    'parent_unit_id' => $collegeId,
                    'unit_code' => $college['code'] . '_D' . ($deptIndex + 1),
                    'unit_name_ar' => 'قسم ' . $deptName,
                    'unit_name_en' => 'Department of ' . $deptName,
                    'unit_type' => 'department',
                    'is_active' => true,
                    'created_at' => now(),
                ]);
            }
        }

        // 12. App Roles & Super Admin
        $roleAdminId = Str::uuid()->toString();
        $roleHrId = Str::uuid()->toString();
        $roleViewerId = Str::uuid()->toString();

        DB::table('app_roles')->insertOrIgnore([
            ['role_id' => $roleAdminId, 'role_name' => 'مدير النظام (Super Admin)', 'description' => 'صلاحيات كاملة على كل النظام وإدارة المستخدمين والنسخ الاحتياطي'],
            ['role_id' => $roleHrId, 'role_name' => 'مسؤول شؤون الكادر (HR Manager)', 'description' => 'إدارة ملفات الكادر، القرارات، والتكليفات والأرشفة'],
            ['role_id' => $roleViewerId, 'role_name' => 'مستعرض تقارير (Viewer)', 'description' => 'استعراض البيانات والتقارير الأرشيفية فقط دون تعديل'],
        ]);

        $permissions = [
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'manage_employees', 'description' => 'إضافة وتعديل بيانات الكادر'],
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'delete_employees', 'description' => 'أرشفة وتعطيل ملفات الكادر'],
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'manage_decisions', 'description' => 'إصدار وتعديل القرارات الإدارية'],
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'delete_decisions', 'description' => 'حذف وإلغاء القرارات الإدارية'],
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'manage_archive', 'description' => 'رفع وترقية الوثائق بالأرشيف الرقمي'],
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'delete_archive', 'description' => 'حذف وثائق الأرشيف'],
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'manage_master_data', 'description' => 'إدارة الهيكل والبيانات المرجعية'],
            ['permission_id' => Str::uuid()->toString(), 'permission_code' => 'view_reports', 'description' => 'استعراض وتصدير التقارير والإحصائيات'],
        ];

        foreach ($permissions as $p) {
            DB::table('app_permissions')->insertOrIgnore($p);
        }

        $existingAdmin = DB::table('app_users')->where('username', 'admin')->first();
        if (!$existingAdmin) {
            $superUserId = Str::uuid()->toString();
            DB::table('app_users')->insert([
                'user_id' => $superUserId,
                'username' => 'admin',
                'password_hash' => bcrypt('admin123'),
                'is_active' => true,
                'created_at' => now(),
            ]);
        } else {
            $superUserId = $existingAdmin->user_id;
        }

        $adminRole = DB::table('app_roles')->where('role_name', 'like', '%Super Admin%')->first();
        if ($adminRole) {
            DB::table('user_roles')->insertOrIgnore([
                'user_id' => $superUserId,
                'role_id' => $adminRole->role_id,
            ]);
        }
    }
}
