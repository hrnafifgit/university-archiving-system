<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AppRole extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_roles';
    protected $primaryKey = 'role_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AppUser::class, 'user_roles', 'role_id', 'user_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AppPermission::class, 'role_permissions', 'role_id', 'permission_id');
    }
}
