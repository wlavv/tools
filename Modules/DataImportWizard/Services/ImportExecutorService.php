<?php

namespace Modules\DataImportWizard\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\DataImportWizard\Models\DataImportBatch;
use Modules\DataImportWizard\Models\DataImportRow;
use Modules\DataImportWizard\Support\ImportModes;
use RuntimeException;
use Throwable;

class ImportExecutorService
{
    public function __construct(
        private readonly ImportRegistry $registry,
        private readonly ImportSchemaBuilder $schemaBuilder
    ) {
    }

    public function execute(DataImportBatch $batch, ?string $mode = null): DataImportBatch
    {
        $mode = $mode ?: $batch->mode ?: config('data-import-wizard.default_mode', ImportModes::UPSERT);

        if ($mode === ImportModes::VALIDATE_ONLY) {
            $batch->update([
                'status' => 'validated',
                'finished_at' => now(),
            ]);

            return $batch->fresh();
        }

        $rootClass = $this->registry->require($batch->profile_key);
        $schema = $this->schemaBuilder->build($rootClass);

        $batch->update([
            'status' => 'processing',
            'mode' => $mode,
            'started_at' => now(),
        ]);

        $success = 0;
        $failed = 0;

        $batch->rows()
            ->where('status', 'valid')
            ->orderBy('row_number')
            ->chunkById(100, function ($rows) use ($schema, $mode, &$success, &$failed) {
                foreach ($rows as $row) {
                    try {
                        $result = DB::transaction(function () use ($row, $schema, $mode) {
                            return $this->processRow($row, $schema, $mode);
                        });

                        $row->update([
                            'status' => 'imported',
                            'operation' => $result['operation'] ?? null,
                            'target_model' => $result['target_model'] ?? null,
                            'target_id' => $result['target_id'] ?? null,
                            'errors' => [],
                        ]);

                        $success++;
                    } catch (Throwable $exception) {
                        $row->update([
                            'status' => 'failed',
                            'errors' => [$exception->getMessage()],
                        ]);

                        $failed++;
                    }
                }
            });

        $batch->refreshCounters();
        $batch->update([
            'status' => $failed > 0 ? 'completed_with_errors' : 'completed',
            'finished_at' => now(),
        ]);

        return $batch->fresh();
    }

    private function processRow(DataImportRow $row, array $schema, string $mode): array
    {
        $data = $row->normalized_data ?: [];
        $resolved = [];
        $rootResult = null;

        foreach ($schema['graph']['nodes'] as $node) {
            $nodeMode = $node['is_root'] ? $mode : ($node['mode'] ?? ImportModes::RESOLVE_ONLY);
            $result = $this->resolveNode($node, $data, $schema, $resolved, $nodeMode);
            $resolved[$node['id']] = $result;

            if ($node['is_root']) {
                $rootResult = $result;
            }
        }

        if (! $rootResult || ! ($rootResult['model'] ?? null)) {
            throw new RuntimeException('Root import record was not created or resolved.');
        }

        /** @var Model $model */
        $model = $rootResult['model'];

        return [
            'operation' => $rootResult['operation'],
            'target_model' => $model::class,
            'target_id' => $model->getKey(),
        ];
    }

    private function resolveNode(array $node, array $rowData, array $schema, array $resolved, string $mode): array
    {
        $class = $node['class'];
        $attributes = $this->attributesForNode($node, $rowData, $schema);
        $attributes = array_merge($attributes, $this->foreignKeysForNode($node, $schema, $resolved));

        $model = null;

        if ($mode !== ImportModes::CREATE_ONLY && $mode !== ImportModes::CREATE) {
            $model = $this->findExisting($node, $rowData, $schema, $attributes);
        }

        if ($model) {
            if (in_array($mode, [ImportModes::UPDATE, ImportModes::UPSERT, ImportModes::RESOLVE_OR_UPDATE], true)) {
                $model->fill($attributes);
                $model->save();
                return ['model' => $model, 'operation' => 'updated'];
            }

            return ['model' => $model, 'operation' => 'resolved'];
        }

        if (in_array($mode, [ImportModes::RESOLVE_ONLY, ImportModes::UPDATE], true)) {
            throw new RuntimeException("Unable to resolve import dependency/model [{$class}].");
        }

        if ($mode === ImportModes::OPTIONAL_RESOLVE) {
            return ['model' => null, 'operation' => 'skipped'];
        }

        /** @var Model $model */
        $model = new $class();
        $model->fill($attributes);
        $model->save();

        return ['model' => $model, 'operation' => 'created'];
    }

