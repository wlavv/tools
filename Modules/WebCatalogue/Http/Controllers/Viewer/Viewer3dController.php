<?php

namespace Modules\WebCatalogue\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;

class Viewer3dController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Admin/internal product viewer.
     */
    public function product(Product $product): View
    {
        return $this->renderViewer($product, false);
    }

    /**
     * Public product viewer.
     */
    public function show(Product $product): View
    {
        return $this->renderViewer($product, true);
    }

    protected function renderViewer(Product $product, bool $public = false): View
    {
        $product->loadMissing(['store', 'resources']);

        $model = $this->firstResource($product, ['model_3d']);
        $ar = $this->firstResource($product, ['ar_file']);
        $vr = $this->firstResource($product, ['vr_file', 'vr_scene']);
        $thumbnail = $this->firstResource($product, ['thumbnail', 'cover', 'image', 'gallery_image']);

        $gallery = $product->resources
            ->filter(fn ($resource) => $resource->is_image)
            ->values();

        return $this->view('webcatalogue::viewer.show', compact(
            'product',
            'model',
            'ar',
            'vr',
            'thumbnail',
            'gallery',
            'public'
        ));
    }

    protected function firstResource(Product $product, array $types): ?Resource
    {
        return $product->resources
            ->whereIn('resource_type', $types)
            ->sortBy([
                ['is_main', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();
    }
}
