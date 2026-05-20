# ModuleIntegrationValidator

Validador LSG responsável por verificar se um módulo encaixa corretamente no WebTools Manager / B.O. Custom LSG.

## Dependência

Este módulo depende do `ModuleComplianceCore`, que fornece:

- `ModuleValidatorInterface`
- `ModuleValidationContext`
- `ModuleValidationResult`
- `ModuleValidationFinding`
- enums de status/severity
- scoring comum

## O que valida

- `module.json` legível e com chaves obrigatórias;
- provider declarado e ficheiro existente;
- métodos `register()` e `boot()`;
- chamadas `loadRoutesFrom`, `loadViewsFrom`, `loadTranslationsFrom`;
- `routes/web.php` com named routes e middleware;
- namespace/estrutura de views;
- traduções PT/EN;
- permissões com prefixo `permission_*`;
- metadata de menu;
- padrões básicos de assets;
- isolamento de core;
- metadata LSG.

## Uso via service

```php
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleIntegrationValidator\Services\ModuleIntegrationValidatorService;

$context = new ModuleValidationContext(
    moduleName: 'IdeaLab',
    modulePath: base_path('Modules/IdeaLab'),
    metadata: ['source' => 'manual']
);

$result = app(ModuleIntegrationValidatorService::class)->validate($context);
```

## Uso via B.O.

Aceder a:

```text
/module-integration-validator
```

Permissões necessárias:

- `permission_module_integration_validator_view`
- `permission_module_integration_validator_run`
- `permission_module_integration_validator_configure`

## Integração futura

Este validator foi preparado para ser chamado pelo futuro `ModuleComplianceCenter`.
