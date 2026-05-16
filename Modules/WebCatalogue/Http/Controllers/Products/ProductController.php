<?php

namespace Modules\WebCatalogue\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductPrice;
use Modules\WebCatalogue\Models\Promotion;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Services\Storage\WebCatalogueStorageService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class ProductController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }

    protected function viewData(array $extra = []): array
    {
        $item = $extra['item'] ?? null;
        $storeId = (int) old('id_store', $item->id_store ?? request('id_store', 0));

        return array_merge([
            'stores' => Store::query()->orderBy('name')->get(),
            'promotions' => Promotion::query()->orderBy('name')->get(),
            'catalogues' => Catalogue::query()
                ->when($storeId > 0, fn ($query) => $query->where('id_store', $storeId))
                ->orderBy('name')
                ->limit(1000)
                ->get(),
        ], $extra);
    }

    public function index(Request $request): View
    {
        $storeId = $request->integer('id_store') ?: null;
        $store = $storeId ? Store::find($storeId) : null;
        $search = trim((string) $request->input('q', ''));
        $items = Product::query()
            ->with(['store','mainImageResource','prices','resources','catalogues'])
            ->withCount(['resources','catalogues'])
            ->when($storeId, fn ($query) => $query->where('id_store', $storeId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('reference', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%')
                        ->orWhere('ean13', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
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
                'label' => 'New product',
                'name' => 'New product',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
                'url' => route('webcatalogue.products.create', array_merge(['id_store' => $store->id], $returnQuery)),
                'route' => 'webcatalogue.products.create',
                'type' => 'link',
            ]);
        }

        return $this->view('webcatalogue::products.index', compact('items', 'store'));
    }

    public function create(): View
    {
        return $this->view('webcatalogue::products.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.products.store'), 'method' => 'POST']));
    }

    public function store(Request $request, WebCatalogueStorageService $storage, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $data = $this->cleanProductData($request);
        $item = Product::create($data);
        $storage->ensureProductStructure((int) $item->id_store, (int) $item->id);
        $this->handleProductUploads($request, $item, $resources);
        $this->syncProductCommercials($request, $item);
        $this->syncProductCatalogues($request, $item);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.products.show', $item))->with('success', 'Product created.');
    }

    public function show(Product $product): View { return $this->view('webcatalogue::products.show', ['item' => $product->load(['resources','prices','promotions','store','catalogues','mainImageResource'])]); }

    public function edit(Product $product): View
    {
        return $this->view('webcatalogue::products.form', $this->viewData(['item' => $product->load(['prices','promotions','resources','catalogues']), 'action' => route('webcatalogue.products.update', $product), 'method' => 'PUT']));
    }

    public function update(Request $request, Product $product, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $product->update($this->cleanProductData($request));
        $this->handleProductUploads($request, $product, $resources);
        $this->syncProductCommercials($request, $product);
        $this->syncProductCatalogues($request, $product);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.products.show', $product))->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('webcatalogue.products.index')->with('success', 'Product deleted.');
    }

    protected function cleanProductData(Request $request): array
    {
        $data = $this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'default_status' => 'draft']);

        foreach ([
            'main_image','gallery_images','model_3d_file','ar_file','vr_file','manual_file','audio_file','video_file',
            'price_rule','promotion_rule','promotion_ids','catalogue_ids'
        ] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    protected function syncProductCommercials(Request $request, Product $product): void
    {
        $price = (array) $request->input('price_rule', []);
        if (!empty($price['enabled'])) {
            ProductPrice::updateOrCreate(
                [
                    'id_store' => (int) $product->id_store,
                    'id_product' => (int) $product->id,
                    'price_type' => $price['price_type'] ?? 'standard',
                    'currency' => strtoupper($price['currency'] ?? $product->currency ?? 'EUR'),
                ],
                [
                    'regular_price' => ($price['regular_price'] ?? '') !== '' ? $price['regular_price'] : null,
                    'sale_price' => ($price['sale_price'] ?? '') !== '' ? $price['sale_price'] : null,
                    'tax_included' => !empty($price['tax_included']),
                    'tax_rate' => ($price['tax_rate'] ?? '') !== '' ? $price['tax_rate'] : null,
                    'valid_from' => $price['valid_from'] ?? null,
                    'valid_until' => $price['valid_until'] ?? null,
                    'status' => $price['status'] ?? 'active',
                    'metadata' => null,
                ]
            );
        }

        $promotionIds = array_filter((array) $request->input('promotion_ids', []));
        if (!empty($promotionIds)) {
            $sync = [];
            foreach ($promotionIds as $promotionId) {
                $sync[(int) $promotionId] = [
                    'id_store' => (int) $product->id_store,
                    'status' => 'active',
                    'sort_order' => 0,
                    'metadata' => null,
                ];
            }
            $product->promotions()->syncWithoutDetaching($sync);
        }

        $promo = (array) $request->input('promotion_rule', []);
        if (!empty($promo['enabled']) && !empty($promo['name'])) {
            $promotion = Promotion::updateOrCreate(
                [
                    'id_store' => (int) $product->id_store,
                    'slug' => \Illuminate\Support\Str::slug(($promo['slug'] ?? '') !== '' ? $promo['slug'] : $promo['name']),
                ],
                [
                    'id_catalogue' => $promo['id_catalogue'] ?? null,
                    'name' => $promo['name'],
                    'description' => $promo['description'] ?? null,
                    'promotion_type' => $promo['promotion_type'] ?? 'campaign',
                    'badge_label' => $promo['badge_label'] ?? null,
                    'discount_type' => $promo['discount_type'] ?? null,
                    'discount_value' => ($promo['discount_value'] ?? '') !== '' ? $promo['discount_value'] : null,
                    'starts_at' => $promo['starts_at'] ?? null,
                    'ends_at' => $promo['ends_at'] ?? null,
                    'status' => $promo['status'] ?? 'draft',
                ]
            );
            $product->promotions()->syncWithoutDetaching([
                $promotion->id => [
                    'id_store' => (int) $product->id_store,
                    'custom_badge_label' => $promo['custom_badge_label'] ?? null,
                    'custom_sale_price' => ($promo['custom_sale_price'] ?? '') !== '' ? $promo['custom_sale_price'] : null,
                    'sort_order' => 0,
                    'status' => 'active',
                    'metadata' => null,
                ],
            ]);
        }
    }

    protected function syncProductCatalogues(Request $request, Product $product): void
    {
        $catalogueIds = Catalogue::query()
            ->where('id_store', $product->id_store)
            ->whereIn('id', array_filter((array) $request->input('catalogue_ids', [])))
            ->pluck('id')
            ->values();

        $sync = [];
        foreach ($catalogueIds as $index => $catalogueId) {
            $sync[(int) $catalogueId] = [
                'id_store' => (int) $product->id_store,
                'sort_order' => $index,
                'is_featured' => false,
                'status' => 'active',
                'metadata' => null,
            ];
        }

        $product->catalogues()->sync($sync);
    }

    protected function handleProductUploads(Request $request, Product $product, WebCatalogueResourceUploadService $resources): void
    {
        $base = [
            'id_store' => (int) $product->id_store,
            'id_product' => (int) $product->id,
            'resource_owner_type' => 'product',
            'resource_owner_id' => (int) $product->id,
            'status' => 'active',
        ];

        $single = [
            'main_image' => ['image', true, 'Main image'],
            'model_3d_file' => ['model_3d', true, '3D model'],
            'ar_file' => ['ar_file', true, 'AR file'],
            'vr_file' => ['vr_file', true, 'VR file'],
            'manual_file' => ['manual', true, 'Manual / datasheet'],
            'audio_file' => ['audio', true, 'Audio'],
            'video_file' => ['video', true, 'Video'],
        ];

        foreach ($single as $field => [$type, $isMain, $title]) {
            if ($request->hasFile($field) && $request->file($field)->isValid()) {
                $resources->storeUploadedResource($request->file($field), array_merge($base, [
                    'resource_type' => $type,
                    'title' => $product->reference . ' · ' . $title,
                    'is_main' => $isMain,
                ]));
            }
        }

        if ($request->hasFile('gallery_images')) {
            foreach ((array) $request->file('gallery_images') as $index => $file) {
                if ($file && $file->isValid()) {
                    $resources->storeUploadedResource($file, array_merge($base, [
                        'resource_type' => 'gallery_image',
                        'title' => $product->reference . ' · Gallery image ' . ($index + 1),
                        'is_main' => false,
                        'sort_order' => $index + 1,
                    ]));
                }
            }
        }
    }
}
