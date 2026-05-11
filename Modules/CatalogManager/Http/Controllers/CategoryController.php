<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if (!CatalogTable::exists('catalog_store_categories') || !CatalogTable::exists('catalog_stores')) {
            $categories = CatalogTable::emptyCollection();

            return view('catalogmanager::categories.index', compact('categories'));
        }

        $query = DB::table('catalog_store_categories as c')
            ->join('catalog_stores as s', 's.id', '=', 'c.store_id')
            ->select('c.*', 's.name as store_name', 'cl.name as category_name', 'cl.link_rewrite');

        if (CatalogTable::exists('catalog_store_category_lang')) {
            $query->leftJoin('catalog_store_category_lang as cl', 'cl.store_category_id', '=', 'c.id');
        } else {
            $query->select('c.*', 's.name as store_name', DB::raw('NULL as category_name'), DB::raw('NULL as link_rewrite'));
        }

        $categories = $query->orderBy('s.name')->orderBy('c.parent_id')->orderBy('c.position')->get();

        return view('catalogmanager::categories.index', compact('categories'));
    }

    public function create()
    {
        $stores = CatalogTable::safeGet('catalog_stores', function ($query) {
            $query->orderBy('name');
        });
        $parents = $this->parentOptions();

        return view('catalogmanager::categories.create', compact('stores', 'parents'));
    }

    public function store(Request $request)
    {
        if (!CatalogTable::exists('catalog_store_categories') || !CatalogTable::exists('catalog_store_category_lang')) {
            return back()->withErrors(['catalog' => 'As tabelas de categorias ainda nao existem. Corre as migrations do modulo.'])->withInput();
        }

        $data = $this->validateCategory($request);

        try {
            $categoryId = DB::table('catalog_store_categories')->insertGetId([
                'store_id' => $data['store_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'code' => $data['code'] ?? null,
                'active' => $request->boolean('active', true),
                'position' => $data['position'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('catalog_store_category_lang')->insert([
                'store_category_id' => $categoryId,
                'locale' => $data['locale'] ?? 'pt',
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'link_rewrite' => $data['link_rewrite'] ?? Str::slug($data['name']),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return back()->withErrors(['catalog' => 'Nao foi possivel criar a categoria.'])->withInput();
        }

        return redirect()->route('catalog-manager.categories.index')->with('success', 'Categoria criada.');
    }

    public function edit(int $id)
    {
        abort_if(!CatalogTable::exists('catalog_store_categories'), 404);

        $category = DB::table('catalog_store_categories as c')
            ->select('c.*', 'cl.locale', 'cl.name', 'cl.description', 'cl.link_rewrite', 'cl.meta_title', 'cl.meta_description')
            ->where('c.id', $id)
            ->when(CatalogTable::exists('catalog_store_category_lang'), function ($query) {
                $query->leftJoin('catalog_store_category_lang as cl', 'cl.store_category_id', '=', 'c.id');
            }, function ($query) {
                $query->select(
                    'c.*',
                    DB::raw('NULL as locale'),
                    DB::raw('NULL as name'),
                    DB::raw('NULL as description'),
                    DB::raw('NULL as link_rewrite'),
                    DB::raw('NULL as meta_title'),
                    DB::raw('NULL as meta_description')
                );
            })
            ->first();

        abort_if(!$category, 404);

        $stores = CatalogTable::safeGet('catalog_stores', function ($query) {
            $query->orderBy('name');
        });
        $parents = $this->parentOptions($id);

        return view('catalogmanager::categories.edit', compact('category', 'stores', 'parents'));
    }

    public function update(Request $request, int $id)
    {
        if (!CatalogTable::exists('catalog_store_categories') || !CatalogTable::exists('catalog_store_category_lang')) {
            return back()->withErrors(['catalog' => 'As tabelas de categorias ainda nao existem.'])->withInput();
        }

        $data = $this->validateCategory($request);

        try {
            DB::table('catalog_store_categories')->where('id', $id)->update([
                'store_id' => $data['store_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'code' => $data['code'] ?? null,
                'active' => $request->boolean('active'),
                'position' => $data['position'] ?? 0,
                'updated_at' => now(),
            ]);

            DB::table('catalog_store_category_lang')->updateOrInsert(
                ['store_category_id' => $id, 'locale' => $data['locale'] ?? 'pt'],
                [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'link_rewrite' => $data['link_rewrite'] ?? Str::slug($data['name']),
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__, 'id' => $id]);

            return back()->withErrors(['catalog' => 'Nao foi possivel atualizar a categoria.'])->withInput();
        }

        return redirect()->route('catalog-manager.categories.index')->with('success', 'Categoria atualizada.');
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'store_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'code' => ['nullable', 'string', 'max:120'],
            'active' => ['nullable'],
            'position' => ['nullable', 'integer'],
            'locale' => ['nullable', 'string', 'max:8'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link_rewrite' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);
    }

    private function parentOptions(?int $exceptId = null)
    {
        if (!CatalogTable::exists('catalog_store_categories') || !CatalogTable::exists('catalog_stores')) {
            return CatalogTable::emptyCollection();
        }

        $query = DB::table('catalog_store_categories as c')
            ->join('catalog_stores as s', 's.id', '=', 'c.store_id')
            ->select('c.id', 's.name as store_name', DB::raw('NULL as category_name'));

        if (CatalogTable::exists('catalog_store_category_lang')) {
            $query->leftJoin('catalog_store_category_lang as cl', 'cl.store_category_id', '=', 'c.id')
                ->select('c.id', 's.name as store_name', 'cl.name as category_name');
        }

        if ($exceptId) {
            $query->where('c.id', '!=', $exceptId);
        }

        return $query->orderBy('s.name')->orderBy('category_name')->get();
    }
}
