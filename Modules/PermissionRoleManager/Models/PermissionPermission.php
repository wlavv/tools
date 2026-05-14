<?php

namespace Modules\PermissionRoleManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionPermission extends Model
{
    use SoftDeletes;

    protected $table = 'permission_permissions';

    protected $fillable = [
        'key', 'label', 'module', 'group', 'risk', 'description', 'is_system', 'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionRole::class,
            'permission_role_permission',
            'permission_permission_id',
            'permission_role_id'
        )->withTimestamps();
    }
}
