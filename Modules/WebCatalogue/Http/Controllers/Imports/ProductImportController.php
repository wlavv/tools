<?php

namespace Modules\WebCatalogue\Http\Controllers\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\ImportBatch;
use Modules\WebCatalogue\Models\Store;

class ProductImportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('webcatalogue::imports.products', [
            'stores' => Store::query()->orderBy('name')->get(),
            'batches' => ImportBatch::query()->latest('id')->limit(20)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['csv_file' => ['required','file','mimes:csv,txt']]);
        $path = $request->file('csv_file')->store('webcatalogue/temp/imports', config('webcatalogue.storage_disk', 'public'));
        ImportBatch::create([
            'id_store' => $request->input('id_store'),
            'source_type' => 'csv:products',
            'filename' => $request->file('csv_file')->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'uploaded',
            'metadata' => ['note' => 'Foundation upload only. Parser service will be added next.'],
        ]);
        return redirect()->route('webcatalogue.imports.index')->with('success', 'Product CSV uploaded and registered.');
    }
}
