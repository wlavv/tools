<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\PermissionRoleManager\Services\RoutePermissionAccessService;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('permission-role-manager.route_access_enforcement_enabled', true)) {
            return $next($request);
        }

        $allowed = app(RoutePermissionAccessService::class)
            ->canAccessRoute($request->user()?->id, $request->route());

        abort_unless($allowed, 403, 'Sem permissao para aceder a esta rota.');

        return $next($request);
    }
}
