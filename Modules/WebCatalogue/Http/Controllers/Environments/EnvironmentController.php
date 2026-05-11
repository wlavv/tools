<?php

namespace Modules\WebCatalogue\Http\Controllers\Environments;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\StoreEnvironment;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class EnvironmentController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }
    protected function viewData(array $extra = []): array { return array_merge(['stores' => Store::query()->orderBy('name')->get()], $extra); }

    public function index(Request $request): View
    {
        $items = StoreEnvironment::query()->with('store')->latest('id')->get();
        return $this->view('webcatalogue::environments.index', compact('items'));
    }
    public function create(): View { return $this->view('webcatalogue::environments.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.environments.store'), 'method' => 'POST'])); }
    public function store(Request $request, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $item = StoreEnvironment::create($this->cleanWebCatalogueData($request, ['json' => ['metadata', 'vr_scene_config', 'ar_scene_config'], 'boolean' => ['is_default'], 'default_status' => 'active']));
        $this->handleEnvironmentUploads($request, $item, $resources);
        return redirect()->route('webcatalogue.environments.show', $item)->with('success', 'Environment created.');
    }
    public function show(StoreEnvironment $environment): View { return $this->view('webcatalogue::environments.show', ['item' => $environment]); }
    public function edit(StoreEnvironment $environment): View { return $this->view('webcatalogue::environments.form', $this->viewData(['item' => $environment, 'action' => route('webcatalogue.environments.update', $environment), 'method' => 'PUT'])); }
    public function update(Request $request, StoreEnvironment $environment, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $environment->update($this->cleanWebCatalogueData($request, ['json' => ['metadata', 'vr_scene_config', 'ar_scene_config'], 'boolean' => ['is_default'], 'default_status' => 'active']));
        $this->handleEnvironmentUploads($request, $environment, $resources);
        return redirect()->route('webcatalogue.environments.show', $environment)->with('success', 'Environment updated.');
    }
    public function destroy(StoreEnvironment $environment): RedirectResponse { $environment->delete(); return redirect()->route('webcatalogue.environments.index')->with('success', 'Environment deleted.'); }

    protected function handleEnvironmentUploads(Request $request, StoreEnvironment $environment, WebCatalogueResourceUploadService $resources): void
    {
        $map = [
            'background_upload' => ['environment_background', 'background_resource_id', 'Background'],
            'skybox_upload' => ['skybox', 'skybox_resource_id', 'Skybox / HDR'],
            'floor_upload' => ['floor_texture', 'floor_resource_id', 'Floor texture'],
            'ambient_audio_upload' => ['ambient_audio', null, 'Ambient audio'],
            'vr_scene_upload' => ['vr_file', null, 'VR scene file'],
        ];

        foreach ($map as $field => [$type, $column, $label]) {
            if ($request->hasFile($field) && $request->file($field)->isValid()) {
                $resource = $resources->storeUploadedResource($request->file($field), [
                    'id_store' => (int) $environment->id_store,
                    'resource_owner_type' => 'store_environment',
                    'resource_owner_id' => (int) $environment->id,
                    'resource_type' => $type,
                    'title' => $environment->name . ' · ' . $label,
                    'is_main' => $column !== null,
                    'status' => 'active',
                ]);
                if ($column) {
                    $environment->update([$column => $resource->id]);
                }
            }
        }
    }
}
