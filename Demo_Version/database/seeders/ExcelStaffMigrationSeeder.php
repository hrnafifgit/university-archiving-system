<?php

namespace Database\Seeders;

use App\Models\AcademicRank;
use App\Models\Country;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\EmployeeQualification;
use App\Models\EmploymentRecord;
use App\Models\EmploymentStatus;
use App\Models\JobGrade;
use App\Models\JobTitle;
use App\Models\OrganizationalUnit;
use App\Models\QualificationLevel;
use App\Models\Specialization;
use App\Models\University;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExcelStaffMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('excel_legacy_records.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("File excel_legacy_records.json not found!");
            return;
        }

        $records = json_decode(file_get_contents($jsonPath), true);
        if (!$records || count($records) === 0) {
            $this->command->error("No records found in json file!");
            return;
        }

        $this->command->info("Starting migration of " . count($records) . " staff records from Excel into 3NF relational PostgreSQL...");

        DB::beginTransaction();

        try {
            // 1. Data Source
            $dataSource = DB::table('data_sources')->where('source_name', 'LIKE', '%الإكسل%')->first();
            $dataSourceId = $dataSource ? $dataSource->data_source_id : Str::uuid()->toString();

            // 2. Academic Category
            $academicCat = EmployeeCategory::where('category_code', 'ACADEMIC')->first();
            $academicCatId = $academicCat ? $academicCat->employee_category_id : 1;

            // 3. Document Type for Archive PDF
            $docType = DocumentType::where('type_code', 'SERVICE_STATEMENT')->first();
            if (!$docType) {
                $docType = DocumentType::first();
            }

            // 4. University Entity (Root)
            $universityRoot = OrganizationalUnit::where('unit_code', 'IBB_UNIV')->first();
            if (!$universityRoot) {
                $universityRoot = OrganizationalUnit::create([
                    'unit_code' => 'IBB_UNIV',
                    'unit_name_ar' => 'جامعة إب',
                    'unit_type' => 'university',
                    'is_active' => true,
                ]);
            }

            // 5. Qualification Levels
            $bachelorLevel = QualificationLevel::where('level_code', 'BACHELOR')->first();
            $masterLevel = QualificationLevel::where('level_code', 'MASTER')->first();
            $phdLevel = QualificationLevel::where('level_code', 'DOCTORATE')->first();

            $importedCount = 0;

            foreach ($records as $index => $row) {
                $fullName = trim($row['الاسم'] ?? '');
                if (empty($fullName)) {
                    continue;
                }

                $jobNum = trim($row['الرقم الوظيفي'] ?? '');
                if ($jobNum === 'غير محدد' || empty($jobNum)) {
                    $jobNum = 'TEMP_' . ($index + 1);
                }

                $nationalId = trim($row['الرقم الوطني'] ?? '');
                if ($nationalId === 'غير محدد' || empty($nationalId)) {
                    $nationalId = null;
                }

                $genderRaw = trim($row['الجنس'] ?? '');
                $gender = ($genderRaw === 'انثى' || $genderRaw === 'أنثى') ? 'F' : 'M';

                // Birth Date
                $birthYear = trim($row['تاريخ الميلاد'] ?? '');
                $birthDate = null;
                if (!empty($birthYear) && is_numeric($birthYear)) {
                    $birthDate = $birthYear . '-01-01';
                }

                $birthPlace = trim($row['محل الميلاد'] ?? '');
                if ($birthPlace === 'غير محدد' || empty($birthPlace)) {
                    $birthPlace = null;
                }

                $phone = trim($row['رقم التليفون'] ?? '');
                if ($phone === 'غير محدد' || empty($phone)) {
                    $phone = null;
                }

                $exactSpec = trim($row['التخصص الدقيق'] ?? '');

                // A. Insert Employee
                $employee = Employee::create([
                    'employee_number' => $jobNum,
                    'national_id' => $nationalId,
                    'full_name_ar' => $fullName,
                    'gender' => $gender,
                    'date_of_birth' => $birthDate,
                    'place_of_birth' => $birthPlace,
                    'phone_number' => $phone,
                    'notes' => (!empty($exactSpec) && $exactSpec !== 'غير محدد') ? "التخصص الدقيق: {$exactSpec}" : null,
                    'is_active_record' => true,
                    'created_from_source_id' => $dataSourceId,
                ]);

                // B. Organizational Units (Faculty & Dept)
                $facultyName = trim($row['الجهة'] ?? '');
                $deptName = trim($row['الادارة/القسم'] ?? '');

                $assignedUnitId = $universityRoot->organizational_unit_id;

                if (!empty($facultyName) && $facultyName !== 'غير محدد') {
                    $faculty = OrganizationalUnit::where('unit_name_ar', $facultyName)->first();
                    if (!$faculty) {
                        $faculty = OrganizationalUnit::create([
                            'unit_name_ar' => $facultyName,
                            'parent_unit_id' => $universityRoot->organizational_unit_id,
                            'unit_code' => 'FAC_' . Str::upper(Str::random(8)),
                            'unit_type' => 'faculty',
                            'is_active' => true,
                        ]);
                    }
                    $assignedUnitId = $faculty->organizational_unit_id;

                    if (!empty($deptName) && $deptName !== 'عام' && $deptName !== 'غير محدد') {
                        $dept = OrganizationalUnit::where('unit_name_ar', $deptName)
                            ->where('parent_unit_id', $faculty->organizational_unit_id)
                            ->first();
                        if (!$dept) {
                            $dept = OrganizationalUnit::create([
                                'unit_name_ar' => $deptName,
                                'parent_unit_id' => $faculty->organizational_unit_id,
                                'unit_code' => 'DEPT_' . Str::upper(Str::random(8)),
                                'unit_type' => 'department',
                                'is_active' => true,
                            ]);
                        }
                        $assignedUnitId = $dept->organizational_unit_id;
                    }
                }

                // C. Academic Rank
                $rankRaw = trim($row['الدرجة الحالية'] ?? '');
                $academicRankId = null;
                if (!empty($rankRaw)) {
                    if (str_contains($rankRaw, 'مشارك')) {
                        $rnk = AcademicRank::where('rank_code', 'ASSOC_PROF')->first();
                    } elseif (str_contains($rankRaw, 'مساعد')) {
                        $rnk = AcademicRank::where('rank_code', 'ASST_PROF')->first();
                    } elseif (str_contains($rankRaw, 'استاذ') || str_contains($rankRaw, 'أستاذ')) {
                        $rnk = AcademicRank::where('rank_code', 'PROF')->first();
                    } elseif (str_contains($rankRaw, 'مدرس')) {
                        $rnk = AcademicRank::where('rank_code', 'LECTURER')->first();
                    } elseif (str_contains($rankRaw, 'معيد')) {
                        $rnk = AcademicRank::where('rank_code', 'TEACHING_ASST')->first();
                    } elseif (str_contains($rankRaw, 'باحث')) {
                        $rnk = AcademicRank::where('rank_code', 'RESEARCHER')->first();
                    } else {
                        $rnk = AcademicRank::first();
                    }
                    if ($rnk) {
                        $academicRankId = $rnk->academic_rank_id;
                    }
                }

                // D. Employment Status
                $statusRaw = trim($row['الوضع الحالي'] ?? '');
                $statusId = null;
                if (!empty($statusRaw)) {
                    if (str_contains($statusRaw, 'موجود') || str_contains($statusRaw, 'مثبت')) {
                        $st = EmploymentStatus::where('status_code', 'ACTIVE')->first();
                    } elseif (str_contains($statusRaw, 'موفد بالخارج') || $statusRaw === 'موفد') {
                        $st = EmploymentStatus::where('status_code', 'SCHOLARSHIP_OUT')->first();
                    } elseif (str_contains($statusRaw, 'موفد بالداخل')) {
                        $st = EmploymentStatus::where('status_code', 'SCHOLARSHIP_IN')->first();
                    } elseif (str_contains($statusRaw, 'تفرغ علمي')) {
                        $st = EmploymentStatus::where('status_code', 'SABBATICAL')->first();
                    } elseif (str_contains($statusRaw, 'تفرغ براتب')) {
                        $st = EmploymentStatus::where('status_code', 'PAID_LEAVE')->first();
                    } elseif (str_contains($statusRaw, 'بدون راتب')) {
                        $st = EmploymentStatus::where('status_code', 'UNPAID_LEAVE')->first();
                    } elseif (str_contains($statusRaw, 'تقاعد') || str_contains($statusRaw, 'الاجلين')) {
                        $st = EmploymentStatus::where('status_code', 'RETIRED')->first();
                    } elseif (str_contains($statusRaw, 'منقول')) {
                        $st = EmploymentStatus::where('status_code', 'TRANSFERRED')->first();
                    } elseif (str_contains($statusRaw, 'مريض')) {
                        $st = EmploymentStatus::where('status_code', 'SICK_LEAVE')->first();
                    } elseif (str_contains($statusRaw, 'منقطع')) {
                        $st = EmploymentStatus::where('status_code', 'DISCONTINUED')->first();
                    } elseif (str_contains($statusRaw, 'مهمة وطنية')) {
                        $st = EmploymentStatus::where('status_code', 'NATIONAL_MISSION')->first();
                    } else {
                        // Match or create status
                        $st = EmploymentStatus::firstOrCreate(
                            ['status_name_ar' => $statusRaw],
                            ['status_code' => Str::upper(Str::substr(md5($statusRaw), 0, 10)), 'is_active_status' => true]
                        );
                    }
                    if ($st) {
                        $statusId = $st->employment_status_id;
                    }
                }

                // E. Hire Date
                $hireDateRaw = trim($row['تاريخ التعين بالجامعة اكاديمي'] ?? '');
                if (empty($hireDateRaw) || $hireDateRaw === 'غير محدد') {
                    $hireDateRaw = trim($row['تاريخ التعيين بالوظيفة لاول مرة'] ?? '');
                }
                $startDate = null;
                if (!empty($hireDateRaw) && $hireDateRaw !== 'غير محدد') {
                    try {
                        $startDate = Carbon::parse(str_replace('/', '-', $hireDateRaw))->toDateString();
                    } catch (\Exception $e) {
                        $startDate = '2010-01-01';
                    }
                } else {
                    $startDate = '2010-01-01';
                }

                // F. Employment Record
                EmploymentRecord::create([
                    'employee_id' => $employee->employee_id,
                    'employee_category_id' => $academicCatId,
                    'organizational_unit_id' => $assignedUnitId,
                    'academic_rank_id' => $academicRankId,
                    'employment_status_id' => $statusId,
                    'start_date' => $startDate,
                    'is_primary' => true,
                    'appointment_basis' => trim($row['الموقف الوظيفي'] ?? 'مثبت'),
                    'payroll_status' => trim($row['الحالة بكشف الراتب'] ?? 'غير محدد'),
                    'payroll_job_title' => trim($row['الوظيفة بكشف الراتب'] ?? 'غير محدد'),
                    'payroll_job_grade' => trim($row['الدرجة بكشف الراتب'] ?? 'غير محدد'),
                    'qualification_at_appointment' => trim($row['المؤهل عند التعيين بالجامعة اكاديمي'] ?? 'غير محدد'),
                    'initial_rank' => trim($row['الدرجة المعين فيها'] ?? 'معيد'),
                    'first_appointment_date' => $startDate,
                    'academic_appointment_date' => $startDate,
                    'data_source_id' => $dataSourceId,
                    'notes' => 'تم الترحيل من نظام الإكسل السابق',
                ]);

                // G. Qualifications
                $hasPhd = !empty(trim($row['تخصص دك'] ?? '')) && trim($row['تخصص دك'] ?? '') !== 'غير محدد';
                $hasMaster = !empty(trim($row['تخصص مج'] ?? '')) && trim($row['تخصص مج'] ?? '') !== 'غير محدد';
                $hasBachelor = !empty(trim($row['تخصص بك'] ?? '')) && trim($row['تخصص بك'] ?? '') !== 'غير محدد';

                // 1. Bachelor
                if ($hasBachelor && $bachelorLevel) {
                    $this->createQualification(
                        $employee->employee_id,
                        $bachelorLevel->qualification_level_id,
                        trim($row['تخصص بك']),
                        trim($row['تقدير بك'] ?? ''),
                        trim($row['تاريخ بك'] ?? ''),
                        trim($row['جامعة بك'] ?? ''),
                        trim($row['الدولة بك'] ?? ''),
                        !$hasPhd && !$hasMaster,
                        $dataSourceId
                    );
                }

                // 2. Master
                if ($hasMaster && $masterLevel) {
                    $this->createQualification(
                        $employee->employee_id,
                        $masterLevel->qualification_level_id,
                        trim($row['تخصص مج']),
                        null,
                        trim($row['تاريخ مج'] ?? ''),
                        trim($row['جامعة مج'] ?? ''),
                        trim($row['الدولة مج'] ?? ''),
                        !$hasPhd,
                        $dataSourceId
                    );
                }

                // 3. PhD
                if ($hasPhd && $phdLevel) {
                    $this->createQualification(
                        $employee->employee_id,
                        $phdLevel->qualification_level_id,
                        trim($row['تخصص دك']),
                        null,
                        trim($row['تاريخ دك'] ?? ''),
                        trim($row['جامعة دك'] ?? ''),
                        trim($row['الدولة دك'] ?? ''),
                        true,
                        $dataSourceId
                    );
                }

                // H. Archive Document
                $docFileName = trim($row['اسم الملف الالكتروني'] ?? '');
                if (!empty($docFileName) && $docFileName !== 'غير محدد') {
                    $autoId = trim($row['اسم ملف الربط التلقائي'] ?? ($index + 1));
                    $document = Document::create([
                        'employee_id' => $employee->employee_id,
                        'document_type_id' => $docType ? $docType->document_type_id : null,
                        'document_title' => "الملف الإلكتروني الشامل للكادر ({$docFileName})",
                        'document_number' => $autoId,
                        'document_date' => $startDate,
                        'issuing_authority' => 'جامعة إب - الشؤون الأكاديمية',
                        'description' => "أصل ملف الأرشيف الرقمي المأخوذ من المسار المرجعي D:\\archac26\\{$docFileName}",
                        'is_confidential' => false,
                    ]);

                    DocumentVersion::create([
                        'document_id' => $document->document_id,
                        'version_number' => 1,
                        'storage_key' => "documents/archive/{$employee->employee_id}_{$docFileName}",
                        'original_file_name' => $docFileName,
                        'mime_type' => 'application/pdf',
                        'file_size_bytes' => 102400,
                        'checksum_sha256' => hash('sha256', $docFileName . '_' . $employee->employee_id),
                        'is_current' => true,
                        'uploaded_at' => now(),
                    ]);
                }

                $importedCount++;
            }

            DB::commit();
            $this->command->info("SUCCESS: Successfully imported {$importedCount} employees and their 3NF relations!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("ERROR during migration: " . $e->getMessage() . " at line " . $e->getLine());
            throw $e;
        }
    }

    private function createQualification(
        string $employeeId,
        int $levelId,
        string $specName,
        ?string $grade,
        ?string $year,
        ?string $univName,
        ?string $countryName,
        bool $isHighest,
        string $dataSourceId
    ): void {
        if (empty($specName) || $specName === 'غير محدد') {
            return;
        }

        // 1. Country
        $countryId = null;
        if (!empty($countryName) && $countryName !== 'غير محدد') {
            $c = Country::where('country_name_ar', 'LIKE', "%{$countryName}%")->first();
            if (!$c) {
                $c = Country::create([
                    'country_name_ar' => $countryName,
                    'iso_code' => null,
                ]);
            }
            $countryId = $c->country_id;
        }

        // 2. University
        $univId = null;
        if (!empty($univName) && $univName !== 'غير محدد') {
            $u = University::firstOrCreate(
                [
                    'university_name_ar' => $univName,
                    'country_id' => $countryId,
                ]
            );
            $univId = $u->university_id;
        }

        // 3. Specialization
        $spec = Specialization::firstOrCreate(
            [
                'specialization_name_ar' => $specName,
                'specialization_type' => 'GENERAL',
            ]
        );

        // 4. Graduation Date
        $gradDate = null;
        if (!empty($year) && is_numeric($year)) {
            $gradDate = $year . '-06-01';
        }

        EmployeeQualification::create([
            'employee_id' => $employeeId,
            'qualification_level_id' => $levelId,
            'specialization_id' => $spec->specialization_id,
            'university_id' => $univId,
            'country_id' => $countryId,
            'degree_title' => $specName,
            'grade_or_gpa' => (!empty($grade) && $grade !== 'غير محدد') ? $grade : null,
            'graduation_date' => $gradDate,
            'is_highest_qualification' => $isHighest,
            'data_source_id' => $dataSourceId,
        ]);
    }
}
