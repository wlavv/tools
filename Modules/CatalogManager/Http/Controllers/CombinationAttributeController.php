<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CombinationAttributeController extends BaseCatalogController
{
    public function index()
    {
        $this->setActions([
            [
                'key' => 'new_combination_attribute',
                'label' => 'Novo atributo',
                'name' => 'Novo atributo',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
                'url' => route('catalog-manager.combination-attributes.create'),
                'route' => 'catalog-manager.combination-attributes.create',
                'type' => 'link',
            ],
        ]);

        $attributes = $this->tablesReady()
            ? DB::table('catalog_combination_attributes')
                ->orderBy('position')
                ->orderBy('name')
                ->get()
            : collect();

        return view('catalogmanager::combination_attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('catalogmanager::combination_attributes.create', [
            'attribute' => null,
            'valuesText' => '',
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->tablesReady()) {
            return back()->withErrors(['catalog' => 'As tabelas de atributos de combinacao ainda nao existem. Corre as migrations.'])->withInput();
        }

        $data = $this->validatedData($request);
        $id = DB::table('catalog_combination_attributes')->insertGetId($this->payload($data));
        $this->syncValues($id, $data['values'] ?? '');

        return redirect()->route('catalog-manager.combination-attributes.index')->with('success', 'Atributo de combinacao criado.');
    }

    public function edit(int $id)
    {
        abort_unless($this->tablesReady(), 404);

        $attribute = DB::table('catalog_combination_attributes')->where('id', $id)->first();
        abort_if(!$attribute, 404);

        $valuesText = Schema::hasTable('catalog_combination_attribute_values')
            ? DB::table('catalog_combination_attribute_values')
                ->where('attribute_id', $id)
                ->orderBy('position')
                ->orderBy('label')
                ->pluck('label')
                ->implode(PHP_EOL)
            : '';

        return view('catalogmanager::combination_attributes.edit', compact('attribute', 'valuesText'));
    }

    public function update(Request $request, int $id)
    {
        abort_unless($this->tablesReady(), 404);

        $data = $this->validatedData($request, $id);
        DB::table('catalog_combination_attributes')->where('id', $id)->update($this->payload($data, false));
        $this->syncValues($id, $data['values'] ?? '');

        return redirect()->route('catalog-manager.combination-attributes.index')->with('success', 'Atributo de combinacao atualizado.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180'],
            'display_type' => ['required', 'string', 'max:40'],
            'values' => ['nullable', 'string', 'max:10000'],
            'is_required' => ['nullable', 'boolean'],
            'affects_price' => ['nullable', 'boolean'],
            'affects_stock' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function payload(array $data, bool $creating = true): array
    {
        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'display_type' => $data['display_type'],
            'is_required' => (bool) ($data['is_required'] ?? false),
            'affects_price' => (bool) ($data['affects_price'] ?? true),
            'affects_stock' => (bool) ($data['affects_stock'] ?? true),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'position' => $data['position'] ?? 0,
            'updated_at' => now(),
        ];

        if ($creating) {
            $payload['created_at'] = now();
        }

        return $payload;
    }

    private function syncValues(int $attributeId, string $valuesText): void
    {
        if (!Schema::hasTable('catalog_combination_attribute_values')) {
            return;
        }

        DB::table('catalog_combination_attribute_values')
            ->where('attribute_id', $attributeId)
            ->delete();

        $rows = collect(preg_split('/\r\n|\r|\n/', $valuesText))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $label, int $index) => [
                'attribute_id' => $attributeId,
                'value' => Str::slug($label, '_'),
                'label' => $label,
                'position' => $index + 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows) {
            DB::table('catalog_combination_attribute_values')->insert($rows);
        }
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('catalog_combination_attributes');
    }
}
