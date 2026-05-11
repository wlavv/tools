<?php

namespace Modules\CatalogManager\Services\ActionPanels\Panels;

use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\ActionPanels\ActionPanelResult;
use Modules\CatalogManager\Services\ActionPanels\Contracts\ActionPanelInterface;
use Modules\CatalogManager\Support\CatalogTable;

class SyncStatusPanel extends AbstractActionPanel implements ActionPanelInterface
{
    public function resolve(array $context = []): ActionPanelResult
    {
        $status = $context['panel_config']['status'] ?? 'pending';

        if (!CatalogTable::exists('catalog_prestashop_sync_queue')) {
            return new ActionPanelResult(key: 'sync_' . $status, title: 'Sync ' . ucfirst($status));
        }

        $query = DB::table('catalog_prestashop_sync_queue')->where('status', $status);
        $count = (clone $query)->count();

        $items = $query->select('id', 'entity_type', 'entity_id', 'operation', 'last_error')
            ->orderByDesc('id')
            ->limit($this->limit())
            ->get()
            ->map(fn ($row) => [
                'title' => ucfirst($row->entity_type ?: 'Entidade') . ' #' . ($row->entity_id ?: $row->id),
                'subtitle' => $status === 'failed'
                    ? ($row->last_error ?: 'Falha de sincronização')
                    : ('Operação: ' . ($row->operation ?: 'sync')),
                'badge' => $status,
                'url' => $this->syncUrl(['status' => $status]),
            ])
            ->toArray();

        return new ActionPanelResult(key: 'sync_' . $status, title: 'Sync ' . ucfirst($status), count: $count, items: $items);
    }
}
