<?php

namespace Modules\ModuleHealthBridge\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ReflectionMethod;
use Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleComplianceCore\DTO\ModuleValidationFinding;
use Modules\ModuleComplianceCore\DTO\ModuleValidationResult;
use Modules\ModuleComplianceCore\Enums\ValidationSeverity;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;
use Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator;
use Throwable;

class ModuleHealthBridgeService implements ModuleValidatorInterface
{
    public function __construct(
        protected ComplianceScoreCalculator $scoreCalculator
    ) {
    }

    public function key(): string
    {
        return 'health_bridge';
    }

    public function label(): string
    {
        return 'Module Health Bridge';
    }

    public function area(): string
    {
        return 'health';
    }

    public function validate(ModuleValidationContext $context): ModuleValidationResult
    {
        $findings = [];
        $metadata = [
            'module_name' => $context->moduleName,
            'module_path' => $context->modulePath,
            'bridge_mode' => 'auto_detect',
            'module_health_service' => null,
            'module_health_method' => null,
            'external_result_received' => false,
        ];

        if (! is_dir($context->modulePath)) {
            $findings[] = ModuleValidationFinding::failed(
                code: 'HEALTH_TARGET_MODULE_PATH_MISSING',
                title: 'Target module path missing',
                message: 'The target module path does not exist and cannot be analyzed by Module Health.',
                severity: ValidationSeverity::Blocker,
                filePath: $context->modulePath,
                recommendation: 'Confirm the module path before running health validation.'
            );

            return $this->result($findings, $metadata);
        }

        $findings[] = ModuleValidationFinding::passed(
            code: 'HEALTH_TARGET_MODULE_PATH_EXISTS',
            title: 'Target module path exists',
            message: 'The module path exists and can be passed to Module Health.',
            filePath: $context->modulePath
        );

        $externalResult = $context->option('module_health_result');
        if (is_array($externalResult)) {
            $metadata['bridge_mode'] = 'external_payload';
            $metadata['external_result_received'] = true;
            $findings = array_merge($findings, $this->normalizeExternalResult($externalResult));

            return $this->result($findings, $metadata);
        }

        $healthPath = (string) config('module-health-bridge.module_health_path', base_path('Modules/ModuleHealth'));
        if (! is_dir($healthPath)) {
            $findings[] = $this->missingModuleHealthFinding($healthPath);

            return $this->result($findings, $metadata);
        }

        $findings[] = ModuleValidationFinding::passed(
            code: 'HEALTH_MODULE_HEALTH_MODULE_FOUND',
            title: 'Module Health module found',
            message: 'The Module Health directory exists. The bridge will try to call a compatible health service.',
            filePath: $healthPath
        );

        $serviceCall = $this->resolveCallableHealthService();
        if ($serviceCall === null) {
            $findings[] = ModuleValidationFinding::warning(
                code: 'HEALTH_SERVICE_NOT_RESOLVED',
                title: 'Module Health service not resolved',
                message: 'Module Health exists, but no compatible service/method was resolved automatically.',
                severity: ValidationSeverity::High,
                filePath: $healthPath,
                recommendation: 'Add the concrete Module Health service class and method to config/module-health-bridge.php.'
            );

            return $this->result($findings, $metadata);
        }

        [$service, $serviceClass, $method] = $serviceCall;
        $metadata['module_health_service'] = $serviceClass;
        $metadata['module_health_method'] = $method;

        $findings[] = ModuleValidationFinding::passed(
            code: 'HEALTH_SERVICE_RESOLVED',
            title: 'Module Health service resolved',
            message: sprintf('Resolved %s::%s for health analysis.', $serviceClass, $method),
            metadata: [
                'service' => $serviceClass,
                'method' => $method,
            ]
        );

        try {
            $rawResult = $this->callHealthService($service, $method, $context);
            $metadata['bridge_mode'] = 'service_call';
            $metadata['raw_result_type'] = get_debug_type($rawResult);

            $rawResult = $this->filterRawHealthResultForContext($rawResult, $context);
            $normalized = $this->normalizeRawHealthResult($rawResult);
            if (empty($normalized)) {
                $findings[] = ModuleValidationFinding::warning(
                    code: 'HEALTH_EMPTY_OR_UNKNOWN_RESULT',
                    title: 'Empty or unknown Module Health result',
                    message: 'Module Health returned a result, but the bridge could not extract checks/findings from it.',
                    severity: ValidationSeverity::Medium,
                    recommendation: 'Expose a result array with findings/results/checks/issues, or configure a custom adapter.'
                );
            } else {
                $findings = array_merge($findings, $normalized);
            }
        } catch (Throwable $exception) {
            Log::warning('ModuleHealthBridge failed to call Module Health service.', [
                'module' => $context->moduleName,
                'service' => $serviceClass,
                'method' => $method,
                'exception' => $exception->getMessage(),
            ]);

            $findings[] = ModuleValidationFinding::failed(
                code: 'HEALTH_SERVICE_CALL_FAILED',
                title: 'Module Health service call failed',
                message: $exception->getMessage(),
                severity: ValidationSeverity::High,
                recommendation: 'Check the Module Health service signature or add a dedicated adapter method.'
            );
        }

        return $this->result($findings, $metadata);
    }

