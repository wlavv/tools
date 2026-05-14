<?php

namespace Modules\ErrorCenter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\ErrorCenter\Models\ErrorEvent;
use Modules\ErrorCenter\Models\ErrorOccurrence;

class ErrorCenterController extends Controller
{
    public function index()
    {
        return $this->view('error-center::index');
    }

    public function show(ErrorEvent $errorEvent)
    {
        return $this->view('error-center::show', [
            'errorEvent' => $errorEvent,
        ]);
    }

    public function stats(): JsonResponse
    {
        $topModules = ErrorEvent::query()
            ->select('module', DB::raw('SUM(occurrence_count) as count'))
            ->where('last_seen_at', '>=', now()->subDays(7))
            ->groupBy('module')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn (ErrorEvent $event): array => [
                'module' => $event->module ?: 'unknown',
                'count' => (int) $event->count,
            ])
            ->values();

        return response()->json([
            'total_open' => ErrorEvent::query()->open()->count(),
            'new_today' => ErrorEvent::query()
                ->where('status', ErrorEvent::STATUS_NEW)
                ->where('created_at', '>=', now()->startOfDay())
                ->count(),
            'critical_open' => ErrorEvent::query()
                ->open()
                ->where('severity', ErrorEvent::SEVERITY_CRITICAL)
                ->count(),
            'resolved_this_week' => ErrorEvent::query()
                ->where('status', ErrorEvent::STATUS_RESOLVED)
                ->where('resolved_at', '>=', now()->startOfWeek())
                ->count(),
            'top_modules' => $topModules,
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = ErrorEvent::query();
        $this->applyEventFilters($query, $request);

        $paginator = $query
            ->orderByDesc('last_seen_at')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'data' => collect($paginator->items())->map(fn (ErrorEvent $event): array => $this->serializeEvent($event))->values(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function eventDetail(ErrorEvent $errorEvent): JsonResponse
    {
        $latestOccurrence = $errorEvent->occurrences()
            ->orderByDesc('occurred_at')
            ->first();

        $recentOccurrences = $errorEvent->occurrences()
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get()
            ->map(fn (ErrorOccurrence $occurrence): array => $this->serializeOccurrence($occurrence, includeTechnicalData: false));

        return response()->json([
            'data' => array_merge($this->serializeEvent($errorEvent), [
                'hash' => $errorEvent->hash,
                'assigned_to' => $errorEvent->assigned_to,
                'resolved_at' => optional($errorEvent->resolved_at)->toISOString(),
                'resolved_by' => $errorEvent->resolved_by,
                'last_notification_sent_at' => optional($errorEvent->last_notification_sent_at)->toISOString(),
                'notification_count' => $errorEvent->notification_count,
                'last_notification_event' => $errorEvent->last_notification_event,
                'latest_occurrence' => $latestOccurrence
                    ? $this->serializeOccurrence($latestOccurrence, includeTechnicalData: true)
                    : null,
                'recent_occurrences' => $recentOccurrences,
            ]),
        ]);
    }

    public function occurrences(Request $request, ErrorEvent $errorEvent): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = $errorEvent->occurrences()->orderByDesc('occurred_at');

        foreach (['user_id', 'request_id', 'correlation_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->query($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->where('occurred_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('occurred_at', '<=', $request->query('date_to'));
        }

        $paginator = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => collect($paginator->items())->map(fn (ErrorOccurrence $occurrence): array => $this->serializeOccurrence($occurrence, includeTechnicalData: true))->values(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function updateStatus(Request $request, ErrorEvent $errorEvent): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(ErrorEvent::statuses())],
        ]);

        $updates = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === ErrorEvent::STATUS_RESOLVED) {
            $updates['resolved_at'] = now();
            $updates['resolved_by'] = $this->currentUserId($request);
        } elseif ($errorEvent->status === ErrorEvent::STATUS_RESOLVED) {
            $updates['resolved_at'] = null;
            $updates['resolved_by'] = null;
        }

        $errorEvent->forceFill($updates)->save();

        return response()->json([
            'data' => $this->serializeEvent($errorEvent->fresh() ?: $errorEvent),
        ]);
    }

    public function resolve(Request $request, ErrorEvent $errorEvent): JsonResponse
    {
        $errorEvent->forceFill([
            'status' => ErrorEvent::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolved_by' => $this->currentUserId($request),
        ])->save();

        return response()->json([
            'data' => $this->serializeEvent($errorEvent->fresh() ?: $errorEvent),
        ]);
    }

    public function ignore(ErrorEvent $errorEvent): JsonResponse
    {
        $errorEvent->forceFill([
            'status' => ErrorEvent::STATUS_IGNORED,
        ])->save();

        return response()->json([
            'data' => $this->serializeEvent($errorEvent->fresh() ?: $errorEvent),
        ]);
    }

    private function applyEventFilters($query, Request $request): void
    {
        foreach (['status', 'severity', 'module', 'environment', 'source'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->query($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->where('last_seen_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('last_seen_at', '<=', $request->query('date_to'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));

            $query->where(function ($inner) use ($search): void {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('error_type', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('hash', 'like', "%{$search}%");
            });
        }
    }

    private function serializeEvent(ErrorEvent $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'message' => $event->message,
            'error_type' => $event->error_type,
            'severity' => $event->severity,
            'status' => $event->status,
            'module' => $event->module,
            'source' => $event->source,
            'environment' => $event->environment,
            'occurrence_count' => $event->occurrence_count,
            'affected_users_count' => $event->affected_users_count,
            'first_seen_at' => optional($event->first_seen_at)->toISOString(),
            'last_seen_at' => optional($event->last_seen_at)->toISOString(),
            'url' => route(config('error-center.route_name_prefix', 'error-center.') . 'show', ['errorEvent' => $event->id]),
        ];
    }

    private function serializeOccurrence(ErrorOccurrence $occurrence, bool $includeTechnicalData): array
    {
        $data = [
            'id' => $occurrence->id,
            'error_event_id' => $occurrence->error_event_id,
            'occurred_at' => optional($occurrence->occurred_at)->toISOString(),
            'user_id' => $occurrence->user_id,
            'tenant_id' => $occurrence->tenant_id,
            'request_id' => $occurrence->request_id,
            'correlation_id' => $occurrence->correlation_id,
            'endpoint' => $occurrence->endpoint,
            'http_method' => $occurrence->http_method,
            'status_code' => $occurrence->status_code,
            'ip_address' => $occurrence->ip_address,
            'user_agent' => $occurrence->user_agent,
        ];

        if ($includeTechnicalData) {
            $data['stack_trace'] = $occurrence->stack_trace;
            $data['payload_snapshot'] = $occurrence->payload_snapshot;
            $data['context_json'] = $occurrence->context_json;
        }

        return $data;
    }

    private function currentUserId(Request $request): ?string
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'getAuthIdentifier')) {
            return (string) $user->getAuthIdentifier();
        }

        return isset($user->id) ? (string) $user->id : null;
    }
}
