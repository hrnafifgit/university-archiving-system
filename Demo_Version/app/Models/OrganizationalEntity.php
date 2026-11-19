<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalEntity extends Model
{
    use HasFactory;

    protected $table = 'organizational_entities';

    protected $fillable = [
        'name',
        'entity_type',
        'nature',
        'code',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(DepartmentUnit::class, 'entity_id');
    }
}
