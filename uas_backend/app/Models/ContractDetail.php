<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDetail extends Model
{
    use HasFactory;

    protected $table = 'contract_details';

    protected $fillable = [
        'staff_id',
        'contract_type',
        'approved_hours',
        'hourly_rate',
        'contract_start_date',
        'contract_end_date',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffBase::class, 'staff_id');
    }
}
