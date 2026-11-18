<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicDetail extends Model
{
    use HasFactory;

    protected $table = 'academic_details';

    protected $fillable = [
        'staff_id',
        'rank_id',
        'general_specialization',
        'exact_specialization',
        'academic_status',
        'teaching_load',
        'last_promotion_date',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffBase::class, 'staff_id');
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(AcademicRank::class, 'rank_id');
    }
}
