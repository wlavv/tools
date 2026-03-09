<?php

namespace Modules\AssetLibrary\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AssetLibrary\Models\AssetLibrary;

class AssetLibraryService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = AssetLibrary::query()->latest('id');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('filename', 'like', '%' . $search . '%')
                    ->orWhere('tags', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate((int) config('asset-library.per_page', 24))->withQueryString();
    }

    public function store(array $data, UploadedFile $file): AssetLibrary
    {
        return DB::transaction(function () use ($data, $file) {
            $stored = $this->storeFile($file, $data['type']);

            return AssetLibrary::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'type' => $data['type'],
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'alt_text' => $data['alt_text'] ?? null,
                'tags' => $data['tags'] ?? null,
                'disk' => $stored['disk'],
                'directory' => $stored['directory'],
                'filename' => $stored['filename'],
                'original_filename' => $stored['original_filename'],
                'extension' => $stored['extension'],
                'mime_type' => $stored['mime_type'],
                'file_size' => $stored['file_size'],
                'file_path' => $stored['file_path'],
                'preview_path' => null,
                'is_public' => (bool) ($data['is_public'] ?? false),
            ]);
        });
    }

    public function update(AssetLibrary $asset, array $data, ?UploadedFile $file = null): AssetLibrary
    {
        return DB::transaction(function () use ($asset, $data, $file) {
            $payload = [
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name'], $asset->id),
                'type' => $data['type'],
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'alt_text' => $data['alt_text'] ?? null,
                'tags' => $data['tags'] ?? null,
                'is_public' => (bool) ($data['is_public'] ?? false),
            ];

            if ($file) {
                $this->deleteFileIfExists($asset->disk, $asset->file_path);

                $stored = $this->storeFile($file, $data['type']);

                $payload = array_merge($payload, [
                    'disk' => $stored['disk'],
                    'directory' => $stored['directory'],
                    'filename' => $stored['filename'],
                    'original_filename' => $stored['original_filename'],
                    'extension' => $stored['extension'],
                    'mime_type' => $stored['mime_type'],
                    'file_size' => $stored['file_size'],
                    'file_path' => $stored['file_path'],
                ]);
            }

            $asset->update($payload);

            return $asset->fresh();
        });
    }

    public function delete(AssetLibrary $asset): void
    {
        $this->deleteFileIfExists($asset->disk, $asset->file_path);

        if ($asset->preview_path) {
            $this->deleteFileIfExists($asset->disk, $asset->preview_path);
        }

        $asset->delete();
    }

    public function publicUrl(?string $path, ?string $disk = null): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($disk ?: config('asset-library.disk', 'public'))->url($path);
    }

    public function stats(): array
    {
        return [
            'total' => AssetLibrary::count(),
            'active' => AssetLibrary::where('status', 'active')->count(),
            'images' => AssetLibrary::where('type', 'image')->count(),
            'models' => AssetLibrary::where('type', 'model')->count(),
        ];
    }

    public function types(): array
    {
        return [
            'image' => 'Image',
            'model' => 'Model',
            'video' => 'Video',
            'texture' => 'Texture',
            'hdri' => 'HDRI',
            'document' => 'Document',
            'other' => 'Other',
        ];
    }

    public function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'active' => 'Active',
            'archived' => 'Archived',
        ];
    }

    protected function storeFile(UploadedFile $file, string $type): array
    {
        $disk = config('asset-library.disk', 'public');
        $baseDirectory = trim((string) config('asset-library.directory', 'webcatalogue/assets'), '/');
        $directory = $baseDirectory . '/' . $type;

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::uuid()->toString() . '.' . $extension;
        $filePath = $file->storeAs($directory, $filename, $disk);

        return [
            'disk' => $disk,
            'directory' => $directory,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'extension' => $extension,
            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
            'file_size' => (int) $file->getSize(),
            'file_path' => $filePath,
        ];
    }

    protected function deleteFileIfExists(?string $disk, ?string $path): void
    {
        if (!$path) {
            return;
        }

        $disk = $disk ?: config('asset-library.disk', 'public');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'asset';
        $slug = $base;
        $counter = 1;

        while (
            AssetLibrary::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }
}
