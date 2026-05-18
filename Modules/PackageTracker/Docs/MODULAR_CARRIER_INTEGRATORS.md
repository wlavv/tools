# Modular Carrier Integrators

This module now supports a plugin-like carrier architecture.

## Goal

A carrier is added by adding one integrator file, without changing the core tracking service.

Example sequence:

1. Start with `DpdIntegrator.php`.
2. Add `CttIntegrator.php` later.
3. Add `UpsIntegrator.php` later.
4. Add `NacexIntegrator.php` later.

The module discovers available integrators from:

```txt
PackageTracker/Services/Carriers/Integrators/*Integrator.php
```

Optional extra locations can be configured in:

```php
config('package_tracker.integrator_paths')
```

## Files per carrier

Each carrier should have:

```txt
src/Services/Carriers/Integrators/{Carrier}Integrator.php
src/Services/Carriers/Drivers/{Carrier}TrackingClient.php
```

The integrator describes the carrier:

- code
- name
- client class
- default base URL
- capabilities
- credential schema
- default settings
- tracking number hints

The driver performs the actual API call.

## Discovery command

```bash
php artisan package-tracker:install-carriers
```

Force refresh existing carrier records:

```bash
php artisan package-tracker:install-carriers --force
```

## Client access / commercial control

Global availability is not the same as client availability.

A carrier may exist in the platform but only be enabled for selected clients through:

```txt
package_tracker_client_carriers
```

This allows plans such as:

- client A: DPD only
- client B: DPD + CTT
- client C: UPS + DHL + InPost

## Suggested upsell flow

When the client searches in a contracted carrier and no tracking is found, the platform can optionally probe other active carriers.

Enable with:

```env
PACKAGE_TRACKER_PROBE_UNCONTRACTED_CARRIERS=true
```

If a match is found in a non-contracted carrier, a record is created in:

```txt
package_tracker_carrier_suggestions
```

This supports commercial flows such as:

> “This tracking appears to belong to UPS. Add UPS to your package tracking plan?”

## Important production note

Cross-carrier probing should be controlled because it can consume API quota and may be restricted by carrier terms or credentials. Use it first as an internal diagnostic/upsell feature, not as default public behavior.
