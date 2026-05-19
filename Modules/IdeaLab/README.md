# IdeaLab Module

Base LSG Laravel module for WebTools Manager.

## Purpose

IdeaLab captures raw ideas, matures them through structured AI Consensus templates/chat, scores their strategic value and prepares one-click conversion into Project Manager.

## Main Features

- Idea CRUD
- Categories and tags
- Status / priority workflow
- AI Consensus template registry
- AI Consensus chat-style message history
- AI payload endpoint for central AI Consensus integration
- Project conversion payload generation
- Scoring service
- Activity/conversion persistence
- LSG-style permissions in `module.json`
- Views, translations, routes, migrations and provider included

## Install

1. Copy `IdeaLab` into your modules directory.
2. Register `Modules\IdeaLab\Providers\IdeaLabServiceProvider` if your Module Loader does not auto-register from `module.json`.
3. Run migrations:

```bash
php artisan migrate
```

4. Seed base categories/templates:

```bash
php artisan db:seed --class="Modules\\IdeaLab\\Database\\Seeders\\IdeaLabDatabaseSeeder"
```

5. Open:

```text
/idealab
```

## AI Consensus Integration

The module currently creates structured payloads. Configure `config/idealab.php`:

```php
'ai_consensus' => [
    'service_class' => App\Services\AiConsensus\CentralAiConsensusService::class,
]
```

Recommended central AI Consensus contract:

```php
$response = app($serviceClass)->run(array $payload);
```

The payload includes:

- `entrypoint`: `idealab`
- `entrypoint_type`: template-specific type, e.g. `idea_discussion`
- `mode`: `template` or `chat`
- `template`: system prompt, user template, schema
- `input`: idea fields and scores
- `history`: previous chat messages

## Project Manager Integration

Configure:

```php
'project_manager' => [
    'service_class' => App\Services\ProjectManager\ProjectCreationService::class,
]
```

Expected method:

```php
createFromIdeaPayload(array $payload)
```

When no service is configured, IdeaLab stores the conversion payload only. This keeps the module safe and non-invasive.
