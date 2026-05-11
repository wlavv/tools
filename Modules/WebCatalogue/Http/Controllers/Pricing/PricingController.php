<?php

namespace Modules\WebCatalogue\Http\Controllers\Pricing;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductPrice;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class PricingController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }
    protected function viewData(array $extra = []): array { return array_merge(['stores' => Store::query()->orderBy('name')->get(), 'products' => Product::query()->orderBy('reference')->get()], $extra); }
    public function index(): View
    {
        $items = ProductPrice::query()->with(['product.store'])->latest('id')->get();
        return $this->view('webcatalogue::pricing.index', compact('items'));
    }
    public function create(): View { return $this->view('webcatalogue::pricing.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.pricing.store'), 'method' => 'POST'])); }
    public function store(Request $request): RedirectResponse
    {
        $item = ProductPrice::create($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['tax_included'], 'default_status' => 'active']));
        return redirect()->route('webcatalogue.pricing.show', $item)->with('success', 'Price created.');
    }
    public function show(ProductPrice $pricing): View { return $this->view('webcatalogue::pricing.show', ['item' => $pricing]); }
    public function edit(ProductPrice $pricing): View { return $this->view('webcatalogue::pricing.form', $this->viewData(['item' => $pricing, 'action' => route('webcatalogue.pricing.update', $pricing), 'method' => 'PUT'])); }
    public function update(Request $request, ProductPrice $pricing): RedirectResponse
    {
        $pricing->update($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['tax_included'], 'default_status' => 'active']));
        return redirect()->route('webcatalogue.pricing.show', $pricing)->with('success', 'Price updated.');
    }
    public function destroy(ProductPrice $pricing): RedirectResponse { $pricing->delete(); return redirect()->route('webcatalogue.pricing.index')->with('success', 'Price deleted.'); }
}
