<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'documents';
    protected $primaryKey = 'document_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'employee_id',
        'document_type_id',
        'document_title',
        'document_number',
        'document_date',
        'issuing_authority',
        'description',
        'is_confidential',
        'retention_until',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'document_date' => 'date',
        'is_confidential' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'document_type_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'document_id', 'document_id')->orderBy('version_number', 'desc');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class, 'document_id', 'document_id')->where('is_current', true);
    }

    public function decisions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EmployeeDecision::class, 'decision_documents', 'document_id', 'decision_id');
    }

    public function qualifications(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EmployeeQualification::class, 'qualification_documents', 'document_id', 'qualification_id');
    }

    public function employmentRecords(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EmploymentRecord::class, 'employment_documents', 'document_id', 'employment_record_id');
    }
}
