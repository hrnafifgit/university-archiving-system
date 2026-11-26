<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeQualification;
use App\Models\EmploymentRecord;
use App\Models\EmploymentStatus;
use App\Models\JobGrade;
use App\Models\JobTitle;
use App\Models\OrganizationalUnit;
use App\Models\QualificationLevel;
use App\Models\Specialization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportAdminStaffCommand extends Command
{
    protected $signature = 'uas:import-admin-staff';
    protected $description = 'استيراد بيانات الكادر الإداري (485 موظف) من ملف قاعدة بيانات الموظفين إلى PostgreSQL';

    public function handle(): int
    {
        $jsonPath = storage_path('app/admin_staff_import.json');
        if (!file_exists($jsonPath)) {
            $this->error("File not found: {$jsonPath}");
            return 1;
        }

        $records = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($records)) {
            $this->error('Invalid JSON format.');
            return 1;
        }

        $this->info('Starting import of ' . count($records) . ' administrative staff records...');

        $inserted = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            $unitCache = [];
            $gradeCache = [];
            $titleCache = [];
            $statusCache = [];
            $qualLevelCache = [];
            $specCache = [];

            foreach ($records as $idx => $row) {
                $facultyName = trim($row['faculty'] ?? 'جامعة إب');
                $deptName = trim($row['department'] ?? $facultyName);

                // 1. الجهة الأم (الكلية / المركز / الإدارة العامة / النيابة)
                if (!isset($unitCache[$facultyName])) {
                    $u = OrganizationalUnit::where('unit_name_ar', $facultyName)->first();
                    if (!$u) {
                        $uType = str_contains($facultyName, 'مركز') ? 'center'
                            : (str_contains($facultyName, 'كلية') ? 'faculty' : 'administration');
                        $u = OrganizationalUnit::create([
                            'unit_code' => 'ADM-' . strtoupper(Str::random(5)),
                            'unit_name_ar' => $facultyName,
                            'unit_type' => $uType,
                            'is_active' => true,
                        ]);
                    }
                    $unitCache[$facultyName] = $u->organizational_unit_id;
                }
                $facultyId = $unitCache[$facultyName];

                // 2. القسم / الإدارة التابعة
                $targetUnitId = $facultyId;
                if ($deptName !== $facultyName) {
                    $deptKey = $facultyName . '::' . $deptName;
                    if (!isset($unitCache[$deptKey])) {
                        $d = OrganizationalUnit::where('unit_name_ar', $deptName)
                            ->where('parent_unit_id', $facultyId)
                            ->first();
                        if (!$d) {
                            $d = OrganizationalUnit::create([
                                'unit_code' => 'DEP-' . strtoupper(Str::random(5)),
                                'unit_name_ar' => $deptName,
                                'unit_type' => 'department',
                                'parent_unit_id' => $facultyId,
                                'is_active' => true,
                            ]);
                        }
                        $unitCache[$deptKey] = $d->organizational_unit_id;
                    }
                    $targetUnitId = $unitCache[$deptKey];
                }

                // 3. الدرجة الوظيفية
                $gradeName = trim($row['current_grade'] ?? 'غير محدد');
                if (!isset($gradeCache[$gradeName])) {
                    $g = JobGrade::where('grade_name_ar', $gradeName)->first();
                    if (!$g) {
                        $g = JobGrade::create([
                            'grade_code' => 'GRD-' . strtoupper(Str::random(5)),
                            'grade_name_ar' => $gradeName,
                        ]);
                    }
                    $gradeCache[$gradeName] = $g->job_grade_id;
                }
                $gradeId = $gradeCache[$gradeName];

                // 4. المسمى الوظيفي
                $titleName = trim($row['job_title'] ?? 'غير محدد');
                if (!isset($titleCache[$titleName])) {
                    $jt = JobTitle::firstOrCreate(
                        ['title_name_ar' => $titleName],
                        ['is_active' => true]
                    );
                    $titleCache[$titleName] = $jt->job_title_id;
                }
                $titleId = $titleCache[$titleName];

                // 5. الحالة الوظيفية
                $stName = trim($row['status_name'] ?? 'غير محدد');
                if (!isset($statusCache[$stName])) {
                    $st = EmploymentStatus::where('status_name_ar', $stName)->first();
                    if (!$st) {
                        $st = EmploymentStatus::create([
                            'status_code' => 'ST-' . strtoupper(Str::random(5)),
                            'status_name_ar' => $stName,
                            'is_active_status' => !in_array($stName, ['متوفي', 'مفصول', 'منقطع']),
                        ]);
                    }
                    $statusCache[$stName] = $st->employment_status_id;
                }
                $statusId = $statusCache[$stName];

                // 6. سجل الموظف (تجنب التكرار بالرقم الوظيفي أو الوطني أو الاسم)
                $empNum = $row['employee_number'] ? trim($row['employee_number']) : null;
                $natId = $row['national_id'] ? trim($row['national_id']) : null;
                $fullName = trim($row['full_name_ar']);

                $emp = null;
                if ($empNum) {
                    $emp = Employee::where('employee_number', $empNum)->first();
                }
                if (!$emp && $natId) {
                    $emp = Employee::where('national_id', $natId)->first();
                }
                if (!$emp) {
                    $emp = Employee::where('full_name_ar', $fullName)->first();
                }

                if (!$emp) {
                    $emp = Employee::create([
                        'employee_number' => $empNum ?: ('ADM-' . str_pad((string) ($idx + 1), 5, '0', STR_PAD_LEFT)),
                        'national_id' => $natId,
                        'full_name_ar' => $fullName,
                        'gender' => $row['gender'] ?? 'male',
                        'date_of_birth' => $row['date_of_birth'],
                        'phone_number' => $row['phone_number'],
                        'is_active_record' => true,
                        'created_at' => now(),
                    ]);
                    $inserted++;
                } else {
                    $emp->update([
                        'phone_number' => $emp->phone_number ?: $row['phone_number'],
                        'national_id' => $emp->national_id ?: $natId,
                        'date_of_birth' => $emp->date_of_birth ?: $row['date_of_birth'],
                    ]);
                    $updated++;
                }

                // 7. سجل التوظيف الإداري (employee_category_id = 2)
                $hasRecord = EmploymentRecord::where('employee_id', $emp->employee_id)
                    ->where('is_primary', true)
                    ->exists();

                if (!$hasRecord) {
                    EmploymentRecord::create([
                        'employee_id' => $emp->employee_id,
                        'employee_category_id' => 2, // إداري
                        'organizational_unit_id' => $targetUnitId,
                        'job_grade_id' => $gradeId,
                        'job_title_id' => $titleId,
                        'employment_status_id' => $statusId,
                        'start_date' => $row['start_date'] ?? '2015-01-01',
                        'is_primary' => true,
                        'appointment_basis' => $row['appointment_basis'] ?? 'مثبت',
                        'first_appointment_date' => $row['first_appointment_date'],
                        'qualification_at_appointment' => $row['qualification_at_appointment'],
                        'payroll_status' => $row['payroll_status'] ?? $stName,
                        'payroll_job_title' => $titleName,
                        'payroll_job_grade' => $gradeName,
                        'notes' => 'استيراد من كشف بيانات الكادر الإداري',
                        'created_at' => now(),
                    ]);
                }

                // 8. المؤهل العلمي إن وجد
                $qualName = trim($row['qualification_at_appointment'] ?? '');
                if ($qualName && !in_array($qualName, ['غير محدد', 'بدون', 'خبرة'])) {
                    if (!isset($qualLevelCache[$qualName])) {
                        $ql = QualificationLevel::where('level_name_ar', $qualName)->first();
                        if (!$ql) {
                            $ql = QualificationLevel::create([
                                'level_code' => 'QL-' . strtoupper(Str::random(4)),
                                'level_name_ar' => $qualName,
                                'level_order' => 1,
                            ]);
                        }
                        $qualLevelCache[$qualName] = $ql->qualification_level_id;
                    }

                    $specId = null;
                    $specName = trim($row['specialization'] ?? '');
                    if ($specName && $specName !== 'غير محدد') {
                        if (!isset($specCache[$specName])) {
                            $sp = Specialization::where('specialization_name_ar', $specName)->first();
                            if (!$sp) {
                                $sp = Specialization::create([
                                    'specialization_name_ar' => $specName,
                                    'specialization_type' => 'general',
                                ]);
                            }
                            $specCache[$specName] = $sp->specialization_id;
                        }
                        $specId = $specCache[$specName];
                    }

                    $hasQual = EmployeeQualification::where('employee_id', $emp->employee_id)->exists();
                    if (!$hasQual) {
                        EmployeeQualification::create([
                            'employee_id' => $emp->employee_id,
                            'qualification_level_id' => $qualLevelCache[$qualName],
                            'specialization_id' => $specId,
                            'degree_title' => $specName ? "{$qualName} — {$specName}" : $qualName,
                            'is_highest_qualification' => true,
                            'verification_status' => 'verified',
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            Cache::forget('dashboard_core_metrics');
            @unlink($jsonPath);

            $this->info("Completed! Inserted: {$inserted}, Updated: {$updated}, Total Employees in DB: " . Employee::count());
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage() . ' at line ' . $e->getLine());
            return 1;
        }
    }
}
