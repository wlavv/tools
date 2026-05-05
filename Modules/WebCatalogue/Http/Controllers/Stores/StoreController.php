<?php

namespace Modules\WebCatalogue\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Services\Storage\WebCatalogueStorageService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class StoreController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }

    public function index(Request $request): View
    {
        $items = Store::query()->with('logoResource')->withCount(['catalogues','products','resources','themes','environments'])->latest('id')->paginate(20);
        return $this->view('webcatalogue::stores.index', compact('items'));
    }

    public function create(): View
    {
        return $this->view('webcatalogue::stores.form', ['item' => null, 'action' => route('webcatalogue.stores.store'), 'method' => 'POST']);
    }

    public function store(Request $request, WebCatalogueStorageService $storage, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $data = $this->storePayload($request);
        $item = Store::create($data);
        $storage->ensureStoreStructure((int) $item->id);
        $this->handleLogoUpload($request, $item, $resources);
        return redirect()->route('webcatalogue.stores.show', $item)->with('success', 'Store created.');
    }

    public function show(Store $store): View { return $this->view('webcatalogue::stores.show', ['item' => $store]); }

    public function edit(Store $store): View
    {
        return $this->view('webcatalogue::stores.form', ['item' => $store, 'action' => route('webcatalogue.stores.update', $store), 'method' => 'PUT']);
    }

    public function update(Request $request, Store $store, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $store->update($this->storePayload($request));
        $this->handleLogoUpload($request, $store, $resources);
        return redirect()->route('webcatalogue.stores.show', $store)->with('success', 'Store updated.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $store->delete();
        return redirect()->route('webcatalogue.stores.index')->with('success', 'Store deleted.');
    }

    protected function storePayload(Request $request): array
    {
        $data = $this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'default_status' => 'active']);

        $frontFields = [
            'front_layout' => 'layout',
            'front_primary_color' => 'primary_color',
            'front_secondary_color' => 'secondary_color',
            'front_header_color' => 'header_color',
            'front_header_text_color' => 'header_text_color',
            'front_background_color' => 'background_color',
            'front_surface_color' => 'surface_color',
            'front_text_color' => 'text_color',
            'front_muted_text_color' => 'muted_text_color',
            'front_font_family' => 'font_family',
            'front_heading_font_family' => 'heading_font_family',
            'front_base_font_size' => 'base_font_size',
            'front_title_size' => 'title_size',
            'front_container_width' => 'container_width',
            'front_border_radius' => 'border_radius',
            'front_image_background' => 'image_background',
            'front_image_fit' => 'image_fit',
            'front_intro_text' => 'intro_text',
            'front_hide_downloads' => 'hide_downloads',
        ];

        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $metadata['front'] = is_array($metadata['front'] ?? null) ? $metadata['front'] : [];

        foreach ($frontFields as $requestKey => $metadataKey) {
            if (array_key_exists($requestKey, $data)) {
                $metadata['front'][$metadataKey] = $requestKey === 'front_hide_downloads'
                    ? $request->boolean($requestKey)
                    : $data[$requestKey];
                unset($data[$requestKey]);
            }
        }

        $data['metadata'] = $metadata;

        return $data;
    }

    protected function handleLogoUpload(Request $request, Store $store, WebCatalogueResourceUploadService $resources): void
    {
        if (!$request->hasFile('logo_upload') || !$request->file('logo_upload')->isValid()) {
            return;
        }

        $resource = $resources->storeUploadedResource($request->file('logo_upload'), [
            'id_store' => (int) $store->id,
            'resource_owner_type' => 'store',
            'resource_owner_id' => (int) $store->id,
            'resource_type' => 'logo',
            'title' => $store->name . ' · Logo',
            'is_main' => true,
            'status' => 'active',
        ]);

        $store->update(['logo_path' => $resource->public_url ?: $resource->file_path]);
    }
}
