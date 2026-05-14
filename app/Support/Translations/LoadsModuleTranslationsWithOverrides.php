<?php

namespace App\Support\Translations;

trait LoadsModuleTranslationsWithOverrides
{
    /**
     * Wrapper for module providers.
     *
     * Runtime override merge is handled globally by StorageOverrideFileLoader.
     * This method keeps module providers explicit and standardised.
     */
    protected function loadModuleTranslationsWithOverrides(string $path, string $namespace): void
    {
        $this->loadTranslationsFrom($path, $namespace);
    }
}
