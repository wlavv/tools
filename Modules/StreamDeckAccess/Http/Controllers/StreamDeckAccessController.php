<?php

namespace Modules\StreamDeckAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\StreamDeckAccess\Http\Requests\StoreStreamDeckAccessPointRequest;
use Modules\StreamDeckAccess\Http\Requests\UpdateStreamDeckAccessPointRequest;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;
use Modules\StreamDeckAccess\Services\StreamDeckAccessService;

class StreamDeckAccessController extends Controller
{
    public function __construct(protected StreamDeckAccessService $service)
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = [
            'q' => $request->string('q')->toString(),
            'type' => $request->string('type')->toString(),
            'enabled' => $request->has('enabled') ? (string) $request->input('enabled') : '',
        ];

        $accessPoints = $this->service->list($filters);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $accessPoints,
            ]);
        }

        return $this->view('streamdeck-access::Index', [
            'accessPoints' => $accessPoints,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return $this->view('streamdeck-access::pages.form', [
            'accessPoint' => null,
            'action' => route('streamdeck_access.store'),
            'method' => 'POST',
        ]);
    }

    public function store(StoreStreamDeckAccessPointRequest $request): RedirectResponse|JsonResponse
    {
        $result = $this->service->createForUser(
            userId: $request->user()?->id ? (int) $request->user()->id : null,
            data: $request->validated()
        );

        if ($request->expectsJson()) {
            return response()->json($result, 201);
        }

        return redirect()
            ->route('streamdeck_access.show', $result['access_point'])
            ->with('success', 'Access point criado com sucesso.')
            ->with('streamdeck_access_token', $result);
    }

    public function show(Request $request, StreamDeckAccessPoint $accessPoint): View|JsonResponse
    {
        $recentLogs = $accessPoint->logs()->latest('id')->limit(30)->get();

        if ($request->expectsJson()) {
            return response()->json([
                'access_point' => $accessPoint->loadCount('logs'),
                'recent_logs' => $recentLogs,
            ]);
        }

        return $this->view('streamdeck-access::pages.show', [
            'accessPoint' => $accessPoint->loadCount('logs'),
            'recentLogs' => $recentLogs,
        ]);
    }

    public function edit(StreamDeckAccessPoint $accessPoint): View
    {
        return $this->view('streamdeck-access::pages.form', [
            'accessPoint' => $accessPoint,
            'action' => route('streamdeck_access.update', $accessPoint),
            'method' => 'PUT',
        ]);
    }

    public function update(UpdateStreamDeckAccessPointRequest $request, StreamDeckAccessPoint $accessPoint): RedirectResponse|JsonResponse
    {
        $accessPoint = $this->service->update($accessPoint, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'access_point' => $accessPoint,
            ]);
        }

        return redirect()
            ->route('streamdeck_access.show', $accessPoint)
            ->with('success', 'Access point atualizado com sucesso.');
    }

    public function rotateToken(Request $request, StreamDeckAccessPoint $accessPoint): RedirectResponse|JsonResponse
    {
        abort_unless(auth()->check(), 403);

        $result = $this->service->rotateToken($accessPoint);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('streamdeck_access.show', $accessPoint)
            ->with('success', 'Token rodado com sucesso.')
            ->with('streamdeck_access_token', $result);
    }

    public function destroy(Request $request, StreamDeckAccessPoint $accessPoint): RedirectResponse|JsonResponse
    {
        $this->service->delete($accessPoint);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'deleted',
            ]);
        }

        return redirect()
            ->route('streamdeck_access.index')
            ->with('success', 'Access point removido com sucesso.');
    }
}
