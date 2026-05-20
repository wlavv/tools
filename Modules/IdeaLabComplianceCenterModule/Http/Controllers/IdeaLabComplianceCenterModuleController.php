<?php

namespace Modules\IdeaLabComplianceCenterModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class IdeaLabComplianceCenterModuleController extends Controller
{
    public function index(): View
    {
        $this->setPageTitle('Module Compliance Center');
        $this->setBreadcrumbs([
            [
                'label' => 'Dashboard',
                'url' => route('dashboard.index'),
                'translate' => false,
            ],
            [
                'label' => 'Admin',
                'url' => route('administration.index'),
                'translate' => false,
            ],
            [
                'label' => 'Module Compliance',
                'url' => null,
                'translate' => false,
            ],
        ]);
        $this->setActions($this->pageActions());

        return view('module-compliance::index');
    }

    protected function pageActions(): array
    {
        $actions = [
            [
                'key' => 'back',
                'label' => 'Back',
                'icon' => 'fa-solid fa-angle-left',
                'url' => route('administration.index'),
                'type' => 'link',
            ],
            [
                'key' => 'new',
                'label' => 'New check',
                'icon' => 'fa-solid fa-plus',
                'url' => route('module-compliance.index'),
                'type' => 'link',
            ],
            [
                'key' => 'config',
                'label' => 'Configure',
                'icon' => 'fa-solid fa-cog',
                'url' => route('module-compliance.index'),
                'type' => 'link',
            ],
        ];

        if (Route::has('module_health.index')) {
            $actions[] = [
                'key' => 'module_health',
                'label' => 'Module Health',
                'icon' => 'fa-solid fa-heart-pulse',
                'url' => route('module_health.index'),
                'type' => 'link',
            ];
        }

        if (Route::has('ai_consensus.index')) {
            $actions[] = [
                'key' => 'ai_consensus',
                'label' => 'AI Consensus',
                'icon' => 'fa-solid fa-brain',
                'url' => route('ai_consensus.index'),
                'type' => 'link',
            ];
        }

        return $actions;
    }
}
