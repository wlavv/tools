# Carrier Payload Examples

This document defines the normalized event shape expected by the TrackingService.

```php
[
    'carrier_event_id' => 'unique-carrier-event-id-or-hash',
    'raw_status' => 'carrier native status/code',
    'normalized_status' => null, // optional; StatusNormalizer fills this if omitted
    'substatus' => 'optional detailed status',
    'description' => 'human readable checkpoint description',
    'location' => 'city/depot/country',
    'event_at' => '2026-05-18T10:00:00+00:00',
    'raw_payload' => [],
]
```

The module currently stores the complete raw carrier payload inside the shipment metadata under `last_raw_response` for diagnostics.
