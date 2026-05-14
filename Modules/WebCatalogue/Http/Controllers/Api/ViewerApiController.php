<?php

namespace Modules\WebCatalogue\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\WebCatalogue\Models\Product;

class ViewerApiController extends Controller
{
    public function __construct()
    {
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless(in_array((string) $product->status, config('webcatalogue.front_visible_statuses', ['published', 'active']), true), 404);

        $resources = $product->resources()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($resource) => [
                'id' => $resource->id,
                'resource_type' => $resource->resource_type,
                'title' => $resource->title,
                'description' => $resource->description,
                'url' => $resource->resolved_url,
                'mime_type' => $resource->mime_type,
                'extension' => $resource->extension,
                'is_main' => (bool) $resource->is_main,
                'sort_order' => (int) $resource->sort_order,
            ]);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'reference' => $product->reference,
                'sku' => $product->sku,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' => $product->short_description,
                'brand' => $product->brand,
                'category' => $product->category,
                'status' => $product->status,
            ],
            'resources' => $resources,
        ]);
    }
}
