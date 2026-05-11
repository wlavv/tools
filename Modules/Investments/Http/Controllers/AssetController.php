<?php

namespace Modules\Investments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Investments\Models\Asset;

class AssetController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('investments::assets.index', [
            'assets' => Asset::orderBy('symbol')->paginate(config('investments.pagination', 25)),
        ]);
    }

    public function create(): View
    {
        return $this->view('investments::assets.form', [
            'asset' => new Asset(['broker' => 'ibkr', 'type' => 'stock']),
            'action' => route('investments.assets.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Asset::create($request->validate([
            'symbol' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:255'],
            'broker' => ['nullable', 'string', 'max:40'],
            'external_instrument_id' => ['nullable', 'string', 'max:120'],
            'type' => ['required', 'string', 'max:40'],
            'exchange' => ['nullable', 'string', 'max:80'],
        ]));

        return redirect()->route('investments.assets.index')->with('success', 'Ativo criado com sucesso.');
    }
}
