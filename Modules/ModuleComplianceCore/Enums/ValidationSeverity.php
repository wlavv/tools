<?php

namespace Modules\ModuleComplianceCore\Enums;

enum ValidationSeverity: string
{
    case Info = 'info';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';
    case Blocker = 'blocker';
}
