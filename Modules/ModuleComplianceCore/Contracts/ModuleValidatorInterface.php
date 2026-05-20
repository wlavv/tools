<?php

namespace Modules\ModuleComplianceCore\Contracts;

use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleComplianceCore\DTO\ModuleValidationResult;

interface ModuleValidatorInterface
{
    public function key(): string;

    public function label(): string;

    public function area(): string;

    public function validate(ModuleValidationContext $context): ModuleValidationResult;
}
