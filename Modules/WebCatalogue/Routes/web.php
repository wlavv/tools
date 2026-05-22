<?php

use Illuminate\Support\Facades\Route;
use Modules\WebCatalogue\Http\Controllers\DashboardController;
use Modules\WebCatalogue\Http\Controllers\Stores\StoreController;
use Modules\WebCatalogue\Http\Controllers\Catalogues\CatalogueController;
use Modules\WebCatalogue\Http\Controllers\Products\ProductController;
use Modules\WebCatalogue\Http\Controllers\Resources\ResourceController;
use Modules\WebCatalogue\Http\Controllers\Imports\ImportCenterController;
use Modules\WebCatalogue\Http\Controllers\Imports\ProductImportController;
use Modules\WebCatalogue\Http\Controllers\Public\PublicCatalogueController;
use Modules\WebCatalogue\Http\Controllers\Front\FrontCatalogueController;
use Modules\WebCatalogue\Http\Controllers\Viewer\Viewer3dController;
use Modules\WebCatalogue\Http\Controllers\Viewer\ArViewerController;
use Modules\WebCatalogue\Http\Controllers\Viewer\VrViewerController;
use Modules\WebCatalogue\Http\Controllers\Api\ViewerApiController;
use Modules\WebCatalogue\Http\Controllers\Api\AssetApiController;
use Modules\WebCatalogue\Http\Controllers\Api\SessionLogApiController;
use Modules\WebCatalogue\Http\Controllers\Pricing\PricingController;
use Modules\WebCatalogue\Http\Controllers\Promotions\PromotionController;
use Modules\WebCatalogue\Http\Controllers\Themes\ThemeController;
use Modules\WebCatalogue\Http\Controllers\Environments\EnvironmentController;
use Modules\WebCatalogue\Http\Controllers\Studio\ThreeDGenerationJobController;
use Modules\WebCatalogue\Http\Controllers\Front\VisualRecognitionController;
use Modules\WebCatalogue\Http\Controllers\Recognition\RecognitionDashboardController;
use Modules\WebCatalogue\Http\Controllers\Recognition\RecognitionSessionController;
use Modules\WebCatalogue\Http\Controllers\Recognition\UnmatchedLeadController;
use Modules\WebCatalogue\Http\Controllers\Publish\StorePublishController;
use Modules\WebCatalogue\Http\Controllers\TemporaryTcgSeedController;


// Temporary one-shot online seed endpoint. Remove after running the TCG-Collectors import.
Route::middleware(['web'])
    ->get('/webcatalogue/temp/seed/tcg-collectors-mirrodin', TemporaryTcgSeedController::class)
    ->name('webcatalogue.temp.seed.tcg_collectors_mirrodin');

