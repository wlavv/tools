<?php

namespace Modules\PackageTracker\Services;

use InvalidArgumentException;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Services\Carriers\CarrierClientInterface;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierIntegratorInterface;
use Modules\PackageTracker\Services\Carriers\Discovery\CarrierIntegratorRegistry;

class CarrierClientFactory
{
    public function __construct(private readonly CarrierIntegratorRegistry $registry) {}

    public function make(Carrier $carrier): CarrierClientInterface
    {
        $driver = $carrier->driver;

        if (!$driver && $integrator = $this->registry->get($carrier->code)) {
            $driver = $integrator->clientClass();
        }

        if (!$driver && config('package_tracker.carriers.' . $carrier->code . '.driver')) {
            $driver = config('package_tracker.carriers.' . $carrier->code . '.driver');
        }

        if (!$driver || !class_exists($driver)) {
            throw new InvalidArgumentException("Carrier driver not found for {$carrier->code}");
        }

        $client = app($driver);

        if ($client instanceof CarrierIntegratorInterface) {
            $driver = $client->clientClass();
            $client = app($driver);
        }

        if (!$client instanceof CarrierClientInterface) {
            throw new InvalidArgumentException("Carrier driver {$driver} must implement CarrierClientInterface");
        }

        return $client;
    }
}
