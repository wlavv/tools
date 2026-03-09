<?php

namespace App\Core\Modules;

class ModuleRegistry
{
    /** @var array<string, Module> */
    protected array $modules = [];

    public function add(Module $module): void
    {
        $this->modules[$module->slug()] = $module;
    }

    /** @return array<string, Module> */
    public function all(): array
    {
        return $this->modules;
    }

    /** @return array<string, Module> */
    public function enabled(): array
    {
        return array_filter($this->modules, fn (Module $module) => $module->enabled());
    }

    public function find(string $slug): ?Module
    {
        return $this->modules[$slug] ?? null;
    }
}
