<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;

class OpenCvRecognitionClient
{
    public function enabled(): bool
    {
        return (bool) config('webcatalogue.recognition.opencv.enabled')
            && filled(config('webcatalogue.recognition.opencv.base_url'));
    }

    public function normalizeCapture(VisualRecognitionCapture $capture): ?array
    {
        if (!$this->enabled() || !$capture->file_path || !Storage::disk('public')->exists($capture->file_path)) {
            return null;
        }

        try {
            $baseUrl = rtrim((string) config('webcatalogue.recognition.opencv.base_url'), '/');
            $token = config('webcatalogue.recognition.opencv.token');
            $timeout = (int) config('webcatalogue.recognition.opencv.timeout', 20);
            $path = Storage::disk('public')->path($capture->file_path);
            $request = Http::timeout($timeout)
                ->acceptJson();

            if (filled($token)) {
                $request = $request->withToken((string) $token);
            }

            $response = $request
                ->attach('image', fopen($path, 'rb'), basename($path))
                ->post($baseUrl . '/recognition/normalize', [
                    'mode' => 'rectangular_object',
                    'debug' => (bool) config('webcatalogue.recognition.opencv.store_debug_image', true) ? '1' : '0',
                ]);

            if (!$response->ok()) {
                $this->rememberFailure($capture, 'http_' . $response->status());
                return null;
            }

            $payload = $response->json();
            if (!is_array($payload) || empty($payload['ok']) || empty($payload['normalized_image_base64'])) {
                $this->rememberFailure($capture, 'invalid_response');
                return null;
            }

            $directory = trim(dirname($capture->file_path), '/') . '/analysis';
            $suffix = Str::lower(Str::random(6));
            $normalizedPath = $directory . '/' . pathinfo($capture->file_path, PATHINFO_FILENAME) . '_opencv_normalized_' . $suffix . '.jpg';
            Storage::disk('public')->put($normalizedPath, base64_decode((string) $payload['normalized_image_base64']));

            $debugPath = null;
            if (!empty($payload['debug_image_base64'])) {
                $debugPath = $directory . '/' . pathinfo($capture->file_path, PATHINFO_FILENAME) . '_opencv_debug_' . $suffix . '.jpg';
                Storage::disk('public')->put($debugPath, base64_decode((string) $payload['debug_image_base64']));
            }

            $metadata = $capture->metadata ?: [];
            $capture->update([
                'metadata' => array_replace_recursive($metadata, [
                    'opencv_analysis' => [
                        'ok' => true,
                        'provider' => 'opencv_microservice',
                        'normalized_path' => $normalizedPath,
                        'normalized_url' => Storage::disk('public')->url($normalizedPath),
                        'debug_path' => $debugPath,
                        'debug_url' => $debugPath ? Storage::disk('public')->url($debugPath) : null,
                        'contour' => $payload['contour'] ?? null,
                        'confidence' => $payload['confidence'] ?? null,
                        'mode' => $payload['mode'] ?? 'rectangular_object',
                        'generated_at' => now()->toIso8601String(),
                    ],
                ]),
            ]);

            return [
                'normalized_path' => $normalizedPath,
                'normalized_url' => Storage::disk('public')->url($normalizedPath),
                'debug_path' => $debugPath,
                'debug_url' => $debugPath ? Storage::disk('public')->url($debugPath) : null,
                'payload' => $payload,
            ];
        } catch (\Throwable $exception) {
            $this->rememberFailure($capture, class_basename($exception));
            return null;
        }
    }

    public function extractMarkers(string $publicPath, int $maxMarkers = 250): ?array
    {
        if (!$this->enabled() || !Storage::disk('public')->exists($publicPath)) {
            return null;
        }

        try {
            $baseUrl = rtrim((string) config('webcatalogue.recognition.opencv.base_url'), '/');
            $token = config('webcatalogue.recognition.opencv.token');
            $timeout = (int) config('webcatalogue.recognition.opencv.timeout', 20);
            $path = Storage::disk('public')->path($publicPath);
            $request = Http::timeout($timeout)->acceptJson();

            if (filled($token)) {
                $request = $request->withToken((string) $token);
            }

            $response = $request
                ->attach('image', fopen($path, 'rb'), basename($path))
                ->post($baseUrl . '/recognition/markers', [
                    'max_markers' => (string) max(20, min(1000, $maxMarkers)),
                ]);

            if (!$response->ok()) {
                return null;
            }

            $payload = $response->json();
            return is_array($payload) && !empty($payload['ok']) ? $payload : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function rememberFailure(VisualRecognitionCapture $capture, string $reason): void
    {
        $metadata = $capture->metadata ?: [];
        $capture->update([
            'metadata' => array_replace_recursive($metadata, [
                'opencv_analysis' => [
                    'ok' => false,
                    'provider' => 'opencv_microservice',
                    'error' => $reason,
                    'attempted_at' => now()->toIso8601String(),
                ],
            ]),
        ]);
    }
}
