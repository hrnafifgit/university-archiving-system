<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobGrade extends Model
{
    use HasUuids;

    protected $table = 'job_grades';
    protected $primaryKey = 'job_grade_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['grade_code', 'grade_name_ar', 'grade_name_en', 'description'];

    public function employmentRecords()
    {
        return $this->hasMany(EmploymentRecord::class, 'job_grade_id', 'job_grade_id');
    }
}
