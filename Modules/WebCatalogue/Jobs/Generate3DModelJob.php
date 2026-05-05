<?php

namespace Modules\WebCatalogue\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ThreeDGenerationJob;
use Modules\WebCatalogue\Services\ThreeD\Meshy3DProvider;
use Throwable;

class Generate3DModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(public int $jobId) {}

    public function handle(): void
    {
        $job = ThreeDGenerationJob::query()->with(['store', 'product'])->find($this->jobId);

        if (!$job) {
            return;
        }

        if ($job->status === 'completed' && $job->result_resource_id) {
            $this->sendCompletedNotification($job);
            return;
        }

        $job->update([
            'status' => 'processing',
            'error_message' => null,
            'metadata' => array_merge((array) $job->metadata, [
                'started_at' => now()->toDateTimeString(),
                'execution_mode' => config('webcatalogue.3d_generation.mode', 'mock'),
            ]),
        ]);

        try {
            $mode = (string) config('webcatalogue.3d_generation.mode', 'mock');

            if ($mode === 'meshy') {
                $this->runMeshyGeneration($job);
                return;
            }

            if ($mode !== 'mock') {
                throw new \RuntimeException('3D provider [' . $mode . '] is not implemented yet. Use WEBCATALOGUE_3D_GENERATION_MODE=mock or meshy.');
            }

            $result = $this->createMockModelResources($job);

            $job->update([
                'status' => 'completed',
                'provider_status' => 'SUCCEEDED',
                'progress' => 100,
                'result_resource_id' => $result['model']->id,
                'ar_resource_id' => $result['ar']->id,
                'vr_resource_id' => $result['vr']->id,
                'completed_at' => now(),
                'metadata' => array_merge((array) $job->metadata, [
                    'completed_at' => now()->toDateTimeString(),
                    'provider_result' => 'mock_placeholder_cube',
                    'source_images_count' => count((array) $job->source_resource_ids),
                ]),
            ]);

            $this->sendCompletedNotification($job->fresh(['store', 'product']));
        } catch (Throwable $e) {
            $job->update([
                'status' => 'failed',
                'provider_status' => 'FAILED',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
                'metadata' => array_merge((array) $job->metadata, [
                    'failed_at' => now()->toDateTimeString(),
                ]),
            ]);
        }
    }


    protected function runMeshyGeneration(ThreeDGenerationJob $job): void
    {
        /** @var Meshy3DProvider $provider */
        $provider = app(Meshy3DProvider::class);

        if (empty($job->provider_task_id)) {
            $taskId = $provider->submit($job);

            $job->update([
                'provider' => 'meshy',
                'provider_task_id' => $taskId,
                'provider_status' => 'SUBMITTED',
                'status' => 'processing',
                'progress' => 0,
                'started_at' => now(),
                'metadata' => array_merge((array) $job->metadata, [
                    'provider' => 'meshy',
                    'provider_task_id' => $taskId,
                    'submitted_at' => now()->toDateTimeString(),
                ]),
            ]);
        }

        $attempts = max(1, (int) config('webcatalogue.3d_generation.providers.meshy.poll_attempts', 60));
        $sleep = max(1, (int) config('webcatalogue.3d_generation.providers.meshy.poll_sleep_seconds', 10));
        $terminalSuccess = ['SUCCEEDED', 'succeeded', 'COMPLETED', 'completed'];
        $terminalFailure = ['FAILED', 'failed', 'CANCELED', 'cancelled', 'CANCELLED'];

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $task = $provider->retrieve((string) $job->provider_task_id);
            $providerStatus = (string) ($task['status'] ?? 'UNKNOWN');
            $progress = (int) ($task['progress'] ?? 0);

            $job->update([
                'provider_status' => $providerStatus,
                'progress' => $progress,
                'metadata' => array_merge((array) $job->metadata, [
                    'last_provider_status_at' => now()->toDateTimeString(),
                    'last_provider_payload' => [
                        'id' => $task['id'] ?? null,
                        'status' => $providerStatus,
                        'progress' => $progress,
                        'consumed_credits' => $task['consumed_credits'] ?? null,
                    ],
                ]),
            ]);

            if (in_array($providerStatus, $terminalFailure, true)) {
                $message = $task['task_error']['message'] ?? 'Meshy generation failed.';
                throw new \RuntimeException($message ?: 'Meshy generation failed.');
            }

            if (in_array($providerStatus, $terminalSuccess, true)) {
                $result = $provider->downloadResults($job->fresh(['store', 'product']), $task);

                $job->update([
                    'status' => 'completed',
                    'provider_status' => $providerStatus,
                    'progress' => 100,
                    'result_resource_id' => $result['model']->id ?? null,
                    'ar_resource_id' => $result['ar']->id ?? null,
                    'vr_resource_id' => $result['vr']->id ?? null,
                    'completed_at' => now(),
                    'metadata' => array_merge((array) $job->metadata, [
                        'completed_at' => now()->toDateTimeString(),
                        'provider_result' => 'meshy',
                        'provider_task_id' => $job->provider_task_id,
                    ]),
                ]);

                $this->sendCompletedNotification($job->fresh(['store', 'product']));
                return;
            }

            if ($attempt < $attempts) {
                sleep($sleep);
            }
        }

        $job->update([
            'status' => 'processing',
            'metadata' => array_merge((array) $job->metadata, [
                'polling_paused_at' => now()->toDateTimeString(),
                'polling_note' => 'Provider task is still running. Run the job again to continue polling.',
            ]),
        ]);
    }

    protected function createMockModelResources(ThreeDGenerationJob $job): array
    {
        $disk = (string) config('webcatalogue.storage_disk', 'public');
        $storeId = (int) $job->id_store;
        $productId = (int) $job->id_product;
        $base = 'webcatalogue/stores/' . $storeId . '/products/' . $productId;

        Storage::disk($disk)->makeDirectory($base . '/models');
        Storage::disk($disk)->makeDirectory($base . '/ar');
        Storage::disk($disk)->makeDirectory($base . '/vr');

        $stamp = now()->format('Ymd_His') . '_' . substr(md5((string) microtime(true)), 0, 6);
        $modelFilename = $storeId . '_' . $productId . '_model_3d_' . $stamp . '.glb';
        $modelPath = $base . '/models/' . $modelFilename;

        Storage::disk($disk)->put($modelPath, base64_decode($this->mockGlbBase64(), true));

        $model = Resource::create([
            'id_store' => $storeId,
            'id_product' => $productId,
            'resource_owner_type' => '3d_generation_job',
            'resource_owner_id' => $job->id,
            'resource_type' => 'model_3d',
            'title' => 'Generated 3D model · Job #' . $job->id,
            'description' => 'Mock generated GLB placeholder. Replace provider mode when connecting a real image-to-3D API.',
            'source_type' => 'generated',
            'file_path' => $modelPath,
            'public_url' => Storage::disk($disk)->url($modelPath),
            'filename' => $modelFilename,
            'mime_type' => 'model/gltf-binary',
            'file_size' => Storage::disk($disk)->size($modelPath),
            'extension' => 'glb',
            'is_main' => true,
            'sort_order' => 0,
            'status' => 'active',
            'metadata' => [
                'provider' => 'mock',
                'job_id' => $job->id,
                'note' => 'This is a generated placeholder for validating the WebCatalogue 3D workflow.',
            ],
        ]);

        $ar = Resource::create([
            'id_store' => $storeId,
            'id_product' => $productId,
            'resource_owner_type' => '3d_generation_job',
            'resource_owner_id' => $job->id,
            'resource_type' => 'ar_file',
            'title' => 'AR-ready GLB · Job #' . $job->id,
            'description' => 'AR placeholder using the generated GLB. USDZ export can be added later for iOS Quick Look.',
            'source_type' => 'generated',
            'file_path' => $modelPath,
            'public_url' => Storage::disk($disk)->url($modelPath),
            'filename' => $modelFilename,
            'mime_type' => 'model/gltf-binary',
            'file_size' => Storage::disk($disk)->size($modelPath),
            'extension' => 'glb',
            'is_main' => false,
            'sort_order' => 1,
            'status' => 'active',
            'metadata' => [
                'provider' => 'mock',
                'job_id' => $job->id,
                'ar_modes' => ['webxr', 'scene-viewer'],
            ],
        ]);

        $vrFilename = $storeId . '_' . $productId . '_vr_scene_' . $stamp . '.json';
        $vrPath = $base . '/vr/' . $vrFilename;
        Storage::disk($disk)->put($vrPath, json_encode([
            'type' => 'webcatalogue_vr_scene',
            'job_id' => $job->id,
            'product_id' => $productId,
            'model_resource_id' => $model->id,
            'model_url' => Storage::disk($disk)->url($modelPath),
            'environment' => 'default_showroom',
            'created_at' => now()->toDateTimeString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $vr = Resource::create([
            'id_store' => $storeId,
            'id_product' => $productId,
            'resource_owner_type' => '3d_generation_job',
            'resource_owner_id' => $job->id,
            'resource_type' => 'vr_scene',
            'title' => 'VR scene config · Job #' . $job->id,
            'description' => 'Generated VR scene configuration pointing to the generated 3D model.',
            'source_type' => 'generated',
            'file_path' => $vrPath,
            'public_url' => Storage::disk($disk)->url($vrPath),
            'filename' => $vrFilename,
            'mime_type' => 'application/json',
            'file_size' => Storage::disk($disk)->size($vrPath),
            'extension' => 'json',
            'is_main' => false,
            'sort_order' => 2,
            'status' => 'active',
            'metadata' => [
                'provider' => 'mock',
                'job_id' => $job->id,
                'viewer' => 'webxr_threejs',
            ],
        ]);

        return ['model' => $model, 'ar' => $ar, 'vr' => $vr];
    }

    protected function sendCompletedNotification(ThreeDGenerationJob $job): void
    {
        if (!function_exists('notifications_send')) {
            return;
        }

        notifications_send([
            'title' => 'Modelo 3D concluido',
            'message' => 'A execução do modelo 3D foi concluida em ' . date('y-m-d'),
            'type' => 'info',
            'category' => 'system',
            'priority' => 'normal',
            'channels' => ['internal'],
            'users'    => [1]
        ]);
    }

    protected function mockGlbBase64(): string
    {
        return 'Z2xURgIAAADAAwAAtAIAAEpTT057InNjZW5lIjowLCJzY2VuZXMiOlt7Im5vZGVzIjpbMF19XSwiYXNzZXQiOnsidmVyc2lvbiI6IjIuMCIsImdlbmVyYXRvciI6Imh0dHBzOi8vZ2l0aHViLmNvbS9taWtlZGgvdHJpbWVzaCJ9LCJhY2Nlc3NvcnMiOlt7ImNvbXBvbmVudFR5cGUiOjUxMjUsInR5cGUiOiJTQ0FMQVIiLCJidWZmZXJWaWV3IjowLCJjb3VudCI6MzYsIm1heCI6WzddLCJtaW4iOlswXX0seyJjb21wb25lbnRUeXBlIjo1MTI2LCJ0eXBlIjoiVkVDMyIsImJ5dGVPZmZzZXQiOjAsImJ1ZmZlclZpZXciOjEsImNvdW50Ijo4LCJtYXgiOlswLjUsMC41LDAuNV0sIm1pbiI6Wy0wLjUsLTAuNSwtMC41XX1dLCJtZXNoZXMiOlt7Im5hbWUiOiJnZW9tZXRyeV8wIiwiZXh0cmFzIjp7InNoYXBlIjoiYm94IiwiZXh0ZW50cyI6WzEuMCwxLjAsMS4wXX0sInByaW1pdGl2ZXMiOlt7ImF0dHJpYnV0ZXMiOnsiUE9TSVRJT04iOjF9LCJpbmRpY2VzIjowLCJtb2RlIjo0fV19XSwibm9kZXMiOlt7Im5hbWUiOiJ3b3JsZCIsImNoaWxkcmVuIjpbMV19LHsibmFtZSI6Imdlb21ldHJ5XzAiLCJtZXNoIjowfV0sImJ1ZmZlcnMiOlt7ImJ5dGVMZW5ndGgiOjI0MH1dLCJidWZmZXJWaWV3cyI6W3siYnVmZmVyIjowLCJieXRlT2Zmc2V0IjowLCJieXRlTGVuZ3RoIjoxNDR9LHsiYnVmZmVyIjowLCJieXRlT2Zmc2V0IjoxNDQsImJ5dGVMZW5ndGgiOjk2fV19ICAgIPAAAABCSU4AAQAAAAMAAAAAAAAABAAAAAEAAAAAAAAAAAAAAAMAAAACAAAAAgAAAAQAAAAAAAAAAQAAAAcAAAADAAAABQAAAAEAAAAEAAAABQAAAAcAAAABAAAAAwAAAAcAAAACAAAABgAAAAQAAAACAAAAAgAAAAcAAAAGAAAABgAAAAUAAAAEAAAABwAAAAUAAAAGAAAAAAAAvwAAAL8AAAC/AAAAvwAAAL8AAAA/AAAAvwAAAD8AAAC/AAAAvwAAAD8AAAA/AAAAPwAAAL8AAAC/AAAAPwAAAL8AAAA/AAAAPwAAAD8AAAC/AAAAPwAAAD8AAAA/';
    }
}
