<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionStatus extends Model
{
    protected $table = 'decision_statuses';
    protected $primaryKey = 'decision_status_id';
    public $timestamps = false;

    protected $fillable = ['status_code', 'status_name_ar', 'status_name_en'];
}
