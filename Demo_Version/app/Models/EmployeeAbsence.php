<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAbsence extends Model
{
    use HasUuids;

    protected $table = 'employee_absences';
    protected $primaryKey = 'absence_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'absence_type',
        'start_date',
        'end_date',
        'destination_or_host',
        'status',
        'decision_id',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(EmployeeDecision::class, 'decision_id', 'decision_id');
    }
}
