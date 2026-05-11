<?php

namespace Modules\CatalogManager\Services\IssuePanels\Panels;

use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\IssuePanels\Contracts\IssuePanelInterface;
use Modules\CatalogManager\Services\IssuePanels\IssuePanelResult;
use Modules\CatalogManager\Support\CatalogTable;

class MissingSupplierPanel extends AbstractIssuePanel implements IssuePanelInterface
{
    public function resolve(array $context = []): IssuePanelResult
    {
        if (!CatalogTable::exists('catalog_core_products') || !CatalogTable::exists('catalog_core_product_suppliers')) {
            return new IssuePanelResult(key: 'missing_supplier', title: 'Produtos sem Fornecedor');
        }

        $query = DB::table('catalog_core_products as p')
            ->leftJoin('catalog_core_product_suppliers as ps', 'ps.product_id', '=', 'p.id')
            ->whereNull('ps.id');

        $count = (clone $query)->count();

        $items = $query->select('p.id', 'p.name', 'p.reference', 'p.status')
            ->orderByDesc('p.id')->limit($this->limit())->get()
            ->map(fn ($product) => [
                'title' => $product->name ?: ($product->reference ?: 'Produto #' . $product->id),
                'subtitle' => 'Sem fornecedor associado',
                'badge' => $product->status ?: 'draft',
                'url' => $this->productUrl($product->id),
            ])->toArray();

        return new IssuePanelResult(key: 'missing_supplier', title: 'Produtos sem Fornecedor', count: $count, items: $items);
    }
}