// Public WebCatalogue front layer. Kept outside the authenticated admin prefix.
Route::middleware(config('webcatalogue.front_middleware', ['web']))
    ->prefix('catalogue')
    ->name('webcatalogue.front.')
    ->group(function () {
        Route::get('/preview/{token}', [FrontCatalogueController::class, 'preview'])->name('preview.store');
        Route::get('/link/{token}', [FrontCatalogueController::class, 'publicLink'])->name('public_link');
        Route::get('/scan', [VisualRecognitionController::class, 'globalIndex'])->name('scan.global.index');
        Route::post('/scan/session', [VisualRecognitionController::class, 'globalSession'])->name('scan.global.session');
        Route::post('/scan/capture', [VisualRecognitionController::class, 'globalCapture'])->name('scan.global.capture');
        Route::post('/scan/match', [VisualRecognitionController::class, 'globalMatch'])->name('scan.global.match');
        Route::post('/scan/unmatched', [VisualRecognitionController::class, 'globalUnmatched'])->name('scan.global.unmatched');
        Route::get('/scan/result/{session_token}', [VisualRecognitionController::class, 'globalResult'])->name('scan.global.result');
        Route::get('/{store_slug}/scan', [VisualRecognitionController::class, 'index'])->name('scan.index');
        Route::post('/{store_slug}/scan/session', [VisualRecognitionController::class, 'session'])->name('scan.session');
        Route::post('/{store_slug}/scan/capture', [VisualRecognitionController::class, 'capture'])->name('scan.capture');
        Route::post('/{store_slug}/scan/match', [VisualRecognitionController::class, 'match'])->name('scan.match');
        Route::post('/{store_slug}/scan/unmatched', [VisualRecognitionController::class, 'unmatched'])->name('scan.unmatched');
        Route::get('/{store_slug}/scan/result/{session_token}', [VisualRecognitionController::class, 'result'])->name('scan.result');
        Route::get('/{store_slug}', [FrontCatalogueController::class, 'store'])->name('store.show');
        Route::get('/{store_slug}/product/{product_slug}', [FrontCatalogueController::class, 'product'])->name('product.show');
        Route::get('/{store_slug}/product/{product_slug}/viewer', [FrontCatalogueController::class, 'viewer'])->name('product.viewer');
        Route::get('/{store_slug}/{catalogue_slug}', [FrontCatalogueController::class, 'catalogue'])->name('catalogue.show');
        Route::get('/{store_slug}/{catalogue_slug}/product/{product_slug}', [FrontCatalogueController::class, 'catalogueProduct'])->name('catalogue.product.show');
        Route::get('/{store_slug}/{catalogue_slug}/product/{product_slug}/viewer', [FrontCatalogueController::class, 'catalogueViewer'])->name('catalogue.product.viewer');
    });

