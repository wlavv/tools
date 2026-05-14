<?php

namespace Modules\ConfigInspector\Inspectors;

class StorageInspector extends BaseInspector
{
    public function key(): string { return 'storage'; }
    public function label(): string { return 'Storage'; }

    public function inspect(): array
    {
        $paths = [
            storage_path(),
            storage_path('logs'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/views'),
            base_path('bootstrap/cache'),
        ];

        $items = [];
        foreach ($paths as $path) {
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            $items[] = $this->item(
                !$exists ? 'error' : ($writable ? 'success' : 'warning'),
                basename($path) ?: $path,
                !$exists ? 'Diretório inexistente.' : ($writable ? 'Diretório existe e é escrevível.' : 'Diretório existe mas pode não ser escrevível.'),
                ['path' => $path, 'writable' => $writable]
            );
        }
        return $items;
    }
}
