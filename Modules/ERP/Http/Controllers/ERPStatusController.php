<?php

namespace Modules\ERP\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ERP\Models\ERPStatus;

class ERPStatusController extends Controller
{
    public function index()
    {
        $items = ERPStatus::query()->latest()->paginate(50);

        return $this->view('erp::settings.statuses.index', compact('items'));
    }

    public function create()
    {
        return $this->view('erp::settings.statuses.form', ['item' => new ERPStatus()]);
    }

    public function store(Request $request)
    {
        ERPStatus::create($request->all());

        return redirect()->route('erp.settings.statuses.index')->with('success', __('erp::messages.saved'));
    }

    public function edit($id)
    {
        $item = ERPStatus::findOrFail($id);

        return $this->view('erp::settings.statuses.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ERPStatus::findOrFail($id);
        $item->update($request->all());

        return redirect()->route('erp.settings.statuses.index')->with('success', __('erp::messages.saved'));
    }

    public function destroy($id)
    {
        $item = ERPStatus::findOrFail($id);
        $item->delete();

        return redirect()->route('erp.settings.statuses.index')->with('success', __('erp::messages.deleted'));
    }
}
