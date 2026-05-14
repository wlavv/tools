<?php

namespace App\Support\Translations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;

class StorageOverrideFileLoader extends FileLoader
{
    protected string $storageOverridePath;

    public function __construct(Filesystem $files, string $path, ?string $storageOverridePath = null)
    {
        parent::__construct($files, $path);

        $this->storageOverridePath = $storageOverridePath ?: storage_path('app/translations/modules');
    }

    /**
     * Load the messages for the given locale.
     *
     * This keeps Laravel's native behaviour and adds one extra layer:
     * storage/app/translations/modules/{namespace}/{locale}/{group}.php
     *
     * The storage layer overrides the module base file without changing tracked files.
     */
    public function load($locale, $group, $namespace = null): array
    {
        $lines = parent::load($locale, $group, $namespace);

        if ($namespace === null || $namespace === '*' || $group === '*') {
            return $lines;
        }

        $overridePath = $this->overrideFilePath((string) $namespace, (string) $locale, (string) $group);

        if (! $this->files->exists($overridePath)) {
            return $lines;
        }

        $override = $this->files->getRequire($overridePath);

        if (! is_array($override)) {
            return $lines;
        }

        return array_replace_recursive($lines, $override);
    }

    protected function overrideFilePath(string $namespace, string $locale, string $group): string
    {
        return rtrim($this->storageOverridePath, '/\\')
            . DIRECTORY_SEPARATOR . $namespace
            . DIRECTORY_SEPARATOR . $locale
            . DIRECTORY_SEPARATOR . $group . '.php';
    }
}
