<?php

namespace Modules\WebCatalogue\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\ThreeDGenerationJob;
use Modules\WebCatalogue\Jobs\Generate3DModelJob;
use Modules\WebCatalogue\Services\Resources\WebCatalogueResourceUploadService;
use Modules\WebCatalogue\Support\Concerns\HandlesWebCatalogueFormData;

class ThreeDGenerationJobController extends Controller
{
    use HandlesWebCatalogueFormData;

    public function __construct(){ parent::__construct(); $this->pageTitle = $this->resolvePageTitle(); }

    protected function viewData(array $extra = []): array
    {
        return array_merge([
            'stores' => Store::query()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->limit(500)->get(),
        ], $extra);
    }

    public function index(): View
    {
        $items = ThreeDGenerationJob::query()->with(['store','product','resultResource'])->latest('id')->paginate(20);
        return $this->view('webcatalogue::studio.3d_jobs.index', compact('items'));
    }

    public function create(): View
    {
        return $this->view('webcatalogue::studio.3d_jobs.form', $this->viewData(['item' => null, 'action' => route('webcatalogue.studio.3d_jobs.store'), 'method' => 'POST']));
    }

    public function store(Request $request, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $data = $this->cleanJobData($request);
        $data['status'] = 'queued';
        $job = ThreeDGenerationJob::create($data);
        $this->handleUploads($request, $job, $resources);
        $this->dispatchGeneration($job->fresh());

        return redirect()->route('webcatalogue.studio.3d_jobs.show', $job)->with('success', '3D generation job created and queued automatically.');
    }

    public function show(ThreeDGenerationJob $threeDGenerationJob): View
    {
        return $this->view('webcatalogue::studio.3d_jobs.show', ['item' => $threeDGenerationJob->load(['store','product','resultResource','arResource','vrResource'])]);
    }

    public function edit(ThreeDGenerationJob $threeDGenerationJob): View
    {
        return $this->view('webcatalogue::studio.3d_jobs.form', $this->viewData(['item' => $threeDGenerationJob->load(['product']), 'action' => route('webcatalogue.studio.3d_jobs.update', $threeDGenerationJob), 'method' => 'PUT']));
    }

    public function update(Request $request, ThreeDGenerationJob $threeDGenerationJob, WebCatalogueResourceUploadService $resources): RedirectResponse
    {
        $threeDGenerationJob->update($this->cleanJobData($request));
        $this->handleUploads($request, $threeDGenerationJob, $resources);
        return redirect()->route('webcatalogue.studio.3d_jobs.show', $threeDGenerationJob)->with('success', '3D generation job updated.');
    }

    public function run(ThreeDGenerationJob $threeDGenerationJob): RedirectResponse
    {
        $threeDGenerationJob->update(['status' => 'queued', 'error_message' => null]);
        $this->dispatchGeneration($threeDGenerationJob->fresh());

        return redirect()->route('webcatalogue.studio.3d_jobs.show', $threeDGenerationJob)->with('success', '3D generation job queued.');
    }

    public function status(ThreeDGenerationJob $threeDGenerationJob)
    {
        $threeDGenerationJob->load(['resultResource', 'arResource', 'vrResource']);

        return response()->json([
            'id' => $threeDGenerationJob->id,
            'status' => $threeDGenerationJob->status,
            'provider' => $threeDGenerationJob->provider,
            'provider_task_id' => $threeDGenerationJob->provider_task_id,
            'provider_status' => $threeDGenerationJob->provider_status,
            'progress' => (int) ($threeDGenerationJob->progress ?? 0),
            'error_message' => $threeDGenerationJob->error_message,
            'result_resource_id' => $threeDGenerationJob->result_resource_id,
            'ar_resource_id' => $threeDGenerationJob->ar_resource_id,
            'vr_resource_id' => $threeDGenerationJob->vr_resource_id,
            'result_url' => optional($threeDGenerationJob->resultResource)->resolved_url,
            'ar_url' => optional($threeDGenerationJob->arResource)->resolved_url,
            'vr_url' => optional($threeDGenerationJob->vrResource)->resolved_url,
        ]);
    }

    public function destroy(ThreeDGenerationJob $threeDGenerationJob): RedirectResponse
    {
        $threeDGenerationJob->delete();
        return redirect()->route('webcatalogue.studio.3d_jobs.index')->with('success', '3D generation job deleted.');
    }

    protected function cleanJobData(Request $request): array
    {
        $data = $this->cleanWebCatalogueData($request, ['json' => ['metadata', 'source_resource_ids'], 'default_status' => 'draft']);
        foreach (['source_images','result_model_3d','ar_file','vr_file'] as $field) unset($data[$field]);
        return $data;
    }

    protected function handleUploads(Request $request, ThreeDGenerationJob $job, WebCatalogueResourceUploadService $resources): void
    {
        $base = [
            'id_store' => (int) $job->id_store,
            'id_product' => (int) $job->id_product,
            'resource_owner_type' => '3d_generation_job',
            'resource_owner_id' => (int) $job->id,
            'status' => 'active',
        ];

        $sourceIds = (array) ($job->source_resource_ids ?: []);
        if ($request->hasFile('source_images')) {
            foreach ((array) $request->file('source_images') as $index => $file) {
                if ($file && $file->isValid()) {
                    $resource = $resources->storeUploadedResource($file, array_merge($base, [
                        'resource_type' => 'image',
                        'title' => '3D source image ' . ($index + 1),
                        'is_main' => false,
                        'sort_order' => $index + 1,
                    ]));
                    $sourceIds[] = $resource->id;
                }
            }
        }

        $updates = [];
        if ($request->hasFile('result_model_3d') && $request->file('result_model_3d')->isValid()) {
            $resource = $resources->storeUploadedResource($request->file('result_model_3d'), array_merge($base, [
                'resource_type' => 'model_3d',
                'title' => 'Generated 3D model',
                'is_main' => true,
            ]));
            $updates['result_resource_id'] = $resource->id;
            $updates['status'] = 'completed';
        }
        if ($request->hasFile('ar_file') && $request->file('ar_file')->isValid()) {
            $resource = $resources->storeUploadedResource($request->file('ar_file'), array_merge($base, [
                'resource_type' => 'ar_file',
                'title' => 'AR export',
                'is_main' => true,
            ]));
            $updates['ar_resource_id'] = $resource->id;
        }
        if ($request->hasFile('vr_file') && $request->file('vr_file')->isValid()) {
            $resource = $resources->storeUploadedResource($request->file('vr_file'), array_merge($base, [
                'resource_type' => 'vr_file',
                'title' => 'VR export',
                'is_main' => true,
            ]));
            $updates['vr_resource_id'] = $resource->id;
        }
        if (!empty($sourceIds)) $updates['source_resource_ids'] = array_values(array_unique(array_map('intval', $sourceIds)));
        if (!empty($updates)) $job->update($updates);
    }

    protected function dispatchGeneration(?ThreeDGenerationJob $job): void
    {
        if (!$job) {
            return;
        }

        if ($job->status === 'completed' && $job->result_resource_id) {
            return;
        }

        $mode = (string) config('webcatalogue.3d_generation.dispatch', 'sync');

        if ($mode === 'queue') {
            Generate3DModelJob::dispatch($job->id);
            return;
        }

        Generate3DModelJob::dispatchSync($job->id);
    }
}

