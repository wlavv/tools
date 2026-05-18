<?php

namespace Modules\PackageTracker\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PackageTracker\Models\Carrier;

class CarrierController extends Controller
{
    public function index()
    {
        $carriers = Carrier::orderBy('name')->paginate(25);
        return $this->view('package-tracker::carriers.index', compact('carriers'));
    }

    public function create()
    {
        return $this->view('package-tracker::carriers.form', ['carrier' => new Carrier()]);
    }

    public function store(Request $request)
    {
        Carrier::create($this->validated($request));
        return redirect()->route('package_tracker.carriers.index')->with('success', 'Carrier created successfully.');
    }

    public function edit(Carrier $carrier)
    {
        return $this->view('package-tracker::carriers.form', compact('carrier'));
    }

    public function update(Request $request, Carrier $carrier)
    {
        $carrier->update($this->validated($request, $carrier, true));
        return redirect()->route('package_tracker.carriers.index')->with('success', 'Carrier updated successfully.');
    }

    private function validated(Request $request, ?Carrier $carrier = null, bool $isUpdate = false): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:package_tracker_carriers,code' . ($carrier?->id ? ',' . $carrier->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'driver' => ['nullable', 'string', 'max:255'],
            'api_base_url' => ['nullable', 'url', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'api_secret' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'supports_webhooks' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
            'supports_webhooks' => $request->boolean('supports_webhooks'),
        ];

        if ($isUpdate) {
            foreach (['api_key', 'api_secret'] as $credentialField) {
                if (($data[$credentialField] ?? '') === '') {
                    unset($data[$credentialField]);
                }
            }
        }

        return $data;
    }
}
