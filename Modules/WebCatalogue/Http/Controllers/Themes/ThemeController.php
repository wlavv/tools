<?php

namespace Modules\WebCatalogue\Http\Controllers\Themes;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\StoreTheme;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class ThemeController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }
    protected function viewData(array $extra = []): array { return array_merge(['stores' => Store::query()->orderBy('name')->get()], $extra); }

    public function index(Request $request): View
    {
        $items = StoreTheme::query()->with('store')->latest('id')->paginate(20);
        return $this->view('webcatalogue::themes.index', compact('items'));
    }
    public function create(): View { return $this->view('webcatalogue::themes.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.themes.store'), 'method' => 'POST'])); }
    public function store(Request $request, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $item = StoreTheme::create($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['is_default'], 'default_status' => 'active']));
        $this->handleThemeUploads($request, $item, $resources);
        return redirect()->route('webcatalogue.themes.show', $item)->with('success', 'Theme created.');
    }
    public function show(StoreTheme $theme): View { return $this->view('webcatalogue::themes.show', ['item' => $theme]); }
    public function edit(StoreTheme $theme): View { return $this->view('webcatalogue::themes.form', $this->viewData(['item' => $theme, 'action' => route('webcatalogue.themes.update', $theme), 'method' => 'PUT'])); }
    public function update(Request $request, StoreTheme $theme, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $theme->update($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['is_default'], 'default_status' => 'active']));
        $this->handleThemeUploads($request, $theme, $resources);
        return redirect()->route('webcatalogue.themes.show', $theme)->with('success', 'Theme updated.');
    }
    public function destroy(StoreTheme $theme): RedirectResponse { $theme->delete(); return redirect()->route('webcatalogue.themes.index')->with('success', 'Theme deleted.'); }

    protected function handleThemeUploads(Request $request, StoreTheme $theme, WebCatalogueResourceUploadService $resources): void
    {
        if ($request->hasFile('logo_upload') && $request->file('logo_upload')->isValid()) {
            $resource = $resources->storeUploadedResource($request->file('logo_upload'), [
                'id_store' => (int) $theme->id_store,
                'resource_owner_type' => 'store_theme',
                'resource_owner_id' => (int) $theme->id,
                'resource_type' => 'logo',
                'title' => $theme->name . ' · Logo',
                'is_main' => true,
                'status' => 'active',
            ]);
            $theme->update(['logo_resource_id' => $resource->id]);
        }

        if ($request->hasFile('favicon_upload') && $request->file('favicon_upload')->isValid()) {
            $resource = $resources->storeUploadedResource($request->file('favicon_upload'), [
                'id_store' => (int) $theme->id_store,
                'resource_owner_type' => 'store_theme',
                'resource_owner_id' => (int) $theme->id,
                'resource_type' => 'favicon',
                'title' => $theme->name . ' · Favicon',
                'is_main' => false,
                'status' => 'active',
            ]);
            $theme->update(['favicon_resource_id' => $resource->id]);
        }
    }
}
