<?php

namespace Modules\WebCatalogue\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\WebCatalogue\Models\Product;

class VrViewerController extends Controller
{
    public function show(Product $product): View
    {
        return view('webcatalogue::viewer.vr.show', compact('product'));
    }
}
