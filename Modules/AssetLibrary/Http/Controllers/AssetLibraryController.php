<?php

namespace Modules\AssetLibrary\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\AssetLibrary\Http\Requests\StoreAssetLibraryRequest;
use Modules\AssetLibrary\Http\Requests\UpdateAssetLibraryRequest;
use Modules\AssetLibrary\Models\AssetLibrary;
use Modules\AssetLibrary\Services\AssetLibraryService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetLibraryController extends Controller
{
    public function __construct(
        protected AssetLibraryService $service
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => (string) $request->string('search'),
            'type' => (string) $request->string('type'),
            'status' => (string) $request->string('status'),
        ];

        return view('asset-library::Index', [
            'assets' => $this->service->paginate($filters),
            'filters' => $filters,
            'stats' => $this->service->stats(),
            'types' => $this->service->types(),
            'statuses' => $this->service->statuses(),
        ]);
    }

    public function create(): View
    {
        return view('asset-library::pages.form', [
            'asset' => new AssetLibrary(),
            'types' => $this->service->types(),
            'statuses' => $this->service->statuses(),
            'formAction' => route('asset_library.store'),
            'httpMethod' => 'POST',
        ]);
    }

    public function store(StoreAssetLibraryRequest $request): RedirectResponse
    {
        $asset = $this->service->store($request->validated(), $request->file('file'));

        return redirect()
            ->route('asset_library.show', $asset)
            ->with('success', 'Asset criado com sucesso.');
    }

    public function show(AssetLibrary $assetLibrary): View
    {
        return view('asset-library::pages.show', [
            'asset' => $assetLibrary,
            'previewUrl' => $this->service->publicUrl($assetLibrary->file_path, $assetLibrary->disk),
        ]);
    }

    public function edit(AssetLibrary $assetLibrary): View
    {
        return view('asset-library::pages.form', [
            'asset' => $assetLibrary,
            'types' => $this->service->types(),
            'statuses' => $this->service->statuses(),
            'formAction' => route('asset_library.update', $assetLibrary),
            'httpMethod' => 'PUT',
        ]);
    }

    public function update(UpdateAssetLibraryRequest $request, AssetLibrary $assetLibrary): RedirectResponse
    {
        $asset = $this->service->update($assetLibrary, $request->validated(), $request->file('file'));

        return redirect()
            ->route('asset_library.show', $asset)
            ->with('success', 'Asset atualizado com sucesso.');
    }

    public function destroy(AssetLibrary $assetLibrary): RedirectResponse
    {
        $this->service->delete($assetLibrary);

        return redirect()
            ->route('asset_library.index')
            ->with('success', 'Asset removido com sucesso.');
    }

    public function download(AssetLibrary $assetLibrary): StreamedResponse
    {
        $disk = $assetLibrary->disk ?: config('asset-library.disk', 'public');

        abort_unless(Storage::disk($disk)->exists($assetLibrary->file_path), 404);

        return Storage::disk($disk)->download(
            $assetLibrary->file_path,
            $assetLibrary->original_filename ?: $assetLibrary->filename
        );
    }
}
