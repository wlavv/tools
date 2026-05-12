# ERP Module — Complete LSG Timeline Version

This module is the renamed/evolved base for OMS -> ERP.

## Included

- `module.json`
- `ERPServiceProvider`
- Routes `/erp`
- Configs:
  - `config.php`
  - `actions.php`
  - `breadcrumbs.php`
  - `page_titles.php`
  - `navigation.php`
  - `statuses.php`
  - `timeline.php`
- Migrations:
  - `erp_configurations`
  - `erp_document_types`
  - `erp_statuses`
  - `erp_workflows`
  - `erp_numbering_sequences`
  - `erp_dashboard_widgets`
  - `erp_supplier_term_levels`
  - `erp_audit_events`
  - `erp_timeline_tasks`
- Models
- Services:
  - `ERPNumberingService`
  - `ERPAuditService`
  - `ERPTimelineService`
- Controllers
- Views with LSG timeline 8/4 layout
- PT/EN translations
- CSS/JS assets
- Seeder

## Install

Copy:

```text
Modules/ERP
```

into your Laravel project.

## Register

If your module loader does not auto-discover modules, register:

```php
Modules\ERP\Providers\ERPServiceProvider::class
```

## Migrate

```bash
php artisan migrate
```

## Seed configuration

Call the seeder manually or include it in your DatabaseSeeder:

```php
$this->call(\Modules\ERP\Database\Seeders\ERPConfigurationSeeder::class);
```

## Publish assets

```bash
php artisan vendor:publish --tag=erp-assets
```

Or manually copy:

```text
Modules/ERP/Resources/assets/css/erp.css -> public/modules/erp/css/erp.css
Modules/ERP/Resources/assets/js/erp.js -> public/modules/erp/js/erp.js
```

## Main route

```text
/erp
```

## Layout

The ERP dashboard uses a workflow-first structure:

1. Full-width hero
2. Full-width timeline tabs
3. Main grid:
   - `col-xl-8`: active operational step
   - `col-xl-4`: pending tasks and context

## Important

This package is non-destructive. It does not delete or rename existing OMS tables.
Operational OMS -> ERP data mapping should be handled in a second phase after validation.
