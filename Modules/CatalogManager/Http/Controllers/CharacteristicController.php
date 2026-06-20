<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CharacteristicController extends BaseCatalogController
{
    public function index()
    {
        $this->setActions([
            [
                'key' => 'new_characteristic',
                'label' => 'Nova Caracteristica',
                'name' => 'Nova Caracteristica',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
                'url' => route('catalog-manager.characteristics.create'),
                'route' => 'catalog-manager.characteristics.create',
                'type' => 'link',
            ],
        ]);

        $characteristics = $this->tablesReady()
            ? DB::table('lsg_catalog_core_characteristics')
                ->orderBy('name')
                ->get()
            : collect();

        return view('catalogmanager::characteristics.index', compact('characteristics'));
    }

    public function create()
    {
        return view('catalogmanager::characteristics.create', [
            'characteristic' => null,
            'valuesRows' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->tablesReady()) {
            return back()->withErrors(['catalog' => 'As tabelas de caracteristicas ainda nao existem. Corre as migrations.'])->withInput();
        }

        $data = $this->validatedData($request);

        $id = DB::table('lsg_catalog_core_characteristics')->insertGetId($this->payload($data));
        $this->syncValues($id, $data['values'] ?? '');

        return redirect()->route('catalog-manager.characteristics.index')->with('success', 'Caracteristica criada.');
    }

    public function edit(int $id)
    {
        abort_unless($this->tablesReady(), 404);

        $characteristic = DB::table('lsg_catalog_core_characteristics')->where('id', $id)->first();
        abort_if(!$characteristic, 404);

        $valuesRows = Schema::hasTable('lsg_catalog_core_characteristic_values')
            ? DB::table('lsg_catalog_core_characteristic_values')
                ->where('characteristic_id', $id)
                ->orderBy('position')
                ->orderBy('label')
                ->get()
            : collect();

        return view('catalogmanager::characteristics.edit', compact('characteristic', 'valuesRows'));
    }

    public function update(Request $request, int $id)
    {
        abort_unless($this->tablesReady(), 404);

        $data = $this->validatedData($request, $id);

        DB::table('lsg_catalog_core_characteristics')->where('id', $id)->update($this->payload($data, false));
        $this->syncValues($id, $data['values'] ?? '');

        return redirect()->route('catalog-manager.characteristics.index')->with('success', 'Caracteristica atualizada.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180'],
            'data_type' => ['required', 'string', 'max:40'],
            'usage_scope' => ['required', 'in:product,combination,both'],
            'unit' => ['nullable', 'string', 'max:40'],
            'values' => ['nullable'],
            'values.*.id' => ['nullable', 'integer'],
            'values.*.label' => ['nullable', 'string', 'max:180'],
            'values.*.value' => ['nullable', 'string', 'max:180'],
            'values.*.image_url' => ['nullable', 'string', 'max:2048'],
            'values.*.image_upload' => ['nullable', 'image', 'max:2048'],
            'values.*.image_alt' => ['nullable', 'string', 'max:180'],
            'values.*.position' => ['nullable', 'integer'],
            'values.*.active' => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
            'is_searchable' => ['nullable', 'boolean'],
            'is_seo_keyword' => ['nullable', 'boolean'],
            'is_syncable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function payload(array $data, bool $creating = true): array
    {
        $now = now();
        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'data_type' => $data['data_type'],
            'usage_scope' => $data['usage_scope'] ?? 'product',
            'unit' => $data['unit'] ?? null,
            'is_filterable' => (bool) ($data['is_filterable'] ?? false),
            'is_searchable' => (bool) ($data['is_searchable'] ?? false),
            'is_seo_keyword' => (bool) ($data['is_seo_keyword'] ?? false),
            'is_syncable' => (bool) ($data['is_syncable'] ?? true),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_at' => $now,
        ];

        if ($creating) {
            $payload['created_at'] = $now;
        }

        return $payload;
    }

    private function syncValues(int $characteristicId, $values): void
    {
        if (!Schema::hasTable('lsg_catalog_core_characteristic_values')) {
            return;
        }

        $rows = is_array($values)
            ? collect($values)
            : collect(preg_split('/\r\n|\r|\n/', (string) $values))
                ->map(fn ($line) => ['label' => trim((string) $line)]);

        $keepIds = [];
        $hasImageUrl = Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_url');
        $hasImageAlt = Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_alt');

        $rows
            ->filter(fn ($row) => is_array($row) && filled($row['label'] ?? null))
            ->values()
            ->each(function (array $row, int $index) use ($characteristicId, &$keepIds, $hasImageUrl, $hasImageAlt): void {
                $label = trim((string) ($row['label'] ?? ''));
                $value = trim((string) ($row['value'] ?? '')) ?: Str::slug($label, '_');
                $value = $value !== '' ? $value : 'value_' . substr(md5($label), 0, 8);
                $payload = [
                    'characteristic_id' => $characteristicId,
                    'value' => $value,
                    'label' => $label,
                    'position' => (int) ($row['position'] ?? ($index + 1)),
                    'active' => (bool) ($row['active'] ?? true),
                    'updated_at' => now(),
                ];

                if ($hasImageUrl) {
                    $payload['image_url'] = $this->storeValueImage($row, $characteristicId, $value)
                        ?: (filled($row['image_url'] ?? null) ? trim((string) $row['image_url']) : null);
                }

                if ($hasImageAlt) {
                    $payload['image_alt'] = filled($row['image_alt'] ?? null) ? trim((string) $row['image_alt']) : null;
                }

                $existingId = (int) ($row['id'] ?? 0);
                if ($existingId > 0 && DB::table('lsg_catalog_core_characteristic_values')->where('id', $existingId)->where('characteristic_id', $characteristicId)->exists()) {
                    DB::table('lsg_catalog_core_characteristic_values')->where('id', $existingId)->update($payload);
                    $keepIds[] = $existingId;

                    return;
                }

                $existingByValue = DB::table('lsg_catalog_core_characteristic_values')
                    ->where('characteristic_id', $characteristicId)
                    ->where('value', $value)
                    ->value('id');

                if ($existingByValue) {
                    DB::table('lsg_catalog_core_characteristic_values')->where('id', $existingByValue)->update($payload);
                    $keepIds[] = (int) $existingByValue;

                    return;
                }

                $payload['created_at'] = now();
                $keepIds[] = (int) DB::table('lsg_catalog_core_characteristic_values')->insertGetId($payload);
            });

        $deleteQuery = DB::table('lsg_catalog_core_characteristic_values')
            ->where('characteristic_id', $characteristicId);

        if ($keepIds) {
            $deleteQuery->whereNotIn('id', $keepIds);
        }

        $deleteQuery->delete();
    }

    private function storeValueImage(array $row, int $characteristicId, string $value): ?string
    {
        $file = $row['image_upload'] ?? null;
        if (!$file || !method_exists($file, 'isValid') || !$file->isValid()) {
            return null;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $filename = Str::slug($value) . '-' . substr(md5((string) microtime(true)), 0, 8) . '.' . $extension;
        $path = $file->storeAs(
            'catalog-manager/characteristics/' . $characteristicId,
            $filename,
            'public'
        );

        return '/storage/' . ltrim($path, '/');
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('lsg_catalog_core_characteristics');
    }
}
