<?php

namespace Modules\PackageTracker\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\ClientCarrierAccess;
use Modules\PackageTracker\Models\PackageTrackerClient;

class ClientController extends Controller
{
    public function index()
    {
        $clients = PackageTrackerClient::query()
            ->withCount('shipments')
            ->orderBy('name')
            ->paginate(25);

        return $this->view('package-tracker::clients.index', compact('clients'));
    }

    public function create()
    {
        return $this->form(new PackageTrackerClient());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $client = PackageTrackerClient::query()->create($data);
        $this->syncCarrierAccess($client, $request->input('carrier_codes', []));

        return redirect()->route('package_tracker.clients.show', $client)->with('success', 'Client account created successfully.');
    }

    public function show(PackageTrackerClient $client)
    {
        $client->load(['carrierAccesses', 'shipments.carrier']);
        $enabledCarriers = $client->carrierAccesses->where('is_enabled', true)->pluck('carrier_code')->all();

        if ($client->publicUrl()) {
            $this->addAction([
                'key' => 'public_portal',
                'label' => 'Public portal',
                'name' => 'Public portal',
                'icon' => 'fa-solid fa-up-right-from-square',
                'class' => 'lsg-action-btn lsg-action-btn--neutral',
                'url' => $client->publicUrl(),
                'type' => 'link',
            ]);
        }

        return $this->view('package-tracker::clients.show', compact('client', 'enabledCarriers'));
    }

    public function edit(PackageTrackerClient $client)
    {
        return $this->form($client);
    }

    public function update(Request $request, PackageTrackerClient $client)
    {
        $client->update($this->validated($request, $client));
        $this->syncCarrierAccess($client, $request->input('carrier_codes', []));

        return redirect()->route('package_tracker.clients.show', $client)->with('success', 'Client account updated successfully.');
    }

    private function form(PackageTrackerClient $client)
    {
        $carriers = Carrier::query()->where('is_active', true)->orderBy('name')->get();
        $enabledCarrierCodes = $client->exists
            ? ClientCarrierAccess::query()->where('client_key', $client->client_key)->where('is_enabled', true)->pluck('carrier_code')->all()
            : [];

        return $this->view('package-tracker::clients.form', compact('client', 'carriers', 'enabledCarrierCodes'));
    }

    private function validated(Request $request, ?PackageTrackerClient $client = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_key' => ['nullable', 'string', 'max:120', 'unique:package_tracker_clients,client_key' . ($client?->id ? ',' . $client->id : '')],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'theme.brand_name' => ['nullable', 'string', 'max:80'],
            'theme.logo_url' => ['nullable', 'url', 'max:255'],
            'theme.primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.accent_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.background_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'carrier_codes' => ['nullable', 'array'],
            'carrier_codes.*' => ['string', 'exists:package_tracker_carriers,code'],
        ]);

        $theme = array_filter(Arr::get($data, 'theme', []), fn ($value) => filled($value));

        return [
            'name' => $data['name'],
            'client_key' => filled($data['client_key'] ?? null) ? Str::slug($data['client_key']) : ($client?->client_key ?: null),
            'contact_email' => $data['contact_email'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'theme' => $theme ?: null,
        ];
    }

    private function syncCarrierAccess(PackageTrackerClient $client, array $carrierCodes): void
    {
        $carrierCodes = array_values(array_unique(array_filter($carrierCodes)));

        ClientCarrierAccess::query()
            ->where('client_key', $client->client_key)
            ->whereNotIn('carrier_code', $carrierCodes ?: ['__none__'])
            ->update(['is_enabled' => false, 'disabled_at' => now()]);

        foreach ($carrierCodes as $carrierCode) {
            ClientCarrierAccess::query()->updateOrCreate([
                'client_key' => $client->client_key,
                'carrier_code' => $carrierCode,
            ], [
                'is_enabled' => true,
                'enabled_at' => now(),
                'disabled_at' => null,
            ]);
        }
    }
}
