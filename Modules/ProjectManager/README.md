# ProjectManager

Módulo unificado para gestão de projetos de desenvolvimento.

## Rota base

A rota raiz do módulo é:

```php
route('project_manager.index')
```

Também existe alias:

```php
route('project_manager.dashboard')
```

## Rotas principais

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

## Rotas das áreas do projeto

Todas as áreas têm rotas explícitas por projeto:

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

Cada área também tem:

```php
.create
.store
.edit
.update
.destroy
```

Exemplo:

```php
route('project_manager.projects.tasks.index', $project->id)
route('project_manager.projects.tasks.create', $project->id)
route('project_manager.projects.tasks.edit', [$project->id, $task->id])
```

## Notas

- As rotas antigas `project_manager.sections.*` foram removidas da navegação interna.
- A gestão de cada área continua centralizada no `SectionController`, mas as rotas públicas são explícitas e estáveis.
- O módulo espera que as tabelas do SQL incluído já existam na base de dados.
