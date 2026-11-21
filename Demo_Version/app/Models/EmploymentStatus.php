<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentStatus extends Model
{
    protected $table = 'employment_statuses';
    protected $primaryKey = 'employment_status_id';
    public $timestamps = false;

    protected $fillable = ['status_code', 'status_name_ar', 'status_name_en', 'is_active_status'];

    public function employmentRecords()
    {
        return $this->hasMany(EmploymentRecord::class, 'employment_status_id', 'employment_status_id');
    }
}
