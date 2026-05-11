<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    public function diskName(): string
    {
        return (string) config('documentmanager.storage_disk', 'local');
    }

    public function root(): string
    {
        return trim((string) config('documentmanager.storage_root', 'document-manager'), '/');
    }

    public function storeUploadedFile(UploadedFile $file, string $documentUuid): array
    {
        $checksum = $this->checksum($file->getRealPath());
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $extension = $extension ?: strtolower((string) $file->guessExtension());
        $name = (string) Str::uuid() . ($extension ? '.' . $extension : '');
        $path = $this->root() . '/' . date('Y/m') . '/' . $documentUuid . '/' . $name;

        Storage::disk($this->diskName())->put($path, file_get_contents($file->getRealPath()));

        return [
            'disk' => $this->diskName(),
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
            'extension' => $extension,
            'size_bytes' => (int) $file->getSize(),
            'checksum_algorithm' => config('documentmanager.checksum_algorithm', 'sha256'),
            'checksum' => $checksum,
        ];
    }

    public function checksum(string $path): ?string
    {
        $algorithm = (string) config('documentmanager.checksum_algorithm', 'sha256');

        return is_file($path) ? hash_file($algorithm, $path) : null;
    }

    public function health(): array
    {
        try {
            $disk = Storage::disk($this->diskName());
            $probe = $this->root() . '/.health-' . Str::random(12);
            $disk->put($probe, 'ok');
            $exists = $disk->exists($probe);
            $disk->delete($probe);

            return [
                'ok' => $exists,
                'disk' => $this->diskName(),
                'root' => $this->root(),
                'message' => $exists ? 'Storage writable' : 'Storage probe failed',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'disk' => $this->diskName(),
                'root' => $this->root(),
                'message' => $e->getMessage(),
            ];
        }
    }
}
