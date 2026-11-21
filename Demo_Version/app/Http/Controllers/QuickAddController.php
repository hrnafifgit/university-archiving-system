<?php

namespace App\Http\Controllers;

use App\Models\AcademicRank;
use App\Models\DecisionType;
use App\Models\DocumentType;
use App\Models\EmployeeCategory;
use App\Models\EmploymentStatus;
use App\Models\JobGrade;
use App\Models\OrganizationalUnit;
use App\Models\QualificationLevel;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuickAddController extends Controller
{
    /**
     * إضافة سريعة لوحدة تنظيمية (كلية أو قسم)
     */
    public function storeUnit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|in:faculty,department,center,administration,office',
            'parent_id' => 'nullable|exists:organizational_units,organizational_unit_id',
        ]);

        $name = trim($request->name);

        $existing = OrganizationalUnit::where('unit_name_ar', $name)
            ->where('unit_type', $request->type)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'id' => $existing->organizational_unit_id,
                'name' => $existing->unit_name_ar,
                'parent_id' => $existing->parent_unit_id,
            ]);
        }

        $code = strtoupper(Str::slug($name, '_'));
        if (strlen($code) < 3) $code = 'UNT_' . Str::random(5);
        if (OrganizationalUnit::where('unit_code', $code)->exists()) {
            $code .= '_' . rand(10, 99);
        }

        $unit = OrganizationalUnit::create([
            'unit_name_ar' => $name,
            'unit_code' => $code,
            'unit_type' => $request->type,
            'parent_unit_id' => $request->parent_id ?: null,
            'is_active' => true,
        ]);

        AuditLogService::log('UNIT_QUICK_CREATE', 'organizational_units', $unit->organizational_unit_id, null, [
            'unit_name_ar' => $unit->unit_name_ar,
            'unit_code' => $unit->unit_code,
        ]);

        return response()->json([
            'success' => true,
            'id' => $unit->organizational_unit_id,
            'name' => $unit->unit_name_ar,
            'parent_id' => $unit->parent_unit_id,
        ]);
    }

    /**
     * إضافة سريعة لرتبة أكاديمية
     */
    public function storeRank(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $name = trim($request->name);

        $existing = AcademicRank::where('rank_name_ar', $name)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'id' => $existing->academic_rank_id,
                'name' => $existing->rank_name_ar,
            ]);
        }

        $code = 'RNK_' . strtoupper(Str::slug($name, '_'));
        if (strlen($code) < 5) $code = 'RNK_' . Str::random(4);
        if (AcademicRank::where('rank_code', $code)->exists()) {
            $code .= '_' . rand(10, 99);
        }

        $maxOrder = AcademicRank::max('rank_order') ?? 0;

        $rank = AcademicRank::create([
            'rank_code' => $code,
            'rank_name_ar' => $name,
            'rank_order' => $maxOrder + 1,
        ]);

        AuditLogService::log('RANK_QUICK_CREATE', 'academic_ranks', $rank->academic_rank_id, null, [
            'rank_name_ar' => $rank->rank_name_ar,
            'rank_code' => $rank->rank_code,
        ]);

        return response()->json([
            'success' => true,
            'id' => $rank->academic_rank_id,
            'name' => $rank->rank_name_ar,
        ]);
    }

    /**
     * إضافة سريعة لدرجة وظيفية
     */
    public function storeGrade(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = trim($request->name);

        $existing = JobGrade::where('grade_name_ar', $name)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'id' => $existing->job_grade_id,
                'name' => $existing->grade_name_ar,
            ]);
        }

        $code = 'GRD_' . strtoupper(Str::slug($name, '_'));
        if (strlen($code) < 5) $code = 'GRD_' . Str::random(4);
        if (JobGrade::where('grade_code', $code)->exists()) {
            $code .= '_' . rand(10, 99);
        }

        $grade = JobGrade::create([
            'grade_code' => $code,
            'grade_name_ar' => $name,
        ]);

        AuditLogService::log('GRADE_QUICK_CREATE', 'job_grades', $grade->job_grade_id, null, [
            'grade_name_ar' => $grade->grade_name_ar,
            'grade_code' => $grade->grade_code,
        ]);

        return response()->json([
            'success' => true,
            'id' => $grade->job_grade_id,
            'name' => $grade->grade_name_ar,
        ]);
    }

    /**
     * إضافة سريعة لحالة وظيفية
     */
    public function storeStatus(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $name = trim($request->name);

        $existing = EmploymentStatus::where('status_name_ar', $name)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'id' => $existing->employment_status_id,
                'name' => $existing->status_name_ar,
            ]);
        }

        $code = 'STS_' . strtoupper(Str::slug($name, '_'));
        if (strlen($code) < 5) $code = 'STS_' . Str::random(4);
        if (EmploymentStatus::where('status_code', $code)->exists()) {
            $code .= '_' . rand(10, 99);
        }

        $status = EmploymentStatus::create([
            'status_code' => $code,
            'status_name_ar' => $name,
            'is_active_status' => true,
        ]);

        AuditLogService::log('STATUS_QUICK_CREATE', 'employment_statuses', $status->employment_status_id, null, [
            'status_name_ar' => $status->status_name_ar,
            'status_code' => $status->status_code,
        ]);

        return response()->json([
            'success' => true,
            'id' => $status->employment_status_id,
            'name' => $status->status_name_ar,
        ]);
    }

    /**
     * إضافة سريعة لنوع وثيقة
     */
    public function storeDocType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:160',
        ]);

        $name = trim($request->name);

        $existing = DocumentType::where('type_name_ar', $name)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'id' => $existing->document_type_id,
                'name' => $existing->type_name_ar,
            ]);
        }

        $code = 'DOC_' . strtoupper(Str::slug($name, '_'));
        if (strlen($code) < 5) $code = 'DOC_' . Str::random(4);
        if (DocumentType::where('type_code', $code)->exists()) {
            $code .= '_' . rand(10, 99);
        }

        $docType = DocumentType::create([
            'type_code' => $code,
            'type_name_ar' => $name,
            'applies_to' => 'employee',
            'is_active' => true,
        ]);

        AuditLogService::log('DOCTYPE_QUICK_CREATE', 'document_types', $docType->document_type_id, null, [
            'type_name_ar' => $docType->type_name_ar,
            'type_code' => $docType->type_code,
        ]);

        return response()->json([
            'success' => true,
            'id' => $docType->document_type_id,
            'name' => $docType->type_name_ar,
        ]);
    }

    /**
     * إضافة سريعة لمستوى مؤهل علمي
     */
    public function storeQualLevel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $name = trim($request->name);

        $existing = QualificationLevel::where('level_name_ar', $name)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'id' => $existing->qualification_level_id,
                'name' => $existing->level_name_ar,
            ]);
        }

        $code = 'QL_' . strtoupper(Str::slug($name, '_'));
        if (strlen($code) < 4) $code = 'QL_' . Str::random(4);
        if (QualificationLevel::where('level_code', $code)->exists()) {
            $code .= '_' . rand(10, 99);
        }

        $maxOrder = QualificationLevel::max('level_order') ?? 0;

        $qualLevel = QualificationLevel::create([
            'level_code' => $code,
            'level_name_ar' => $name,
            'level_order' => $maxOrder + 1,
        ]);

        AuditLogService::log('QUAL_LEVEL_QUICK_CREATE', 'qualification_levels', $qualLevel->qualification_level_id, null, [
            'level_name_ar' => $qualLevel->level_name_ar,
            'level_code' => $qualLevel->level_code,
        ]);

        return response()->json([
            'success' => true,
            'id' => $qualLevel->qualification_level_id,
            'name' => $qualLevel->level_name_ar,
        ]);
    }
}
