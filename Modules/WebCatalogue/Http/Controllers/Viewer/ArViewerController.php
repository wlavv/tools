<?php

namespace Modules\WebCatalogue\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\WebCatalogue\Models\Product;

class ArViewerController extends Controller
{
    public function __construct()
    {
    }

    public function show(Product $product): View
    {
        return view('webcatalogue::viewer.ar.show', compact('product'));
    }
}
