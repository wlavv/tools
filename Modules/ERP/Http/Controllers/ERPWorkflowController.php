<?php

namespace Modules\ERP\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ERP\Models\ERPWorkflow;

class ERPWorkflowController extends Controller
{
    public function index()
    {
        $items = ERPWorkflow::query()->latest()->paginate(50);

        return $this->view('erp::settings.workflows.index', compact('items'));
    }

    public function create()
    {
        return $this->view('erp::settings.workflows.form', ['item' => new ERPWorkflow()]);
    }

    public function store(Request $request)
    {
        ERPWorkflow::create($request->all());

        return redirect()->route('erp.settings.workflows.index')->with('success', __('erp::messages.saved'));
    }

    public function edit($id)
    {
        $item = ERPWorkflow::findOrFail($id);

        return $this->view('erp::settings.workflows.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ERPWorkflow::findOrFail($id);
        $item->update($request->all());

        return redirect()->route('erp.settings.workflows.index')->with('success', __('erp::messages.saved'));
    }

    public function destroy($id)
    {
        $item = ERPWorkflow::findOrFail($id);
        $item->delete();

        return redirect()->route('erp.settings.workflows.index')->with('success', __('erp::messages.deleted'));
    }
}
