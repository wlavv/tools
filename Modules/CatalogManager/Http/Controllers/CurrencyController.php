<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class CurrencyController extends BaseCatalogController
{
    public function index()
    {
        $currencies = CatalogTable::safeGet('catalog_currencies', function ($query): void {
            $query->orderBy('position')->orderBy('iso_code');
        });

        return view('catalogmanager::currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('catalogmanager::currencies.create', ['currency' => null]);
    }

    public function store(Request $request)
    {
        if (!CatalogTable::exists('catalog_currencies')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_currencies ainda nao existe. Corre as migrations do modulo.'])->withInput();
        }

        $data = $this->validatedData($request);
        $data['iso_code'] = strtoupper($data['iso_code']);

        try {
            DB::table('catalog_currencies')->insert([
                'iso_code' => $data['iso_code'],
                'name' => $data['name'],
                'symbol' => $data['symbol'] ?? null,
                'conversion_rate_to_eur' => $data['conversion_rate_to_eur'] ?? 1,
                'position' => $data['position'] ?? 0,
                'active' => $request->boolean('active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return back()->withErrors(['catalog' => 'Nao foi possivel criar a moeda.'])->withInput();
        }

        return redirect()->route('catalog-manager.currencies.index')->with('success', 'Moeda criada.');
    }

    public function edit(int $id)
    {
        abort_if(!CatalogTable::exists('catalog_currencies'), 404);

        $currency = DB::table('catalog_currencies')->where('id', $id)->first();
        abort_if(!$currency, 404);

        return view('catalogmanager::currencies.edit', compact('currency'));
    }

    public function update(Request $request, int $id)
    {
        if (!CatalogTable::exists('catalog_currencies')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_currencies ainda nao existe.'])->withInput();
        }

        $data = $this->validatedData($request, $id);
        $data['iso_code'] = strtoupper($data['iso_code']);

        try {
            DB::table('catalog_currencies')->where('id', $id)->update([
                'iso_code' => $data['iso_code'],
                'name' => $data['name'],
                'symbol' => $data['symbol'] ?? null,
                'conversion_rate_to_eur' => $data['conversion_rate_to_eur'] ?? 1,
                'position' => $data['position'] ?? 0,
                'active' => $request->boolean('active'),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar a moeda.'])->withInput();
        }

        return redirect()->route('catalog-manager.currencies.index')->with('success', 'Moeda atualizada.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'iso_code' => ['required', 'string', 'size:3', 'unique:catalog_currencies,iso_code' . ($id ? ',' . $id : '')],
            'name' => ['required', 'string', 'max:120'],
            'symbol' => ['nullable', 'string', 'max:8'],
            'conversion_rate_to_eur' => ['required', 'numeric', 'min:0'],
            'position' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable'],
        ]);
    }
}
