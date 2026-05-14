<?php

namespace Modules\ErrorCenter\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RequestContextFactory
{
    public static function fromRequest(Request $request, ?Throwable $throwable = null): array
    {
        $route = $request->route();
        $routeAction = is_object($route) ? $route->getAction() : [];

        return [
            'module' => self::resolveModule($request, $routeAction),
            'source' => 'backend',
            'environment' => app()->environment(),

            'user_id' => self::resolveUserId($request),
            'tenant_id' => self::resolveTenantId($request),

            'request_id' => self::resolveRequestId($request),
            'correlation_id' => $request->headers->get('X-Correlation-ID'),

            'endpoint' => '/' . ltrim($request->path(), '/'),
            'http_method' => $request->method(),
            'status_code' => self::resolveStatusCode($throwable),

            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),

            'payload' => self::safePayload($request),
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'params' => self::routeParameters($request),

            'extra' => [
                'route_name' => is_object($route) ? $route->getName() : null,
                'controller' => Arr::get($routeAction, 'controller'),
                'middleware' => Arr::get($routeAction, 'middleware'),
                'captured_by' => 'error_center_middleware',
            ],
        ];
    }

    private static function resolveModule(Request $request, array $routeAction): string
    {
        $controller = (string) Arr::get($routeAction, 'controller', '');

        if (preg_match('/Modules\\\\([^\\\\]+)\\\\/', $controller, $matches)) {
            return Str::kebab($matches[1]);
        }

        $firstSegment = $request->segment(1);

        if ($firstSegment !== null && $firstSegment !== '') {
            return Str::kebab($firstSegment);
        }

        return 'application';
    }

    private static function resolveUserId(Request $request): ?string
    {
        try {
            $user = $request->user();

            if ($user === null) {
                return null;
            }

            if (method_exists($user, 'getAuthIdentifier')) {
                return (string) $user->getAuthIdentifier();
            }

            return isset($user->id) ? (string) $user->id : null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function resolveTenantId(Request $request): ?string
    {
        try {
            if ($request->attributes->has('tenant')) {
                $tenant = $request->attributes->get('tenant');
                return self::extractId($tenant);
            }

            if (property_exists($request, 'tenant') && $request->tenant) {
                return self::extractId($request->tenant);
            }

            if (app()->bound('currentTenant')) {
                return self::extractId(app('currentTenant'));
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private static function extractId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_object($value)) {
            if (method_exists($value, 'getKey')) {
                return (string) $value->getKey();
            }

            if (isset($value->id)) {
                return (string) $value->id;
            }
        }

        if (is_array($value) && isset($value['id'])) {
            return (string) $value['id'];
        }

        return null;
    }

    private static function resolveRequestId(Request $request): string
    {
        return (string) (
            $request->headers->get('X-Request-ID')
            ?: $request->headers->get('X-Request-Id')
            ?: $request->headers->get('Request-ID')
            ?: Str::uuid()
        );
    }

    private static function resolveStatusCode(?Throwable $throwable): int
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->getStatusCode();
        }

        return 500;
    }

    private static function safePayload(Request $request): array
    {
        if (Str::startsWith((string) $request->headers->get('Content-Type'), 'multipart/form-data')) {
            return [
                '_notice' => 'multipart/form-data payload omitted',
            ];
        }

        return $request->all();
    }

    private static function routeParameters(Request $request): array
    {
        try {
            return $request->route()?->parameters() ?? [];
        } catch (Throwable) {
            return [];
        }
    }
}
