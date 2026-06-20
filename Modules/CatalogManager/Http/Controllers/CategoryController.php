<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class CategoryController extends BaseCatalogController
{
    public function index(Request $request)
    {
        if (!CatalogTable::exists('catalog_store_categories') || !CatalogTable::exists('catalog_stores')) {
            $categories = CatalogTable::emptyCollection();

            return view('catalogmanager::categories.index', compact('categories'));
        }

        $query = DB::table('catalog_store_categories as c')
            ->join('catalog_stores as s', 's.id', '=', 'c.store_id')
            ->where(function ($query) {
                $query->where('s.record_type', 'store')->orWhereNull('s.record_type');
            })
            ->select('c.*', 's.name as store_name', 'cl.name as name', 'cl.name as category_name', 'cl.link_rewrite');

        if (CatalogTable::exists('catalog_store_category_lang')) {
            $query->leftJoin('catalog_store_category_lang as cl', 'cl.store_category_id', '=', 'c.id');
        } else {
            $query->select('c.*', 's.name as store_name', DB::raw('NULL as name'), DB::raw('NULL as category_name'), DB::raw('NULL as link_rewrite'));
        }

        $categories = $query->orderBy('s.name')->orderBy('c.parent_id')->orderBy('c.position')->get();

        return view('catalogmanager::categories.index', compact('categories'));
    }

    public function create()
    {
        $stores = CatalogTable::safeGet('catalog_stores', function ($query) {
            $query->where(function ($nested) {
                $nested->where('record_type', 'store')->orWhereNull('record_type');
            });
            $query->orderBy('name');
        });
        $parents = $this->parentOptions();
        $characteristics = $this->characteristicOptions();
        $combinationAttributes = $this->combinationAttributeOptions();
        $selectedCategoryCharacteristics = [];
        $selectedCategoryCombinationAttributes = [];

        return view('catalogmanager::categories.create', compact('stores', 'parents', 'characteristics', 'combinationAttributes', 'selectedCategoryCharacteristics', 'selectedCategoryCombinationAttributes'));
    }

    public function store(Request $request)
    {
        if (!CatalogTable::exists('catalog_store_categories') || !CatalogTable::exists('catalog_store_category_lang')) {
            return back()->withErrors(['catalog' => 'As tabelas de categorias ainda nao existem. Corre as migrations do modulo.'])->withInput();
        }

        $data = $this->validateCategory($request);

        try {
            foreach ($this->targetStoreIds($data) as $storeId) {
                $categoryId = DB::table('catalog_store_categories')->insertGetId([
                    'store_id' => $storeId,
                    'parent_id' => $this->parentIdForStore($data['parent_id'] ?? null, $storeId),
                    'code' => $data['code'] ?? null,
                    'active' => $request->boolean('active', true),
                    'position' => $data['position'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('catalog_store_category_lang')->insert(
                    $this->categoryLangPayload($data, ['store_category_id' => $categoryId])
                );

                $this->syncCategoryCharacteristics(
                    $categoryId,
                    $data['characteristic_ids'] ?? [],
                    $data['required_characteristic_ids'] ?? [],
                    $data['characteristic_allowed_values'] ?? []
                );
                $this->syncCategoryCombinationAttributes(
                    $categoryId,
                    $data['combination_attribute_ids'] ?? [],
                    $data['required_combination_attribute_ids'] ?? [],
                    $data['combination_allowed_values'] ?? []
                );
            }
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
            ->select($this->categoryEditSelectColumns())
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
                    DB::raw('NULL as meta_description'),
                    DB::raw('NULL as ai_prompt')
                );
            })
            ->first();

        abort_if(!$category, 404);

        $stores = CatalogTable::safeGet('catalog_stores', function ($query) {
            $query->where(function ($nested) {
                $nested->where('record_type', 'store')->orWhereNull('record_type');
            });
            $query->orderBy('name');
        });
        $parents = $this->parentOptions($id);
        $characteristics = $this->characteristicOptions();
        $combinationAttributes = $this->combinationAttributeOptions();
        $selectedCategoryCharacteristics = $this->selectedCategoryCharacteristics($id);
        $selectedCategoryCombinationAttributes = $this->selectedCategoryCombinationAttributes($id);

        return view('catalogmanager::categories.edit', compact('category', 'stores', 'parents', 'characteristics', 'combinationAttributes', 'selectedCategoryCharacteristics', 'selectedCategoryCombinationAttributes'));
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
                $this->categoryLangPayload($data)
            );

            $this->syncCategoryCharacteristics(
                $id,
                $data['characteristic_ids'] ?? [],
                $data['required_characteristic_ids'] ?? [],
                $data['characteristic_allowed_values'] ?? []
            );
            $this->syncCategoryCombinationAttributes(
                $id,
                $data['combination_attribute_ids'] ?? [],
                $data['required_combination_attribute_ids'] ?? [],
                $data['combination_allowed_values'] ?? []
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
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer'],
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
            'ai_prompt' => ['nullable', 'string', 'max:6000'],
            'characteristic_ids' => ['nullable', 'array'],
            'characteristic_ids.*' => ['integer'],
            'required_characteristic_ids' => ['nullable', 'array'],
            'required_characteristic_ids.*' => ['integer'],
            'characteristic_allowed_values' => ['nullable', 'array'],
            'characteristic_allowed_values.*' => ['nullable', 'array'],
            'characteristic_allowed_values.*.*' => ['nullable', 'string', 'max:180'],
            'combination_attribute_ids' => ['nullable', 'array'],
            'combination_attribute_ids.*' => ['integer'],
            'required_combination_attribute_ids' => ['nullable', 'array'],
            'required_combination_attribute_ids.*' => ['integer'],
            'combination_allowed_values' => ['nullable', 'array'],
            'combination_allowed_values.*' => ['nullable', 'array'],
            'combination_allowed_values.*.*' => ['nullable', 'string', 'max:180'],
        ]);
    }

    private function categoryLangPayload(array $data, array $extra = []): array
    {
        $payload = array_merge($extra, [
            'locale' => $data['locale'] ?? 'pt',
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'link_rewrite' => $data['link_rewrite'] ?? Str::slug($data['name']),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($this->categoryLangHasAiPrompt()) {
            $payload['ai_prompt'] = $data['ai_prompt'] ?? null;
        }

        return $payload;
    }

    private function categoryLangHasAiPrompt(): bool
    {
        return Schema::hasTable('catalog_store_category_lang') && Schema::hasColumn('catalog_store_category_lang', 'ai_prompt');
    }

    private function categoryEditSelectColumns(): array
    {
        $columns = ['c.*', 'cl.locale', 'cl.name', 'cl.description', 'cl.link_rewrite', 'cl.meta_title', 'cl.meta_description'];
        $columns[] = $this->categoryLangHasAiPrompt() ? 'cl.ai_prompt' : DB::raw('NULL as ai_prompt');

        return $columns;
    }

    private function targetStoreIds(array $data): array
    {
        $storeIds = collect($data['store_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        return $storeIds ?: [(int) $data['store_id']];
    }

    private function parentIdForStore(?int $parentId, int $storeId): ?int
    {
        if (!$parentId) {
            return null;
        }

        $parent = DB::table('catalog_store_categories')->where('id', $parentId)->first();

        return $parent && (int) $parent->store_id === $storeId ? $parentId : null;
    }

    private function parentOptions(?int $exceptId = null)
    {
        if (!CatalogTable::exists('catalog_store_categories') || !CatalogTable::exists('catalog_stores')) {
            return CatalogTable::emptyCollection();
        }

        $query = DB::table('catalog_store_categories as c')
            ->join('catalog_stores as s', 's.id', '=', 'c.store_id')
            ->where(function ($query) {
                $query->where('s.record_type', 'store')->orWhereNull('s.record_type');
            })
            ->select('c.id', 's.name as store_name', DB::raw('NULL as name'), DB::raw('NULL as category_name'));

        if (CatalogTable::exists('catalog_store_category_lang')) {
            $query->leftJoin('catalog_store_category_lang as cl', 'cl.store_category_id', '=', 'c.id')
                ->select('c.id', 's.name as store_name', 'cl.name as name', 'cl.name as category_name');
        }

        if ($exceptId) {
            $query->where('c.id', '!=', $exceptId);
        }

        return $query->orderBy('s.name')->orderBy('category_name')->get();
    }

    private function characteristicTablesReady(): bool
    {
        return Schema::hasTable('lsg_catalog_core_characteristics')
            && Schema::hasTable('lsg_catalog_category_characteristics');
    }

    private function characteristicOptions()
    {
        if (!$this->characteristicTablesReady()) {
            return collect();
        }

        return DB::table('lsg_catalog_core_characteristics')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'unit', 'data_type'])
            ->map(function ($characteristic) {
                $characteristic->values = $this->characteristicValues((int) $characteristic->id);

                return $characteristic;
            });
    }

    private function selectedCategoryCharacteristics(int $categoryId): array
    {
        if (!$this->characteristicTablesReady()) {
            return [];
        }

        return DB::table('lsg_catalog_category_characteristics')
            ->where('store_category_id', $categoryId)
            ->get(['characteristic_id', 'is_required', 'allowed_values'])
            ->mapWithKeys(fn ($row) => [
                (int) $row->characteristic_id => [
                    'is_required' => (bool) $row->is_required,
                    'allowed_values' => $this->decodeAllowedValues($row->allowed_values ?? null),
                ],
            ])
            ->all();
    }

    private function syncCategoryCharacteristics(int $categoryId, array $characteristicIds, array $requiredCharacteristicIds, array $allowedValuesByCharacteristic): void
    {
        if (!$this->characteristicTablesReady()) {
            return;
        }

        $validIds = DB::table('lsg_catalog_core_characteristics')
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedIds = collect($characteristicIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $validIds, true))
            ->unique()
            ->values();

        $requiredIds = collect($requiredCharacteristicIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        DB::table('lsg_catalog_category_characteristics')
            ->where('store_category_id', $categoryId)
            ->delete();

        $now = now();
        $rows = $selectedIds
            ->map(fn (int $characteristicId, int $index) => [
                'store_category_id' => $categoryId,
                'characteristic_id' => $characteristicId,
                'is_required' => in_array($characteristicId, $requiredIds, true),
                'allowed_values' => $this->allowedValuesPayload($allowedValuesByCharacteristic[$characteristicId] ?? []),
                'position' => $index + 1,
                'section' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows) {
            DB::table('lsg_catalog_category_characteristics')->insert($rows);
        }
    }

    private function combinationAttributeTablesReady(): bool
    {
        return Schema::hasTable('catalog_combination_attributes')
            && Schema::hasTable('catalog_category_combination_attributes');
    }

    private function combinationAttributeOptions()
    {
        if (!$this->combinationAttributeTablesReady()) {
            return collect();
        }

        return DB::table('catalog_combination_attributes')
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'display_type'])
            ->map(function ($attribute) {
                $attribute->values = $this->combinationAttributeValues((int) $attribute->id);

                return $attribute;
            });
    }

    private function selectedCategoryCombinationAttributes(int $categoryId): array
    {
        if (!$this->combinationAttributeTablesReady()) {
            return [];
        }

        return DB::table('catalog_category_combination_attributes')
            ->where('store_category_id', $categoryId)
            ->get(['attribute_id', 'is_required', 'allowed_values'])
            ->mapWithKeys(fn ($row) => [
                (int) $row->attribute_id => [
                    'is_required' => (bool) $row->is_required,
                    'allowed_values' => $this->decodeAllowedValues($row->allowed_values ?? null),
                ],
            ])
            ->all();
    }

    private function syncCategoryCombinationAttributes(int $categoryId, array $attributeIds, array $requiredAttributeIds, array $allowedValuesByAttribute): void
    {
        if (!$this->combinationAttributeTablesReady()) {
            return;
        }

        $validIds = DB::table('catalog_combination_attributes')
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedIds = collect($attributeIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $validIds, true))
            ->unique()
            ->values();

        $requiredIds = collect($requiredAttributeIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        DB::table('catalog_category_combination_attributes')
            ->where('store_category_id', $categoryId)
            ->delete();

        $now = now();
        $rows = $selectedIds
            ->map(fn (int $attributeId, int $index) => [
                'store_category_id' => $categoryId,
                'attribute_id' => $attributeId,
                'is_required' => in_array($attributeId, $requiredIds, true),
                'allowed_values' => $this->allowedValuesPayload($allowedValuesByAttribute[$attributeId] ?? []),
                'position' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows) {
            DB::table('catalog_category_combination_attributes')->insert($rows);
        }
    }

    private function characteristicValues(int $characteristicId): array
    {
        if (!Schema::hasTable('lsg_catalog_core_characteristic_values')) {
            return [];
        }

        return DB::table('lsg_catalog_core_characteristic_values')
            ->where('characteristic_id', $characteristicId)
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('label')
            ->get(['value', 'label'])
            ->map(fn ($row) => ['value' => $row->value, 'label' => $row->label ?: $row->value])
            ->all();
    }

    private function combinationAttributeValues(int $attributeId): array
    {
        if (!Schema::hasTable('catalog_combination_attribute_values')) {
            return [];
        }

        return DB::table('catalog_combination_attribute_values')
            ->where('attribute_id', $attributeId)
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('label')
            ->get(['value', 'label'])
            ->map(fn ($row) => ['value' => $row->value, 'label' => $row->label ?: $row->value])
            ->all();
    }

    private function decodeAllowedValues($value): array
    {
        if (!$value) {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode((string) $value, true);

        return is_array($decoded) ? array_values(array_filter($decoded, fn ($item) => filled($item))) : [];
    }

    private function allowedValuesPayload(array $values): ?string
    {
        $values = collect($values)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->values()
            ->all();

        return $values ? json_encode($values) : null;
    }
}
