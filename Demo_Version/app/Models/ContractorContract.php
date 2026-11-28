<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractorContract extends Model
{
    use HasUuids;

    protected $table = 'contractor_contracts';
    protected $primaryKey = 'contract_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'contract_number',
        'wage_system',
        'approved_wage',
        'weekly_hours',
        'contract_start_date',
        'contract_end_date',
        'contract_status',
        'created_at',
    ];

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'approved_wage' => 'decimal:2',
    ];

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'employee_id', 'employee_id');
    }

    public function isExpired(): bool
    {
        return $this->contract_end_date && $this->contract_end_date->lt(now()->startOfDay());
    }

    public function getComputedStatusAttribute(): string
    {
        return $this->isExpired() ? 'منتهٍ' : 'ساري المفعول';
    }
}
