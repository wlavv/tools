<?php

namespace Modules\PermissionRoleManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionRole extends Model
{
    use SoftDeletes;

    protected $table = 'permission_roles';

    protected $fillable = [
        'name', 'slug', 'guard_name', 'description', 'color', 'is_system', 'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionPermission::class,
            'permission_role_permission',
            'permission_role_id',
            'permission_permission_id'
        )->withTimestamps();
    }
}
