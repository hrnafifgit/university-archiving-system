<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionType extends Model
{
    protected $table = 'decision_types';
    protected $primaryKey = 'decision_type_id';
    public $timestamps = false;

    protected $fillable = ['type_code', 'type_name_ar', 'type_name_en', 'requires_end_date'];

    public function decisions()
    {
        return $this->hasMany(EmployeeDecision::class, 'decision_type_id', 'decision_type_id');
    }
}
