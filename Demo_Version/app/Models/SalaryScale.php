<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryScale extends Model
{
    use HasFactory;

    protected $table = 'salary_scale';

    protected $fillable = [
        'academic_rank_id',
        'job_grade_id',
        'base_salary',
        'housing_allowance',
        'specialization_allowance',
        'total_entitlement',
    ];

    public function academicRank(): BelongsTo
    {
        return $this->belongsTo(AcademicRank::class, 'academic_rank_id');
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade_id');
    }
}
