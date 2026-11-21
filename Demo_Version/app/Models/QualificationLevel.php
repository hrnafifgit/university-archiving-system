<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualificationLevel extends Model
{
    protected $table = 'qualification_levels';
    protected $primaryKey = 'qualification_level_id';
    public $timestamps = false;

    protected $fillable = ['level_code', 'level_name_ar', 'level_name_en', 'level_order'];
}
