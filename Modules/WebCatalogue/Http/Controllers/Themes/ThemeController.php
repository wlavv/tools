<?php

namespace Modules\WebCatalogue\Http\Controllers\Themes;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\StoreTheme;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class ThemeController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }
    protected function viewData(array $extra = []): array
    {
        $item = $extra['item'] ?? null;
        $storeId = (int) old('id_store', $item->id_store ?? request('id_store', 0));

        return array_merge([
            'stores' => Store::query()->orderBy('name')->get(),
            'resources' => Resource::query()
                ->when($storeId > 0, fn ($query) => $query->where('id_store', $storeId))
                ->where(function ($query) {
                    $query->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover', 'logo', 'favicon'])
                        ->orWhere('mime_type', 'like', 'image/%');
                })
                ->orderByDesc('is_main')
                ->orderBy('resource_type')
                ->orderBy('title')
                ->limit(1000)
                ->get(),
            'fontOptions' => [
                'Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif' => 'Inter / System',
                'Arial, Helvetica, sans-serif' => 'Arial',
                'Georgia, Times New Roman, serif' => 'Georgia',
                'Montserrat, Inter, system-ui, sans-serif' => 'Montserrat',
                'Poppins, Inter, system-ui, sans-serif' => 'Poppins',
                'Roboto, Arial, sans-serif' => 'Roboto',
                'Playfair Display, Georgia, serif' => 'Playfair Display',
                'Source Sans 3, Inter, sans-serif' => 'Source Sans 3',
                'Nunito Sans, Inter, sans-serif' => 'Nunito Sans',
            ],
            'buttonStyleOptions' => [
                'solid' => 'Solid',
                'outline' => 'Outline',
                'soft' => 'Soft',
                'minimal' => 'Minimal',
                'premium' => 'Premium',
                'pill' => 'Pill',
                'square' => 'Square',
            ],
            'cardStyleOptions' => [
                'flat' => 'Flat',
                'bordered' => 'Bordered',
                'shadow' => 'Shadow',
                'premium' => 'Premium',
                'compact' => 'Compact',
                'editorial' => 'Editorial',
                'glass' => 'Glass',
            ],
        ], $extra);
    }

    public function index(Request $request): View
    {
        $storeId = $request->integer('id_store') ?: null;
        $store = $storeId ? Store::find($storeId) : null;
        $items = StoreTheme::query()
            ->with('store')
            ->when($storeId, fn ($query) => $query->where('id_store', $storeId))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        if ($store) {
            $returnQuery = $request->filled('return_to') ? ['return_to' => $request->input('return_to')] : [];
            $this->replaceAction('back', ['label' => 'Store hub', 'name' => 'Store hub', 'icon' => 'fa-solid fa-store', 'url' => $this->safeReturnTo($request) ?: route('webcatalogue.stores.show', $store), 'route' => 'webcatalogue.stores.show', 'type' => 'link']);
            $this->replaceAction('new', ['label' => 'New theme', 'name' => 'New theme', 'icon' => 'fa-solid fa-plus', 'class' => 'lsg-action-btn lsg-action-btn--success', 'url' => route('webcatalogue.themes.create', array_merge(['id_store' => $store->id], $returnQuery)), 'route' => 'webcatalogue.themes.create', 'type' => 'link']);
        }

        return $this->view('webcatalogue::themes.index', compact('items', 'store'));
    }
    public function create(): View { return $this->view('webcatalogue::themes.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.themes.store'), 'method' => 'POST'])); }
    public function store(Request $request, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $item = StoreTheme::create($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['is_default'], 'default_status' => 'active']));
        $this->handleThemeUploads($request, $item, $resources);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.themes.show', $item))->with('success', 'Theme created.');
    }
    public function show(StoreTheme $theme): View { return $this->view('webcatalogue::themes.show', ['item' => $theme]); }
    public function edit(StoreTheme $theme): View { return $this->view('webcatalogue::themes.form', $this->viewData(['item' => $theme, 'action' => route('webcatalogue.themes.update', $theme), 'method' => 'PUT'])); }
    public function update(Request $request, StoreTheme $theme, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $theme->update($this->cleanWebCatalogueData($request, ['json' => ['metadata'], 'boolean' => ['is_default'], 'default_status' => 'active']));
        $this->handleThemeUploads($request, $theme, $resources);
        return redirect()->to($this->safeReturnTo($request) ?: route('webcatalogue.themes.show', $theme))->with('success', 'Theme updated.');
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
