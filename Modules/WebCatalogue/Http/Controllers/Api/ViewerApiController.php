<?php

namespace Modules\WebCatalogue\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\WebCatalogue\Models\Product;

class ViewerApiController extends Controller
{
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'product' => $product,
            'resources' => $product->resources()->where('status', 'active')->orderBy('sort_order')->get(),
        ]);
    }
}
