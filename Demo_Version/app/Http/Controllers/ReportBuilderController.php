<?php

namespace App\Http\Controllers;

use App\Models\DepartmentUnit;
use App\Models\OrganizationalEntity;
use App\Models\StaffBase;
use Illuminate\Http\Request;

class ReportBuilderController extends Controller
{
    /**
     * تعريف الحقول والأعمدة المتاحة للاختيار (مكافئ لأعمدة Feuil1 و ReportForm في الإكسل)
     */
    public static function getAvailableColumns(): array
    {
        return [
            'personal' => [
                'title' => 'البيانات الشخصية والأساسية',
                'columns' => [
                    'job_number' => 'الرقم الوظيفي',
                    'full_name' => 'الاسم الرباعي',
                    'national_id' => 'الرقم الوطني / البطاقة',
                    'gender' => 'النوع الاجتماعي (الجنس)',
                    'birth_date' => 'تاريخ الميلاد',
                    'phone' => 'رقم الهاتف',
                    'email' => 'البريد الإلكتروني',
                ]
            ],
            'academic' => [
                'title' => 'البيانات الأكاديمية والتنظيمية',
                'columns' => [
                    'entity_name' => 'الكلية / المركز',
                    'department_name' => 'القسم العلمي / الإدارة',
                    'staff_type' => 'نوع الكادر',
                    'academic_rank' => 'الرتبة العلمية الأكاديمية',
                    'general_specialization' => 'التخصص العام',
                    'exact_specialization' => 'التخصص الدقيق',
                    'academic_status' => 'الحالة الأكاديمية',
                    'hire_date' => 'تاريخ التعيين بالجامعة',
                    'job_status' => 'الموقف الوظيفي المعتمد',
                ]
            ],
            'administrative' => [
                'title' => 'البيانات الإدارية والمالية',
                'columns' => [
                    'job_grade' => 'الدرجة بكشف الراتب (المالية)',
                    'job_title' => 'المسمى الوظيفي الإداري',
                    'work_nature' => 'طبيعة العمل',
                ]
            ],
            'qualifications' => [
                'title' => 'المؤهلات العلمية',
                'columns' => [
                    'degree_type' => 'أعلى مؤهل علمي',
                    'granting_institution' => 'جامعة التخرج',
                    'graduation_year' => 'سنة التخرج',
                    'country' => 'دولة التخرج',
                ]
            ],
        ];
    }

