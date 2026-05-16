<?php

namespace Modules\Mtg\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Mtg\Models\mtg_sets;
use Modules\Mtg\Services\TcgCollectorsWebCatalogueService;

class TcgCollectorsController extends Controller
{
    public function index(Request $request, TcgCollectorsWebCatalogueService $service): View
    {
        $search = strtolower(trim((string) $request->query('q', '')));
        $sets = collect($service->listSets($request->boolean('refresh')))
            ->when($search !== '', fn ($sets) => $sets->filter(fn ($set) =>
                str_contains(strtolower($set['name']), $search)
                || str_contains(strtolower($set['code']), $search)
                || str_contains(strtolower($set['set_type']), $search)
            ))
            ->values();

        return view('mtg::tcg_collectors.mtg_sets.index', [
            'sets' => $sets,
            'imported' => $service->importedSetCodes(),
            'search' => $search,
        ]);
    }

    public function import(Request $request, string $setCode, TcgCollectorsWebCatalogueService $service): RedirectResponse
    {
        $result = $service->importSet($setCode, $request->boolean('refresh_images'));

        return redirect()
            ->route('mtg.tcg_collectors.index', ['q' => $result['set']['code']])
            ->with('success', 'Set ' . $result['set']['name'] . ' importado: ' . $result['cards_processed'] . ' cartas, ' . $result['images_downloaded'] . ' imagens descarregadas/atualizadas.');
    }

    public function importFromSet(Request $request, string $setCode, TcgCollectorsWebCatalogueService $service): RedirectResponse
    {
        $set = mtg_sets::getSet($setCode);

        if (!$set) {
            abort(404);
        }

        $data = $request->validate([
            'store_slug' => ['required', 'string', 'max:120'],
            'store_name' => ['required', 'string', 'max:180'],
            'store_code' => ['required', 'string', 'max:80'],
            'store_domain' => ['nullable', 'string', 'max:180'],
            'catalogue_name' => ['required', 'string', 'max:180'],
            'catalogue_slug' => ['required', 'string', 'max:180'],
            'catalogue_description' => ['nullable', 'string'],
            'include_sealed_products' => ['nullable', 'boolean'],
            'skip_card_sync' => ['nullable', 'boolean'],
        ]);

        $data['include_sealed_products'] = $request->boolean('include_sealed_products', true);
        $data['skip_card_sync'] = $request->boolean('skip_card_sync');
        $result = $service->importLocalSet($set, $data);

        return redirect()
            ->route('mtg.showSet', [$setCode, 1])
            ->with('success', 'Set enviado para WebCatalogue: ' . $result['products'] . ' produtos, ' . $result['resources'] . ' recursos, ' . $result['cards_processed'] . ' cartas.');
    }
}