    protected function result(array $findings, array $metadata = []): ModuleValidationResult
    {
        $score = $this->scoreCalculator->calculate($findings);
        $status = ValidationStatus::Passed;

        foreach ($findings as $finding) {
            if ($finding->status === ValidationStatus::Failed) {
                $status = ValidationStatus::Failed;
                break;
            }

            if ($finding->status === ValidationStatus::Warning && $status !== ValidationStatus::Failed) {
                $status = ValidationStatus::Warning;
            }

            if ($finding->status === ValidationStatus::ManualReviewRequired && $status === ValidationStatus::Passed) {
                $status = ValidationStatus::ManualReviewRequired;
            }
        }

        return new ModuleValidationResult(
            validator: $this->key(),
            area: $this->area(),
            label: $this->label(),
            findings: $findings,
            score: $score,
            status: $status,
            metadata: $metadata
        );
    }

    protected function missingModuleHealthFinding(string $healthPath): ModuleValidationFinding
    {
        $fail = (bool) config('module-health-bridge.fail_without_module_health', false);

        if ($fail) {
            return ModuleValidationFinding::failed(
                code: 'HEALTH_MODULE_HEALTH_MISSING',
                title: 'Module Health missing',
                message: 'Module Health was not found. The health bridge cannot run the health layer.',
                severity: ValidationSeverity::Critical,
                filePath: $healthPath,
                recommendation: 'Install/enable Module Health or disable fail_without_module_health.'
            );
        }

        return new ModuleValidationFinding(
            code: 'HEALTH_MODULE_HEALTH_MISSING_MANUAL_REVIEW',
            status: ValidationStatus::ManualReviewRequired,
            severity: ValidationSeverity::High,
            title: 'Module Health requires manual review',
            message: 'Module Health was not found. The bridge returns manual review instead of failing the validator.',
            filePath: $healthPath,
            recommendation: 'Install/enable Module Health, or pass an external module_health_result in the validation context.',
        );
    }

    protected function resolveCallableHealthService(): ?array
    {
        $serviceClasses = (array) config('module-health-bridge.candidate_services', []);
        $methods = (array) config('module-health-bridge.candidate_methods', []);

        foreach ($serviceClasses as $serviceClass) {
            if (! is_string($serviceClass) || ! class_exists($serviceClass)) {
                continue;
            }

            try {
                $service = app($serviceClass);
            } catch (Throwable) {
                continue;
            }

            foreach ($methods as $method) {
                if (is_string($method) && method_exists($service, $method)) {
                    return [$service, $serviceClass, $method];
                }
            }
        }

        return null;
    }

    protected function callHealthService(object $service, string $method, ModuleValidationContext $context): mixed
    {
        $payload = [
            'module_name' => $context->moduleName,
            'module_path' => $context->modulePath,
            'source_type' => $context->sourceType,
            'source_id' => $context->sourceId,
            'options' => $context->options,
            'requested_by' => $context->requestedBy,
        ];

        $reflection = new ReflectionMethod($service, $method);
        $requiredParameters = $reflection->getNumberOfRequiredParameters();
        $totalParameters = $reflection->getNumberOfParameters();

        if ($totalParameters === 0) {
            return $service->{$method}();
        }

        if ($requiredParameters <= 1 && $totalParameters === 1) {
            $parameter = $reflection->getParameters()[0];
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin() && is_a($context, $type->getName())) {
                return $service->{$method}($context);
            }

            return $service->{$method}($payload);
        }

