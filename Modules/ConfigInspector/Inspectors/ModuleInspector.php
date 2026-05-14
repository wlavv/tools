<?php

namespace Modules\ConfigInspector\Inspectors;

class ModuleInspector extends BaseInspector
{
    public function key(): string
    {
        return 'modules';
    }

    public function label(): string
    {
        return 'Modules';
    }

    public function inspect(): array
    {
        $items = [];
        $modulesPath = base_path('Modules');

        if (! is_dir($modulesPath)) {
            return [$this->item('error', 'Modules path', 'Diretorio Modules nao encontrado.', ['path' => $modulesPath])];
        }

        $requiredKeys = config('config-inspector.module_required_manifest_keys', ['name', 'slug', 'enabled', 'version', 'provider']);
        $expectedPaths = config('config-inspector.module_expected_paths', []);
        $modules = array_filter(glob($modulesPath . '/*'), 'is_dir');

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $manifestPath = $modulePath . '/module.json';

            if (! file_exists($manifestPath)) {
                $items[] = $this->item('error', $moduleName . ' manifest', 'module.json inexistente.', ['module' => $moduleName]);
                continue;
            }

            $json = json_decode(file_get_contents($manifestPath), true);

            if (! is_array($json)) {
                $items[] = $this->item('critical', $moduleName . ' manifest', 'module.json invalido.', ['module' => $moduleName]);
                continue;
            }

            foreach ($requiredKeys as $key) {
                $items[] = $this->item(
                    array_key_exists($key, $json) ? 'success' : 'error',
                    $moduleName . ' manifest key: ' . $key,
                    array_key_exists($key, $json) ? 'Chave presente.' : 'Chave obrigatoria ausente.',
                    ['module' => $moduleName, 'key' => $key]
                );
            }

            $provider = $json['provider'] ?? null;

            if ($provider) {
                $providerPath = $modulePath . '/Providers/' . class_basename($provider) . '.php';
                $items[] = $this->item(
                    file_exists($providerPath) ? 'success' : 'error',
                    $moduleName . ' provider file',
                    file_exists($providerPath) ? 'Provider encontrado.' : 'Provider definido no manifest nao encontrado em Providers.',
                    ['module' => $moduleName, 'provider' => $provider, 'path' => $providerPath]
                );
            }

            foreach ($expectedPaths as $relativePath) {
                $full = $modulePath . '/' . $relativePath;
                $items[] = $this->item(
                    file_exists($full) ? 'success' : 'warning',
                    $moduleName . ' path: ' . $relativePath,
                    file_exists($full) ? 'Estrutura encontrada.' : 'Estrutura recomendada em falta.',
                    ['module' => $moduleName, 'path' => $full]
                );
            }
        }

        return $items;
    }
}
