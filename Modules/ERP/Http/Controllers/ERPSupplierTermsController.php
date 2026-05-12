<?php

namespace Modules\ERP\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ERP\Models\ERPSupplierTermLevel;

class ERPSupplierTermsController extends Controller
{
    public function index()
    {
        $items = ERPSupplierTermLevel::query()->latest()->paginate(50);

        return $this->view('erp::settings.supplier-terms.index', compact('items'));
    }

    public function create()
    {
        return $this->view('erp::settings.supplier-terms.form', ['item' => new ERPSupplierTermLevel()]);
    }

    public function store(Request $request)
    {
        ERPSupplierTermLevel::create($request->all());

        return redirect()->route('erp.settings.supplier-terms.index')->with('success', __('erp::messages.saved'));
    }

    public function edit($id)
    {
        $item = ERPSupplierTermLevel::findOrFail($id);

        return $this->view('erp::settings.supplier-terms.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ERPSupplierTermLevel::findOrFail($id);
        $item->update($request->all());

        return redirect()->route('erp.settings.supplier-terms.index')->with('success', __('erp::messages.saved'));
    }

    public function destroy($id)
    {
        $item = ERPSupplierTermLevel::findOrFail($id);
        $item->delete();

        return redirect()->route('erp.settings.supplier-terms.index')->with('success', __('erp::messages.deleted'));
    }
}
