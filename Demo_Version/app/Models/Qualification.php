<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Qualification extends Model
{
    use HasFactory;

    protected $table = 'qualifications';

    protected $fillable = [
        'staff_id',
        'degree_type',
        'specialization',
        'granting_institution',
        'graduation_year',
        'country',
        'thesis_title',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffBase::class, 'staff_id');
    }
}
