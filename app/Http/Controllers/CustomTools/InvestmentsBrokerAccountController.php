<?php

namespace App\Http\Controllers\CustomTools;

use App\Models\webToolsManager\wt_investments_brokerAccount;
use Illuminate\Http\Request;

class InvestmentsBrokerAccountController extends customToolsController
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->actions[]     = [];
    }

    public function index()
    {
        $data = [ 
            'breadcrumbs'   => $this->breadcrumbs,
            'accounts'      => wt_investments_brokerAccount::where('user_id', auth()->id())->orderBy('name')->paginate(20)
        ];

        $this->setViewData($data);
        return view('customTools/investments_broker_accounts/index')->with( $this->viewData );
    }

    public function create()
    {
        $data = [ 
            'breadcrumbs'   => $this->breadcrumbs,
        ];

        $this->setViewData($data);
        return view('customTools.investments_broker_accounts.create')->with( $this->viewData );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'broker'=> 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'is_demo'  => 'nullable|boolean',
        ]);

        $data['user_id'] = auth()->id();
        $data['is_demo'] = $request->boolean('is_demo', true);

        wt_investments_brokerAccount::create($data);

        return redirect()->route('broker-accounts.index')->with('success', 'Conta criada com sucesso.');
    }
}
