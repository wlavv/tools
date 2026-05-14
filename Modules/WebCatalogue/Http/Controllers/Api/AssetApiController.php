<?php

namespace Modules\WebCatalogue\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\WebCatalogue\Models\Resource;

class AssetApiController extends Controller
{
    public function __construct()
    {
    }

    public function show(Resource $resource): JsonResponse
    {
        abort_unless($resource->status === 'active', 404);

        if ($resource->product) {
            abort_unless(in_array((string) $resource->product->status, config('webcatalogue.front_visible_statuses', ['published', 'active']), true), 404);
        }

        return response()->json([
            'resource' => [
                'id' => $resource->id,
                'resource_type' => $resource->resource_type,
                'title' => $resource->title,
                'description' => $resource->description,
                'url' => $resource->resolved_url,
                'mime_type' => $resource->mime_type,
                'extension' => $resource->extension,
                'file_size' => $resource->file_size,
                'is_main' => (bool) $resource->is_main,
            ],
        ]);
    }
}
