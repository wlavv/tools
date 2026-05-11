<?php

namespace Modules\CatalogManager\Services\IssuePanels\Panels;

use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\IssuePanels\Contracts\IssuePanelInterface;
use Modules\CatalogManager\Services\IssuePanels\IssuePanelResult;
use Modules\CatalogManager\Support\CatalogTable;

class MissingStoreContentPanel extends AbstractIssuePanel implements IssuePanelInterface
{
    public function resolve(array $context = []): IssuePanelResult
    {
        if (!CatalogTable::exists('catalog_store_products') || !CatalogTable::exists('catalog_core_products') || !CatalogTable::exists('catalog_stores') || !CatalogTable::exists('catalog_store_product_lang')) {
            return new IssuePanelResult(key: 'missing_store_content', title: 'Sem Conteúdo por Loja');
        }

        $query = DB::table('catalog_store_products as sp')
            ->join('catalog_core_products as p', 'p.id', '=', 'sp.product_id')
            ->join('catalog_stores as s', 's.id', '=', 'sp.store_id')
            ->leftJoin('catalog_store_product_lang as spl', 'spl.store_product_id', '=', 'sp.id')
            ->where(function ($q) { $q->whereNull('spl.id')->orWhereNull('spl.name')->orWhere('spl.name', ''); });

        $count = (clone $query)->count();

        $items = $query->select('p.id as product_id', 'p.name', 'p.reference', 's.name as store_name', 'sp.status')
            ->orderByDesc('sp.id')
            ->limit($this->limit())
            ->get()
            ->map(fn ($row) => [
                'title' => $row->name ?: ($row->reference ?: 'Produto #' . $row->product_id),
                'subtitle' => 'Loja: ' . ($row->store_name ?: '—') . ' · Sem conteúdo',
                'badge' => $row->status ?: 'draft',
                'url' => $this->productUrl($row->product_id),
            ])
            ->toArray();

        return new IssuePanelResult(key: 'missing_store_content', title: 'Sem Conteúdo por Loja', count: $count, items: $items);
    }
}
