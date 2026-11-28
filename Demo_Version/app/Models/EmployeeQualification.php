<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmployeeQualification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_qualifications';
    protected $primaryKey = 'qualification_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'qualification_level_id',
        'specialization_id',
        'university_id',
        'country_id',
        'dispatch_decision_id',
        'degree_title',
        'grade_or_gpa',
        'study_mode',
        'graduation_date',
        'award_date',
        'is_highest_qualification',
        'verification_status',
        'verification_date',
        'data_source_id',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'graduation_date' => 'date',
        'award_date' => 'date',
        'verification_date' => 'date',
        'is_highest_qualification' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(QualificationLevel::class, 'qualification_level_id', 'qualification_level_id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'specialization_id', 'specialization_id');
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_id', 'university_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'qualification_documents', 'qualification_id', 'document_id');
    }
}
