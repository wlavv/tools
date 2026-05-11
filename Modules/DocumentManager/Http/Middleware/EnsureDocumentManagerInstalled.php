<?php

namespace Modules\DocumentManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\DocumentManager\Support\DocumentTable;

class EnsureDocumentManagerInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(DocumentTable::missingTables()) && !$request->routeIs('document-manager.diagnostics.index')) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'DocumentManager esta em safe mode porque existem tabelas em falta.');
        }

        return $next($request);
    }
}