    private function attributesForNode(array $node, array $rowData, array $schema): array
    {
        $attributes = [];
        $nodeFields = $schema['node_fields'][$node['id']] ?? [];

        foreach ($nodeFields as $fieldKey => $definition) {
            if (($definition['fillable'] ?? true) === false) {
                continue;
            }

            $csvKey = $definition['csv_key'];
            $column = $definition['column'] ?? $fieldKey;

            if (array_key_exists($csvKey, $rowData)) {
                $attributes[$column] = $rowData[$csvKey];
            }
        }

        return $attributes;
    }

    private function foreignKeysForNode(array $node, array $schema, array $resolved): array
    {
        $attributes = [];

        foreach ($schema['graph']['edges'] as $edge) {
            if ($edge['parent_id'] !== $node['id']) {
                continue;
            }

            $childResult = $resolved[$edge['child_id']] ?? null;
            $childModel = $childResult['model'] ?? null;

            if (! $childModel instanceof Model) {
                if (($edge['required'] ?? true) === true) {
                    throw new RuntimeException("Required dependency [{$edge['child_id']}] was not resolved.");
                }

                continue;
            }

            if ($edge['foreign_key']) {
                $ownerKey = $edge['owner_key'] ?: 'id';
                $attributes[$edge['foreign_key']] = $childModel->{$ownerKey};
            }
        }

        return $attributes;
    }

    private function findExisting(array $node, array $rowData, array $schema, array $attributes): ?Model
    {
        $class = $node['class'];

        if (method_exists($class, 'resolveImportRecord')) {
            $customModel = $class::resolveImportRecord($attributes, [
                'node' => $node,
                'row' => $rowData,
                'schema' => $schema,
            ]);

            if ($customModel instanceof Model) {
                return $customModel;
            }
        }

        $lookup = $this->lookupAttributesForNode($node, $rowData, $schema);

        if (empty($lookup)) {
            return null;
        }

        /** @var Model $model */
        $model = new $class();
        $query = $model->newQuery();

        foreach ($lookup as $column => $value) {
            if ($value === null || $value === '') {
                return null;
            }

            $query->where($column, $value);
        }

        return $query->first();
    }

    private function lookupAttributesForNode(array $node, array $rowData, array $schema): array
    {
        $class = $node['class'];
        $lookupFields = [];

        if (method_exists($class, 'importLookupColumns')) {
            $lookupFields = $class::importLookupColumns();
        } elseif (method_exists($class, 'importUniqueBy')) {
            $lookupFields = $class::importUniqueBy();
        }

        if (empty($lookupFields)) {
            foreach (($schema['node_fields'][$node['id']] ?? []) as $fieldKey => $definition) {
                if (($definition['lookup'] ?? false) === true) {
                    $lookupFields[] = $fieldKey;
                }
            }
        }

        $lookup = [];

        foreach ($lookupFields as $fieldKey) {
            $definition = $schema['node_fields'][$node['id']][$fieldKey] ?? null;
            if (! $definition) {
                continue;
            }

            $csvKey = $definition['csv_key'];
            $column = $definition['column'] ?? $fieldKey;
            $lookup[$column] = $rowData[$csvKey] ?? null;
        }

        return $lookup;
    }
}
