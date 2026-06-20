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
            if (CatalogTable::exists('catalog_supplier_stores') && CatalogTable::exists('catalog_stores')) {
                $query->select('catalog_core_suppliers.*')
                    ->selectSub(function ($subQuery) {
                        $subQuery->from('catalog_supplier_stores as ss')
                            ->join('catalog_stores as s', 's.id', '=', 'ss.store_id')
                            ->whereColumn('ss.supplier_id', 'catalog_core_suppliers.id')
                            ->selectRaw('GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ", ")');
                    }, 'store_names');
            }
            $query->orderBy('name');
        });

        return view('catalogmanager::suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('catalogmanager::suppliers.create', [
            'stores' => $this->stores(),
            'selectedStoreIds' => [],
            'currencyOptions' => $this->currencyOptions(),
        ]);
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
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer'],
        ]);

        try {
            $supplierPayload = [
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'currency' => strtoupper($data['currency'] ?? 'EUR'),
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'active' => $request->boolean('active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $supplierId = DB::table('catalog_core_suppliers')->insertGetId($supplierPayload);
            $this->syncStores($supplierId, $data['store_ids'] ?? []);
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

        return view('catalogmanager::suppliers.edit', [
            'supplier' => $supplier,
            'stores' => $this->stores(),
            'selectedStoreIds' => $this->selectedStores($id),
            'currencyOptions' => $this->currencyOptions(),
        ]);
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
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer'],
        ]);

        try {
            $supplierPayload = [
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'currency' => strtoupper($data['currency'] ?? 'EUR'),
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'active' => $request->boolean('active'),
                'updated_at' => now(),
            ];
            DB::table('catalog_core_suppliers')->where('id', $id)->update($supplierPayload);
            $this->syncStores($id, $data['store_ids'] ?? []);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar o fornecedor.'])->withInput();
        }

        return redirect()->route('catalog-manager.suppliers.index')->with('success', 'Fornecedor atualizado.');
    }

    private function stores()
    {
        return CatalogTable::safeGet('catalog_stores', function ($query) {
            $query->where(function ($nested) {
                $nested->where('record_type', 'store')->orWhereNull('record_type');
            })->where('active', true)->orderBy('name');
        });
    }

    private function currencyOptions(): array
    {
        if (!CatalogTable::exists('catalog_currencies')) {
            return [
                'EUR' => 'EUR - Euro',
                'USD' => 'USD - US Dollar',
                'GBP' => 'GBP - Pound Sterling',
                'CHF' => 'CHF - Swiss Franc',
                'JPY' => 'JPY - Japanese Yen',
            ];
        }

        return DB::table('catalog_currencies')
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('iso_code')
            ->get(['iso_code', 'name'])
            ->mapWithKeys(fn ($currency) => [
                (string) $currency->iso_code => trim($currency->iso_code . ' - ' . $currency->name),
            ])
            ->all();
    }

    private function selectedStores(int $supplierId): array
    {
        if (!CatalogTable::exists('catalog_supplier_stores')) {
            return [];
        }

        return DB::table('catalog_supplier_stores')
            ->where('supplier_id', $supplierId)
            ->pluck('store_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function syncStores(int $supplierId, array $storeIds): void
    {
        if (!CatalogTable::exists('catalog_supplier_stores')) {
            return;
        }

        DB::table('catalog_supplier_stores')->where('supplier_id', $supplierId)->delete();

        $rows = collect($storeIds)->filter()->unique()->map(fn ($storeId) => [
            'supplier_id' => $supplierId,
            'store_id' => (int) $storeId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        if ($rows) {
            DB::table('catalog_supplier_stores')->insert($rows);
        }
    }
}
