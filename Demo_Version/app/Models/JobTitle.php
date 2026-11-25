<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTitle extends Model
{
    use HasUuids;

    protected $table = 'job_titles';
    protected $primaryKey = 'job_title_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['title_name_ar', 'title_name_en', 'employee_category_id', 'is_active'];

    public function employmentRecords(): HasMany
    {
        return $this->hasMany(EmploymentRecord::class, 'job_title_id', 'job_title_id');
    }

    /**
     * جلب الكادر المرتبط بهذا المسمى سواء عبر المعرف أو نص الوظيفة بكشف الراتب
     */
    public function getEmployeesAttribute()
    {
        $fromRecords = $this->employmentRecords->map(fn($r) => $r->employee)->filter();
        
        $fromPayroll = EmploymentRecord::where('payroll_job_title', $this->title_name_ar)
            ->with(['employee'])
            ->get()
            ->map(fn($r) => $r->employee)
            ->filter();

        return $fromRecords->merge($fromPayroll)->unique('employee_id');
    }
}
