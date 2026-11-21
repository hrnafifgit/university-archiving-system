<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasUuids;

    protected $table = 'specializations';
    protected $primaryKey = 'specialization_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['parent_specialization_id', 'specialization_name_ar', 'specialization_name_en', 'specialization_type'];
}
