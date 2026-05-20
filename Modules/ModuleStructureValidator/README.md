# Module Structure Validator

First LSG validator module. Requires `ModuleComplianceCore`.

## Purpose

Validates module structure before the future `ModuleComplianceCenter` orchestrates all validators.

## Checks included

- module path exists
- module.json exists and is valid JSON
- required manifest keys
- provider file exists
- provider has register/boot/routes/views/translations/migrations hooks
- required files and folders
- permission prefix `permission_*`
- root folder standard
- explicit migration index names that appear too long

## Manual run UI

`/module-structure-validator`

## Service usage

```php
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleStructureValidator\Services\ModuleStructureValidatorService;

$result = app(ModuleStructureValidatorService::class)->validate(
    ModuleValidationContext::fromArray([
        'module_name' => 'IdeaLab',
        'module_path' => base_path('Modules/IdeaLab'),
    ])
);

$array = $result->toArray();
```

## Future integration

The future `ModuleComplianceCenter` should call this service through the common validator contract.
