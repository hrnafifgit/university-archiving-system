<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdministrativeDecision extends Model
{
    use HasFactory;

    protected $table = 'administrative_decisions';

    protected $fillable = [
        'staff_id',
        'decision_number',
        'decision_type',
        'issued_date',
        'effective_date',
        'status',
        'notes',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffBase::class, 'staff_id');
    }

    public function archives(): HasMany
    {
        return $this->hasMany(DigitalArchive::class, 'decision_id');
    }
}
