<?php

namespace Modules\DataImportWizard\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DataImportWizard\Models\DataImportBatch;

class BatchController extends Controller
{
    public function index()
    {
        return $this->view('data-import-wizard::batches.index', [
            'batches' => DataImportBatch::query()->latest()->paginate(25),
        ]);
    }

    public function preview(DataImportBatch $batch)
    {
        return $this->view('data-import-wizard::batches.preview', [
            'batch' => $batch,
            'rows' => $batch->rows()->orderBy('row_number')->paginate(100),
        ]);
    }

    public function show(DataImportBatch $batch)
    {
        return $this->view('data-import-wizard::batches.show', [
            'batch' => $batch,
            'rows' => $batch->rows()->orderBy('row_number')->paginate(100),
        ]);
    }
}
