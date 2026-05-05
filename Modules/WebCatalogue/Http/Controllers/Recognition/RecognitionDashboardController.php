<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\BrandProspectLead;
use Modules\WebCatalogue\Models\UnmatchedProductLead;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class RecognitionDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('webcatalogue::recognition.index', [
            'sessionsCount' => VisualRecognitionSession::count(),
            'todaySessionsCount' => VisualRecognitionSession::whereDate('created_at', now()->toDateString())->count(),
            'newLeadsCount' => UnmatchedProductLead::where('status', 'new')->count(),
            'brandProspectsCount' => BrandProspectLead::count(),
            'recentSessions' => VisualRecognitionSession::with('store')->latest()->limit(8)->get(),
            'recentLeads' => UnmatchedProductLead::with('store')->latest()->limit(8)->get(),
        ]);
    }
}
