<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmploymentRecord extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employment_records';
    protected $primaryKey = 'employment_record_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'employee_category_id',
        'organizational_unit_id',
        'job_title_id',
        'job_grade_id',
        'academic_rank_id',
        'employment_status_id',
        'start_date',
        'end_date',
        'is_primary',
        'appointment_basis',
        'payroll_status',
        'payroll_job_title',
        'payroll_job_grade',
        'qualification_at_appointment',
        'first_appointment_date',
        'academic_appointment_date',
        'initial_rank',
        'decision_id',
        'data_source_id',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'first_appointment_date' => 'date',
        'academic_appointment_date' => 'date',
        'is_primary' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EmployeeCategory::class, 'employee_category_id', 'employee_category_id');
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id', 'organizational_unit_id');
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id', 'job_title_id');
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade_id', 'job_grade_id');
    }

    public function academicRank(): BelongsTo
    {
        return $this->belongsTo(AcademicRank::class, 'academic_rank_id', 'academic_rank_id');
    }

    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class, 'employment_status_id', 'employment_status_id');
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(EmployeeDecision::class, 'decision_id', 'decision_id');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'employment_documents', 'employment_record_id', 'document_id');
    }
}
