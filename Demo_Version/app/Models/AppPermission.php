<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AppPermission extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_permissions';
    protected $primaryKey = 'permission_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'permission_code',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AppRole::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
