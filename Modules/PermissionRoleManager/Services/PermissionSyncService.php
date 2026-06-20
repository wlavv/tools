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

        foreach ($this->permissionConfigFiles($modulesPath) as $configPath) {
            $modulePath = dirname($configPath, 2);

            $config = include $configPath;
            $moduleName = $this->moduleName($modulePath);
            $group = $config['group'] ?? $moduleName;

            foreach (($config['permissions'] ?? []) as $key => $data) {
                if (!is_array($data)) {
                    $data = ['label' => (string) $data];
                }

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

    /** @return array<int, string> */
    private function permissionConfigFiles(string $modulesPath): array
    {
        $files = [];

        foreach (File::directories($modulesPath) as $modulePath) {
            $configPath = $modulePath . '/Config/permissions.php';

            if (File::exists($configPath)) {
                $files[] = $configPath;
            }
        }

        $lsgPath = $modulesPath . '/LSG';

        if (File::isDirectory($lsgPath)) {
            foreach (File::directories($lsgPath) as $modulePath) {
                $configPath = $modulePath . '/Config/permissions.php';

                if (File::exists($configPath)) {
                    $files[] = $configPath;
                }
            }
        }

        $productGrowthPath = $modulesPath . '/LSG/ProductGrowth';

        if (File::isDirectory($productGrowthPath)) {
            foreach (File::directories($productGrowthPath) as $modulePath) {
                $configPath = $modulePath . '/Config/permissions.php';

                if (File::exists($configPath)) {
                    $files[] = $configPath;
                }
            }
        }

        $files = array_values(array_unique($files));
        sort($files);

        return $files;
    }

    private function moduleName(string $modulePath): string
    {
        $manifestPath = $modulePath . '/module.json';

        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);

            if (is_array($manifest) && !empty($manifest['name'])) {
                return (string) $manifest['name'];
            }
        }

        return basename($modulePath);
    }
}
