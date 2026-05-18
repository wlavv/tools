# LSG Integration Notes

## Direct integration in B.O. Laravel LSG

Copy the folder:

```txt
PackageTracker/
```

into:

```txt
Modules/PackageTracker/
```

## Provider

If your module loader reads `module.json`, it should detect:

```txt
Modules\\PackageTracker\\Providers\\PackageTrackerServiceProvider
```

If not, register it manually in your module/provider registry.

## Menu entry

Suggested menu item:

```php
[
    'title' => 'Package Tracker',
    'icon' => 'fa-solid fa-truck-fast',
    'route' => 'package_tracker.dashboard',
    'permission' => 'package_tracker_view',
]
```

## Commands

```bash
php artisan optimize:clear
php artisan migrate
php artisan package-tracker:sync --limit=25 --sync
```

## Scheduler

The provider registers:

```txt
package-tracker:sync --limit=100 every 15 minutes
```

Make sure your Laravel scheduler cron is active.

## Queue

The sync job uses:

```env
PACKAGE_TRACKER_QUEUE=default
```

## Test flow

1. Open `/package-tracker/carriers`.
2. Confirm `Manual / Generic` and `Mock Carrier` exist.
3. Create one shipment using `Mock Carrier`.
4. Open the shipment detail.
5. Click Sync.
6. Run queue worker or run the command synchronously:

```bash
php artisan package-tracker:sync --limit=1 --sync
```

The mock carrier should advance status through the lifecycle until delivered.

## Carrier connector setup

After installing the module and running migrations:

```bash
php artisan package-tracker:install-carriers
php artisan optimize:clear
```

Then open the carriers screen, configure credentials/endpoints and only then activate the carrier.

For the first production test, use one shipment per carrier and run:

```bash
php artisan package-tracker:sync --limit=10 --sync
```

Check the shipment metadata `last_raw_response` if the carrier returns an unexpected format.
