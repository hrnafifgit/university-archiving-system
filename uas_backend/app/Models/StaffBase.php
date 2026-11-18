<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StaffBase extends Model
{
    use HasFactory;

    protected $table = 'staff_base';

    protected $fillable = [
        'department_id',
        'job_number',
        'national_id',
        'full_name',
        'gender',
        'birth_date',
        'phone',
        'email',
        'hire_date',
        'staff_type',
        'job_status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
    ];

    /**
     * القسم أو الوحدة التابع لها الكادر
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentUnit::class, 'department_id');
    }

    /**
     * تفاصيل الكادر الأكاديمي (1:1 Subtype)
     */
    public function academicDetails(): HasOne
    {
        return $this->hasOne(AcademicDetail::class, 'staff_id');
    }

    /**
     * تفاصيل الكادر الإداري (1:1 Subtype)
     */
    public function administrativeDetails(): HasOne
    {
        return $this->hasOne(AdministrativeDetail::class, 'staff_id');
    }

    /**
     * تفاصيل المتعاقد بالساعات (1:1 Subtype)
     */
    public function contractDetails(): HasOne
    {
        return $this->hasOne(ContractDetail::class, 'staff_id');
    }

    /**
     * المؤهلات العلمية التراكمية (1:N)
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class, 'staff_id');
    }

    /**
     * القرارات الإدارية الرسمية (1:N)
     */
    public function decisions(): HasMany
    {
        return $this->hasMany(AdministrativeDecision::class, 'staff_id');
    }

    /**
     * الأرشيف الرقمي الشجري لملفات PDF (1:N)
     */
    public function archives(): HasMany
    {
        return $this->hasMany(DigitalArchive::class, 'staff_id');
    }
}
