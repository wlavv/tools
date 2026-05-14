<?php

namespace Modules\DataImportWizard\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\DataImportWizard\Models\DataImportBatch;
use Modules\DataImportWizard\Services\ImportExecutorService;
use Modules\DataImportWizard\Support\ImportModes;

class ExecuteController extends Controller
{
    public function execute(DataImportBatch $batch, Request $request, ImportExecutorService $executor)
    {
        $request->validate([
            'mode' => ['nullable', 'in:' . implode(',', ImportModes::mainModes())],
        ]);

        $executor->execute($batch, $request->input('mode', $batch->mode));

        return redirect()->route('data_import_wizard.batches.show', $batch);
    }
}
