# Package Tracker — LSG Module

Laravel LSG module for direct integration in WebTools Manager / B.O. Custom LSG.

## What it includes

- Carrier registry
- Shipment tracking records
- Carrier events history
- Normalized statuses
- SLA breach detection
- Stale shipment detection
- Dashboard with counters
- Shipment list and detail
- Manual shipment creation
- Queue job for polling carrier status
- Artisan command for sync
- Webhook endpoint registration table
- LSG-style permissions and manifest

## Install

1. Copy `PackageTracker` into your `Modules/` directory.
2. Register the provider if your module loader does not auto-discover it:

```php
Modules\PackageTracker\Providers\PackageTrackerServiceProvider::class,
```

3. Run migrations:

```bash
php artisan migrate
```

4. Clear caches:

```bash
php artisan optimize:clear
```

5. Optional: test command:

```bash
php artisan package-tracker:sync --limit=25
```

## Route

```txt
/package-tracker
```

## Permissions

- package_tracker_view
- package_tracker_manage_shipments
- package_tracker_manage_carriers
- package_tracker_manage_settings
- package_tracker_run_sync

## Notes

The module ships with `manual` and `mock` carrier clients so it is immediately testable. Real carriers should be added as dedicated classes implementing `CarrierClientInterface`.

## Carrier connector layer

This version includes request/response contracts and concrete connector drivers for:

- DPD
- DHL Unified Tracking
- CTT
- UPS Track API
- NACEX
- InPost
- Mondial Relay

Prepare standard carrier records:

```bash
php artisan package-tracker:install-carriers
```

Read `docs/CARRIER_CONNECTORS.md` before enabling production polling because some carriers require account-specific endpoint paths and credentials.

## Modular carrier integrators

Carriers are now discovered through integrator files in:

```txt
src/Services/Carriers/Integrators/*Integrator.php
```

Run:

```bash
php artisan package-tracker:install-carriers
```

The command scans the available integrator files and creates/updates the carrier records. This keeps the platform incremental: adding a new carrier requires adding an integrator and a driver, not rewriting core services.

Client-specific carrier availability is controlled through `package_tracker_client_carriers`. Suggestions for carriers not included in a client's plan are stored in `package_tracker_carrier_suggestions`.

See `docs/MODULAR_CARRIER_INTEGRATORS.md`.
