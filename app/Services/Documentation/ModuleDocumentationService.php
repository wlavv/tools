<?php

namespace App\Services\Documentation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ModuleDocumentationService
{
    public function all(): Collection
    {
        return collect()
            ->merge($this->docsDirectory())
            ->merge($this->moduleReadmes())
            ->sortBy([['group', 'asc'], ['title', 'asc']])
            ->values();
    }

    public function find(string $slug): array
    {
        $document = $this->all()->firstWhere('slug', $slug);

        if (!$document) {
            throw new RuntimeException('Documentation page not found.');
        }

        $content = File::get($document['path']);

        return array_merge($document, [
            'content' => $content,
            'html' => Str::markdown($content, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

    private function docsDirectory(): Collection
    {
        $directory = base_path('docs');

        if (!File::isDirectory($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'md')
            ->map(function ($file) {
                $name = $file->getBasename('.md');

                return [
                    'slug' => 'docs-' . Str::slug($name),
                    'title' => $this->titleFromMarkdown($file->getPathname()) ?: Str::headline($name),
                    'summary' => $this->summaryFromMarkdown($file->getPathname()),
                    'group' => 'Documentacao Interna',
                    'source' => 'docs',
                    'relative_path' => 'docs/' . $file->getFilename(),
                    'path' => $file->getPathname(),
                    'updated_at' => date('Y-m-d H:i', $file->getMTime()),
                ];
            });
    }

    private function moduleReadmes(): Collection
    {
        $directory = base_path('Modules');

        if (!File::isDirectory($directory)) {
            return collect();
        }

        return collect(File::directories($directory))
            ->map(fn ($moduleDirectory) => $moduleDirectory . DIRECTORY_SEPARATOR . 'README.md')
            ->filter(fn ($path) => File::isFile($path))
            ->map(function ($path) {
                $moduleName = basename(dirname($path));

                return [
                    'slug' => 'module-' . Str::slug($moduleName),
                    'title' => $this->titleFromMarkdown($path) ?: $moduleName,
                    'summary' => $this->summaryFromMarkdown($path),
                    'group' => 'Modulos',
                    'source' => 'module',
                    'relative_path' => 'Modules/' . $moduleName . '/README.md',
                    'path' => $path,
                    'updated_at' => date('Y-m-d H:i', filemtime($path)),
                ];
            });
    }

    private function titleFromMarkdown(string $path): ?string
    {
        foreach ($this->firstLines($path, 20) as $line) {
            if (preg_match('/^#\s+(.+)$/', trim($line), $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function summaryFromMarkdown(string $path): string
    {
        foreach ($this->firstLines($path, 60) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '```')) {
                continue;
            }

            return Str::limit(strip_tags($line), 180);
        }

        return 'Documentacao tecnica do modulo.';
    }

    private function firstLines(string $path, int $limit): array
    {
        $lines = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        while (($line = fgets($handle)) !== false && count($lines) < $limit) {
            $lines[] = $line;
        }

        fclose($handle);

        return $lines;
    }
}
