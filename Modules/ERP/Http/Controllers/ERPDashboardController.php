<?php

namespace Modules\ERP\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ERP\Models\ERPDashboardWidget;
use Modules\ERP\Models\ERPDocumentType;
use Modules\ERP\Models\ERPStatus;
use Modules\ERP\Services\ERPTimelineService;

class ERPDashboardController extends Controller
{
    public function index(ERPTimelineService $timelineService)
    {
        return $this->timeline(null, $timelineService);
    }

    public function timeline(?string $step = null, ?ERPTimelineService $timelineService = null)
    {
        $timelineService = $timelineService ?: app(ERPTimelineService::class);

        $steps = $timelineService->steps($step);
        $activeStep = $steps->firstWhere('status', 'active') ?? $steps->first();

        $widgets = ERPDashboardWidget::query()
            ->where('is_enabled', true)
            ->orderBy('zone')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('zone');

        $documentTypes = ERPDocumentType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $statuses = ERPStatus::query()
            ->where('is_active', true)
            ->where('scope', 'document')
            ->orderBy('sort_order')
            ->get();

        return $this->view('erp::dashboard.index', compact(
            'steps',
            'activeStep',
            'widgets',
            'documentTypes',
            'statuses'
        ));
    }
}
