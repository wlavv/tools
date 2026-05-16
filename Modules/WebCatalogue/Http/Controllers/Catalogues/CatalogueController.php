<?php

namespace Modules\WebCatalogue\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class CatalogueController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }

    protected function viewData(array $extra = []): array
    {
        $item = $extra['item'] ?? null;
        $storeId = (int) old('id_store', $item->id_store ?? request('id_store', 0));

        return array_merge([
            'stores' => Store::query()->orderBy('name')->get(),
            'products' => Product::query()
                ->when($storeId > 0, fn ($query) => $query->where('id_store', $storeId))
                ->orderBy('reference')
                ->limit(1000)
                ->get(),
        ], $extra);
    }

    public function index(Request $request): View
    {
        $storeId = $request->integer('id_store') ?: null;
        $store = $storeId ? Store::find($storeId) : null;
        $search = trim((string) $request->input('q', ''));
        $items = Catalogue::query()
            ->with(['store','coverResource'])
            ->withCount(['products'])
            ->when($storeId, fn ($query) => $query->where('id_store', $storeId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('catalogue_type', 'like', '%' . $search . '%')
                        ->orWhere('visibility', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        if ($store) {
            $returnQuery = $request->filled('return_to') ? ['return_to' => $request->input('return_to')] : [];
            $this->replaceAction('back', [
                'label' => 'Store hub',
                'name' => 'Store hub',
                'icon' => 'fa-solid fa-store',
                'class' => 'lsg-action-btn lsg-action-btn--back',
                'url' => $this->safeReturnTo($request) ?: route('webcatalogue.stores.show', $store),
                'route' => 'webcatalogue.stores.show',
                'type' => 'link',
            ]);
            $this->replaceAction('new', [
                'label' => 'New catalogue',
                'name' => 'New catalogue',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
                'url' => route('webcatalogue.catalogues.create', array_merge(['id_store' => $store->id], $returnQuery)),
                'route' => 'webcatalogue.catalogues.create',
                'type' => 'link',
            ]);
        }

        return $this->view('webcatalogue::catalogues.index', compact('items', 'store'));
    }

    public function create(): View
    {
        return $this->view('webcatalogue::catalogues.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.catalogues.store'), 'method' => 'POST']));
    }

    public function store(Request $request, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $data = $this->cataloguePayload($request);
        $item = Catalogue::create($data);
        $this->handleCoverUpload($request, $item, $resources);
        $this->syncCatalogueProducts($request, $item);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.catalogues.show', $item))->with('success', 'Catalogue created.');
    }

    public function show(Catalogue $catalogue): View { return $this->view('webcatalogue::catalogues.show', ['item' => $catalogue->load(['products'])->loadCount('products')]); }

    public function edit(Catalogue $catalogue): View
    {
        return $this->view('webcatalogue::catalogues.form', $this->viewData(['item' => $catalogue->load('products'), 'action' => route('webcatalogue.catalogues.update', $catalogue), 'method' => 'PUT']));
    }

    public function update(Request $request, Catalogue $catalogue, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $catalogue->update($this->cataloguePayload($request));
        $this->handleCoverUpload($request, $catalogue, $resources);
        $this->syncCatalogueProducts($request, $catalogue);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.catalogues.show', $catalogue))->with('success', 'Catalogue updated.');
    }

    public function destroy(Catalogue $catalogue): RedirectResponse
    {
        $catalogue->delete();
        return redirect()->route('webcatalogue.catalogues.index')->with('success', 'Catalogue deleted.');
    }

    protected function handleCoverUpload(Request $request, Catalogue $catalogue, WebCatalogueResourceUploadService $resources): void
    {
        if (!$request->hasFile('cover_upload') || !$request->file('cover_upload')->isValid()) {
            return;
        }

        $resource = $resources->storeUploadedResource($request->file('cover_upload'), [
            'id_store' => (int) $catalogue->id_store,
            'id_catalogue' => (int) $catalogue->id,
            'resource_owner_type' => 'catalogue',
            'resource_owner_id' => (int) $catalogue->id,
            'resource_type' => 'cover',
            'title' => $catalogue->name . ' · Cover',
            'is_main' => true,
            'status' => 'active',
        ]);

        $catalogue->update(['cover_resource_id' => $resource->id]);
    }

    protected function cataloguePayload(Request $request): array
    {
        $data = $this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['show_prices', 'show_promotions'], 'default_status' => 'draft']);
        unset($data['product_ids']);

        return $data;
    }

    protected function syncCatalogueProducts(Request $request, Catalogue $catalogue): void
    {
        $productIds = Product::query()
            ->where('id_store', $catalogue->id_store)
            ->whereIn('id', array_filter((array) $request->input('product_ids', [])))
            ->pluck('id')
            ->values();

        $sync = [];
        foreach ($productIds as $index => $productId) {
            $sync[(int) $productId] = [
                'id_store' => (int) $catalogue->id_store,
                'sort_order' => $index,
                'is_featured' => false,
                'status' => 'active',
                'metadata' => null,
            ];
        }

        $catalogue->products()->sync($sync);
    }
}
