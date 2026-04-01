# RoadmapManager v3.3

This version is aligned with the existing `wt_projects` table and does **not** require `group_id` on that table.

## Included
- Roadmap dashboard with counters and charts
- Roadmap groups CRUD
- Project CRUD using existing `wt_projects`
- Group ↔ project links through `wt_project_group_links`
- Milestones CRUD
- Tasks CRUD using `wt_task_items`
- Task tree
- Basic Kanban
- Basic Gantt
- Comments, time logs, attachments

## Routes
All module routes live under:

```text
/roadmap
```

## Install
```bash
composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class="Modules\RoadmapManager\Database\Seeders\RoadmapManagerDatabaseSeeder"
php artisan storage:link
```

## Notes
- Views extend `layouts.app`
- Attachments use `public` disk
- Charts use CDN Chart.js
- Gantt is intentionally lightweight
