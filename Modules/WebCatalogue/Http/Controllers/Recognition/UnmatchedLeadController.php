<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\UnmatchedProductLead;

class UnmatchedLeadController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('webcatalogue::recognition.leads.index', [
            'items' => UnmatchedProductLead::with(['store', 'session'])->latest()->get(),
        ]);
    }

    public function show(UnmatchedProductLead $lead): View
    {
        return $this->view('webcatalogue::recognition.leads.show', [
            'item' => $lead->load(['store', 'session.captures']),
        ]);
    }

    public function status(Request $request, UnmatchedProductLead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
        ]);

        $lead->update($validated);

        return back()->with('success', 'Lead status updated.');
    }
}
