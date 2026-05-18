<?php

namespace Modules\PackageTracker\Services\Carriers\Contracts;

enum CarrierCapability: string
{
    case Tracking = 'tracking';
    case PushTracking = 'push_tracking';
    case LabelCreation = 'label_creation';
    case PickupPoints = 'pickup_points';
    case Returns = 'returns';
}
