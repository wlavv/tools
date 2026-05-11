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
        return array_merge(['stores' => Store::query()->orderBy('name')->get(), 'catalogues' => Catalogue::query()->orderBy('name')->get()], $extra);
    }
    public function index(Request $request): View
    {
        $items = Promotion::query()->withCount('products')->latest('id')->get();
        return $this->view('webcatalogue::promotions.index', compact('items'));
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
