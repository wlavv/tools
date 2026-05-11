<?php

namespace Modules\DocumentManager\Http\Controllers;

use App\Http\Controllers\Controller;

abstract class BaseDocumentController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $routeName = request()->route()?->getName();
        $routeAction = $routeName ? last(explode('.', $routeName)) : null;

        if (in_array($routeAction, ['create', 'edit'], true)) {
            $this->setModuleHomeRoute('document-manager.dashboard');
            return;
        }

        $this->hasPageActions = false;
        $this->clearActions();
    }
}
