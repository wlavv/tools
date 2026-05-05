<?php

namespace Modules\WebCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\VisualRecognitionSession;
use Modules\WebCatalogue\Models\UnmatchedProductLead;
use Modules\WebCatalogue\Services\Storage\WebCatalogueStorageService;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(WebCatalogueStorageService $storage): View
    {
        $storage->ensureBaseStructure();

        return $this->view('webcatalogue::dashboard.index', [
            'storesCount' => Store::count(),
            'cataloguesCount' => Catalogue::count(),
            'productsCount' => Product::count(),
            'resourcesCount' => Resource::count(),
            'recognitionSessionsCount' => VisualRecognitionSession::count(),
            'recognitionLeadsCount' => UnmatchedProductLead::where('status', 'new')->count(),
        ]);
    }
}
