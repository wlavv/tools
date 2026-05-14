<?php

namespace Modules\DataExportCenter\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\DataExportCenter\Models\DataExportBatch;
use Modules\DataExportCenter\Services\ExportExecutorService;

class ExportController extends Controller
{
    public function store(string $profile, Request $request, ExportExecutorService $executor)
    {
        $batch = $executor->executeByKey(
            profileKey: $profile,
            filters: $request->input('filters', []),
            context: $request->input('context', []),
            format: $request->input('format')
        );

        return redirect()->route('data_export_center.batches.show', $batch);
    }

    public function showBatch(DataExportBatch $batch)
    {
        return $this->view('data-export-center::batches.show', [
            'batch' => $batch,
        ]);
    }

    public function download(DataExportBatch $batch)
    {
        abort_unless($batch->status === 'completed' && $batch->disk && $batch->path, 404);

        return Storage::disk($batch->disk)->download($batch->path, $batch->download_name);
    }
}
