<?php

namespace Modules\CatalogManager\Services\ActionPanels\Panels;

use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\ActionPanels\ActionPanelResult;
use Modules\CatalogManager\Services\ActionPanels\Contracts\ActionPanelInterface;
use Modules\CatalogManager\Support\CatalogTable;

class ProductsWithoutStorePanel extends AbstractActionPanel implements ActionPanelInterface
{
    public function resolve(array $context = []): ActionPanelResult
    {
        if (!CatalogTable::exists('catalog_core_products') || !CatalogTable::exists('catalog_store_products')) {
            return new ActionPanelResult(key: 'products_without_store', title: 'Produtos sem Loja');
        }

        $query = DB::table('catalog_core_products as p')
            ->leftJoin('catalog_store_products as sp', 'sp.product_id', '=', 'p.id')
            ->whereNull('sp.id');

        $count = (clone $query)->count();

        $items = $query->select('p.id', 'p.reference', 'p.name', 'p.status')
            ->orderByDesc('p.id')
            ->limit($this->limit())
            ->get()
            ->map(fn ($product) => [
                'title' => $product->name ?: ($product->reference ?: 'Produto #' . $product->id),
                'subtitle' => 'Produto master sem perfil de loja',
                'badge' => $product->status ?: 'draft',
                'url' => $this->productUrl($product->id),
            ])
            ->toArray();

        return new ActionPanelResult(key: 'products_without_store', title: 'Produtos sem Loja', count: $count, items: $items);
    }
}
