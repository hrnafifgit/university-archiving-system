<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contractor extends Model
{
    protected $table = 'contractors';
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'first_contract_date',
        'job_classification',
    ];

    protected $casts = [
        'first_contract_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ContractorContract::class, 'employee_id', 'employee_id')->orderByDesc('contract_start_date');
    }
}
