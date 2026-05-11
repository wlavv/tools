<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class SyncController extends BaseCatalogController
{
    public function index(Request $request)
    {
        if (!CatalogTable::exists('catalog_prestashop_sync_queue')) {
            $queue = collect();
            $counts = collect();

            return view('catalogmanager::sync.index', compact('queue', 'counts'));
        }

        try {
            $queue = DB::table('catalog_prestashop_sync_queue')
                ->orderByDesc('id')
                ->get();

            $counts = DB::table('catalog_prestashop_sync_queue')
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            $queue = collect();
            $counts = collect();
        }

        return view('catalogmanager::sync.index', compact('queue', 'counts'));
    }
}
