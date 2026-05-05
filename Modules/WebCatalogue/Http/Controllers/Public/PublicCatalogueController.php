<?php

namespace Modules\WebCatalogue\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;

class PublicCatalogueController extends Controller
{
    public function show(string $slug): View
    {
        $catalogue = Catalogue::query()->where('slug', $slug)->firstOrFail();
        return view('webcatalogue::front.catalogue.show', compact('catalogue'));
    }

    public function product(string $slug): View
    {
        $product = Product::query()->where('slug', $slug)->firstOrFail();
        return view('webcatalogue::front.product.show', compact('product'));
    }
}
