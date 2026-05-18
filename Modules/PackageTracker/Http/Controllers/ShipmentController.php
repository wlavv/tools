<?php

namespace Modules\PackageTracker\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\PackageTrackerClient;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\TrackingService;
use Throwable;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::query()
            ->with(['carrier', 'client'])
            ->when($request->filled('client_key'), fn ($q) => $q->where('client_key', $request->string('client_key')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('tracking_number', 'like', $term)
                        ->orWhere('order_reference', 'like', $term)
                        ->orWhere('external_reference', 'like', $term)
                        ->orWhere('customer_email', 'like', $term);
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $clients = PackageTrackerClient::query()->where('is_active', true)->orderBy('name')->get();

        return $this->view('package-tracker::shipments.index', compact('shipments', 'clients'));
    }

    public function create()
    {
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $clients = PackageTrackerClient::query()->where('is_active', true)->orderBy('name')->get();

        return $this->view('package-tracker::shipments.create', compact('carriers', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Shipment::create($data + ['status' => 'pending']);

        return redirect()->route('package_tracker.shipments.index')->with('success', 'Shipment created successfully.');
    }

    public function edit(Shipment $shipment)
    {
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $clients = PackageTrackerClient::query()->where('is_active', true)->orderBy('name')->get();

        return $this->view('package-tracker::shipments.form', compact('shipment', 'carriers', 'clients'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $shipment->update($this->validated($request, $shipment));

        return redirect()->route('package_tracker.shipments.show', $shipment)->with('success', 'Shipment updated successfully.');
    }

    private function validated(Request $request, ?Shipment $shipment = null): array
    {
        $data = $request->validate([
            'carrier_id' => ['required', 'exists:package_tracker_carriers,id'],
            'client_key' => ['nullable', 'exists:package_tracker_clients,client_key'],
            'tracking_number' => ['required', 'string', 'max:120'],
            'order_reference' => ['nullable', 'string', 'max:120'],
            'external_reference' => ['nullable', 'string', 'max:120'],
            'store_code' => ['nullable', 'string', 'max:80'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'destination_country' => ['nullable', 'string', 'size:2'],
            'destination_postal_code' => ['nullable', 'string', 'max:20'],
            'sla_due_at' => ['nullable', 'date'],
        ]);

        $metadata = $shipment?->metadata ?? [];

        if (($data['destination_postal_code'] ?? '') !== '') {
            $metadata['destination_postal_code'] = strtoupper(str_replace(' ', '', $data['destination_postal_code']));
        } else {
            unset($metadata['destination_postal_code']);
        }

        unset($data['destination_postal_code']);

        $data['destination_country'] = strtoupper((string) ($data['destination_country'] ?? config('package_tracker.default_destination_country', 'PT')));
        $data['metadata'] = $metadata;

        return $data;
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['carrier', 'events']);

        if ($shipment->publicUrl()) {
            $this->addAction([
                'key' => 'public_page',
                'label' => 'Public page',
                'name' => 'Public page',
                'icon' => 'fa-solid fa-up-right-from-square',
                'class' => 'lsg-action-btn lsg-action-btn--neutral',
                'url' => $shipment->publicUrl(),
                'type' => 'link',
            ]);
        }

        $this->addAction([
            'key' => 'edit_shipment',
            'label' => 'Edit',
            'name' => 'Edit',
            'icon' => 'fa-solid fa-pencil',
            'class' => 'lsg-action-btn lsg-action-btn--warning',
            'route' => 'package_tracker.shipments.edit',
            'params' => [$shipment],
        ]);

        return $this->view('package-tracker::shipments.show', compact('shipment'));
    }

    public function sync(Shipment $shipment, TrackingService $trackingService)
    {
        try {
            $trackingService->syncShipment($shipment->load('carrier'));

            return back()->with('success', 'Tracking sync executed.');
        } catch (Throwable $exception) {
            $trackingService->markFailedPoll($shipment, $exception);

            return back()->with('error', 'Tracking sync failed: ' . $exception->getMessage());
        }
    }
}
