<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\customTools\customToolsController;
use App\Models\webToolsManager\wt_investments_asset;
use Illuminate\Http\Request;

class InvestmentsAssetController extends customToolsController
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $data = [
            'assets'        => wt_investments_asset::orderBy('symbol')->paginate(20),
            'breadcrumbs'   => $this->breadcrumbs,
        ];

        $this->setViewData($data);
        return view('customTools/investments_assets/index')->with( $this->viewData );
    }

    public function create()
    {
        $this->setViewData([]);
        return view('customTools/investments_assets/create')->with( $this->viewData );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol'  => 'required|string|max:20',
            'name'    => 'required|string|max:255',
            'type'    => 'required|string|max:50',
            'exchange'=> 'nullable|string|max:50',
        ]);

        $data['broker'] = 'ibkr';

        wt_investments_asset::create($data);

        return redirect()->route('assets.index')->with('success', 'Ativo criado com sucesso.');
    }
}
