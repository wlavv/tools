<?php

namespace Modules\WebCatalogue\Support\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait HandlesWebCatalogueFormData
{
    protected function cleanWebCatalogueData(Request $request, array $options = []): array
    {
        $data = $request->except(['_token', '_method']);

        // Never persist file inputs as model attributes. Laravel includes UploadedFile
        // objects in request data when using all()/except() unless we explicitly
        // remove every file field. This keeps upload fields such as logo_upload,
        // cover_upload, main_image, gallery_images, model_3d_file, ar_file, etc.
        // out of INSERT/UPDATE statements and avoids unknown-column errors.
        foreach (array_keys($request->allFiles()) as $fileField) {
            unset($data[$fileField]);
        }

        foreach (($options['boolean'] ?? []) as $field) {
            $data[$field] = $request->boolean($field);
        }

        foreach (($options['json'] ?? []) as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            if ($data[$field] === null || trim((string) $data[$field]) === '') {
                $data[$field] = null;
                continue;
            }

            $decoded = json_decode((string) $data[$field], true);
            $data[$field] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (array_key_exists('slug', $data) && trim((string) $data['slug']) === '') {
            $data['slug'] = Str::slug($data['name'] ?? $data['title'] ?? $data['reference'] ?? uniqid());
        } elseif (!array_key_exists('slug', $data) && (array_key_exists('name', $data) || array_key_exists('title', $data))) {
            $data['slug'] = Str::slug($data['name'] ?? $data['title'] ?? uniqid());
        }

        if (array_key_exists('code', $data) && trim((string) $data['code']) === '' && !empty($data['name'])) {
            $data['code'] = Str::upper(Str::slug($data['name'], '_'));
        }

        if (!array_key_exists('status', $data) || trim((string) ($data['status'] ?? '')) === '') {
            $data['status'] = $options['default_status'] ?? 'draft';
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
