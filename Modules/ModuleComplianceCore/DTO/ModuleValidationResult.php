<?php

namespace Modules\ModuleComplianceCore\DTO;

use Illuminate\Support\Collection;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;

class ModuleValidationResult
{
    /** @param array<int, ModuleValidationFinding> $findings */
    public function __construct(
        public readonly string $validator,
        public readonly string $area,
        public readonly string $label,
        public readonly array $findings = [],
        public readonly int $score = 100,
        public readonly ValidationStatus $status = ValidationStatus::Passed,
        public readonly array $metadata = [],
    ) {
    }

    public function findings(): Collection
    {
        return collect($this->findings);
    }

    public function failedCount(): int
    {
        return $this->findings()->filter(fn ($finding) => $finding->status === ValidationStatus::Failed)->count();
    }

    public function warningCount(): int
    {
        return $this->findings()->filter(fn ($finding) => $finding->status === ValidationStatus::Warning)->count();
    }

    public function toArray(): array
    {
        return [
            'validator' => $this->validator,
            'area' => $this->area,
            'label' => $this->label,
            'status' => $this->status->value,
            'score' => $this->score,
            'failed_count' => $this->failedCount(),
            'warning_count' => $this->warningCount(),
            'findings' => array_map(fn ($finding) => $finding->toArray(), $this->findings),
            'metadata' => $this->metadata,
        ];
    }
}
