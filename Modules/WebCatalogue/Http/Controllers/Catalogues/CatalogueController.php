<?php

namespace Modules\WebCatalogue\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class CatalogueController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }

    protected function viewData(array $extra = []): array
    {
        return array_merge(['stores' => Store::query()->orderBy('name')->get()], $extra);
    }

    public function index(Request $request): View
    {
        $items = Catalogue::query()->with(['store','coverResource'])->withCount(['products'])->latest('id')->paginate(20);
        return $this->view('webcatalogue::catalogues.index', compact('items'));
    }

    public function create(): View
    {
        return $this->view('webcatalogue::catalogues.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.catalogues.store'), 'method' => 'POST']));
    }

    public function store(Request $request, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $data = $this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['show_prices', 'show_promotions'], 'default_status' => 'draft']);
        $item = Catalogue::create($data);
        $this->handleCoverUpload($request, $item, $resources);
        return redirect()->route('webcatalogue.catalogues.show', $item)->with('success', 'Catalogue created.');
    }

    public function show(Catalogue $catalogue): View { return $this->view('webcatalogue::catalogues.show', ['item' => $catalogue]); }

    public function edit(Catalogue $catalogue): View
    {
        return $this->view('webcatalogue::catalogues.form', $this->viewData(['item' => $catalogue, 'action' => route('webcatalogue.catalogues.update', $catalogue), 'method' => 'PUT']));
    }

    public function update(Request $request, Catalogue $catalogue, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $catalogue->update($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['show_prices', 'show_promotions'], 'default_status' => 'draft']));
        $this->handleCoverUpload($request, $catalogue, $resources);
        return redirect()->route('webcatalogue.catalogues.show', $catalogue)->with('success', 'Catalogue updated.');
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
}
