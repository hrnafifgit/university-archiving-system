<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'employee_number',
        'national_id',
        'full_name_ar',
        'full_name_en',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'phone_number',
        'email',
        'address',
        'photo_document_id',
        'notes',
        'is_active_record',
        'created_from_source_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active_record' => 'boolean',
    ];

    /**
     * سجلات التوظيف والتكليف المؤرخة (Employment History)
     */
    public function employmentRecords(): HasMany
    {
        return $this->hasMany(EmploymentRecord::class, 'employee_id', 'employee_id');
    }

    /**
     * التكليف الأساسي الحالي النشط (Current Primary Employment)
     */
    public function currentEmployment(): HasOne
    {
        return $this->hasOne(EmploymentRecord::class, 'employee_id', 'employee_id')
            ->where('is_primary', true)
            ->whereNull('end_date')
            ->latest('start_date');
    }

    /**
     * أحدث تكليف رسمي مسجل (سواء كان نشطاً أو منتهي الصلاحية) لمنع الـ null للمتقاعدين
     */
    public function latestEmployment(): HasOne
    {
        return $this->hasOne(EmploymentRecord::class, 'employee_id', 'employee_id')
            ->where('is_primary', true)
            ->latest('start_date');
    }

    /**
     * جلب التكليف النشط أو الأحدث تلقائياً
     */
    public function getActiveEmploymentAttribute(): ?EmploymentRecord
    {
        return $this->currentEmployment ?? $this->latestEmployment;
    }

    /**
     * المؤهلات الأكاديمية التراكمية
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(EmployeeQualification::class, 'employee_id', 'employee_id');
    }

    /**
     * سجل القرارات الإدارية الخاصة بالموظف
     */
    public function decisions(): HasMany
    {
        return $this->hasMany(EmployeeDecision::class, 'employee_id', 'employee_id');
    }

    /**
     * ملفات الأرشيف والوثائق الرسمية
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'employee_id', 'employee_id');
    }

    /**
     * الإجازات والإيفاد والغياب
     */
    public function absences(): HasMany
    {
        return $this->hasMany(EmployeeAbsence::class, 'employee_id', 'employee_id');
    }

    /**
     * وثيقة الصورة الشخصية الرسمية
     */
    public function photoDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'photo_document_id', 'document_id');
    }
}