    /**
     * واجهة منشئ التقارير المخصصة وتوليد النتائج اللحظية
     */
    public function index(Request $request)
    {
        $allGroups = self::getAvailableColumns();
        
        // تجميع كل مفاتيح الأعمدة المتاحة
        $flatAvailable = [];
        foreach ($allGroups as $group) {
            foreach ($group['columns'] as $key => $label) {
                $flatAvailable[$key] = $label;
            }
        }

        // الأعمدة الافتراضية إذا لم يتم تحديدها
        $defaultColumns = ['job_number', 'full_name', 'department_name', 'academic_rank', 'job_status', 'phone'];
        $selectedColumns = $request->input('columns', $defaultColumns);
        if (!is_array($selectedColumns) || empty($selectedColumns)) {
            $selectedColumns = $defaultColumns;
        }

        // استعلام الكادر وفق المحددات المحددة
        $query = StaffBase::with([
            'department.entity',
            'academicDetails.rank',
            'administrativeDetails.jobGrade',
            'contractDetails',
            'qualifications'
        ]);

        if ($request->filled('entity_id')) {
            $query->whereHas('department', function ($q) use ($request) {
                $q->where('entity_id', $request->entity_id);
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->staff_type);
        }

        if ($request->filled('job_status')) {
            $query->where('job_status', $request->job_status);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $staffList = $query->orderBy('full_name')->get();

        // تحضير خيارات الفلترة المنسدلة
        $entities = OrganizationalEntity::orderBy('name')->get();
        $departments = DepartmentUnit::orderBy('name')->get();
        $lookups = StaffController::getExcelLookups();

        // عنوان التقرير المخصص
        $selectedEntity = $request->filled('entity_id') ? OrganizationalEntity::find($request->entity_id) : null;
        $reportTitle = $selectedEntity 
            ? 'كشف بيانات كادر: ' . $selectedEntity->name 
            : 'كشف بيانات الكادر العام - جامعة إب';

        return view('reports.builder', compact(
            'allGroups',
            'flatAvailable',
            'selectedColumns',
            'staffList',
            'entities',
            'departments',
            'lookups',
            'reportTitle',
            'selectedEntity'
        ));
    }

    /**
     * تصدير الكشف المخصص بصيغة CSV المتوافقة مع إكسل ومحارف UTF-8
     */
    public function exportCsv(Request $request)
    {
        $allGroups = self::getAvailableColumns();
        $flatAvailable = [];
        foreach ($allGroups as $group) {
            foreach ($group['columns'] as $key => $label) {
                $flatAvailable[$key] = $label;
            }
        }

        $defaultColumns = ['job_number', 'full_name', 'department_name', 'academic_rank', 'job_status', 'phone'];
        $selectedColumns = $request->input('columns', $defaultColumns);
        if (!is_array($selectedColumns) || empty($selectedColumns)) {
            $selectedColumns = $defaultColumns;
        }

        $query = StaffBase::with([
            'department.entity',
            'academicDetails.rank',
            'administrativeDetails.jobGrade',
            'contractDetails',
            'qualifications'
        ]);

        if ($request->filled('entity_id')) {
            $query->whereHas('department', function ($q) use ($request) {
                $q->where('entity_id', $request->entity_id);
            });
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->staff_type);
        }
        if ($request->filled('job_status')) {
            $query->where('job_status', $request->job_status);
        }

        $staffList = $query->orderBy('full_name')->get();

        $filename = 'custom_report_' . date('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($staffList, $selectedColumns, $flatAvailable) {
            $file = fopen('php://output', 'w');
            // إضافة علامة BOM لضمان قراءة اللغة العربية في إكسل بشكل صحيح
            fputs($file, "\xEF\xBB\xBF");

            // كتابة عناوين الأعمدة
            $headersRow = ['م'];
            foreach ($selectedColumns as $colKey) {
                $headersRow[] = $flatAvailable[$colKey] ?? $colKey;
            }
            fputcsv($file, $headersRow);

            // كتابة السجلات
            $seq = 1;
            foreach ($staffList as $staff) {
                $row = [$seq++];
                foreach ($selectedColumns as $colKey) {
                    $val = match ($colKey) {
                        'job_number' => $staff->job_number,
                        'full_name' => $staff->full_name,
                        'national_id' => $staff->national_id,
                        'gender' => $staff->gender,
                        'birth_date' => $staff->birth_date ? $staff->birth_date->format('Y/m/d') : '-',
                        'phone' => $staff->phone ?? '-',
                        'email' => $staff->email ?? '-',
                        'entity_name' => $staff->department && $staff->department->entity ? $staff->department->entity->name : '-',
                        'department_name' => $staff->department ? $staff->department->name : '-',
                        'staff_type' => match ($staff->staff_type) {
                            'academic' => 'أكاديمي',
                            'administrative' => 'إداري',
                            'contract' => 'متعاقد',
                            default => $staff->staff_type
                        },
                        'academic_rank' => $staff->academicDetails && $staff->academicDetails->rank ? $staff->academicDetails->rank->name : '-',
                        'general_specialization' => $staff->academicDetails ? $staff->academicDetails->general_specialization : '-',
                        'exact_specialization' => $staff->academicDetails ? $staff->academicDetails->exact_specialization : '-',
                        'academic_status' => $staff->academicDetails ? $staff->academicDetails->academic_status : '-',
                        'hire_date' => $staff->hire_date ? $staff->hire_date->format('Y/m/d') : '-',
                        'job_status' => $staff->job_status,
                        'job_grade' => $staff->administrativeDetails && $staff->administrativeDetails->jobGrade ? $staff->administrativeDetails->jobGrade->grade_name : '-',
                        'job_title' => $staff->administrativeDetails ? $staff->administrativeDetails->job_title : '-',
                        'work_nature' => $staff->administrativeDetails ? $staff->administrativeDetails->work_nature : '-',
                        'degree_type' => $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->degree_type : '-',
                        'granting_institution' => $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->granting_institution : '-',
                        'graduation_year' => $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->graduation_year : '-',
                        'country' => $staff->qualifications->isNotEmpty() ? $staff->qualifications->first()->country : '-',
                        default => '-',
                    };
                    $row[] = $val;
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
