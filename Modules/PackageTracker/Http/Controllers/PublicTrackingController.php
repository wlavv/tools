<?php

namespace Modules\PackageTracker\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Modules\PackageTracker\Models\Shipment;

class PublicTrackingController extends Controller
{
    public function show(string $token): View
    {
        $shipment = Shipment::query()
            ->with(['carrier', 'events'])
            ->where('public_token', $token)
            ->where('public_tracking_enabled', true)
            ->firstOrFail();

        $shipment->forceFill(['public_viewed_at' => now()])->saveQuietly();

        return view('package-tracker::public.tracking', [
            'shipment' => $shipment,
            'theme' => $this->themeFor($shipment),
            'trackingNumber' => $this->maskTrackingNumber($shipment->tracking_number),
            'statusLabel' => config('package_tracker.normalized_statuses.' . $shipment->status, $shipment->statusEnum()->label()),
        ]);
    }

    private function themeFor(Shipment $shipment): array
    {
        $theme = array_merge(
            config('package_tracker.public.theme', []),
            Arr::get($shipment->metadata ?? [], 'public_theme', [])
        );

        return [
            'brand_name' => $this->safeText($theme['brand_name'] ?? 'Package Tracker', 'Package Tracker'),
            'logo_url' => filter_var($theme['logo_url'] ?? null, FILTER_VALIDATE_URL) ? $theme['logo_url'] : null,
            'primary_color' => $this->safeColor($theme['primary_color'] ?? null, '#0f766e'),
            'accent_color' => $this->safeColor($theme['accent_color'] ?? null, '#2563eb'),
            'background_color' => $this->safeColor($theme['background_color'] ?? null, '#f8fafc'),
        ];
    }

    private function safeText(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        return $value !== '' ? mb_substr($value, 0, 80) : $fallback;
    }

    private function safeColor(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
    }

    private function maskTrackingNumber(?string $trackingNumber): string
    {
        $trackingNumber = (string) $trackingNumber;
        $length = strlen($trackingNumber);

        if ($length <= 8) {
            return str_repeat('*', max(0, $length - 2)) . substr($trackingNumber, -2);
        }

        return substr($trackingNumber, 0, 4) . str_repeat('*', max(0, $length - 8)) . substr($trackingNumber, -4);
    }
}
