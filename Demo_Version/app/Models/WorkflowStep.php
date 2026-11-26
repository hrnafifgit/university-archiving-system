<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    use HasUuids;

    protected $table = 'workflow_steps';
    protected $primaryKey = 'step_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transaction_id',
        'step_number',
        'step_title',
        'required_role',
        'actor_id',
        'actor_name',
        'action',
        'notes',
        'acted_at',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'acted_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WorkflowTransaction::class, 'transaction_id', 'transaction_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'actor_id', 'user_id');
    }
}
