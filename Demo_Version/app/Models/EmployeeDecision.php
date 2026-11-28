<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDecision extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_decisions';
    protected $primaryKey = 'decision_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'decision_type_id',
        'decision_number',
        'annual_number',
        'decision_year',
        'issuing_authority',
        'issue_date',
        'effective_start_date',
        'effective_end_date',
        'decision_status_id',
        'is_active',
        'summary',
        'notes',
        'data_source_id',
        'created_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'decision_employees', 'decision_id', 'employee_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DecisionType::class, 'decision_type_id', 'decision_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(DecisionStatus::class, 'decision_status_id', 'decision_status_id');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'decision_documents', 'decision_id', 'document_id');
    }

    public function employmentRecords(): HasMany
    {
        return $this->hasMany(EmploymentRecord::class, 'decision_id', 'decision_id');
    }
}
