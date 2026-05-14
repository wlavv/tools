<?php

namespace Modules\WebCatalogue\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\SessionLog;

class SessionLogApiController extends Controller
{
    public function __construct()
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => ['required', 'string', 'max:120'],
            'session_token' => ['nullable', 'string', 'max:120'],
            'id_store' => ['nullable', 'integer'],
            'id_product' => ['nullable', 'integer'],
            'url' => ['nullable', 'string', 'max:2000'],
            'ts' => ['nullable'],
        ]);

        $payload = $request->except(['event', 'session_token', 'id_store', 'id_product', 'url']);

        $log = SessionLog::create([
            'id_store' => $validated['id_store'] ?? null,
            'id_product' => $validated['id_product'] ?? null,
            'session_token' => $validated['session_token'] ?? null,
            'event' => $validated['event'],
            'url' => $validated['url'] ?? $request->headers->get('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
        ]);

        return response()->json([
            'status' => 'accepted',
            'id' => $log->id,
        ], 202);
    }
}
