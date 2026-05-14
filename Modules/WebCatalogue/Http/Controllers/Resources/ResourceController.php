<?php

namespace Modules\WebCatalogue\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Storage\WebCatalogueStorageService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class ResourceController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }

    protected function viewData(array $extra = []): array
    {
        return array_merge([
            'stores' => Store::query()->orderBy('name')->get(),
            'catalogues' => Catalogue::query()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('reference')->get(),
        ], $extra);
    }

    public function index(Request $request): View
    {
        $items = Resource::query()->with(['store','product','catalogue'])->latest('id')->paginate(25)->withQueryString();
        return $this->view('webcatalogue::resources.index', compact('items'));
    }

    public function create(): View
    {
        return $this->view('webcatalogue::resources.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.resources.store'), 'method' => 'POST']));
    }

    public function store(Request $request, WebCatalogueStorageService $storage): RedirectResponse
    {
        $data = $this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['is_main'], 'default_status' => 'active']);
        $this->handleUpload($request, $data, $storage);
        $item = Resource::create($data);
        return redirect()->route('webcatalogue.resources.show', $item)->with('success', 'Resource created.');
    }

    public function show(Resource $resource): View { return $this->view('webcatalogue::resources.show', ['item' => $resource]); }

    public function edit(Resource $resource): View
    {
        return $this->view('webcatalogue::resources.form', $this->viewData(['item' => $resource, 'action' => route('webcatalogue.resources.update', $resource), 'method' => 'PUT']));
    }

    public function update(Request $request, Resource $resource, WebCatalogueStorageService $storage): RedirectResponse
    {
        $data = $this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['is_main'], 'default_status' => 'active']);
        $this->handleUpload($request, $data, $storage);
        $resource->update($data);
        return redirect()->route('webcatalogue.resources.show', $resource)->with('success', 'Resource updated.');
    }

    public function destroy(Resource $resource): RedirectResponse
    {
        $resource->delete();
        return redirect()->route('webcatalogue.resources.index')->with('success', 'Resource deleted.');
    }

    protected function handleUpload(Request $request, array &$data, WebCatalogueStorageService $storage): void
    {
        if (!$request->hasFile('uploaded_file') || !$request->file('uploaded_file')->isValid()) {
            return;
        }

        $file = $request->file('uploaded_file');
        $storeId = (int) ($data['id_store'] ?? 0);
        $productId = (int) ($data['id_product'] ?? 0);
        $resourceType = (string) ($data['resource_type'] ?? 'download');
        $disk = (string) config('webcatalogue.storage_disk', 'public');

        if ($storeId > 0 && $productId > 0) {
            $storage->ensureProductStructure($storeId, $productId);
            $folder = match ($resourceType) {
                'image', 'gallery_image', 'cover' => 'images',
                'thumbnail' => 'thumbnails',
                'video' => 'videos',
                'audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track' => 'audio',
                'model_3d' => 'models',
                'ar_file' => 'ar',
                'manual', 'datasheet', 'assembly_instructions', 'download' => 'documents',
                default => 'temp',
            };
            $base = 'webcatalogue/stores/' . $storeId . '/products/' . $productId . '/' . $folder;
        } elseif ($storeId > 0) {
            $storage->ensureStoreStructure($storeId);
            $base = 'webcatalogue/stores/' . $storeId . '/branding';
        } else {
            $storage->ensureBaseStructure();
            $base = 'webcatalogue/temp';
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = trim(($storeId ?: 'store') . '_' . ($productId ?: 'resource') . '_' . $resourceType . '_' . time() . '.' . $extension, '_');
        $path = $file->storeAs($base, $filename, $disk);

        $data['source_type'] = 'upload';
        $data['file_path'] = $path;
        $data['public_url'] = Storage::disk($disk)->url($path);
        $data['filename'] = $filename;
        $data['mime_type'] = $file->getMimeType();
        $data['file_size'] = $file->getSize();
        $data['extension'] = $extension;
    }
}
