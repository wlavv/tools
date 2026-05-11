<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $manufacturers = CatalogTable::safeGet('catalog_core_manufacturers', function ($query) {
            $query->orderBy('name');
        });

        return view('catalogmanager::manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        return view('catalogmanager::manufacturers.create');
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
        ]);

        try {
            DB::table('catalog_core_manufacturers')->insert([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'website' => $data['website'] ?? null,
                'active' => $request->boolean('active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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

        return view('catalogmanager::manufacturers.edit', compact('manufacturer'));
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
        ]);

        try {
            DB::table('catalog_core_manufacturers')->where('id', $id)->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'website' => $data['website'] ?? null,
                'active' => $request->boolean('active'),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar o manufacturer.'])->withInput();
        }

        return redirect()->route('catalog-manager.manufacturers.index')->with('success', 'Manufacturer atualizado.');
    }
}
