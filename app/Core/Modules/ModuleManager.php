<?php

namespace App\Core\Modules;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use RuntimeException;

class ModuleManager
{
    protected bool $bootstrapped = false;

    public function __construct(
        protected Filesystem $files,
        protected ModuleRegistry $registry,
    ) {
    }

    public function bootstrap(): void
    {
        if ($this->bootstrapped) {
            return;
        }

        $modules = $this->discover();

        foreach ($modules as $module) {
            $this->registry->add($module);
        }

        foreach ($this->registry->enabled() as $module) {
            App::register($module->provider());
        }

        $this->bootstrapped = true;
    }

    /** @return Module[] */
    public function discover(): array
    {
        $modulesPath = config('modules.path', base_path('Modules'));

        if (!$this->files->isDirectory($modulesPath)) {
            return [];
        }

        $modules = [];

        foreach ($this->manifestFiles($modulesPath) as $manifestFile) {
            $directory = dirname($manifestFile);

            $data = json_decode($this->files->get($manifestFile), true);

            if (!is_array($data)) {
                throw new RuntimeException("Invalid JSON in module manifest: {$manifestFile}");
            }

            $modules[] = new Module($directory, ModuleManifest::fromArray($data));
        }

        usort($modules, fn (Module $a, Module $b) => strcmp($a->name(), $b->name()));

        return $modules;
    }

    /** @return array<int, string> */
    private function manifestFiles(string $modulesPath): array
    {
        $files = [];

        foreach ($this->files->directories($modulesPath) as $directory) {
            $manifestFile = $directory . '/module.json';

            if ($this->files->exists($manifestFile)) {
                $files[] = $manifestFile;
            }
        }

        $lsgPath = $modulesPath . '/LSG';

        if ($this->files->isDirectory($lsgPath)) {
            foreach ($this->files->directories($lsgPath) as $directory) {
                $manifestFile = $directory . '/module.json';

                if ($this->files->exists($manifestFile)) {
                    $files[] = $manifestFile;
                }
            }
        }

        $productGrowthPath = $modulesPath . '/LSG/ProductGrowth';

        if ($this->files->isDirectory($productGrowthPath)) {
            foreach ($this->files->directories($productGrowthPath) as $directory) {
                $manifestFile = $directory . '/module.json';

                if ($this->files->exists($manifestFile)) {
                    $files[] = $manifestFile;
                }
            }
        }

        sort($files);

        return $files;
    }

    public function registry(): ModuleRegistry
    {
        return $this->registry;
    }

    /** @return array<string, array<int, string>> */
    public function validateStructure(Module $module): array
    {
        $errors = [];
        $required = config('modules.required_files', []);

        foreach ($required as $relativePath) {
            if (!$module->hasFile($relativePath)) {
                $errors[] = "Missing required path: {$relativePath}";
            }
        }

        $dependencies = $module->dependencies();

        foreach (($dependencies['internal_modules'] ?? []) as $internalModuleSlug) {
            if (!$this->registry->find($internalModuleSlug)) {
                $errors[] = "Missing internal module dependency: {$internalModuleSlug}";
            }
        }

        return [$module->slug() => $errors];
    }
}
