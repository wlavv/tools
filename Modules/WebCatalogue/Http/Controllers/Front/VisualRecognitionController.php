<?php

namespace Modules\WebCatalogue\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\VisualRecognitionSession;
use Modules\WebCatalogue\Services\Recognition\VisualRecognitionService;
use Modules\WebCatalogue\Services\Recognition\InternalImageMatchService;

class VisualRecognitionController extends Controller
{
    public function __construct()
    {
    }

    public function index(string $store_slug): View
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        return view('webcatalogue::front.scan.index', [
            'store' => $store,
            'isGlobalScan' => false,
        ]);
    }

    public function globalIndex(): View
    {
        return view('webcatalogue::front.scan.index', [
            'store' => null,
            'isGlobalScan' => true,
        ]);
    }

    public function session(Request $request, string $store_slug, VisualRecognitionService $service): JsonResponse
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        $session = $service->createSession($store, [
            'device_type' => $request->input('device_type'),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'session_token' => $session->session_token,
            'session_id' => $session->id,
        ]);
    }

    public function globalSession(Request $request, VisualRecognitionService $service): JsonResponse
    {
        $session = $service->createSession(null, [
            'device_type' => $request->input('device_type'),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'session_token' => $session->session_token,
            'session_id' => $session->id,
        ]);
    }

    public function capture(Request $request, string $store_slug, VisualRecognitionService $service): JsonResponse
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        $validated = $request->validate([
            'session_token' => ['required', 'string'],
            'capture_type' => ['nullable', 'string', 'max:60'],
            'photo' => ['nullable', 'image', 'max:8192'],
            'photo_data' => ['nullable', 'string'],
            'frame_index' => ['nullable', 'integer', 'min:1', 'max:10'],
            'frame_count' => ['nullable', 'integer', 'min:1', 'max:10'],
            'detection_source' => ['nullable', 'string', 'max:60'],
            'cropped' => ['nullable', 'boolean'],
            'identifiers' => ['nullable', 'array', 'max:8'],
        ]);

        $session = VisualRecognitionSession::where('session_token', $validated['session_token'])
            ->where('id_store', $store->id)
            ->firstOrFail();

        $captureType = $validated['capture_type'] ?? 'object_photo';
        $captureMetadata = [
            'frame_index' => $validated['frame_index'] ?? null,
            'frame_count' => $validated['frame_count'] ?? null,
            'detection_source' => $validated['detection_source'] ?? null,
            'cropped_client_side' => (bool) ($validated['cropped'] ?? false),
            'identifiers' => $this->cleanDetectedIdentifiers($validated['identifiers'] ?? []),
        ];

        if ($request->hasFile('photo')) {
            $capture = $service->storeCapture($session, $request->file('photo'), $captureType, $captureMetadata);
        } elseif (!empty($validated['photo_data'])) {
            $capture = $service->storeCapture($session, $validated['photo_data'], $captureType, $captureMetadata);
        } else {
            return response()->json(['ok' => false, 'message' => 'No image received.'], 422);
        }

        return response()->json([
            'ok' => true,
            'capture_id' => $capture->id,
            'capture_url' => $capture->resolved_url,
        ]);
    }

    public function globalCapture(Request $request, VisualRecognitionService $service): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'string'],
            'capture_type' => ['nullable', 'string', 'max:60'],
            'photo' => ['nullable', 'image', 'max:8192'],
            'photo_data' => ['nullable', 'string'],
            'frame_index' => ['nullable', 'integer', 'min:1', 'max:10'],
            'frame_count' => ['nullable', 'integer', 'min:1', 'max:10'],
            'detection_source' => ['nullable', 'string', 'max:60'],
            'cropped' => ['nullable', 'boolean'],
            'identifiers' => ['nullable', 'array', 'max:8'],
        ]);

        $session = VisualRecognitionSession::where('session_token', $validated['session_token'])
            ->whereNull('id_store')
            ->firstOrFail();

        $captureType = $validated['capture_type'] ?? 'object_photo';
        $captureMetadata = [
            'frame_index' => $validated['frame_index'] ?? null,
            'frame_count' => $validated['frame_count'] ?? null,
            'detection_source' => $validated['detection_source'] ?? null,
            'cropped_client_side' => (bool) ($validated['cropped'] ?? false),
            'identifiers' => $this->cleanDetectedIdentifiers($validated['identifiers'] ?? []),
        ];

        if ($request->hasFile('photo')) {
            $capture = $service->storeCapture($session, $request->file('photo'), $captureType, $captureMetadata);
        } elseif (!empty($validated['photo_data'])) {
            $capture = $service->storeCapture($session, $validated['photo_data'], $captureType, $captureMetadata);
        } else {
            return response()->json(['ok' => false, 'message' => 'No image received.'], 422);
        }

        return response()->json([
            'ok' => true,
            'capture_id' => $capture->id,
            'capture_url' => $capture->resolved_url,
        ]);
    }

    public function match(Request $request, string $store_slug, InternalImageMatchService $matcher): JsonResponse
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        $validated = $request->validate([
            'session_token' => ['required', 'string'],
        ]);

        $session = VisualRecognitionSession::with('captures')
            ->where('session_token', $validated['session_token'])
            ->where('id_store', $store->id)
            ->firstOrFail();

        $result = $matcher->matchSession($session, $store);

        return response()->json([
            'ok' => true,
            'matched' => (bool) ($result['matched'] ?? false),
            'auto_match' => $result['auto_match'] ?? null,
            'suggestions' => $result['suggestions'] ?? [],
            'message' => $result['message'] ?? 'Recognition completed.',
            'product_url' => $result['auto_match']['product_url'] ?? null,
        ]);
    }

    public function globalMatch(Request $request, InternalImageMatchService $matcher): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'string'],
        ]);

        $session = VisualRecognitionSession::with('captures')
            ->where('session_token', $validated['session_token'])
            ->whereNull('id_store')
            ->firstOrFail();

        $result = $matcher->matchGlobalSession($session);

        return response()->json([
            'ok' => true,
            'matched' => (bool) ($result['matched'] ?? false),
            'auto_match' => $result['auto_match'] ?? null,
            'suggestions' => $result['suggestions'] ?? [],
            'message' => $result['message'] ?? 'Recognition completed.',
            'product_url' => null,
            'result_url' => route('webcatalogue.front.scan.global.result', $session->session_token),
        ]);
    }

    public function unmatched(Request $request, string $store_slug, VisualRecognitionService $service): JsonResponse|RedirectResponse
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        $validated = $request->validate([
            'session_token' => ['required', 'string'],
            'brand' => ['nullable', 'string', 'max:190'],
            'model' => ['nullable', 'string', 'max:190'],
            'reference' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'customer_email' => ['nullable', 'email', 'max:190'],
            'label_photo' => ['nullable', 'image', 'max:8192'],
        ]);

        $session = VisualRecognitionSession::where('session_token', $validated['session_token'])
            ->where('id_store', $store->id)
            ->firstOrFail();

        if ($request->hasFile('label_photo')) {
            $labelCapture = $service->storeCapture($session, $request->file('label_photo'), 'label_photo');
            $validated['label_photo_path'] = $labelCapture->file_path;
        }

        $lead = $service->createUnmatchedLead($session, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'lead_id' => $lead->id,
                'message' => 'Thank you. This product request has been submitted.',
            ]);
        }

        return redirect()
            ->route('webcatalogue.front.scan.result', [$store->slug, $session->session_token])
            ->with('success', 'Obrigado. O pedido foi registado.');
    }

    public function globalUnmatched(Request $request, VisualRecognitionService $service): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'string'],
            'brand' => ['nullable', 'string', 'max:190'],
            'model' => ['nullable', 'string', 'max:190'],
            'reference' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'customer_email' => ['nullable', 'email', 'max:190'],
            'label_photo' => ['nullable', 'image', 'max:8192'],
        ]);

        $session = VisualRecognitionSession::where('session_token', $validated['session_token'])
            ->whereNull('id_store')
            ->firstOrFail();

        if ($request->hasFile('label_photo')) {
            $labelCapture = $service->storeCapture($session, $request->file('label_photo'), 'label_photo');
            $validated['label_photo_path'] = $labelCapture->file_path;
        }

        $lead = $service->createUnmatchedLead($session, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'lead_id' => $lead->id,
                'message' => 'Thank you. This product request has been submitted.',
            ]);
        }

        return redirect()
            ->route('webcatalogue.front.scan.global.result', $session->session_token)
            ->with('success', 'Obrigado. O pedido foi registado.');
    }

    public function result(string $store_slug, string $session_token): View
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();
        $session = VisualRecognitionSession::with([
            'lead',
            'captures',
            'matches' => fn ($query) => $query->with('product.store', 'product.resources')->orderBy('rank'),
            'product' => fn ($query) => $query->with(['store', 'resources', 'prices', 'catalogues']),
        ])
            ->where('session_token', $session_token)
            ->where('id_store', $store->id)
            ->firstOrFail();

        return $this->renderResult($store, $session, false);
    }

    public function globalResult(string $session_token): View
    {
        $session = VisualRecognitionSession::with([
            'lead',
            'captures',
            'matches' => fn ($query) => $query->with('product.store', 'product.resources')->orderBy('rank'),
            'product' => fn ($query) => $query->with(['store', 'resources', 'prices', 'catalogues']),
        ])
            ->where('session_token', $session_token)
            ->whereNull('id_store')
            ->firstOrFail();

        return $this->renderResult(null, $session, true);
    }

    private function renderResult(?Store $store, VisualRecognitionSession $session, bool $isGlobalScan = false): View
    {
        $product = $session->product?->loadMissing(['store', 'resources', 'prices', 'catalogues']);
        $sourceStore = $store ?: $product?->store;
        $resources = $product?->resources
            ? $product->resources->whereNotIn('status', ['deleted', 'disabled', 'inactive'])->sortBy([['is_main', 'desc'], ['sort_order', 'asc'], ['id', 'asc']])->values()
            : collect();
        $images = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['image', 'gallery_image', 'thumbnail', 'cover'], true))->values();
        $documents = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['manual', 'datasheet', 'assembly_instructions', 'download', 'safety_sheet', 'spec_sheet'], true))->values();
        $assembly = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['assembly_instructions', 'how_to', 'how_to_video', 'troubleshooting'], true))->values();
        $videos = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['video', 'how_to_video', 'review_video'], true))->values();
        $immersive = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['model_3d', 'ar_file', 'vr_file', 'vr_scene'], true))->values();
        $thumbnail = $images->firstWhere('is_main', true) ?: $images->first();
        $activePrice = $product?->prices
            ? $product->prices->whereIn('status', ['active', 'published'])->sortByDesc('id')->first()
            : null;
        $purchaseUrl = $this->purchaseUrl($product);

        return view('webcatalogue::front.scan.result', [
            'store' => $sourceStore,
            'scanStore' => $store,
            'isGlobalScan' => $isGlobalScan,
            'session' => $session,
            'product' => $product,
            'resources' => $resources,
            'images' => $images,
            'documents' => $documents,
            'assembly' => $assembly,
            'videos' => $videos,
            'immersive' => $immersive,
            'thumbnail' => $thumbnail,
            'activePrice' => $activePrice,
            'purchaseUrl' => $purchaseUrl,
            'suggestions' => $session->matches
                ->where('status', 'suggested')
                ->filter(fn ($match) => $match->product && $match->product->store && in_array((string) $match->product->status, config('webcatalogue.front_visible_statuses', ['published', 'active']), true))
                ->take(3)
                ->values(),
        ]);
    }

    private function purchaseUrl($product): ?string
    {
        if (!$product) {
            return null;
        }

        $metadata = is_array($product->metadata ?? null) ? $product->metadata : [];
        foreach (['purchase_url', 'buy_url', 'product_url', 'external_url', 'source_url'] as $key) {
            if (!empty($metadata[$key])) {
                return (string) $metadata[$key];
            }
        }

        return null;
    }

    private function cleanDetectedIdentifiers(array $identifiers): array
    {
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
                'format' => mb_substr(trim((string) ($identifier['format'] ?? 'unknown')) ?: 'unknown', 0, 60),
                'rawValue' => mb_substr($value, 0, 500),
                'source' => mb_substr(trim((string) ($identifier['source'] ?? 'client_barcode_detector')) ?: 'client_barcode_detector', 0, 80),
            ];
        }

        return array_slice($clean, 0, 8);
    }
}
