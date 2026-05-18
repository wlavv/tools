<?php

namespace Modules\PackageTracker\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total' => Shipment::count(),
            'active' => Shipment::whereNotIn('status', ['delivered', 'returned', 'cancelled'])->count(),
            'delivered' => Shipment::where('status', 'delivered')->count(),
            'exceptions' => Shipment::where('has_exception', true)->count(),
            'stale' => Shipment::where('is_stale', true)->count(),
            'carriers' => Carrier::where('is_active', true)->count(),
        ];

        $byStatus = Shipment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $recentShipments = Shipment::with('carrier')
            ->latest()
            ->limit(10)
            ->get();

        return $this->view('package-tracker::dashboard.index', compact('stats', 'byStatus', 'recentShipments'));
    }
}
