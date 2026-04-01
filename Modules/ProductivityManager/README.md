# ProductivityManager

Unified module for productivity dashboard, task intake, blocked items and alerts.

## Installation

1. Copy `ProductivityManager` folder into `Modules/`
2. Run:
   - `composer dump-autoload`
   - `php artisan optimize:clear`
   - `php artisan migrate`
3. Ensure your module loader reads `module.json`
4. Add a menu entry in your administration area if needed:
   - route: `productivityManager.index`
   - icon: `fa-solid fa-gauge-high`

## Notes

- This module does **not** depend on `Nwidart\Modules\Traits\PathNamespace`
- Provider uses only standard Laravel `ServiceProvider`
- Views alias: `productivitymanager`
- Routes:
  - Web: `/productivity-manager`
  - API: `/api/productivity-manager/*`

## Stream Deck endpoints

- `POST /api/productivity-manager/task/store`
- `POST /api/productivity-manager/task/complete`
- `POST /api/productivity-manager/task/block`
- `POST /api/productivity-manager/alert/store`

## Recommended next integration

Wire this module to your `roadmap-manager` once the base module is stable.
