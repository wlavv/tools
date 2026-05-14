<?php

namespace Modules\DataImportWizard\Services;

use Illuminate\Support\Facades\Validator;

class ImportValidatorService
{
    public function __construct(
        private readonly ImportSchemaBuilder $schemaBuilder
    ) {
    }

    public function validate(string $rootClass, array $parsed): array
    {
        $schema = $this->schemaBuilder->build($rootClass);

        $headers = $parsed['headers'] ?? [];
        $missingHeaders = array_values(array_diff($schema['required_headers'], $headers));
        $unknownHeaders = array_values(array_diff($headers, $schema['headers']));
        $strictHeaders = (bool) config('data-import-wizard.strict_headers', false);

        $validatedRows = [];
        $validCount = 0;
        $errorCount = 0;
        $warningCount = 0;

        foreach (($parsed['rows'] ?? []) as $parsedRow) {
            $data = $parsedRow['data'];
            $errors = [];
            $warnings = [];

            foreach ($missingHeaders as $missingHeader) {
                $errors[] = "Missing required header [{$missingHeader}].";
            }

            if ($strictHeaders) {
                foreach ($unknownHeaders as $unknownHeader) {
                    $errors[] = "Unknown header [{$unknownHeader}].";
                }
            } else {
                foreach ($unknownHeaders as $unknownHeader) {
                    $warnings[] = "Unknown header [{$unknownHeader}] will be ignored.";
                }
            }

            $validator = Validator::make($data, $schema['rules']);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = $message;
                }
            }

            $status = count($errors) > 0 ? 'invalid' : 'valid';

            $validatedRows[] = [
                'row_number' => $parsedRow['row_number'],
                'raw_data' => $data,
                'normalized_data' => $data,
                'status' => $status,
                'errors' => $errors,
                'warnings' => $warnings,
            ];

            $status === 'valid' ? $validCount++ : $errorCount++;
            if (count($warnings) > 0) {
                $warningCount++;
            }
        }

        return [
            'schema' => $schema,
            'headers' => $headers,
            'missing_headers' => $missingHeaders,
            'unknown_headers' => $unknownHeaders,
            'rows' => $validatedRows,
            'summary' => [
                'total_rows' => count($validatedRows),
                'valid_rows' => $validCount,
                'error_rows' => $errorCount,
                'warning_rows' => $warningCount,
            ],
        ];
    }
}
