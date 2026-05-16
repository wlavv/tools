<?php

namespace Modules\WebCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ResourceFingerprint;
use Modules\WebCatalogue\Models\ImportBatch;
use Modules\WebCatalogue\Models\PublicLink;
use Modules\WebCatalogue\Models\ThreeDGenerationJob;
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

        $products = Product::query()
            ->with(['mainImageResource', 'prices', 'resources', 'catalogues'])
            ->get();

        $readyProductsCount = $products->filter(fn (Product $product) => $product->readinessScore() >= 100)->count();
        $needsWorkProductsCount = $products->count() - $readyProductsCount;
        $reviewStatuses = ['suggestions_found', 'no_match', 'unmatched_lead_created', 'capture_missing', 'match_failed'];
        $activePublicLinksCount = PublicLink::query()->where('status', 'active')->usable()->count();
        $candidateImagesCount = Resource::query()
            ->whereNotNull('id_product')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->count();

        return $this->view('webcatalogue::dashboard.index', [
            'storesCount' => Store::count(),
            'cataloguesCount' => Catalogue::count(),
            'productsCount' => $products->count(),
            'readyProductsCount' => $readyProductsCount,
            'needsWorkProductsCount' => $needsWorkProductsCount,
            'resourcesCount' => Resource::count(),
            'recognitionSessionsCount' => VisualRecognitionSession::count(),
            'recognitionLeadsCount' => UnmatchedProductLead::where('status', 'new')->count(),
            'platformCounters' => [
                [
                    'label' => 'Recognition sessions',
                    'value' => VisualRecognitionSession::count(),
                    'hint' => 'Total scans',
                    'icon' => 'fa-solid fa-camera',
                    'url' => route('webcatalogue.recognition.sessions.index', ['group' => 'all']),
                ],
                [
                    'label' => 'Review queue',
                    'value' => VisualRecognitionSession::whereIn('status', $reviewStatuses)->count(),
                    'hint' => 'Needs review',
                    'icon' => 'fa-solid fa-list-check',
                    'url' => route('webcatalogue.recognition.sessions.index'),
                ],
                [
                    'label' => 'New leads',
                    'value' => UnmatchedProductLead::where('status', 'new')->count(),
                    'hint' => 'Unmatched products',
                    'icon' => 'fa-solid fa-bullseye',
                    'url' => route('webcatalogue.recognition.leads.index'),
                ],
                [
                    'label' => 'Fingerprints',
                    'value' => ResourceFingerprint::count(),
                    'hint' => $candidateImagesCount . ' candidate images',
                    'icon' => 'fa-solid fa-fingerprint',
                    'url' => route('webcatalogue.recognition.index'),
                ],
                [
                    'label' => 'Public links',
                    'value' => $activePublicLinksCount,
                    'hint' => 'Active published links',
                    'icon' => 'fa-solid fa-link',
                    'url' => route('webcatalogue.stores.index'),
                ],
                [
                    'label' => 'Ready products',
                    'value' => $readyProductsCount,
                    'hint' => $needsWorkProductsCount . ' need work',
                    'icon' => 'fa-solid fa-circle-check',
                    'url' => route('webcatalogue.products.index'),
                ],
                [
                    'label' => 'Import batches',
                    'value' => ImportBatch::count(),
                    'hint' => ImportBatch::whereIn('status', ['pending', 'preview_ready', 'processing'])->count() . ' active',
                    'icon' => 'fa-solid fa-file-import',
                    'url' => route('webcatalogue.imports.index'),
                ],
                [
                    'label' => '3D jobs',
                    'value' => ThreeDGenerationJob::count(),
                    'hint' => ThreeDGenerationJob::whereIn('status', ['queued', 'processing'])->count() . ' running',
                    'icon' => 'fa-solid fa-cube',
                    'url' => route('webcatalogue.studio.3d_jobs.index'),
                ],
            ],
        ]);
    }
}
