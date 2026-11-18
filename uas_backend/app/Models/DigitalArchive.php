<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalArchive extends Model
{
    use HasFactory;

    protected $table = 'digital_archives';

    protected $fillable = [
        'uuid',
        'staff_id',
        'decision_id',
        'qualification_id',
        'document_title',
        'tree_category',
        'file_path',
        'file_size_bytes',
        'page_count',
        'mime_type',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffBase::class, 'staff_id');
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(AdministrativeDecision::class, 'decision_id');
    }

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class, 'qualification_id');
    }
}
