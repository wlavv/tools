<?php

namespace Modules\PermissionRoleManager\Services;

use Illuminate\Support\Facades\File;
use Modules\PermissionRoleManager\Models\PermissionPermission;

class PermissionSyncService
{
    public function syncFromModuleConfigs(): array
    {
        $created = 0;
        $updated = 0;
        $modulesPath = base_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            return compact('created', 'updated');
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            $configPath = $modulePath . '/Config/permissions.php';
            if (!File::exists($configPath)) {
                continue;
            }

            $config = include $configPath;
            $moduleName = basename($modulePath);
            $group = $config['group'] ?? $moduleName;

            foreach (($config['permissions'] ?? []) as $key => $data) {
                $permission = PermissionPermission::withTrashed()->where('key', $key)->first();
                $payload = [
                    'key' => $key,
                    'label' => $data['label'] ?? $key,
                    'module' => $moduleName,
                    'group' => $group,
                    'risk' => $data['risk'] ?? 'low',
                    'description' => $data['description'] ?? null,
                    'is_system' => true,
                    'is_active' => true,
                    'deleted_at' => null,
                ];

                if ($permission) {
                    $permission->fill($payload)->save();
                    $updated++;
                } else {
                    PermissionPermission::create($payload);
                    $created++;
                }
            }
        }

        return compact('created', 'updated');
    }
}
