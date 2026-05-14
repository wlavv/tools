<?php

namespace Modules\DataImportWizard\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\DataImportWizard\Services\ImportRegistry;
use Modules\DataImportWizard\Services\ImportTemplateGeneratorService;

class TemplateController extends Controller
{
    public function download(
        string $profile,
        Request $request,
        ImportRegistry $registry,
        ImportTemplateGeneratorService $generator
    ) {
        $class = $registry->require($profile);
        $includeExamples = $request->boolean('examples', config('data-import-wizard.csv.include_example_row_by_default', false));
        $csv = $generator->csv($class, $includeExamples);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $profile . '_import_template.csv"',
        ]);
    }
}
