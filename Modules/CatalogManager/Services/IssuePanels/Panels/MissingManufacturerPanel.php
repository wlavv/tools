<?php

namespace Modules\CatalogManager\Services\IssuePanels\Panels;

use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\IssuePanels\Contracts\IssuePanelInterface;
use Modules\CatalogManager\Services\IssuePanels\IssuePanelResult;
use Modules\CatalogManager\Support\CatalogTable;

class MissingManufacturerPanel extends AbstractIssuePanel implements IssuePanelInterface
{
    public function resolve(array $context = []): IssuePanelResult
    {
        if (!CatalogTable::exists('catalog_core_products')) {
            return new IssuePanelResult(key: 'missing_manufacturer', title: 'Produtos sem Marca');
        }

        $query = DB::table('catalog_core_products as p')->whereNull('p.manufacturer_id');
        $count = (clone $query)->count();

        $items = $query->select('p.id', 'p.name', 'p.reference', 'p.status')
            ->orderByDesc('p.id')->limit($this->limit())->get()
            ->map(fn ($product) => [
                'title' => $product->name ?: ($product->reference ?: 'Produto #' . $product->id),
                'subtitle' => 'Sem manufacturer/marca',
                'badge' => $product->status ?: 'draft',
                'url' => $this->productUrl($product->id),
            ])->toArray();

        return new IssuePanelResult(key: 'missing_manufacturer', title: 'Produtos sem Marca', count: $count, items: $items);
    }
}
