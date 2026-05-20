<?php

namespace Modules\ModuleComplianceCore\Enums;

enum ValidationStatus: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Warning = 'warning';
    case Skipped = 'skipped';
    case ManualReviewRequired = 'manual_review_required';
}
