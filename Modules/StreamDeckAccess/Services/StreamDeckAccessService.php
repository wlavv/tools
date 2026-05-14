<?php

namespace Modules\StreamDeckAccess\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\StreamDeckAccess\Exceptions\TriggerRejectedException;
use Modules\StreamDeckAccess\Jobs\RunStreamDeckTaskJob;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;
use Modules\StreamDeckAccess\Support\TokenFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StreamDeckAccessService
{
    public function list(array $filters = []): Collection
    {
        return StreamDeckAccessPoint::query()
            ->withCount('logs')
            ->when(($filters['q'] ?? null), function ($query, string $search) {
                $search = '%' . str_replace('%', '\\%', $search) . '%';

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', $search)
                        ->orWhere('slug', 'like', $search)
                        ->orWhere('task_key', 'like', $search)
                        ->orWhere('target_url', 'like', $search);
                });
            })
            ->when(($filters['type'] ?? null), fn ($query, string $type) => $query->where('type', $type))
            ->when(($filters['enabled'] ?? '') !== '', function ($query) use ($filters) {
                $query->where('enabled', filter_var($filters['enabled'], FILTER_VALIDATE_BOOLEAN));
            })
            ->latest('id')
            ->get();
    }

    public function createForUser(?int $userId, array $data): array
    {
        $plainToken = TokenFactory::makePlainToken();
        $payload = $this->payloadForPersist($data);
        $payload['public_id'] = (string) Str::uuid();
        $payload['token_hash'] = TokenFactory::hash($plainToken);
        $payload['token_hint'] = TokenFactory::hint($plainToken);
        $payload['created_by'] = $userId;

        $accessPoint = StreamDeckAccessPoint::query()->create($payload);

        return $this->tokenResponse($accessPoint->fresh(), $plainToken, 'Guarda este token agora. Só é apresentado uma vez; depois só podes rodar o token.');
    }

    public function update(StreamDeckAccessPoint $accessPoint, array $data): StreamDeckAccessPoint
    {
        $accessPoint->update($this->payloadForPersist($data, update: true));

        return $accessPoint->refresh();
    }

    public function delete(StreamDeckAccessPoint $accessPoint): void
    {
        $accessPoint->delete();
    }

    public function rotateToken(StreamDeckAccessPoint $accessPoint): array
    {
        $plainToken = TokenFactory::makePlainToken();

        $accessPoint->forceFill([
            'token_hash' => TokenFactory::hash($plainToken),
            'token_hint' => TokenFactory::hint($plainToken),
            'use_count' => 0,
            'last_used_at' => null,
        ])->save();

        return $this->tokenResponse($accessPoint->fresh(), $plainToken, 'Substitui o link no Stream Deck. O token anterior deixou de funcionar.');
    }

    public function makeExternalUrl(StreamDeckAccessPoint $accessPoint, string $plainToken): string
    {
        return route('streamdeck_access.external.trigger', [
            'identifier' => $accessPoint->public_id,
            config('streamdeck-access.token_parameter', 'token') => $plainToken,
        ]);
    }

    public function trigger(Request $request, string $identifier): Response
    {
        $startedAt = microtime(true);
        $accessPoint = $this->findAccessPoint($identifier);
        $log = $this->createLog($request, $accessPoint);

        if (! $accessPoint) {
            return $this->reject($log, 'Access point not found.', 404, $startedAt);
        }

        try {
            $this->assertCanTrigger($request, $accessPoint);
            $this->registerUse($accessPoint);

            if ($accessPoint->type === 'redirect') {
                return $this->handleRedirect($accessPoint, $log, $startedAt);
            }

            if ($accessPoint->type === 'task') {
                return $this->handleTask($accessPoint, $log, $startedAt);
            }

            return $this->reject($log, 'Unsupported access point type.', 422, $startedAt);
        } catch (TriggerRejectedException $exception) {
            return $this->reject($log, $exception->getMessage(), $exception->httpStatus, $startedAt);
        } catch (Throwable $exception) {
            return $this->reject($log, $exception->getMessage(), 500, $startedAt, 'error');
        }
    }

    protected function payloadForPersist(array $data, bool $update = false): array
    {
        unset($data['payload_json'], $data['allowed_ips_text']);

        foreach (['target_url', 'task_key', 'queue', 'description', 'expires_at'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if (! $update) {
            $data['enabled'] = array_key_exists('enabled', $data) ? (bool) $data['enabled'] : true;
            $data['respond_json'] = array_key_exists('respond_json', $data) ? (bool) $data['respond_json'] : true;
            $data['use_count'] = 0;
        }

        if (array_key_exists('allowed_ips', $data) && $data['allowed_ips'] === []) {
            $data['allowed_ips'] = null;
        }

        if (array_key_exists('payload', $data) && $data['payload'] === []) {
            $data['payload'] = null;
        }

        if (($data['type'] ?? null) === 'redirect') {
            $data['task_key'] = null;
            $data['payload'] = null;
            $data['queue'] = null;
        }

        if (($data['type'] ?? null) === 'task') {
            $data['target_url'] = null;
        }

        return $data;
    }

    protected function tokenResponse(StreamDeckAccessPoint $accessPoint, string $plainToken, string $warning): array
    {
        return [
            'access_point' => $accessPoint,
            'plain_token' => $plainToken,
            'token_hint' => $accessPoint->token_hint,
            'streamdeck_url' => $this->makeExternalUrl($accessPoint, $plainToken),
            'warning' => $warning,
        ];
    }

    protected function findAccessPoint(string $identifier): ?StreamDeckAccessPoint
    {
        return StreamDeckAccessPoint::query()
            ->where('public_id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
    }

    protected function createLog(Request $request, ?StreamDeckAccessPoint $accessPoint): StreamDeckAccessLog
    {
        return StreamDeckAccessLog::query()->create([
            'request_id' => (string) Str::uuid(),
            'streamdeck_access_point_id' => $accessPoint?->id,
            'status' => 'received',
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'referer' => substr((string) $request->headers->get('referer'), 0, 2048),
            'payload_snapshot' => $this->safePayloadSnapshot($request),
        ]);
    }

    protected function safePayloadSnapshot(Request $request): array
    {
        $data = $request->all();
        unset($data[(string) config('streamdeck-access.token_parameter', 'token')]);
        unset($data['token'], $data['password'], $data['secret']);

        return $data;
    }

    protected function assertCanTrigger(Request $request, StreamDeckAccessPoint $accessPoint): void
    {
        $plainToken = $this->extractToken($request);

        if ($plainToken === null || $plainToken === '') {
            throw new TriggerRejectedException('Missing token.', 401);
        }

        if (! $accessPoint->tokenMatches($plainToken)) {
            throw new TriggerRejectedException('Invalid token.', 403);
        }

        if (! $accessPoint->enabled) {
            throw new TriggerRejectedException('Access point disabled.', 403);
        }

        if ($accessPoint->expires_at && now()->greaterThan($accessPoint->expires_at)) {
            throw new TriggerRejectedException('Access point expired.', 403);
        }

        if ($accessPoint->max_uses !== null && $accessPoint->use_count >= $accessPoint->max_uses) {
            throw new TriggerRejectedException('Usage limit reached.', 403);
        }

        if ($accessPoint->cooldown_seconds > 0 && $accessPoint->last_used_at) {
            $elapsed = now()->diffInSeconds($accessPoint->last_used_at);

            if ($elapsed < $accessPoint->cooldown_seconds) {
                throw new TriggerRejectedException('Access point is in cooldown.', 429);
            }
        }

        $rules = array_values(array_filter(array_merge(
            config('streamdeck-access.allowed_ips', []),
            $accessPoint->allowed_ips ?? []
        )));

        if ($rules !== [] && ! $this->ipIsAllowed((string) $request->ip(), $rules)) {
            throw new TriggerRejectedException('IP address not allowed.', 403);
        }
    }

    protected function extractToken(Request $request): ?string
    {
        $parameter = (string) config('streamdeck-access.token_parameter', 'token');
        $queryToken = $request->query($parameter) ?? $request->input($parameter);

        if (is_string($queryToken) && $queryToken !== '') {
            return $queryToken;
        }

        $bearer = $request->bearerToken();

        return is_string($bearer) && $bearer !== '' ? $bearer : null;
    }

    protected function ipIsAllowed(string $ip, array $rules): bool
    {
        foreach ($rules as $rule) {
            $rule = trim((string) $rule);

            if ($rule === '') {
                continue;
            }

            if ($rule === $ip || Str::is($rule, $ip)) {
                return true;
            }

            if (str_contains($rule, '/') && $this->cidrMatches($ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    protected function cidrMatches(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = array_pad(explode('/', $cidr, 2), 2, null);

        if ($mask === null || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false || filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        $mask = (int) $mask;

        if ($mask < 0 || $mask > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = $mask === 0 ? 0 : -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    protected function registerUse(StreamDeckAccessPoint $accessPoint): void
    {
        DB::transaction(function () use ($accessPoint): void {
            $fresh = StreamDeckAccessPoint::query()->lockForUpdate()->findOrFail($accessPoint->id);

            if (! $fresh->enabled) {
                throw new TriggerRejectedException('Access point disabled.', 403);
            }

            if ($fresh->expires_at && now()->greaterThan($fresh->expires_at)) {
                throw new TriggerRejectedException('Access point expired.', 403);
            }

            if ($fresh->max_uses !== null && $fresh->use_count >= $fresh->max_uses) {
                throw new TriggerRejectedException('Usage limit reached.', 403);
            }

            if ($fresh->cooldown_seconds > 0 && $fresh->last_used_at) {
                $elapsed = now()->diffInSeconds($fresh->last_used_at);

                if ($elapsed < $fresh->cooldown_seconds) {
                    throw new TriggerRejectedException('Access point is in cooldown.', 429);
                }
            }

            $fresh->use_count++;
            $fresh->last_used_at = now();
            $fresh->save();
        });
    }

    protected function handleRedirect(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log, float $startedAt): RedirectResponse|JsonResponse
    {
        $target = (string) $accessPoint->target_url;

        if ($target === '') {
            return $this->reject($log, 'Missing redirect target URL.', 422, $startedAt);
        }

        if (! $this->isSafeRedirectTarget($target)) {
            return $this->reject($log, 'Invalid redirect target URL.', 422, $startedAt);
        }

        $log->forceFill([
            'status' => 'redirected',
            'http_status' => 302,
            'response' => ['target_url' => $target],
            'response_ms' => $this->durationMs($startedAt),
        ])->save();

        return str_starts_with($target, '/')
            ? redirect()->to($target)
            : redirect()->away($target);
    }

    protected function isSafeRedirectTarget(string $target): bool
    {
        if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return true;
        }

        return filter_var($target, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($target, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    protected function handleTask(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log, float $startedAt): JsonResponse
    {
        if (! $accessPoint->task_key || ! array_key_exists($accessPoint->task_key, config('streamdeck-access.tasks', []))) {
            return $this->reject($log, 'Task is not registered.', 422, $startedAt);
        }

        $pendingDispatch = RunStreamDeckTaskJob::dispatch($accessPoint->id, $log->id);
        $queue = $accessPoint->queue ?: config('streamdeck-access.default_queue');

        if ($queue) {
            $pendingDispatch->onQueue($queue);
        }

        $payload = [
            'status' => 'queued',
            'request_id' => $log->request_id,
            'access_point' => [
                'public_id' => $accessPoint->public_id,
                'slug' => $accessPoint->slug,
                'name' => $accessPoint->name,
                'task_key' => $accessPoint->task_key,
            ],
        ];

        $log->forceFill([
            'status' => 'queued',
            'http_status' => 202,
            'response' => $payload,
            'response_ms' => $this->durationMs($startedAt),
        ])->save();

        return response()->json($payload, 202);
    }

    protected function reject(
        StreamDeckAccessLog $log,
        string $message,
        int $httpStatus,
        float $startedAt,
        string $status = 'rejected'
    ): JsonResponse {
        $payload = [
            'status' => $status,
            'message' => $message,
            'request_id' => $log->request_id,
        ];

        $log->forceFill([
            'status' => $status,
            'http_status' => $httpStatus,
            'response' => $payload,
            'error' => $message,
            'response_ms' => $this->durationMs($startedAt),
        ])->save();

        return response()->json($payload, $httpStatus);
    }

    protected function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
