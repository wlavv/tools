<?php

namespace App\Core\Modules;

class Module
{
    public function __construct(
        protected string $basePath,
        protected ModuleManifest $manifest,
    ) {
    }

    public function basePath(string $path = ''): string
    {
        return $path === ''
            ? $this->basePath
            : $this->basePath . '/' . ltrim($path, '/');
    }

    public function manifest(): ModuleManifest
    {
        return $this->manifest;
    }

    public function name(): string
    {
        return $this->manifest->name;
    }

    public function slug(): string
    {
        return $this->manifest->slug;
    }

    public function enabled(): bool
    {
        return $this->manifest->enabled;
    }

    public function provider(): string
    {
        return $this->manifest->provider;
    }

    public function dependencies(): array
    {
        return $this->manifest->dependencies;
    }

    public function hasFile(string $relativePath): bool
    {
        return file_exists($this->basePath($relativePath));
    }
}
