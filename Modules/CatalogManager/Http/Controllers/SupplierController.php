<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class SupplierController extends BaseCatalogController
{
    public function index(Request $request)
    {
        $suppliers = CatalogTable::safeGet('catalog_core_suppliers', function ($query) {
            $query->orderBy('name');
        });

        return view('catalogmanager::suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('catalogmanager::suppliers.create');
    }

    public function store(Request $request)
    {
        if (!CatalogTable::exists('catalog_core_suppliers')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_core_suppliers ainda nao existe. Corre as migrations do modulo.'])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'max:3'],
            'lead_time_days' => ['nullable', 'integer'],
            'active' => ['nullable'],
        ]);

        try {
            DB::table('catalog_core_suppliers')->insert([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'currency' => $data['currency'] ?? 'EUR',
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'active' => $request->boolean('active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return back()->withErrors(['catalog' => 'Nao foi possivel criar o fornecedor.'])->withInput();
        }

        return redirect()->route('catalog-manager.suppliers.index')->with('success', 'Fornecedor criado.');
    }

    public function edit(int $id)
    {
        abort_if(!CatalogTable::exists('catalog_core_suppliers'), 404);

        $supplier = DB::table('catalog_core_suppliers')->where('id', $id)->first();
        abort_if(!$supplier, 404);

        return view('catalogmanager::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, int $id)
    {
        if (!CatalogTable::exists('catalog_core_suppliers')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_core_suppliers ainda nao existe.'])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'max:3'],
            'lead_time_days' => ['nullable', 'integer'],
            'active' => ['nullable'],
        ]);

        try {
            DB::table('catalog_core_suppliers')->where('id', $id)->update([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'currency' => $data['currency'] ?? 'EUR',
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'active' => $request->boolean('active'),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar o fornecedor.'])->withInput();
        }

        return redirect()->route('catalog-manager.suppliers.index')->with('success', 'Fornecedor atualizado.');
    }
}
