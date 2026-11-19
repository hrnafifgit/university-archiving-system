<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniversityArchivingSeeder extends Seeder
{
    public function run(): void
    {
        // قائمة الكيانات العليا المستخرجة بدقة من نظام الإكسل (seting1 / Feuil1)
        $entitiesData = [
            ['name' => 'كلية الهندسة وتكنولوجيا المعلومات', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'ENG',
             'depts' => ['قسم علوم الحاسوب وتقنية المعلومات', 'قسم الهندسة الكهربائية', 'قسم الهندسة المعمارية', 'قسم الهندسة المدنية', 'شؤون الكلية (عام)']],

            ['name' => 'كلية طب الأسنان', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'DENT',
             'depts' => ['قسم تقويم الأسنان', 'قسم جراحة الفم والفكين', 'قسم التعويضات الثابتة', 'قسم العلوم الأساسية لطب الأسنان', 'شؤون الكلية (عام)']],

            ['name' => 'كلية الطب والعلوم الصحية', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'MED',
             'depts' => ['قسم الطب البشري والجراحة', 'قسم المختبرات الطبية', 'قسم التغذية السريرية', 'قسم طب الأطفال', 'شؤون الكلية (عام)']],

            ['name' => 'كلية العلوم التطبيقية', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'SCI',
             'depts' => ['قسم الكيمياء', 'قسم الفيزياء', 'قسم الرياضيات', 'قسم رياضيات حاسوب', 'قسم الميكروبيولوجي (الأحياء الدقيقة)', 'قسم علوم الحياة (البيولوجي)']],

            ['name' => 'كلية العلوم الإدارية', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'ADMIN',
             'depts' => ['قسم إدارة الأعمال', 'قسم المحاسبة', 'قسم العلوم المالية والمصرفية', 'قسم الإدارة العامة', 'قسم السياحة وإدارة الفنادق']],

            ['name' => 'كلية الآداب', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'ARTS',
             'depts' => ['قسم اللغة الإنجليزية', 'قسم اللغة العربية', 'قسم الجغرافيا', 'قسم التاريخ', 'قسم علم الاجتماع', 'قسم علوم القرآن والدراسات الإسلامية']],

            ['name' => 'كلية التربية', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'EDU',
             'depts' => ['قسم المناهج وطرق التدريس', 'قسم تكنولوجيا التعليم', 'قسم الإدارة وأصول التربية', 'قسم العلوم النفسية والتربوية', 'قسم التربية الفنية', 'قسم معلم حاسوب']],

            ['name' => 'كلية الزراعة وعلوم الأغذية', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'AGRI',
             'depts' => ['قسم الاقتصاد والإرشاد الزراعي', 'قسم الإنتاج النباتي', 'قسم الإنتاج الحيواني', 'قسم علوم وتكنولوجيا الأغذية']],

            ['name' => 'كلية القانون', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'LAW',
             'depts' => ['قسم القانون العام', 'قسم القانون الخاص', 'شؤون الكلية']],

            ['name' => 'كلية العلوم التطبيقية والتربوية بالنادرة', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'NAD',
             'depts' => ['قسم الرياضيات', 'قسم العلوم التربوية', 'قسم اللغة العربية', 'قسم علوم القرآن', 'قسم معلم حاسوب']],

            ['name' => 'الكلية النوعية بالسدة', 'type' => 'كلية', 'nature' => 'أكاديمي', 'code' => 'SAD',
             'depts' => ['قسم العلوم التطبيقية', 'قسم العلوم الإدارية', 'شؤون الكلية']],

            ['name' => 'مركز الحاسوب وتقنية المعلومات', 'type' => 'مركز', 'nature' => 'خدمي-تقني', 'code' => 'CCIT',
             'depts' => ['إدارة النظم والبرمجيات', 'إدارة الشبكات والدعم الفني', 'وحدة التدريب والتأهيل']],

            ['name' => 'مركز التعليم المستمر والتنمية البشرية', 'type' => 'مركز', 'nature' => 'تدريبي', 'code' => 'CEC',
             'depts' => ['قسم التنمية البشرية والتعليم المستمر', 'قسم البرامج والدورات التأهيلية']],

            ['name' => 'مركز اللغات والترجمة', 'type' => 'مركز', 'nature' => 'أكاديمي', 'code' => 'LC',
             'depts' => ['وحدة تعليم اللغات', 'وحدة الترجمة والتعريب']],

            ['name' => 'مركز الإرشاد النفسي والتربوي', 'type' => 'مركز', 'nature' => 'خدمي-أكاديمي', 'code' => 'CPC',
             'depts' => ['وحدة الاستشارات والدعم النفسي', 'وحدة البحوث الإرشادية']],

            ['name' => 'مركز البيئة والسلامة الكيميائية', 'type' => 'مركز', 'nature' => 'أكاديمي-بحثي', 'code' => 'ENV',
             'depts' => ['وحدة الدراسات البيئية', 'وحدة السلامة الكيميائية']],

            ['name' => 'رئاسة الجامعة والأمانة العامة', 'type' => 'إدارة عليا', 'nature' => 'إداري', 'code' => 'RECT',
             'depts' => ['الإدارة العامة للشؤون الأكاديمية', 'الإدارة العامة للشؤون الإدارية والموارد البشرية', 'الشؤون المالية والموازنة']],
        ];

        // إدراج الكليات والمراكز وأقسامها إذا لم تكن موجودة
        foreach ($entitiesData as $e) {
            $entity = DB::table('organizational_entities')->where('name', $e['name'])->first();
            if (!$entity) {
                $entityId = DB::table('organizational_entities')->insertGetId([
                    'name' => $e['name'],
                    'entity_type' => $e['type'],
                    'nature' => $e['nature'],
                    'code' => $e['code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $entityId = $entity->id;
            }

            foreach ($e['depts'] as $idx => $deptName) {
                $deptExists = DB::table('departments_units')
                    ->where('entity_id', $entityId)
                    ->where('name', $deptName)
                    ->exists();

                if (!$deptExists) {
                    DB::table('departments_units')->insert([
                        'entity_id' => $entityId,
                        'name' => $deptName,
                        'unit_type' => str_contains($deptName, 'إدارة') ? 'وحدة إدارية' : 'قسم علمي',
                        'code' => $e['code'] . '-' . ($idx + 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // الرتب العلمية المستخرجة من الإكسل
        $ranks = [
            ['name' => 'أستاذ دكتور (بروفيسور)', 'seniority_order' => 1],
            ['name' => 'أستاذ مشارك', 'seniority_order' => 2],
            ['name' => 'أستاذ مساعد', 'seniority_order' => 3],
            ['name' => 'مدرس', 'seniority_order' => 4],
            ['name' => 'معيد', 'seniority_order' => 5],
            ['name' => 'باحث علمي', 'seniority_order' => 6],
        ];

        foreach ($ranks as $r) {
            if (!DB::table('academic_ranks')->where('name', $r['name'])->exists()) {
                DB::table('academic_ranks')->insert([
                    'name' => $r['name'],
                    'seniority_order' => $r['seniority_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // الدرجات الوظيفية للخدمة المدنية للإداريين
        $grades = [
            ['grade_name' => 'الدرجة الأولى', 'executive_level' => 'مدير عام'],
            ['grade_name' => 'الدرجة الثانية', 'executive_level' => 'مدير إدارة'],
            ['grade_name' => 'الدرجة الثالثة', 'executive_level' => 'رئيس قسم'],
            ['grade_name' => 'الدرجة الرابعة', 'executive_level' => 'أخصائي أول'],
            ['grade_name' => 'الدرجة الخامسة', 'executive_level' => 'موظف تنفيذي'],
            ['grade_name' => 'الدرجة السادسة', 'executive_level' => 'فني مساعد'],
        ];

        foreach ($grades as $g) {
            if (!DB::table('job_grades')->where('grade_name', $g['grade_name'])->exists()) {
                DB::table('job_grades')->insert([
                    'grade_name' => $g['grade_name'],
                    'executive_level' => $g['executive_level'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
