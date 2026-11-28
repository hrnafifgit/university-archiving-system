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
        'has_academic_conflict',
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
     * اسم الكلية أو الجهة الرئيسية التابع لها الموظف
     */
    public function getCollegeNameAttribute(): string
    {
        $active = $this->active_employment;
        $unit = $active?->organizationalUnit;
        if (!$unit) {
            return 'جامعة إب';
        }

        // إذا كانت الوحدة نفسها كلية أو مركز أو إدارة رئيسية
        if (in_array($unit->unit_type, ['faculty', 'college', 'center', 'administration'])) {
            return $unit->unit_name_ar;
        }

        // إذا كان قسماً أو مكتباً يتبع كلية
        if ($unit->parent && $unit->parent->unit_type !== 'university') {
            return $unit->parent->unit_name_ar;
        }

        return $unit->unit_name_ar ?? 'جامعة إب';
    }

    /**
     * اسم القسم الأكاديمي أو الإداري
     */
    public function getDepartmentNameAttribute(): string
    {
        $active = $this->active_employment;
        $unit = $active?->organizationalUnit;
        if (!$unit) {
            return '—';
        }

        // إذا كان قسماً يتبع كلية
        if ($unit->parent && $unit->parent->unit_type !== 'university') {
            return $unit->unit_name_ar;
        }

        if (in_array($unit->unit_type, ['faculty', 'college', 'center', 'administration'])) {
            return 'عمادة / إدارة الكلية';
        }

        return $unit->unit_name_ar ?? '—';
    }

    /**
     * الرتبة العلمية الحالية
     */
    public function getRankNameAttribute(): string
    {
        $active = $this->active_employment;
        return $active?->academicRank?->rank_name_ar ?? '—';
    }

    /**
     * الدرجة المالية / الوظيفية
     */
    public function getGradeNameAttribute(): string
    {
        $active = $this->active_employment;
        return $active?->jobGrade?->grade_name_ar ?? ($active?->payroll_job_grade ?? '—');
    }

    /**
     * الحالة / الموقف الوظيفي
     */
    public function getStatusNameAttribute(): string
    {
        $active = $this->active_employment;
        return $active?->employmentStatus?->status_name_ar ?? '—';
    }

    /**
     * المسمى الوظيفي
     */
    public function getJobTitleNameAttribute(): string
    {
        $active = $this->active_employment;
        return $active?->jobTitle?->title_name_ar ?? ($active?->payroll_job_title ?? '—');
    }

    /**
     * حالة المرتب والوظيفة بكشف الراتب
     */
    public function getPayrollStatusNameAttribute(): string
    {
        $active = $this->active_employment;
        return $active?->payroll_status ?? ($active?->payroll_job_title ?? '—');
    }

    /**
     * تاريخ التعيين المنسق
     */
    public function getAppointmentDateFormattedAttribute(): string
    {
        $active = $this->active_employment;
        $date = $active?->first_appointment_date ?? $active?->academic_appointment_date ?? $active?->start_date;
        return $date ? $date->format('Y-m-d') : '—';
    }

    /**
     * بطاقة ملخص بيانات الكادر للشريط الخفيف المنبثق عند مرور المؤشر
     */
    public function getQuickSummaryDataAttribute(): array
    {
        return [
            'id' => $this->employee_id,
            'name' => $this->full_name_ar,
            'number' => $this->employee_number ?? '—',
            'national_id' => $this->national_id ?? '—',
            'college' => $this->college_name,
            'department' => $this->department_name,
            'rank' => $this->rank_name,
            'grade' => $this->grade_name,
            'status' => $this->status_name,
            'job_title' => $this->job_title_name,
            'payroll_status' => $this->payroll_status_name,
            'appointment_date' => $this->appointment_date_formatted,
            'profile_url' => route('employees.show', $this->employee_id),
        ];
    }

    /**
     * المؤهلات الأكاديمية التراكمية
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(EmployeeQualification::class, 'employee_id', 'employee_id');
    }

    public function contractor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Contractor::class, 'employee_id', 'employee_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ContractorContract::class, 'employee_id', 'employee_id')->orderByDesc('contract_start_date');
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
