<?php

namespace Modules\PackageTracker\Services\Carriers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;

interface CarrierClientInterface
{
    public function fetchTracking(Carrier $carrier, Shipment $shipment): CarrierTrackingResponse;

    public function healthCheck(Carrier $carrier): bool;
}
