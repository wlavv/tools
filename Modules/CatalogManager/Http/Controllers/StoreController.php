<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class StoreController extends BaseCatalogController
{
    public function index()
    {
        $stores = CatalogTable::safeGet('catalog_stores', function ($query) {
            $query->orderBy('name');
        });

        return view('catalogmanager::stores.index', compact('stores'));
    }

    public function create()
    {
        return view('catalogmanager::stores.create');
    }

    public function store(Request $request)
    {
        if (!CatalogTable::exists('catalog_stores')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_stores ainda nao existe. Corre as migrations do modulo.'])->withInput();
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'record_type' => ['required', 'string', 'in:store,domain'],
            'site_kind' => ['required', 'string', 'in:store,service,showcase,group,labs'],
            'locale' => ['nullable', 'string', 'max:8'],
            'currency' => ['nullable', 'string', 'max:3'],
            'active' => ['nullable'],
        ]);

        try {
            DB::table('catalog_stores')->insert([
                'code' => $data['code'],
                'name' => $data['name'],
                'domain' => $data['domain'] ?? null,
                'record_type' => $data['record_type'] ?? 'store',
                'site_kind' => $data['site_kind'] ?? (($data['record_type'] ?? 'store') === 'store' ? 'store' : 'service'),
                'locale' => $data['locale'] ?? 'pt',
                'currency' => $data['currency'] ?? 'EUR',
                'active' => $request->boolean('active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return back()->withErrors(['catalog' => 'Nao foi possivel criar a loja.'])->withInput();
        }

        return redirect()->route('catalog-manager.stores.index')->with('success', 'Loja criada.');
    }

    public function edit(int $id)
    {
        abort_if(!CatalogTable::exists('catalog_stores'), 404);

        $store = DB::table('catalog_stores')->where('id', $id)->first();
        abort_if(!$store, 404);

        return view('catalogmanager::stores.edit', compact('store'));
    }

    public function update(Request $request, int $id)
    {
        if (!CatalogTable::exists('catalog_stores')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_stores ainda nao existe.'])->withInput();
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'record_type' => ['required', 'string', 'in:store,domain'],
            'site_kind' => ['required', 'string', 'in:store,service,showcase,group,labs'],
            'locale' => ['nullable', 'string', 'max:8'],
            'currency' => ['nullable', 'string', 'max:3'],
            'active' => ['nullable'],
        ]);

        try {
            DB::table('catalog_stores')->where('id', $id)->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'domain' => $data['domain'] ?? null,
                'record_type' => $data['record_type'] ?? 'store',
                'site_kind' => $data['site_kind'] ?? (($data['record_type'] ?? 'store') === 'store' ? 'store' : 'service'),
                'locale' => $data['locale'] ?? 'pt',
                'currency' => $data['currency'] ?? 'EUR',
                'active' => $request->boolean('active'),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar a loja.'])->withInput();
        }

        return redirect()->route('catalog-manager.stores.index')->with('success', 'Loja atualizada.');
    }
}
