<?php

namespace Modules\EnvironmentManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\EnvironmentManager\Services\EnvironmentManagerService;

class EnvironmentManagerController extends Controller
{
    public function __construct(protected EnvironmentManagerService $service)
    {
        parent::__construct();

        if (method_exists($this, 'resolvePageTitle')) {
            $this->pageTitle = $this->resolvePageTitle();
        }
    }

    public function index(Request $request): View|JsonResponse
    {
        $payload = [
            'overview' => $this->service->overview(),
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return $this->render('environment-manager::Index', $payload);
    }

    public function env(Request $request): View|JsonResponse
    {
        $search = $request->string('q')->toString();
        $payload = [
            'search' => $search,
            'envFileEntries' => $this->service->envFileEntries($search),
            'runtimeEntries' => $this->service->runtimeEnvEntries($search),
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return $this->render('environment-manager::pages.env', $payload);
    }

    public function config(Request $request): View|JsonResponse
    {
        $search = $request->string('q')->toString();
        $payload = [
            'search' => $search,
            'entries' => $this->service->laravelConfigEntries($search),
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return $this->render('environment-manager::pages.config', $payload);
    }

    public function modules(Request $request): View|JsonResponse
    {
        $search = $request->string('q')->toString();
        $payload = [
            'search' => $search,
            'modules' => $this->service->moduleConfigs($search),
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return $this->render('environment-manager::pages.modules', $payload);
    }

    public function showModule(Request $request, string $moduleKey): View|JsonResponse
    {
        $module = $this->service->moduleConfig($moduleKey);

        abort_if($module === null, 404);

        $payload = [
            'module' => $module,
            'search' => $request->string('q')->toString(),
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return $this->render('environment-manager::pages.module-show', $payload);
    }

    public function effective(Request $request, ?string $key = null): View|JsonResponse
    {
        $search = $key ?: $request->string('q')->toString();
        $payload = [
            'search' => $search,
            'effective' => $this->service->effectiveConfig($search),
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return $this->render('environment-manager::pages.effective', $payload);
    }

    protected function render(string $view, array $data = []): View
    {
        if (method_exists($this, 'view')) {
            return $this->view($view, $data);
        }

        return view($view, $data);
    }
}
