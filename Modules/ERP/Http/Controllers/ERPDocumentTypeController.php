<?php

namespace Modules\ERP\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ERP\Models\ERPDocumentType;

class ERPDocumentTypeController extends Controller
{
    public function index()
    {
        $items = ERPDocumentType::query()->latest()->paginate(50);

        return $this->view('erp::settings.document-types.index', compact('items'));
    }

    public function create()
    {
        return $this->view('erp::settings.document-types.form', ['item' => new ERPDocumentType()]);
    }

    public function store(Request $request)
    {
        ERPDocumentType::create($request->all());

        return redirect()->route('erp.settings.document-types.index')->with('success', __('erp::messages.saved'));
    }

    public function edit($id)
    {
        $item = ERPDocumentType::findOrFail($id);

        return $this->view('erp::settings.document-types.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ERPDocumentType::findOrFail($id);
        $item->update($request->all());

        return redirect()->route('erp.settings.document-types.index')->with('success', __('erp::messages.saved'));
    }

    public function destroy($id)
    {
        $item = ERPDocumentType::findOrFail($id);
        $item->delete();

        return redirect()->route('erp.settings.document-types.index')->with('success', __('erp::messages.deleted'));
    }
}
