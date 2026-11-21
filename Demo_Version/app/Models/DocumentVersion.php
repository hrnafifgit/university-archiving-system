<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'document_versions';
    protected $primaryKey = 'document_version_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'version_number',
        'storage_key',
        'original_file_name',
        'mime_type',
        'file_size_bytes',
        'checksum_sha256',
        'uploaded_at',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    public function getVersionIdAttribute(): string
    {
        return (string) $this->document_version_id;
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id', 'document_id');
    }
}
