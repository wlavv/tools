<?php

namespace Modules\ConfigInspector\Inspectors;

class TranslationInspector extends BaseInspector
{
    public function key(): string { return 'translations'; }
    public function label(): string { return 'Translations'; }

    public function inspect(): array
    {
        $items = [];
        $modulesPath = base_path('Modules');
        $modules = is_dir($modulesPath) ? array_filter(glob($modulesPath . '/*'), 'is_dir') : [];

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $slug = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $moduleName));
            $langPath = $modulePath . '/Resources/lang';
            $items[] = $this->item(is_dir($langPath) ? 'success' : 'warning', $moduleName . ' lang path', is_dir($langPath) ? 'Resources/lang existe.' : 'Resources/lang em falta.', ['path' => $langPath]);

            foreach (['pt', 'en'] as $locale) {
                $localePath = $langPath . '/' . $locale;
                if (is_dir($localePath)) {
                    $items[] = $this->item('success', $moduleName . ' locale ' . $locale, 'Locale disponível.', ['path' => $localePath]);
                }
            }

            foreach (['page_titles.php', 'messages.php'] as $file) {
                $path = $langPath . '/pt/' . $file;
                $items[] = $this->item(file_exists($path) ? 'success' : 'info', $moduleName . ' pt/' . $file, file_exists($path) ? 'Ficheiro encontrado.' : 'Ficheiro opcional não encontrado.', ['path' => $path, 'suggested_namespace' => $slug]);
            }
        }

        return $items;
    }
}
