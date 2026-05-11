<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class RecognitionSessionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('webcatalogue::recognition.sessions.index', [
            'items' => VisualRecognitionSession::with(['store', 'product', 'lead'])->latest()->get(),
        ]);
    }

    public function show(VisualRecognitionSession $session): View
    {
        return $this->view('webcatalogue::recognition.sessions.show', [
            'item' => $session->load(['store', 'product', 'captures', 'matches.product', 'lead']),
        ]);
    }
}
