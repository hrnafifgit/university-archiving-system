<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentUnit extends Model
{
    use HasFactory;

    protected $table = 'departments_units';

    protected $fillable = [
        'entity_id',
        'name',
        'unit_type',
        'code',
        'established_at',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(OrganizationalEntity::class, 'entity_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(StaffBase::class, 'department_id');
    }
}
