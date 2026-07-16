<?php

namespace Modules\ProductImageReview\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ProductImageReview\Services\ProductImageReviewService;

class ProductImageReviewController extends Controller
{
    public function __construct(private readonly ProductImageReviewService $service)
    {
    }

    public function index()
    {
        return view('product-image-review::Index', [
            'manufacturers' => $this->service->asmManufacturers(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'manufacturer_id' => ['required', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json($this->service->products(
            (int) $validated['manufacturer_id'],
            (int) ($validated['page'] ?? 1)
        ));
    }
}
