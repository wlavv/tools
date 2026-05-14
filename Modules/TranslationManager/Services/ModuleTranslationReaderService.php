<?php

namespace Modules\TranslationManager\Services;

class ModuleTranslationReaderService
{
    public function __construct(
        protected ModuleTranslationDiscoveryService $discovery,
        protected ArrayDotService $dot
    ) {
    }

    public function read(array $module, string $locale, string $file): array
    {
        $basePath = $this->discovery->basePathForFile($module, $locale, $file);
        $customPath = $this->discovery->overridePath($module['slug'], $locale, $file);

        $base = $this->safeRequire($basePath);
        $custom = $this->safeRequire($customPath);

        $baseFlat = $this->dot->flatten($base);
        $customFlat = $this->dot->flatten($custom);
        $mergedFlat = array_replace($baseFlat, $customFlat);

        $missingKeys = array_diff_key($baseFlat, $customFlat);
        $extraKeys = array_diff_key($customFlat, $baseFlat);
        $emptyKeys = array_filter($mergedFlat, fn ($value) => trim((string) $value) === '');

        $rows = [];
        foreach ($baseFlat as $key => $baseValue) {
            $hasCustom = array_key_exists($key, $customFlat);
            $value = $hasCustom ? $customFlat[$key] : $baseValue;

            $rows[] = [
                'key' => $key,
                'base' => $baseValue,
                'value' => $value,
                'has_custom' => $hasCustom,
                'is_empty' => trim((string) $value) === '',
                'is_changed' => $hasCustom && (string) $customFlat[$key] !== (string) $baseValue,
                'status' => $this->rowStatus($hasCustom, $value, $baseValue),
            ];
        }

        $status = $this->status($customPath, $missingKeys, $extraKeys, $emptyKeys);

        return [
            'module' => $module,
            'locale' => $locale,
            'file' => basename($file),
            'base_path' => $basePath,
            'custom_path' => $customPath,
            'custom_exists' => file_exists($customPath),
            'base' => $baseFlat,
            'custom' => $customFlat,
            'merged' => $mergedFlat,
            'rows' => $rows,
            'missing' => array_keys($missingKeys),
            'extra' => $extraKeys,
            'empty' => array_keys($emptyKeys),
            'status' => $status,
            'stats' => [
                'base_total' => count($baseFlat),
                'custom_total' => count($customFlat),
                'missing_total' => count($missingKeys),
                'empty_total' => count($emptyKeys),
                'extra_total' => count($extraKeys),
                'custom_exists' => file_exists($customPath),
            ],
        ];
    }

    private function safeRequire(?string $path): array
    {
        if (! $path || ! file_exists($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }

    private function status(string $customPath, array $missingKeys, array $extraKeys, array $emptyKeys): string
    {
        if (! file_exists($customPath)) {
            return 'base_only';
        }

        if (count($missingKeys) > 0) {
            return 'partial';
        }

        if (count($emptyKeys) > 0) {
            return 'has_empty';
        }

        if (count($extraKeys) > 0) {
            return 'has_extra';
        }

        return 'custom_full';
    }

    private function rowStatus(bool $hasCustom, mixed $value, mixed $baseValue): string
    {
        if (trim((string) $value) === '') {
            return 'empty';
        }

        if (! $hasCustom) {
            return 'base';
        }

        if ((string) $value !== (string) $baseValue) {
            return 'custom_changed';
        }

        return 'custom_same';
    }
}