        return $service->{$method}($context->moduleName, $context->modulePath, $payload);
    }

    protected function normalizeRawHealthResult(mixed $rawResult): array
    {
        if ($rawResult instanceof ModuleValidationResult) {
            return $rawResult->findings;
        }

        if (is_object($rawResult) && method_exists($rawResult, 'toArray')) {
            $rawResult = $rawResult->toArray();
        }

        if (is_object($rawResult)) {
            $rawResult = get_object_vars($rawResult);
        }

        if (! is_array($rawResult)) {
            return [];
        }

        return $this->normalizeExternalResult($rawResult);
    }

    protected function filterRawHealthResultForContext(mixed $rawResult, ModuleValidationContext $context): mixed
    {
        $payload = $rawResult;

        if (is_object($payload) && method_exists($payload, 'toArray')) {
            $payload = $payload->toArray();
        } elseif (is_object($payload)) {
            $payload = get_object_vars($payload);
        }

        if (! is_array($payload)) {
            return $rawResult;
        }

        $items = $this->extractResultItems($payload);
        if (empty($items)) {
            return $rawResult;
        }

        $targetName = $this->normalizeModuleIdentity($context->moduleName);
        $targetPath = $this->normalizeModulePath($context->modulePath);

        $filtered = [];
        foreach ($items as $item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $item = $item->toArray();
            } elseif (is_object($item)) {
                $item = get_object_vars($item);
            }

            if (! is_array($item)) {
                continue;
            }

            $itemName = $this->normalizeModuleIdentity((string) ($item['module_name'] ?? $item['name'] ?? ''));
            $itemPath = $this->normalizeModulePath((string) ($item['module_path'] ?? $item['path'] ?? ''));

            if (($itemName !== '' && $itemName === $targetName) || ($itemPath !== '' && $itemPath === $targetPath)) {
                $filtered[] = $item;
            }
        }

        if (empty($filtered)) {
            return [
                'findings' => [[
                    'code' => 'HEALTH_TARGET_MODULE_NOT_FOUND_IN_SCAN',
                    'status' => 'warning',
                    'severity' => 'medium',
                    'title' => 'Target module not found in Module Health scan',
                    'message' => 'Module Health returned a scan, but no item matched the module being validated.',
                    'module_name' => $context->moduleName,
                    'module_path' => $context->modulePath,
                ]],
            ];
        }

        return ['items' => $filtered];
    }

    protected function normalizeExternalResult(array $payload): array
    {
        $items = $this->extractResultItems($payload);

        if (empty($items)) {
            $summaryFinding = $this->normalizeSingleItem($payload, 'HEALTH_EXTERNAL_RESULT');
            return $summaryFinding ? [$summaryFinding] : [];
        }

        $findings = [];
        foreach ($items as $index => $item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $item = $item->toArray();
            } elseif (is_object($item)) {
                $item = get_object_vars($item);
            }

            if (! is_array($item)) {
                continue;
            }

            $finding = $this->normalizeSingleItem($item, 'HEALTH_CHECK_' . ($index + 1));
            if ($finding !== null) {
                $findings[] = $finding;
            }
        }

        return $findings;
    }

    protected function extractResultItems(array $payload): array
    {
        $knownKeys = (array) config('module-health-bridge.known_result_keys', []);

        foreach ($knownKeys as $key) {
            $value = Arr::get($payload, $key);
            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        return [];
    }

    protected function normalizeSingleItem(array $item, string $fallbackCode): ?ModuleValidationFinding
    {
        $code = (string) ($item['code'] ?? $item['key'] ?? $item['check'] ?? $fallbackCode);
        $title = (string) ($item['title'] ?? $item['name'] ?? str_replace('_', ' ', $code));
        $message = (string) ($item['message'] ?? $item['description'] ?? $item['summary'] ?? $title);
        $status = $this->normalizeStatus((string) ($item['status'] ?? $item['result'] ?? $item['state'] ?? 'warning'));
        $severity = $this->normalizeSeverity((string) ($item['severity'] ?? $item['level'] ?? 'medium'));
        $filePath = $item['file_path'] ?? $item['filePath'] ?? $item['path'] ?? null;
        $lineNumber = $item['line_number'] ?? $item['line'] ?? null;
        $recommendation = $item['recommendation'] ?? $item['fix'] ?? $item['suggestion'] ?? null;
        $expected = $item['expected_value'] ?? $item['expected'] ?? null;
        $actual = $item['actual_value'] ?? $item['actual'] ?? null;

        return new ModuleValidationFinding(
            code: strtoupper((string) $code),
            status: $status,
            severity: $severity,
            title: $title,
            message: $message,
            filePath: is_string($filePath) ? $filePath : null,
            lineNumber: is_numeric($lineNumber) ? (int) $lineNumber : null,
            expectedValue: $expected,
            actualValue: $actual,
            recommendation: is_string($recommendation) ? $recommendation : null,
            autoFixAvailable: (bool) ($item['auto_fix_available'] ?? $item['autoFixAvailable'] ?? false),
            metadata: Arr::except($item, [
                'code', 'key', 'check', 'title', 'name', 'message', 'description', 'summary',
                'status', 'result', 'state', 'severity', 'level', 'file_path', 'filePath', 'path',
                'line_number', 'line', 'recommendation', 'fix', 'suggestion', 'expected_value',
                'expected', 'actual_value', 'actual', 'auto_fix_available', 'autoFixAvailable',
            ])
        );
    }

    protected function normalizeModuleIdentity(string $value): string
    {
        return Str::of($value)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }

    protected function normalizeModulePath(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return str_replace('\\', '/', strtolower(rtrim($value, '\\/')));
    }

    protected function normalizeStatus(string $status): ValidationStatus
    {
        $normalized = strtolower(trim($status));
        $mapped = config('module-health-bridge.status_aliases.' . $normalized, $normalized);

        return match ($mapped) {
            'passed' => ValidationStatus::Passed,
            'failed' => ValidationStatus::Failed,
            'skipped' => ValidationStatus::Skipped,
            'manual_review_required' => ValidationStatus::ManualReviewRequired,
            default => ValidationStatus::Warning,
        };
    }

    protected function normalizeSeverity(string $severity): ValidationSeverity
    {
        $normalized = strtolower(trim($severity));
        $mapped = config('module-health-bridge.severity_aliases.' . $normalized, $normalized);

        return match ($mapped) {
            'info' => ValidationSeverity::Info,
            'low' => ValidationSeverity::Low,
            'high' => ValidationSeverity::High,
            'critical' => ValidationSeverity::Critical,
            'blocker' => ValidationSeverity::Blocker,
            default => ValidationSeverity::Medium,
        };
    }
}
