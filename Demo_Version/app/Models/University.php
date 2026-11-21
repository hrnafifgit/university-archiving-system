<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class University extends Model
{
    use HasUuids;

    protected $table = 'universities';
    protected $primaryKey = 'university_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['university_name_ar', 'university_name_en', 'country_id'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function qualifications()
    {
        return $this->hasMany(EmployeeQualification::class, 'university_id', 'university_id');
    }
}