Route::middleware(config('webcatalogue.middleware', ['web', 'auth']))
    ->prefix(config('webcatalogue.route_prefix', 'webcatalogue'))
    ->name('webcatalogue.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        Route::prefix('recognition')->name('recognition.')->group(function () {
            Route::get('/', [RecognitionDashboardController::class, 'index'])->name('index');
            Route::get('/pipeline', [RecognitionDashboardController::class, 'pipeline'])->name('pipeline.index');
            Route::get('/pipeline/summary', [RecognitionDashboardController::class, 'pipelineSummary'])->name('pipeline.summary');
            Route::get('/pipeline/export.csv', [RecognitionDashboardController::class, 'pipelineExportCsv'])->name('pipeline.export_csv');
            Route::post('/pipeline/flush', [RecognitionDashboardController::class, 'flushPipeline'])->name('pipeline.flush');
            Route::get('/sessions', [RecognitionSessionController::class, 'index'])->name('sessions.index');
            Route::get('/sessions/{session}', [RecognitionSessionController::class, 'show'])->name('sessions.show');
            Route::delete('/sessions/{session}', [RecognitionSessionController::class, 'destroy'])->name('sessions.destroy');
            Route::post('/sessions/{session}/associate-product', [RecognitionSessionController::class, 'associateProduct'])->name('sessions.associate_product');
            Route::post('/sessions/{session}/compare-product', [RecognitionSessionController::class, 'compareProduct'])->name('sessions.compare_product');
            Route::post('/sessions/{session}/create-lead', [RecognitionSessionController::class, 'createLead'])->name('sessions.create_lead');
            Route::post('/sessions/{session}/create-product', [RecognitionSessionController::class, 'createProduct'])->name('sessions.create_product');
            Route::get('/leads', [UnmatchedLeadController::class, 'index'])->name('leads.index');
            Route::get('/leads/{lead}', [UnmatchedLeadController::class, 'show'])->name('leads.show');
            Route::post('/leads/{lead}/status', [UnmatchedLeadController::class, 'status'])->name('leads.status');
        });

        Route::resource('stores', StoreController::class);
        Route::post('stores/{store}/recognition/fingerprints/rebuild', [StoreController::class, 'rebuildFingerprints'])->name('stores.recognition.fingerprints.rebuild');
        Route::post('stores/{store}/recognition/markers/sync', [StoreController::class, 'syncMarkers'])->name('stores.recognition.markers.sync');
        Route::post('stores/{store}/recognition/markers/rebuild', [StoreController::class, 'rebuildMarkers'])->name('stores.recognition.markers.rebuild');
        Route::get('stores/{store}/publish', [StorePublishController::class, 'show'])->name('stores.publish.show');
        Route::post('stores/{store}/publish/preview', [StorePublishController::class, 'preview'])->name('stores.publish.preview');
        Route::post('stores/{store}/publish', [StorePublishController::class, 'publish'])->name('stores.publish.publish');
        Route::post('stores/{store}/publish/unpublish', [StorePublishController::class, 'unpublish'])->name('stores.publish.unpublish');
        Route::resource('catalogues', CatalogueController::class);
        Route::resource('products', ProductController::class);
        Route::get('products/{product}/viewer', [Viewer3dController::class, 'product'])->name('products.viewer');
        Route::resource('resources', ResourceController::class);
        Route::resource('promotions', PromotionController::class);
        Route::resource('themes', ThemeController::class);
        Route::resource('environments', EnvironmentController::class);

        Route::resource('pricing', PricingController::class);
        Route::post('studio/3d-jobs/{threeDGenerationJob}/run', [ThreeDGenerationJobController::class, 'run'])->name('studio.3d_jobs.run');
        Route::get('studio/3d-jobs/{threeDGenerationJob}/status', [ThreeDGenerationJobController::class, 'status'])->name('studio.3d_jobs.status');
        Route::resource('studio/3d-jobs', ThreeDGenerationJobController::class)->names('studio.3d_jobs')->parameters(['3d-jobs' => 'threeDGenerationJob']);

        Route::get('imports', [ImportCenterController::class, 'index'])->name('imports.index');
        Route::get('imports/{type}/template', [ImportCenterController::class, 'template'])->name('imports.template');
        Route::post('imports/{type}/upload', [ImportCenterController::class, 'upload'])->name('imports.upload');
        Route::get('imports/batches/{batch}/preview', [ImportCenterController::class, 'preview'])->name('imports.preview');
        Route::post('imports/batches/{batch}/confirm', [ImportCenterController::class, 'confirm'])->name('imports.confirm');
        // Backwards-compatible product import routes kept for older dashboard/actions/views.
        Route::get('imports/products/index', [ProductImportController::class, 'index'])->name('imports.products.index');
        Route::post('imports/products/store', [ProductImportController::class, 'store'])->name('imports.products.store');
        Route::get('imports/{type}', [ImportCenterController::class, 'show'])->name('imports.show');
    });


Route::middleware(config('webcatalogue.public_middleware', ['web']))
    ->prefix(config('webcatalogue.public_route_prefix', 'wc'))
    ->name('webcatalogue.public.')
    ->group(function () {
        Route::get('/catalogue/{slug}', [PublicCatalogueController::class, 'show'])->name('catalogue.show');
        Route::get('/product/{slug}', [PublicCatalogueController::class, 'product'])->name('product.show');
        Route::get('/viewer/{product}', [Viewer3dController::class, 'show'])->name('viewer.show');
        Route::get('/ar/{product}', [ArViewerController::class, 'show'])->name('ar.show');
        Route::get('/vr/{product}', [VrViewerController::class, 'show'])->name('vr.show');
    });

Route::middleware(config('webcatalogue.api_middleware', ['web']))
    ->prefix(config('webcatalogue.api_route_prefix', 'webcatalogue/api'))
    ->name('webcatalogue.api.')
    ->group(function () {
        Route::get('/viewer/{product}', [ViewerApiController::class, 'show'])->name('viewer.show');
        Route::get('/assets/{resource}', [AssetApiController::class, 'show'])->name('assets.show');
        Route::post('/session-logs', [SessionLogApiController::class, 'store'])->name('session_logs.store');
    });
