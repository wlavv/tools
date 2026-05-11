<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if (!CatalogTable::exists('catalog_core_products')) {
            $products = CatalogTable::emptyPaginator(25);

            return view('catalogmanager::products.index', compact('products'));
        }

        $query = DB::table('catalog_core_products as p');

        if (CatalogTable::exists('catalog_core_manufacturers')) {
            $query->leftJoin('catalog_core_manufacturers as m', 'm.id', '=', 'p.manufacturer_id')
                ->select('p.*', 'm.name as manufacturer_name');
        } else {
            $query->select('p.*', DB::raw('NULL as manufacturer_name'));
        }

        if ($request->get('action') === 'without_store' && CatalogTable::exists('catalog_store_products')) {
            $query->leftJoin('catalog_store_products as sp', 'sp.product_id', '=', 'p.id')
                ->whereNull('sp.id');
        }

        $products = $query->orderByDesc('p.id')->get();

        return view('catalogmanager::products.index', compact('products'));
    }

    public function create()
    {
        $manufacturers = CatalogTable::exists('catalog_core_manufacturers')
            ? DB::table('catalog_core_manufacturers')->where('active', true)->orderBy('name')->get()
            : collect();

        return view('catalogmanager::products.create', compact('manufacturers'));
    }

    public function store(Request $request)
    {
        if (!CatalogTable::exists('catalog_core_products')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_core_products ainda nao existe. Corre as migrations do modulo.'])->withInput();
        }

        $data = $this->validateProduct($request);

        try {
            $id = DB::table('catalog_core_products')->insertGetId([
                'internal_sku' => !empty($data['reference']) ? 'WT-' . Str::upper(Str::slug($data['reference'], '-')) : null,
                'reference' => $data['reference'] ?? null,
                'ean13' => $data['ean13'] ?? null,
                'name' => $data['name'],
                'manufacturer_id' => $data['manufacturer_id'] ?? null,
                'type' => $data['type'] ?? 'simple',
                'status' => $data['status'] ?? 'draft',
                'weight' => $data['weight'] ?? null,
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'depth' => $data['depth'] ?? null,
                'housing' => $data['housing'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (CatalogTable::exists('catalog_logs_activity')) {
                DB::table('catalog_logs_activity')->insert([
                    'subject_type' => 'product',
                    'subject_id' => $id,
                    'action' => 'created',
                    'new_values' => json_encode($data),
                    'user_id' => optional(auth()->user())->id,
                    'ip' => $request->ip(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return back()->withErrors(['catalog' => 'Nao foi possivel criar o produto.'])->withInput();
        }

        return redirect()->route('catalog-manager.products.show', $id)->with('success', 'Produto criado com sucesso.');
    }

    public function show(int $id)
    {
        abort_if(!CatalogTable::exists('catalog_core_products'), 404);

        $product = DB::table('catalog_core_products as p')
            ->when(CatalogTable::exists('catalog_core_manufacturers'), function ($query) {
                $query->leftJoin('catalog_core_manufacturers as m', 'm.id', '=', 'p.manufacturer_id')
                    ->select('p.*', 'm.name as manufacturer_name');
            }, function ($query) {
                $query->select('p.*', DB::raw('NULL as manufacturer_name'));
            })
            ->where('p.id', $id)
            ->first();

        abort_if(!$product, 404);

        $storeMatrix = $this->buildStoreMatrix($id);
        $storeProducts = $storeMatrix->whereNotNull('store_product_id')->values();

        $suppliers = collect();
        if (CatalogTable::exists('catalog_core_product_suppliers') && CatalogTable::exists('catalog_core_suppliers')) {
            $suppliers = DB::table('catalog_core_product_suppliers as ps')
                ->join('catalog_core_suppliers as s', 's.id', '=', 'ps.supplier_id')
                ->select('ps.*', 's.name as supplier_name')
                ->where('ps.product_id', $id)
                ->get();
        }

        $logs = CatalogTable::exists('catalog_logs_activity')
            ? DB::table('catalog_logs_activity')
                ->where('subject_type', 'product')
                ->where('subject_id', $id)
                ->orderByDesc('id')
                ->limit(20)
                ->get()
            : collect();

        $builderStats = [
            'stores_total' => $storeMatrix->count(),
            'stores_created' => $storeMatrix->whereNotNull('store_product_id')->count(),
            'stores_published' => $storeMatrix->where('is_published', true)->count(),
            'missing_content' => $storeMatrix->where('has_content', false)->count(),
            'missing_price' => $storeMatrix->where('has_price', false)->count(),
            'missing_category' => $storeMatrix->where('has_category', false)->count(),
        ];

        return view('catalogmanager::products.show', compact(
            'product',
            'storeProducts',
            'storeMatrix',
            'builderStats',
            'suppliers',
            'logs'
        ));
    }

    public function edit(int $id)
    {
        abort_if(!CatalogTable::exists('catalog_core_products'), 404);

        $product = DB::table('catalog_core_products')->where('id', $id)->first();
        abort_if(!$product, 404);

        $manufacturers = CatalogTable::exists('catalog_core_manufacturers')
            ? DB::table('catalog_core_manufacturers')->where('active', true)->orderBy('name')->get()
            : collect();

        return view('catalogmanager::products.edit', compact('product', 'manufacturers'));
    }

    public function update(Request $request, int $id)
    {
        if (!CatalogTable::exists('catalog_core_products')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_core_products ainda nao existe.'])->withInput();
        }

        $data = $this->validateProduct($request);

        try {
            DB::table('catalog_core_products')->where('id', $id)->update([
                'reference' => $data['reference'] ?? null,
                'ean13' => $data['ean13'] ?? null,
                'name' => $data['name'],
                'manufacturer_id' => $data['manufacturer_id'] ?? null,
                'type' => $data['type'] ?? 'simple',
                'status' => $data['status'] ?? 'draft',
                'weight' => $data['weight'] ?? null,
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'depth' => $data['depth'] ?? null,
                'housing' => $data['housing'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar o produto.'])->withInput();
        }

        return redirect()->route('catalog-manager.products.show', $id)->with('success', 'Produto atualizado.');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'reference' => ['nullable', 'string', 'max:255'],
            'ean13' => ['nullable', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'manufacturer_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
            'weight' => ['nullable', 'numeric'],
            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'depth' => ['nullable', 'numeric'],
            'housing' => ['nullable', 'string', 'max:120'],
            'internal_notes' => ['nullable', 'string'],
        ]);
    }

    private function buildStoreMatrix(int $productId)
    {
        if (!CatalogTable::exists('catalog_stores')) {
            return collect();
        }

        try {
            $stores = DB::table('catalog_stores')->orderBy('name')->get();
            $storeProducts = CatalogTable::exists('catalog_store_products')
                ? DB::table('catalog_store_products')->where('product_id', $productId)->get()->keyBy('store_id')
                : collect();

            $storeProductIds = $storeProducts->pluck('id')->filter()->values();
            $contentByStoreProduct = collect();
            $priceByStoreProduct = collect();
            $categoryByStoreProduct = collect();

            if ($storeProductIds->isNotEmpty() && CatalogTable::exists('catalog_store_product_lang')) {
                $contentByStoreProduct = DB::table('catalog_store_product_lang')
                    ->whereIn('store_product_id', $storeProductIds)
                    ->get()
                    ->groupBy('store_product_id');
            }

            if ($storeProductIds->isNotEmpty() && CatalogTable::exists('catalog_store_prices')) {
                $priceByStoreProduct = DB::table('catalog_store_prices')
                    ->whereIn('store_product_id', $storeProductIds)
                    ->get()
                    ->keyBy('store_product_id');
            }

            if ($storeProductIds->isNotEmpty() && CatalogTable::exists('catalog_store_product_categories')) {
                $categoryByStoreProduct = DB::table('catalog_store_product_categories')
                    ->whereIn('store_product_id', $storeProductIds)
                    ->get()
                    ->groupBy('store_product_id');
            }

            return $stores->map(function ($store) use ($storeProducts, $contentByStoreProduct, $priceByStoreProduct, $categoryByStoreProduct) {
                $storeProduct = $storeProducts->get($store->id);
                $storeProductId = $storeProduct->id ?? null;
                $contentRows = $storeProductId ? $contentByStoreProduct->get($storeProductId, collect()) : collect();
                $price = $storeProductId ? $priceByStoreProduct->get($storeProductId) : null;
                $categoryRows = $storeProductId ? $categoryByStoreProduct->get($storeProductId, collect()) : collect();
                $hasContent = $contentRows->contains(function ($row) {
                    return !empty($row->name) || !empty($row->description_short) || !empty($row->description);
                });

                return (object) [
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'store_code' => $store->code,
                    'store_locale' => $store->locale,
                    'store_currency' => $store->currency,
                    'store_product_id' => $storeProductId,
                    'status' => $storeProduct->status ?? 'not_created',
                    'active' => (bool) ($storeProduct->active ?? false),
                    'visible' => (bool) ($storeProduct->visible ?? false),
                    'available_for_order' => (bool) ($storeProduct->available_for_order ?? false),
                    'is_published' => (bool) ($storeProduct->is_published ?? false),
                    'published_at' => $storeProduct->published_at ?? null,
                    'price' => $price->price ?? null,
                    'sale_price' => $price->sale_price ?? null,
                    'price_status' => $price->status ?? null,
                    'has_content' => $hasContent,
                    'content_count' => $contentRows->count(),
                    'has_price' => $price !== null && $price->price !== null,
                    'has_category' => $categoryRows->isNotEmpty(),
                    'category_count' => $categoryRows->count(),
                ];
            });
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'product_id' => $productId]);

            return collect();
        }
    }
}
