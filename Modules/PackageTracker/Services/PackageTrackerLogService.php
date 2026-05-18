<?php

namespace Modules\PackageTracker\Services;

use Modules\PackageTracker\Models\Shipment;
use Throwable;

class PackageTrackerLogService
{
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    public function shipmentContext(Shipment $shipment, array $extra = []): array
    {
        $shipment->loadMissing('carrier');

        return array_merge([
            'module' => 'PackageTracker',
            'shipment_id' => $shipment->id,
            'client_key' => $shipment->client_key,
            'carrier_id' => $shipment->carrier_id,
            'carrier_code' => $shipment->carrier?->code,
            'carrier_name' => $shipment->carrier?->name,
            'tracking_number' => $this->maskTrackingNumber($shipment->tracking_number),
            'status' => $shipment->status,
            'poll_attempts' => $shipment->poll_attempts,
        ], $extra);
    }

    public function exceptionContext(Throwable $exception): array
    {
        return [
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
        ];
    }

    private function write(string $level, string $message, array $context = []): void
    {
        $context = $this->sanitize($context);

        if (function_exists('system_log')) {
            system_log($level, '[PackageTracker] ' . $message, $context);
            return;
        }

        logger()->log($level, '[PackageTracker] ' . $message, $context);
    }

    private function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->maskTrackingLikeValues($value);
        }

        if (! is_array($value)) {
            return $value;
        }

        $sensitiveKeys = [
            'api_key', 'apikey', 'api_secret', 'apisecret', 'secret', 'password', 'token',
            'access_token', 'refresh_token', 'authorization', 'cookie', 'credentials',
            'client_secret', 'private_key',
        ];

        $sanitized = [];

        foreach ($value as $key => $item) {
            $keyString = strtolower((string) $key);

            if (in_array($keyString, $sensitiveKeys, true) || str_contains($keyString, 'secret') || str_contains($keyString, 'token')) {
                $sanitized[$key] = '[masked]';
                continue;
            }

            $sanitized[$key] = $this->sanitize($item);
        }

        return $sanitized;
    }

    private function maskTrackingNumber(?string $trackingNumber): ?string
    {
        $trackingNumber = (string) $trackingNumber;
        $length = strlen($trackingNumber);

        if ($trackingNumber === '') {
            return null;
        }

        if ($length <= 8) {
            return str_repeat('*', max(0, $length - 2)) . substr($trackingNumber, -2);
        }

        return substr($trackingNumber, 0, 4) . str_repeat('*', max(0, $length - 8)) . substr($trackingNumber, -4);
    }

    private function maskTrackingLikeValues(string $value): string
    {
        $value = preg_replace_callback('/\b\d{4}\/\d{6,}\b/', function (array $matches) {
            return $this->maskTrackingNumber($matches[0]);
        }, $value) ?? $value;

        return preg_replace_callback('/\b(?=[A-Z0-9]*\d)[A-Z0-9]{10,}\b/i', function (array $matches) {
            return $this->maskTrackingNumber($matches[0]);
        }, $value) ?? $value;
    }
}
