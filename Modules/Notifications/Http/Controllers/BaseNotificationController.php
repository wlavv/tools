<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;

abstract class BaseNotificationController extends Controller
{
    protected array $actions = [];
    protected array $breadcrumbs = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => 'Notifications', 'url' => route('notifications.index')];
    }

    protected function addAction(string $url, string $name, string $icon, string $class = 'btn btn-outline-primary'): void
    {
        $this->actions[] = compact('url', 'name', 'icon', 'class');
    }

    protected function addBreadcrumb(string $label, ?string $url = null, array $params = [], bool $translate = true): void
    {
        if (method_exists(get_parent_class($this), 'addBreadcrumb')) {
            parent::addBreadcrumb($label, $url, $params, $translate);
        }

        $this->breadcrumbs[] = [
            'name' => $label,
            'label' => $label,
            'url' => $url,
            'params' => $params,
            'translate' => $translate,
        ];
    }

    protected function viewData(array $data = []): array
    {
        return array_merge($data, [
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }
}