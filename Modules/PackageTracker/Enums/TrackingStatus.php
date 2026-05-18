<?php

namespace Modules\PackageTracker\Enums;

enum TrackingStatus: string
{
    case Pending = 'pending';
    case LabelCreated = 'label_created';
    case Collected = 'collected';
    case InTransit = 'in_transit';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case DeliveryFailed = 'delivery_failed';
    case Exception = 'exception';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
    case Unknown = 'unknown';

    public function label(): string
    {
        return config('package_tracker.normalized_statuses.' . $this->value, ucfirst(str_replace('_', ' ', $this->value)));
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Delivered => 'badge bg-success',
            self::Exception, self::DeliveryFailed => 'badge bg-danger',
            self::OutForDelivery => 'badge bg-primary',
            self::InTransit, self::Collected => 'badge bg-info text-dark',
            self::Returned, self::Cancelled => 'badge bg-warning text-dark',
            default => 'badge bg-secondary',
        };
    }

    public static function terminalValues(): array
    {
        return [
            self::Delivered->value,
            self::Returned->value,
            self::Cancelled->value,
        ];
    }
}
