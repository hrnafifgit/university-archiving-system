<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\DecisionType;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\EmployeeDecision;
use App\Models\EmployeeQualification;
use App\Models\EmploymentRecord;
use App\Models\EmploymentStatus;
use App\Models\JobGrade;
use App\Models\OrganizationalUnit;
use App\Models\QualificationLevel;
use App\Services\AuditLogService;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeOperationsController extends Controller
{
    /**
     * 1. تعديل البيانات الشخصية والأساسية للموظف (Update Employee Base)
     */
    public function updateBasic(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'full_name_ar' => 'required|string|max:250',
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number,' . $employee->employee_id . ',employee_id',
            'national_id' => 'nullable|string|max:50|unique:employees,national_id,' . $employee->employee_id . ',employee_id',
            'gender' => 'required|in:male,female,M,F,ذكر,أنثى',
            'phone_number' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:254',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:180',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo_file' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('photo_file')) {
            $docPhoto = DocumentStorageService::storeEmployeePhoto($employee, $request->file('photo_file'));
            $validated['photo_document_id'] = $docPhoto->document_id;
        }

        unset($validated['photo_file']);
        
        // توحيد حفظ الجنس معيارياً في قاعدة البيانات
        $rawGender = strtolower(trim($validated['gender'] ?? 'male'));
        $validated['gender'] = in_array($rawGender, ['female', 'f', 'أنثى', 'انثى'], true) ? 'female' : 'male';

        $oldData = ['full_name_ar' => $employee->full_name_ar, 'phone_number' => $employee->phone_number];
        $employee->update($validated);

        AuditLogService::log('EMPLOYEE_UPDATE', 'employees', $employee->employee_id, $oldData, [
            'full_name_ar' => $validated['full_name_ar'],
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        Cache::forget('dashboard_core_metrics');

        return back()->with('success', 'تم تعديل وتحديث البيانات الشخصية للكادر بنجاح.');
    }

    /**
     * 2. تسجيل حركة وظيفية / تكليف جديد (Create New Employment Record)
     * ينهي التكليف السابق تلقائياً ويفتح التكليف الجديد لحفظ السجل التاريخي
     */
    public function storeAssignment(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'employee_category_id' => 'required|exists:employee_categories,employee_category_id',
            'organizational_unit_id' => 'required|exists:organizational_units,organizational_unit_id',
            'employment_status_id' => 'required|exists:employment_statuses,employment_status_id',
            'academic_rank_id' => 'nullable|exists:academic_ranks,academic_rank_id',
            'job_grade_id' => 'nullable|exists:job_grades,job_grade_id',
            'start_date' => 'required|date',
            'appointment_basis' => 'required|string|max:100',
            'decision_number' => 'nullable|string|max:100',
            'first_appointment_date' => 'nullable|date',
            'academic_appointment_date' => 'nullable|date',
            'qualification_at_appointment' => 'nullable|string|max:150',
            'initial_rank' => 'nullable|string|max:100',
            'payroll_status' => 'nullable|string|max:100',
            'payroll_job_title' => 'nullable|string|max:150',
            'payroll_job_grade' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ], [
            'employee_category_id.required' => 'يرجى اختيار الفئة الوظيفية للحركة الجديدة.',
            'organizational_unit_id.required' => 'يرجى اختيار الكلية والقسم للحركة الجديدة.',
            'employment_status_id.required' => 'يرجى تحديد الموقف الوظيفي للحركة.',
            'start_date.required' => 'يرجى إدخال تاريخ سريان التكليف.',
            'appointment_basis.required' => 'يرجى كتابة أساس أو سبب التكليف.',
        ]);

        DB::beginTransaction();
        try {
            // 0. جلب بيانات التكليف السابق لتوريث حقول كشف الراتب وتواريخ التعيين في حال لم يتم إدخالها مجدداً
            $prevRecord = EmploymentRecord::where('employee_id', $employee->employee_id)
                ->where('is_primary', true)
                ->whereNull('end_date')
                ->latest('start_date')
                ->first();

            // 1. إغلاق التكليف الأساسي السابق إن وجد
            EmploymentRecord::where('employee_id', $employee->employee_id)
                ->where('is_primary', true)
                ->whereNull('end_date')
                ->update([
                    'end_date' => $validated['start_date'],
                    'notes' => DB::raw("CONCAT(notes, ' | انتهت الفترة بتكليف رسمي جديد في " . date('Y-m-d') . "')")
                ]);

            // 2. تسجيل قرار التكليف إن وجد رقمه
            $decisionId = null;
            if ($request->filled('decision_number')) {
                $decision = EmployeeDecision::create([
                    'employee_id' => $employee->employee_id,
                    'decision_type_id' => $request->input('decision_type_id', 1),
                    'decision_number' => $request->input('decision_number'),
                    'issuing_authority' => $request->input('issuing_authority', 'رئيس جامعة إب'),
                    'issue_date' => $request->input('issue_date', now()->toDateString()),
                    'effective_start_date' => $validated['start_date'],
                    'decision_status_id' => 1,
                    'summary' => 'حركة تكليف / نقل وظيفي رسمي',
                    'created_at' => now(),
                ]);
                $decisionId = $decision->decision_id;
            }

            // 3. فتح السجل التاريخي الجديد
            EmploymentRecord::create([
                'employee_id' => $employee->employee_id,
                'employee_category_id' => $validated['employee_category_id'],
                'organizational_unit_id' => $validated['organizational_unit_id'],
                'academic_rank_id' => $validated['academic_rank_id'] ?? null,
                'job_grade_id' => $validated['job_grade_id'] ?? null,
                'employment_status_id' => $validated['employment_status_id'],
                'start_date' => $validated['start_date'],
                'end_date' => null,
                'is_primary' => true,
                'appointment_basis' => $validated['appointment_basis'] ?? 'حركة وظيفية رسمية',
                'first_appointment_date' => $validated['first_appointment_date'] ?? $prevRecord?->first_appointment_date,
                'academic_appointment_date' => $validated['academic_appointment_date'] ?? $prevRecord?->academic_appointment_date,
                'qualification_at_appointment' => $validated['qualification_at_appointment'] ?? $prevRecord?->qualification_at_appointment,
                'initial_rank' => $validated['initial_rank'] ?? $prevRecord?->initial_rank,
                'payroll_status' => $validated['payroll_status'] ?? $prevRecord?->payroll_status,
                'payroll_job_title' => $validated['payroll_job_title'] ?? $prevRecord?->payroll_job_title,
                'payroll_job_grade' => $validated['payroll_job_grade'] ?? $prevRecord?->payroll_job_grade,
                'decision_id' => $decisionId,
                'notes' => $validated['notes'] ?? 'تم إصدار التكليف وتحديث السجل التاريخي في النظام',
                'created_at' => now(),
            ]);

            DB::commit();
            Cache::forget('dashboard_core_metrics');
            return redirect()->route('employees.show', [$employee->employee_id, 'tab' => 'history'])
                ->with('success', 'تم تسجيل وتثبيت الحركة الوظيفية الجديدة بنجاح، وتوثيق السجل التاريخي السابق.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.show', [$employee->employee_id, 'tab' => 'history'])
                ->with('error', 'حدث خطأ أثناء تسجيل الحركة الوظيفية: ' . $e->getMessage());
        }
    }

    /**
     * تعديل بيانات السجل الوظيفي القائم (Update Employment Record)
     */
    public function updateAssignment(Request $request, $id, $assignmentId)
    {
        $record = EmploymentRecord::where('employee_id', $id)->where('employment_record_id', $assignmentId)->firstOrFail();

        $validated = $request->validate([
            'employee_category_id' => 'required|exists:employee_categories,employee_category_id',
            'organizational_unit_id' => 'required|exists:organizational_units,organizational_unit_id',
            'employment_status_id' => 'required|exists:employment_statuses,employment_status_id',
            'academic_rank_id' => 'nullable|exists:academic_ranks,academic_rank_id',
            'job_grade_id' => 'nullable|exists:job_grades,job_grade_id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'appointment_basis' => 'nullable|string|max:100',
            'first_appointment_date' => 'nullable|date',
            'academic_appointment_date' => 'nullable|date',
            'qualification_at_appointment' => 'nullable|string|max:150',
            'initial_rank' => 'nullable|string|max:100',
            'payroll_status' => 'nullable|string|max:100',
            'payroll_job_title' => 'nullable|string|max:150',
            'payroll_job_grade' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $record->update($validated);
        Cache::forget('dashboard_core_metrics');

        return redirect()->route('employees.show', [$id, 'tab' => 'history'])
            ->with('success', 'تم تعديل السجل الوظيفي وبيانات التسكين بنجاح.');
    }

    /**
     * 3. إضافة مؤهل أكاديمي معتمد جديد (Create New Qualification)
     */
    public function storeQualification(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'qualification_level_id' => 'required|exists:qualification_levels,qualification_level_id',
            'degree_title' => 'required|string|max:200',
            'graduation_date' => 'nullable|date',
            'award_date' => 'nullable|date',
            'grade_or_gpa' => 'nullable|string|max:100',
            'university_name' => 'nullable|string|max:200',
            'country_id' => 'nullable|exists:countries,country_id',
            'specialization_id' => 'nullable|exists:specializations,specialization_id',
            'study_mode' => 'nullable|in:داخلي,خارجي',
            'dispatch_decision_id' => 'nullable|exists:employee_decisions,decision_id',
            'notes' => 'nullable|string',
        ]);

        $univId = null;
        if ($request->filled('university_name')) {
            $u = \App\Models\University::firstOrCreate(
                ['university_name_ar' => trim($request->input('university_name'))]
            );
            $univId = $u->university_id;
        }

        if ($request->has('is_highest')) {
            EmployeeQualification::where('employee_id', $employee->employee_id)
                ->update(['is_highest_qualification' => false]);
        }

        EmployeeQualification::create([
            'employee_id' => $employee->employee_id,
            'qualification_level_id' => $validated['qualification_level_id'],
            'degree_title' => $validated['degree_title'],
            'university_id' => $univId,
            'country_id' => $validated['country_id'] ?? null,
            'specialization_id' => $validated['specialization_id'] ?? null,
            'study_mode' => $validated['study_mode'] ?? null,
            'dispatch_decision_id' => $validated['dispatch_decision_id'] ?? null,
            'graduation_date' => $validated['graduation_date'] ?? null,
            'award_date' => $validated['award_date'] ?? null,
            'grade_or_gpa' => $validated['grade_or_gpa'] ?? null,
            'is_highest_qualification' => $request->has('is_highest'),
            'verification_status' => 'verified',
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
        ]);

        return redirect()->route('employees.show', [$employee->employee_id, 'tab' => 'quals'])
            ->with('success', 'تمت إضافة وتوثيق المؤهل الأكاديمي الجديد بنجاح في ملف الكادر.');
    }

    /**
     * تعديل مؤهل أكاديمي (Update Qualification)
     */
    public function updateQualification(Request $request, $employeeId, $qualId)
    {
        $qual = EmployeeQualification::where('employee_id', $employeeId)->where('qualification_id', $qualId)->firstOrFail();

        $validated = $request->validate([
            'qualification_level_id' => 'required|exists:qualification_levels,qualification_level_id',
            'degree_title' => 'required|string|max:200',
            'graduation_date' => 'nullable|date',
            'award_date' => 'nullable|date',
            'grade_or_gpa' => 'nullable|string|max:100',
            'university_name' => 'nullable|string|max:200',
            'country_id' => 'nullable|exists:countries,country_id',
            'specialization_id' => 'nullable|exists:specializations,specialization_id',
            'study_mode' => 'nullable|in:داخلي,خارجي',
            'dispatch_decision_id' => 'nullable|exists:employee_decisions,decision_id',
            'notes' => 'nullable|string',
        ]);

        $univId = $qual->university_id;
        if ($request->filled('university_name')) {
            $u = \App\Models\University::firstOrCreate(
                ['university_name_ar' => trim($request->input('university_name'))]
            );
            $univId = $u->university_id;
        }

        if ($request->has('is_highest')) {
            EmployeeQualification::where('employee_id', $employeeId)
                ->where('qualification_id', '!=', $qualId)
                ->update(['is_highest_qualification' => false]);
        }

        $qual->update([
            'qualification_level_id' => $validated['qualification_level_id'],
            'degree_title' => $validated['degree_title'],
            'university_id' => $univId,
            'country_id' => $validated['country_id'] ?? null,
            'specialization_id' => $validated['specialization_id'] ?? null,
            'study_mode' => $validated['study_mode'] ?? null,
            'dispatch_decision_id' => $validated['dispatch_decision_id'] ?? null,
            'graduation_date' => $validated['graduation_date'] ?? null,
            'award_date' => $validated['award_date'] ?? null,
            'grade_or_gpa' => $validated['grade_or_gpa'] ?? null,
            'is_highest_qualification' => $request->has('is_highest'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('employees.show', [$employeeId, 'tab' => 'quals'])
            ->with('success', 'تم تعديل وتحديث بيانات المؤهل بنجاح.');
    }

    /**
     * 4. حذف مؤهل مع حماية المؤهلات السابقة (Delete Qualification)
     * يمنع حذف المؤهل إذا كان الكادر حاصلاً على مؤهل أعلى منه بعد ذلك
     */
    public function deleteQualification($employeeId, $qualId)
    {
        $qual = EmployeeQualification::with('level')->where('employee_id', $employeeId)->where('qualification_id', $qualId)->firstOrFail();

        $currentOrder = $qual->level?->level_order ?? 0;

        // التحقق من وجود مؤهل أعلى ممنوح للكادر
        $higherQual = EmployeeQualification::where('employee_id', $employeeId)
            ->where('qualification_id', '!=', $qualId)
            ->whereHas('level', function ($q) use ($currentOrder) {
                $q->where('level_order', '>', $currentOrder);
            })
            ->with('level')
            ->first();

        if ($higherQual) {
            $higherName = $higherQual->level?->level_name_ar ?? $higherQual->degree_title;
            return redirect()->route('employees.show', [$employeeId, 'tab' => 'quals'])
                ->with('error', "لا يمكن حذف مؤهل ({$qual->degree_title}) نظراً لتوثيق مؤهل أعلى منه ممنوح للكادر ({$higherName}). يجب حذف المؤهل الأعلى أولاً.");
        }

        $qual->delete();

        return redirect()->route('employees.show', [$employeeId, 'tab' => 'quals'])
            ->with('success', 'تم حذف المؤهل الأكاديمي بنجاح.');
    }

    /**
     * 5. إرفاق وثيقة أرشيفية رقمية جديدة مع الـ Checksum (Upload New Document)
     */
    public function storeDocument(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,document_type_id',
            'document_title' => 'required|string|max:250',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:25600',
            'document_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ], [
            'document_file.required' => 'يرجى تحديد واختيار ملف الوثيقة المراد أرشفتها.',
            'document_file.file' => 'الملف المرفوع غير صالح.',
            'document_file.uploaded' => 'تعذر رفع الملف، يرجى التأكد من أن حجم الملف لا يتجاوز 25 ميجابايت وصيغته PDF أو صورة.',
            'document_file.mimes' => 'نوع الملف غير مدعوم. الصيغ المعتمدة: PDF أو صورة (JPG, PNG).',
            'document_file.max' => 'حجم الملف يتجاوز الحد الأقصى المسموح به (25 ميجابايت).',
            'document_title.required' => 'يرجى كتابة عنوان الوثيقة.',
            'document_type_id.required' => 'يرجى اختيار نوع الوثيقة.',
        ]);

        DB::beginTransaction();
        try {
            $document = DocumentStorageService::storeEmployeeDocument($employee, $request->file('document_file'), $validated);

            DB::commit();
            Cache::forget('dashboard_core_metrics');
            return redirect()->route('employees.show', [$employee->employee_id, 'tab' => 'docs'])
                ->with('success', 'تم رفع وحفظ الوثيقة بنجاح ضمن أرشيف الكادر.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء أرشفة الوثيقة: ' . $e->getMessage());
        }
    }

    /**
     * 6. حذف وثيقة أرشيفية (Delete Document)
     */
    public function deleteDocument($employeeId, $docId)
    {
        $doc = Document::with(['versions'])->where('employee_id', $employeeId)->where('document_id', $docId)->firstOrFail();

        DB::beginTransaction();
        try {
            DocumentStorageService::deleteDocument($doc);

            DB::commit();
            Cache::forget('dashboard_core_metrics');
            return back()->with('success', 'تم حذف الوثيقة ومستنداتها وإصداراتها من الأرشيف بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف الوثيقة: ' . $e->getMessage());
        }
    }
}
