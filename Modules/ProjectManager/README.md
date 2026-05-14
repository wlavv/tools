# ProjectManager

ProjectManager is the LSG Operations/Core Governance execution layer.

It coordinates projects, roadmaps, tasks, milestones, blockers, dependencies,
module closure, timeline execution, and ecosystem governance.

## Ecosystem Strategy

Execution follows:

```text
Operations -> Labs -> Revenue
```

Operational tools are validated internally first. Validated workflows can then
move into Labs for abstraction, and only after that into Revenue as SaaS,
commercial platforms, or public products.

No future SaaS/public product should depend structurally on
`webtools-manager.com`.

## ModuleHealth Integration

ProjectManager consumes ModuleHealth scan data, but ModuleHealth remains
independent.

Current dashboard integration:

- module health matrix
- required/recommended/optional component coverage
- closure blockers
- upgrade opportunities
- SaaS readiness candidates
- dependency impact indicators
- execution flow: Discovery, Foundation, Operational, Automation,
  Productization, SaaS

The integration reads the latest `module_health_scans` and
`module_health_scan_items` records through
`Modules\ProjectManager\Services\ModuleHealthGovernanceService`.

## Base Route

```php
route('project_manager.index')
route('project_manager.dashboard')
```

## Main Routes

```php
project_manager.projects.index
project_manager.projects.create
project_manager.projects.store
project_manager.projects.show
project_manager.projects.edit
project_manager.projects.update
project_manager.projects.destroy
project_manager.projects.overview
```

## Project Areas

Each area has explicit per-project routes:

```php
project_manager.projects.modules.index
project_manager.projects.design_profiles.index
project_manager.projects.design_tokens.index
project_manager.projects.assets.index
project_manager.projects.technical_stack.index
project_manager.projects.environments.index
project_manager.projects.guidelines.index
project_manager.projects.documentation.index
project_manager.projects.decisions.index
project_manager.projects.notes.index
project_manager.projects.links.index
project_manager.projects.roadmap_items.index
project_manager.projects.tasks.index
project_manager.projects.task_dependencies.index
project_manager.projects.task_blocks.index
project_manager.projects.blocks.index
project_manager.projects.contacts.index
project_manager.projects.external_dependencies.index
project_manager.projects.activity.index
```

Each area also supports:

```php
.create
.store
.edit
.update
.destroy
```

Example:

```php
route('project_manager.projects.tasks.index', $project->id)
route('project_manager.projects.tasks.create', $project->id)
route('project_manager.projects.tasks.edit', [$project->id, $task->id])
```

## Notes

- Old `project_manager.sections.*` routes were removed from internal navigation.
- Each area is still centrally handled by `SectionController`, while public
  routes remain explicit and stable.
- The module expects the included SQL tables to exist in the database.
