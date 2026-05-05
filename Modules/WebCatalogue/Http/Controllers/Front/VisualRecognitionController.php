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
    public function index(string $store_slug): View
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        return view('webcatalogue::front.scan.index', [
            'store' => $store,
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

    public function capture(Request $request, string $store_slug, VisualRecognitionService $service): JsonResponse
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        $validated = $request->validate([
            'session_token' => ['required', 'string'],
            'capture_type' => ['nullable', 'string', 'max:60'],
            'photo' => ['nullable', 'image', 'max:8192'],
            'photo_data' => ['nullable', 'string'],
        ]);

        $session = VisualRecognitionSession::where('session_token', $validated['session_token'])
            ->where('id_store', $store->id)
            ->firstOrFail();

        $captureType = $validated['capture_type'] ?? 'object_photo';

        if ($request->hasFile('photo')) {
            $capture = $service->storeCapture($session, $request->file('photo'), $captureType);
        } elseif (!empty($validated['photo_data'])) {
            $capture = $service->storeCapture($session, $validated['photo_data'], $captureType);
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

    public function result(string $store_slug, string $session_token): View
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();
        $session = VisualRecognitionSession::with(['lead', 'captures', 'product'])
            ->where('session_token', $session_token)
            ->where('id_store', $store->id)
            ->firstOrFail();

        return view('webcatalogue::front.scan.result', [
            'store' => $store,
            'session' => $session,
        ]);
    }
}
