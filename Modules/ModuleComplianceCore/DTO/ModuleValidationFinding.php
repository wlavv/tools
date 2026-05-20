<?php

namespace Modules\ModuleComplianceCore\DTO;

use Modules\ModuleComplianceCore\Enums\ValidationSeverity;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;

class ModuleValidationFinding
{
    public function __construct(
        public readonly string $code,
        public readonly ValidationStatus $status,
        public readonly ValidationSeverity $severity,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $filePath = null,
        public readonly ?int $lineNumber = null,
        public readonly mixed $expectedValue = null,
        public readonly mixed $actualValue = null,
        public readonly ?string $recommendation = null,
        public readonly bool $autoFixAvailable = false,
        public readonly array $metadata = [],
    ) {
    }

    public static function passed(string $code, string $title, string $message, ?string $filePath = null, array $metadata = []): self
    {
        return new self(
            code: $code,
            status: ValidationStatus::Passed,
            severity: ValidationSeverity::Info,
            title: $title,
            message: $message,
            filePath: $filePath,
            metadata: $metadata,
        );
    }

    public static function failed(
        string $code,
        string $title,
        string $message,
        ValidationSeverity $severity,
        ?string $filePath = null,
        mixed $expectedValue = null,
        mixed $actualValue = null,
        ?string $recommendation = null,
        bool $autoFixAvailable = false,
        array $metadata = []
    ): self {
        return new self(
            code: $code,
            status: ValidationStatus::Failed,
            severity: $severity,
            title: $title,
            message: $message,
            filePath: $filePath,
            expectedValue: $expectedValue,
            actualValue: $actualValue,
            recommendation: $recommendation,
            autoFixAvailable: $autoFixAvailable,
            metadata: $metadata,
        );
    }

    public static function warning(string $code, string $title, string $message, ValidationSeverity $severity, ?string $filePath = null, ?string $recommendation = null, array $metadata = []): self
    {
        return new self(
            code: $code,
            status: ValidationStatus::Warning,
            severity: $severity,
            title: $title,
            message: $message,
            filePath: $filePath,
            recommendation: $recommendation,
            metadata: $metadata,
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'status' => $this->status->value,
            'severity' => $this->severity->value,
            'title' => $this->title,
            'message' => $this->message,
            'file_path' => $this->filePath,
            'line_number' => $this->lineNumber,
            'expected_value' => $this->expectedValue,
            'actual_value' => $this->actualValue,
            'recommendation' => $this->recommendation,
            'auto_fix_available' => $this->autoFixAvailable,
            'metadata' => $this->metadata,
        ];
    }
}
