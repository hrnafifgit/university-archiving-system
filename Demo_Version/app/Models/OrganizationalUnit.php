<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalUnit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'organizational_units';
    protected $primaryKey = 'organizational_unit_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'parent_unit_id',
        'unit_code',
        'unit_name_ar',
        'unit_name_en',
        'unit_type',
        'is_active',
        'created_at',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'parent_unit_id', 'organizational_unit_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganizationalUnit::class, 'parent_unit_id', 'organizational_unit_id');
    }

    public function employmentRecords(): HasMany
    {
        return $this->hasMany(EmploymentRecord::class, 'organizational_unit_id', 'organizational_unit_id');
    }
}
