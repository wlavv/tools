# ModuleHealthBridge

Adapter LSG entre o `ModuleComplianceCore` e o `Module Health`.

## Objetivo

Este módulo **não substitui** o Module Health. A sua função é:

- detetar o Module Health quando disponível;
- chamar um serviço compatível configurado;
- receber resultados externos do Module Health quando passados pelo contexto;
- normalizar resultados para `ModuleValidationResult`;
- permitir que o futuro `ModuleComplianceCenter` agregue o health score com Structure, Design, Security e Integration.

## Dependências

- ModuleComplianceCore
- Module Health opcional, mas recomendado

## Uso via service

```php
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleHealthBridge\Services\ModuleHealthBridgeService;

$context = new ModuleValidationContext(
    moduleName: 'IdeaLab',
    modulePath: base_path('Modules/IdeaLab'),
    sourceType: 'manual_run',
    requestedBy: auth()->id(),
);

$result = app(ModuleHealthBridgeService::class)->validate($context);
```

## Passar resultado externo do Module Health

```php
$context = new ModuleValidationContext(
    moduleName: 'IdeaLab',
    modulePath: base_path('Modules/IdeaLab'),
    options: [
        'module_health_result' => [
            'findings' => [
                [
                    'code' => 'ROUTE_CONFLICT',
                    'status' => 'failed',
                    'severity' => 'high',
                    'message' => 'Route name already exists.',
                ],
            ],
        ],
    ],
);
```

## Configuração

Editar `Config/module-health-bridge.php` e definir o service real do Module Health em `candidate_services`.

## Permissões

- `permission_module_health_bridge_view`
- `permission_module_health_bridge_run`
