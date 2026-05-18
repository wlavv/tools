<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\BrandProspectLead;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\UnmatchedProductLead;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class VisualRecognitionService
{
    public function createSession(?Store $store = null, array $context = []): VisualRecognitionSession
    {
        return VisualRecognitionSession::create([
            'id_store' => $store?->id,
            'session_token' => (string) Str::uuid(),
            'device_type' => $context['device_type'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
            'ip_address' => $context['ip_address'] ?? null,
            'status' => 'started',
            'metadata' => array_merge($context, [
                'scan_scope' => $store ? 'store' : 'global',
            ]),
        ]);
    }

    public function storeCapture(VisualRecognitionSession $session, UploadedFile|string $file, string $captureType = 'object_photo', array $metadata = []): VisualRecognitionCapture
    {
        $directory = $session->id_store
            ? 'webcatalogue/stores/' . (int) $session->id_store . '/recognition/sessions/' . (int) $session->id . '/captures'
            : 'webcatalogue/global/recognition/sessions/' . (int) $session->id . '/captures';
        $mimeType = null;
        $size = null;

        if ($file instanceof UploadedFile) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = $session->id . '_' . $captureType . '_' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(6)) . '.' . $ext;
            $path = $file->storeAs($directory, $filename, 'public');
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();
        } else {
            $path = $this->storeBase64Image($file, $directory, $session->id . '_' . $captureType . '_' . now()->format('Ymd_His') . '.jpg');
            $mimeType = 'image/jpeg';
            $size = Storage::disk('public')->size($path);
        }

        $capture = VisualRecognitionCapture::create([
            'id_session' => $session->id,
            'id_store' => $session->id_store,
            'capture_type' => $captureType,
            'file_path' => $path,
            'public_url' => Storage::disk('public')->url($path),
            'mime_type' => $mimeType,
            'file_size' => $size,
            'status' => 'stored',
            'metadata' => $metadata,
        ]);

        $metadata = $session->metadata ?: [];
        $detectedIdentifiers = $this->detectedIdentifiersFromMetadata($capture->metadata ?: []);
        if ($detectedIdentifiers) {
            $metadata['detected_identifiers'] = $this->mergeDetectedIdentifiers(
                $metadata['detected_identifiers'] ?? [],
                $detectedIdentifiers
            );
        }

        if ($captureType === 'object_photo' && empty($metadata['object_photo_path'])) {
            $metadata['object_photo_path'] = $path;
        }

        if ($detectedIdentifiers || ($captureType === 'object_photo' && empty(($session->metadata ?: [])['object_photo_path']))) {
            $session->update(['metadata' => $metadata]);
        }

        return $capture;
    }

    public function createUnmatchedLead(VisualRecognitionSession $session, array $data): UnmatchedProductLead
    {
        $brand = trim((string)($data['brand'] ?? ''));

        $lead = UnmatchedProductLead::create([
            'id_session' => $session->id,
            'id_store' => $session->id_store,
            'brand' => $brand ?: null,
            'model' => $data['model'] ?? null,
            'reference' => $data['reference'] ?? null,
            'description' => $data['description'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'label_photo_path' => $data['label_photo_path'] ?? null,
            'object_photo_path' => $data['object_photo_path'] ?? ($session->metadata['object_photo_path'] ?? null),
            'status' => 'new',
            'lead_score' => $this->calculateLeadScore($data),
            'metadata' => [
                'source' => 'front_visual_recognition',
                'submitted_at' => now()->toDateTimeString(),
            ],
        ]);

        $session->update(['status' => 'unmatched_lead_created']);

        if ($brand !== '') {
            $prospect = BrandProspectLead::firstOrCreate(
                ['brand' => $brand],
                ['status' => 'new', 'total_requests' => 0]
            );

            $prospect->update([
                'total_requests' => (int)$prospect->total_requests + 1,
                'last_requested_at' => now(),
            ]);
        }

        $this->sendLeadNotification($lead);

        return $lead;
    }

    private function storeBase64Image(string $dataUrl, string $directory, string $filename): string
    {
        if (str_contains($dataUrl, ',')) {
            [, $dataUrl] = explode(',', $dataUrl, 2);
        }
        $binary = base64_decode($dataUrl);
        $path = trim($directory, '/') . '/' . $filename;
        Storage::disk('public')->put($path, $binary);
        return $path;
    }

    private function calculateLeadScore(array $data): int
    {
        $score = 10;
        foreach (['brand', 'model', 'reference', 'description', 'customer_email', 'label_photo_path'] as $field) {
            if (!empty($data[$field])) $score += 10;
        }
        return min($score, 100);
    }

    private function detectedIdentifiersFromMetadata(array $metadata): array
    {
        $identifiers = $metadata['identifiers'] ?? [];
        if (!is_array($identifiers)) {
            return [];
        }

        $clean = [];
        foreach ($identifiers as $identifier) {
            if (!is_array($identifier)) {
                continue;
            }

            $value = trim((string) ($identifier['rawValue'] ?? $identifier['text'] ?? $identifier['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $clean[] = [
                'format' => trim((string) ($identifier['format'] ?? 'unknown')) ?: 'unknown',
                'value' => mb_substr($value, 0, 500),
                'source' => trim((string) ($identifier['source'] ?? 'client_barcode_detector')) ?: 'client_barcode_detector',
                'detected_at' => now()->toIso8601String(),
            ];
        }

        return array_slice($clean, 0, 8);
    }

    private function mergeDetectedIdentifiers(array $existing, array $incoming): array
    {
        $byKey = [];
        foreach (array_merge($existing, $incoming) as $identifier) {
            if (!is_array($identifier)) {
                continue;
            }

            $value = trim((string) ($identifier['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $format = trim((string) ($identifier['format'] ?? 'unknown')) ?: 'unknown';
            $byKey[strtolower($format . ':' . $value)] = [
                'format' => $format,
                'value' => mb_substr($value, 0, 500),
                'source' => $identifier['source'] ?? 'client_barcode_detector',
                'detected_at' => $identifier['detected_at'] ?? now()->toIso8601String(),
            ];
        }

        return array_slice(array_values($byKey), 0, 20);
    }

    private function sendLeadNotification(UnmatchedProductLead $lead): void
    {
        if (!function_exists('notifications_send')) {
            return;
        }

        notifications_send([
            'title' => 'Novo produto não encontrado',
            'message' => 'Um utilizador reportou um produto não existente no WebCatalogue' . ($lead->brand ? ' (' . $lead->brand . ')' : '') . '.',
            'type' => 'info',
            'category' => 'webcatalogue',
            'priority' => 'normal',
            'channels' => ['internal'],
            'users' => [1],
        ]);
    }
}
