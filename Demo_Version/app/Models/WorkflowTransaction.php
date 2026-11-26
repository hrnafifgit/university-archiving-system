<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTransaction extends Model
{
    use HasUuids;

    protected $table = 'workflow_transactions';
    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transaction_code',
        'transaction_type',
        'title',
        'description',
        'amount',
        'department_name',
        'created_by',
        'created_by_name',
        'current_step',
        'status',
        'return_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'current_step' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'created_by', 'user_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class, 'transaction_id', 'transaction_id')->orderBy('step_number');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft' => ['label' => 'مسودة / غير مكتمل', 'bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-300', 'icon' => 'fa-file-pen'],
            'pending_dept_head' => ['label' => 'بانتظار تأكيد رئيس القسم', 'bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'icon' => 'fa-clock'],
            'pending_admin_manager' => ['label' => 'بانتظار تأكيد مدير الإدارة', 'bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'icon' => 'fa-user-check'],
            'pending_sec_general' => ['label' => 'بانتظار اعتماد الأمين العام', 'bg' => 'bg-purple-50', 'text' => 'text-purple-800', 'border' => 'border-purple-200', 'icon' => 'fa-stamp'],
            'approved' => ['label' => 'معتمد نهائياً', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200', 'icon' => 'fa-circle-check'],
            'returned' => ['label' => 'مُعاد للتعديل', 'bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200', 'icon' => 'fa-rotate-left'],
            default => ['label' => $this->status, 'bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'icon' => 'fa-circle-info'],
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'hourly_contract' => 'عقد ساعات',
            'budget' => 'بند موازنة',
            'decision' => 'قرار إداري',
            'employee_update' => 'تعديل بيانات كادر',
            default => $this->transaction_type,
        };
    }
}
