<?php

namespace Modules\Investments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Investments\Models\Asset;
use Modules\Investments\Models\BrokerAccount;
use Modules\Investments\Models\Position;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('investments::Index', [
            'stats' => [
                'accounts' => BrokerAccount::count(),
                'assets' => Asset::count(),
                'open_positions' => Position::where('status', 'open')->count(),
                'closed_positions' => Position::where('status', 'closed')->count(),
            ],
            'positions' => Position::with(['asset', 'brokerAccount'])->latest()->limit(8)->get(),
        ]);
    }
}
