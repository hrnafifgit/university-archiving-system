<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdministrativeDetail extends Model
{
    use HasFactory;

    protected $table = 'administrative_details';

    protected $fillable = [
        'staff_id',
        'job_grade_id',
        'job_title',
        'work_nature',
        'admin_confirmation_date',
        'performance_rating',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffBase::class, 'staff_id');
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade_id');
    }
}
