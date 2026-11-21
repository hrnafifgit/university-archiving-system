<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';
    protected $primaryKey = 'audit_log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action_code',
        'entity_name',
        'entity_id',
        'occurred_at',
        'ip_address',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id', 'user_id');
    }
}
