<?php

namespace Modules\ModuleComplianceCore\DTO;

class ModuleValidationContext
{
    public function __construct(
        public readonly string $moduleName,
        public readonly string $modulePath,
        public readonly ?string $sourceType = null,
        public readonly int|string|null $sourceId = null,
        public readonly array $options = [],
        public readonly int|string|null $requestedBy = null,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            moduleName: (string) ($payload['module_name'] ?? $payload['moduleName'] ?? ''),
            modulePath: (string) ($payload['module_path'] ?? $payload['modulePath'] ?? ''),
            sourceType: $payload['source_type'] ?? null,
            sourceId: $payload['source_id'] ?? null,
            options: $payload['options'] ?? [],
            requestedBy: $payload['requested_by'] ?? null,
        );
    }

    public function option(string $key, mixed $default = null): mixed
    {
        return data_get($this->options, $key, $default);
    }
}
