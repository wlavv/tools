<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class ManufacturerController extends BaseCatalogController
{
    public function index(Request $request)
    {
        $manufacturers = CatalogTable::safeGet('catalog_core_manufacturers', function ($query) {
            if (CatalogTable::exists('catalog_manufacturer_stores') && CatalogTable::exists('catalog_stores')) {
                $query->select('catalog_core_manufacturers.*')
                    ->selectSub(function ($subQuery) {
                        $subQuery->from('catalog_manufacturer_stores as ms')
                            ->join('catalog_stores as s', 's.id', '=', 'ms.store_id')
                            ->whereColumn('ms.manufacturer_id', 'catalog_core_manufacturers.id')
                            ->selectRaw('GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ", ")');
                    }, 'store_names');
            }
            $query->orderBy('name');
        });

        return view('catalogmanager::manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        return view('catalogmanager::manufacturers.create', [
            'stores' => $this->stores(),
            'selectedStoreIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        if (!CatalogTable::exists('catalog_core_manufacturers')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_core_manufacturers ainda nao existe. Corre as migrations do modulo.'])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer'],
        ]);

        try {
            $manufacturerId = DB::table('catalog_core_manufacturers')->insertGetId([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'website' => $data['website'] ?? null,
                'active' => $request->boolean('active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->syncStores($manufacturerId, $data['store_ids'] ?? []);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return back()->withErrors(['catalog' => 'Nao foi possivel criar o manufacturer.'])->withInput();
        }

        return redirect()->route('catalog-manager.manufacturers.index')->with('success', 'Manufacturer criado.');
    }

    public function edit(int $id)
    {
        abort_if(!CatalogTable::exists('catalog_core_manufacturers'), 404);

        $manufacturer = DB::table('catalog_core_manufacturers')->where('id', $id)->first();
        abort_if(!$manufacturer, 404);

        return view('catalogmanager::manufacturers.edit', [
            'manufacturer' => $manufacturer,
            'stores' => $this->stores(),
            'selectedStoreIds' => $this->selectedStores($id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        if (!CatalogTable::exists('catalog_core_manufacturers')) {
            return back()->withErrors(['catalog' => 'A tabela catalog_core_manufacturers ainda nao existe.'])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer'],
        ]);

        try {
            DB::table('catalog_core_manufacturers')->where('id', $id)->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'website' => $data['website'] ?? null,
                'active' => $request->boolean('active'),
                'updated_at' => now(),
            ]);
            $this->syncStores($id, $data['store_ids'] ?? []);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar o manufacturer.'])->withInput();
        }

        return redirect()->route('catalog-manager.manufacturers.index')->with('success', 'Manufacturer atualizado.');
    }

    private function stores()
    {
        return CatalogTable::safeGet('catalog_stores', function ($query) {
            $query->where(function ($nested) {
                $nested->where('record_type', 'store')->orWhereNull('record_type');
            })->where('active', true)->orderBy('name');
        });
    }

    private function selectedStores(int $manufacturerId): array
    {
        if (!CatalogTable::exists('catalog_manufacturer_stores')) {
            return [];
        }

        return DB::table('catalog_manufacturer_stores')
            ->where('manufacturer_id', $manufacturerId)
            ->pluck('store_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function syncStores(int $manufacturerId, array $storeIds): void
    {
        if (!CatalogTable::exists('catalog_manufacturer_stores')) {
            return;
        }

        DB::table('catalog_manufacturer_stores')->where('manufacturer_id', $manufacturerId)->delete();

        $rows = collect($storeIds)->filter()->unique()->map(fn ($storeId) => [
            'manufacturer_id' => $manufacturerId,
            'store_id' => (int) $storeId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        if ($rows) {
            DB::table('catalog_manufacturer_stores')->insert($rows);
        }
    }
}
