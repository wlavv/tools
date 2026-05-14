<?php

namespace Modules\DataImportWizard\Services;

class ImportTemplateGeneratorService
{
    public function __construct(
        private readonly ImportSchemaBuilder $schemaBuilder
    ) {
    }

    public function csv(string $rootClass, bool $includeExampleRow = false): string
    {
        $schema = $this->schemaBuilder->build($rootClass);

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $schema['headers']);

        if ($includeExampleRow) {
            $examples = [];

            foreach ($schema['headers'] as $header) {
                $examples[] = $schema['fields'][$header]['example'] ?? '';
            }

            fputcsv($handle, $examples);
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }
}
