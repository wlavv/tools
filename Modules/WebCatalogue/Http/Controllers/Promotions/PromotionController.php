<?php

namespace Modules\WebCatalogue\Http\Controllers\Promotions;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Promotion;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class PromotionController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }
    protected function viewData(array $extra = []): array
    {
        $storeId = request()->integer('id_store') ?: null;
        return array_merge([
            'stores' => Store::query()->orderBy('name')->get(),
            'catalogues' => Catalogue::query()->when($storeId, fn ($query) => $query->where('id_store', $storeId))->orderBy('name')->get(),
        ], $extra);
    }
    public function index(Request $request): View
    {
        $storeId = $request->integer('id_store') ?: null;
        $store = $storeId ? Store::find($storeId) : null;
        $items = Promotion::query()
            ->with('store')
            ->withCount('products')
            ->when($storeId, fn ($query) => $query->where('id_store', $storeId))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        if ($store) {
            $this->replaceAction('back', ['label' => 'Store hub', 'name' => 'Store hub', 'icon' => 'fa-solid fa-store', 'url' => route('webcatalogue.stores.show', $store), 'route' => 'webcatalogue.stores.show', 'type' => 'link']);
            $this->replaceAction('new', ['label' => 'New promotion', 'name' => 'New promotion', 'icon' => 'fa-solid fa-plus', 'class' => 'lsg-action-btn lsg-action-btn--success', 'url' => route('webcatalogue.promotions.create', ['id_store' => $store->id]), 'route' => 'webcatalogue.promotions.create', 'type' => 'link']);
        }

        return $this->view('webcatalogue::promotions.index', compact('items', 'store'));
    }
    public function create(): View { return $this->view('webcatalogue::promotions.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.promotions.store'), 'method' => 'POST'])); }
    public function store(Request $request): RedirectResponse
    {
        $item = Promotion::create($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'default_status' => 'draft']));
        return redirect()->route('webcatalogue.promotions.show', $item)->with('success', 'Promotion created.');
    }
    public function show(Promotion $promotion): View { return $this->view('webcatalogue::promotions.show', ['item' => $promotion]); }
    public function edit(Promotion $promotion): View { return $this->view('webcatalogue::promotions.form', $this->viewData(['item' => $promotion, 'action' => route('webcatalogue.promotions.update', $promotion), 'method' => 'PUT'])); }
    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'default_status' => 'draft']));
        return redirect()->route('webcatalogue.promotions.show', $promotion)->with('success', 'Promotion updated.');
    }
    public function destroy(Promotion $promotion): RedirectResponse { $promotion->delete(); return redirect()->route('webcatalogue.promotions.index')->with('success', 'Promotion deleted.'); }
}
