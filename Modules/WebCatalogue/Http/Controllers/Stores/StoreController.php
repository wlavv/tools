<?php

namespace Modules\WebCatalogue\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\FingerprintRebuildLog;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Services\Recognition\InternalImageMatchService;
use Modules\WebCatalogue\Services\Recognition\VisualMarkerService;
use Modules\WebCatalogue\Services\Storage\WebCatalogueStorageService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class StoreController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $items = Store::query()
            ->with('logoResource')
            ->withCount(['catalogues','products','resources','themes','environments','prices','promotions'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('domain', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(24)
            ->withQueryString();
        return $this->view('webcatalogue::stores.index', compact('items'));
    }

    public function create(): View
    {
        return $this->view('webcatalogue::stores.form', ['item' => null, 'action' => route('webcatalogue.stores.store'), 'method' => 'POST']);
    }

    public function store(Request $request, WebCatalogueStorageService $storage, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $this->validateLogoUpload($request);
        $data = $this->storePayload($request);
        $item = Store::create($data);
        $storage->ensureStoreStructure((int) $item->id);
        $this->handleLogoUpload($request, $item, $resources);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.stores.show', $item))->with('success', 'Store created.');
    }

    public function show(Store $store): View
    {
        $store->loadCount(['catalogues','products','resources','themes','environments','prices','promotions'])
            ->load([
                'logoResource',
                'latestFingerprintRebuildLog',
                'environments' => fn ($query) => $query->orderByDesc('is_default')->latest('id')->limit(1),
                'catalogues' => fn ($query) => $query->withCount('products')->latest('id')->limit(6),
                'products' => fn ($query) => $query->with(['mainImageResource','prices','resources','catalogues'])->withCount(['resources','catalogues'])->latest('id')->limit(6),
                'resources' => fn ($query) => $query->with(['product','catalogue'])->latest('id')->limit(8),
            ]);

        return $this->view('webcatalogue::stores.show', ['item' => $store]);
    }

    public function edit(Store $store): View
    {
        return $this->view('webcatalogue::stores.form', ['item' => $store, 'action' => route('webcatalogue.stores.update', $store), 'method' => 'PUT']);
    }

    public function update(Request $request, Store $store, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $this->validateLogoUpload($request);
        $store->update($this->storePayload($request));
        $this->handleLogoUpload($request, $store, $resources);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.stores.show', $store))->with('success', 'Store updated.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $store->delete();
        return redirect()->route('webcatalogue.stores.index')->with('success', 'Store deleted.');
    }

    public function rebuildFingerprints(Store $store, InternalImageMatchService $matcher): RedirectResponse
    {
        $startedAt = now();
        $log = FingerprintRebuildLog::create([
            'id_store' => $store->id,
            'trigger' => 'manual',
            'status' => 'running',
            'started_at' => $startedAt,
            'metadata' => ['user_id' => auth()->id()],
        ]);

        try {
            $result = $matcher->rebuildStoreDataset($store);
            $log->update([
                'status' => 'completed',
                'processed' => (int) ($result['processed'] ?? 0),
                'created_count' => (int) ($result['created'] ?? 0),
                'updated_count' => (int) ($result['updated'] ?? 0),
                'failed_count' => (int) ($result['failed'] ?? 0),
                'algorithm' => $result['algorithm'] ?? null,
                'finished_at' => now(),
                'duration_ms' => max(1, $startedAt->diffInMilliseconds(now())),
            ]);
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'duration_ms' => max(1, $startedAt->diffInMilliseconds(now())),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return back()->with(
            'success',
            'Fingerprints rebuilt for ' . $store->name . ': '
                . (int) ($result['processed'] ?? 0) . ' images, '
                . (int) ($result['created'] ?? 0) . ' new fingerprints, '
                . (int) ($result['updated'] ?? 0) . ' updated, '
                . (int) ($result['failed'] ?? 0) . ' failed.'
        );
    }

    public function rebuildMarkers(Store $store, VisualMarkerService $markers): RedirectResponse
    {
        $result = $markers->rebuildStore($store);

        return back()->with(
            'success',
            'Visual markers rebuilt for ' . $store->name . ': '
                . (int) ($result['processed'] ?? 0) . ' images, '
                . (int) ($result['updated'] ?? 0) . ' updated, '
                . (int) ($result['failed'] ?? 0) . ' failed.'
        );
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

    protected function validateLogoUpload(Request $request): void
    {
        $request->validate([
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);
    }
}
