<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class AiController extends Controller
{
    public function index()
    {
        try {
            $generations = CatalogTable::exists('catalog_ai_generations')
                ? DB::table('catalog_ai_generations')->orderByDesc('id')->limit(30)->get()
                : collect();
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            $generations = collect();
        }

        return view('catalogmanager::ai.index', compact('generations'));
    }
}
