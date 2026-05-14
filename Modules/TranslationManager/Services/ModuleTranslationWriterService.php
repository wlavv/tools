<?php

namespace Modules\TranslationManager\Services;

use Illuminate\Support\Facades\File;

class ModuleTranslationWriterService
{
    public function __construct(
        protected ModuleTranslationDiscoveryService $discovery,
        protected ArrayDotService $dot
    ) {
    }

    public function writeOverride(array $module, string $locale, string $file, array $translations): void
    {
        $basePath = $this->discovery->basePathForFile($module, $locale, $file);
        $base = $basePath && file_exists($basePath) ? require $basePath : [];
        $baseFlat = $this->dot->flatten(is_array($base) ? $base : []);

        $customFlat = [];

        foreach ($translations as $key => $value) {
            if (! array_key_exists($key, $baseFlat)) {
                continue;
            }

            $customFlat[$key] = $value ?? '';
        }

        ksort($customFlat);

        $nested = $this->dot->unflatten($customFlat);
        $path = $this->discovery->overridePath($module['slug'], $locale, $file);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $this->toPhpArrayFile($nested));
    }

    public function deleteOverrideFile(array $module, string $locale, string $file): void
    {
        $path = $this->discovery->overridePath($module['slug'], $locale, $file);

        if (file_exists($path)) {
            File::delete($path);
        }
    }

    public function removeKey(array $module, string $locale, string $file, string $key): void
    {
        $path = $this->discovery->overridePath($module['slug'], $locale, $file);

        if (! file_exists($path)) {
            return;
        }

        $data = require $path;
        $flat = $this->dot->flatten(is_array($data) ? $data : []);
        unset($flat[$key]);
        ksort($flat);

        File::put($path, $this->toPhpArrayFile($this->dot->unflatten($flat)));
    }

    private function toPhpArrayFile(array $data): string
    {
        return "<?php\n\nreturn " . $this->exportArray($data) . ";\n";
    }

    private function exportArray(array $array, int $level = 0): string
    {
        $indent = str_repeat('    ', $level);
        $nextIndent = str_repeat('    ', $level + 1);
        $lines = ["["];

        foreach ($array as $key => $value) {
            $exportedKey = var_export($key, true);

            if (is_array($value)) {
                $lines[] = $nextIndent . $exportedKey . ' => ' . $this->exportArray($value, $level + 1) . ',';
            } else {
                $lines[] = $nextIndent . $exportedKey . ' => ' . var_export((string) $value, true) . ',';
            }
        }

        $lines[] = $indent . "]";

        return implode("\n", $lines);
    }
}
