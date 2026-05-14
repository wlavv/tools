<?php

namespace Modules\DataImportWizard\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\DataImportWizard\Models\DataImportBatch;
use Modules\DataImportWizard\Models\DataImportRow;
use Modules\DataImportWizard\Services\CsvParserService;
use Modules\DataImportWizard\Services\ImportRegistry;
use Modules\DataImportWizard\Services\ImportValidatorService;
use Modules\DataImportWizard\Support\ImportModes;

class UploadController extends Controller
{
    public function create(string $profile, ImportRegistry $registry)
    {
        $class = $registry->require($profile);

        return $this->view('data-import-wizard::upload', [
            'profile' => $registry->describe($class),
            'modes' => ImportModes::mainModes(),
        ]);
    }

    public function store(
        string $profile,
        Request $request,
        ImportRegistry $registry,
        CsvParserService $parser,
        ImportValidatorService $validator
    ) {
        $allowed = implode(',', config('data-import-wizard.csv.allowed_extensions', ['csv', 'txt']));

        $request->validate([
            'file' => ['required', 'file', 'mimes:' . $allowed],
            'mode' => ['required', 'in:' . implode(',', ImportModes::mainModes())],
        ]);

        $class = $registry->require($profile);
        $file = $request->file('file');
        $parsed = $parser->parse($file->getRealPath());
        $result = $validator->validate($class, $parsed);

        $path = $file->store('data-import-wizard');

        $batch = DataImportBatch::create([
            'uuid' => (string) Str::uuid(),
            'profile_key' => $profile,
            'profile_class' => $class,
            'status' => $result['summary']['error_rows'] > 0 ? 'failed_validation' : 'validated',
            'mode' => $request->input('mode', config('data-import-wizard.default_mode', 'upsert')),
            'original_filename' => $file->getClientOriginalName(),
            'disk' => config('filesystems.default', 'local'),
            'path' => $path,
            'total_rows' => $result['summary']['total_rows'],
            'valid_rows' => $result['summary']['valid_rows'],
            'error_rows' => $result['summary']['error_rows'],
            'warning_rows' => $result['summary']['warning_rows'],
            'metadata' => [
                'headers' => $result['headers'],
                'missing_headers' => $result['missing_headers'],
                'unknown_headers' => $result['unknown_headers'],
                'schema_warnings' => $result['schema']['warnings'],
            ],
            'created_by' => optional($request->user())->getAuthIdentifier(),
        ]);

        foreach ($result['rows'] as $validatedRow) {
            DataImportRow::create([
                'batch_id' => $batch->id,
                'row_number' => $validatedRow['row_number'],
                'raw_data' => $validatedRow['raw_data'],
                'normalized_data' => $validatedRow['normalized_data'],
                'status' => $validatedRow['status'],
                'errors' => $validatedRow['errors'],
                'warnings' => $validatedRow['warnings'],
            ]);
        }

        return redirect()->route('data_import_wizard.batches.preview', $batch);
    }
}
