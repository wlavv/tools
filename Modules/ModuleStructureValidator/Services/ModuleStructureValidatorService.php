<?php

namespace Modules\ModuleStructureValidator\Services;

use Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleComplianceCore\DTO\ModuleValidationFinding;
use Modules\ModuleComplianceCore\DTO\ModuleValidationResult;
use Modules\ModuleComplianceCore\Enums\ValidationSeverity;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;
use Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator;

class ModuleStructureValidatorService implements ModuleValidatorInterface
{
    public function __construct(
        protected ComplianceScoreCalculator $scoreCalculator,
    ) {
    }

    public function key(): string
    {
        return 'structure';
    }

    public function label(): string
    {
        return 'Module Structure Validator';
    }

    public function area(): string
    {
        return 'structure';
    }

    public function validate(ModuleValidationContext $context): ModuleValidationResult
    {
        $findings = [];
        $modulePath = rtrim($context->modulePath, DIRECTORY_SEPARATOR);

        $findings[] = $this->checkModulePath($modulePath);

        if (! is_dir($modulePath)) {
            return $this->buildResult($findings, ['module_path' => $modulePath]);
        }

        $findings = array_merge($findings, $this->checkRequiredFiles($modulePath));
        $findings = array_merge($findings, $this->checkRequiredDirectories($modulePath));
        $findings = array_merge($findings, $this->checkManifest($modulePath));
        $findings = array_merge($findings, $this->checkRootStructure($modulePath));
        $findings = array_merge($findings, $this->checkMigrations($modulePath));

        return $this->buildResult($findings, [
            'module_name' => $context->moduleName,
            'module_path' => $modulePath,
        ]);
    }

    protected function checkModulePath(string $modulePath): ModuleValidationFinding
    {
        if (is_dir($modulePath)) {
            return ModuleValidationFinding::passed(
                'STRUCTURE_MODULE_PATH_EXISTS',
                'Module path exists',
                'The module path exists and is readable.',
                $modulePath
            );
        }

        return ModuleValidationFinding::failed(
            'STRUCTURE_MODULE_PATH_MISSING',
            'Module path missing',
            'The provided module path does not exist or is not a directory.',
            ValidationSeverity::Blocker,
            $modulePath,
            'Existing module directory',
            $modulePath,
            'Confirm the module path and ensure the module exists under /Modules/{ModuleName}.'
        );
    }

    protected function checkRequiredFiles(string $modulePath): array
    {
        $findings = [];

        foreach (config('module-structure-validator.required_files', []) as $file => $severity) {
            $path = $modulePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
            if (is_file($path)) {
                $findings[] = ModuleValidationFinding::passed(
                    'STRUCTURE_FILE_EXISTS_' . strtoupper(str_replace(['/', '.', '-'], '_', $file)),
                    'Required file exists: ' . $file,
                    'Required file was found.',
                    $path
                );
                continue;
            }

            $findings[] = ModuleValidationFinding::failed(
                'STRUCTURE_FILE_MISSING_' . strtoupper(str_replace(['/', '.', '-'], '_', $file)),
                'Required file missing: ' . $file,
                'A required file is missing from the module.',
                $this->severityFromString($severity),
                $path,
                'File exists',
                'File missing',
                'Create the required file following the LSG module standard.'
            );
        }

        return $findings;
    }

    protected function checkRequiredDirectories(string $modulePath): array
    {
        $findings = [];

        foreach (config('module-structure-validator.required_directories', []) as $directory => $severity) {
            $path = $modulePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory);
            $alternativePath = $this->alternativeDirectoryPath($modulePath, $directory);

            if (is_dir($path) || ($alternativePath && is_dir($alternativePath))) {
                $findings[] = ModuleValidationFinding::passed(
                    'STRUCTURE_DIR_EXISTS_' . strtoupper(str_replace(['/', '.', '-'], '_', $directory)),
                    'Required directory exists: ' . $directory,
                    'Required directory was found.',
                    is_dir($path) ? $path : $alternativePath
                );
                continue;
            }

            $findings[] = ModuleValidationFinding::warning(
                'STRUCTURE_DIR_MISSING_' . strtoupper(str_replace(['/', '.', '-'], '_', $directory)),
                'Recommended directory missing: ' . $directory,
                'A recommended LSG module directory is missing.',
                $this->severityFromString($severity),
                $path,
                'Create the directory if this module requires this layer.'
            );
        }

        return $findings;
    }

    protected function alternativeDirectoryPath(string $modulePath, string $directory): ?string
    {
        return match ($directory) {
            'lang/pt' => $modulePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'pt',
            'lang/en' => $modulePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'en',
            default => null,
        };
    }

    protected function checkManifest(string $modulePath): array
    {
        $findings = [];
        $path = $modulePath . DIRECTORY_SEPARATOR . 'module.json';

        if (! is_file($path)) {
            return $findings;
        }

        $content = file_get_contents($path);
        $manifest = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($manifest)) {
            return [ModuleValidationFinding::failed(
                'STRUCTURE_MANIFEST_INVALID_JSON',
                'Invalid module.json',
                'module.json is not valid JSON: ' . json_last_error_msg(),
                ValidationSeverity::Blocker,
                $path,
                'Valid JSON object',
                json_last_error_msg(),
                'Fix the JSON syntax before loading the module.'
            )];
        }

        $findings[] = ModuleValidationFinding::passed(
            'STRUCTURE_MANIFEST_VALID_JSON',
            'Valid module.json',
            'module.json is valid JSON.',
            $path
        );

        foreach (config('module-structure-validator.manifest_required_keys', []) as $key) {
            if (array_key_exists($key, $manifest) && $manifest[$key] !== null && $manifest[$key] !== '') {
                $findings[] = ModuleValidationFinding::passed(
                    'STRUCTURE_MANIFEST_KEY_EXISTS_' . strtoupper($key),
                    'Manifest key exists: ' . $key,
                    'Required manifest key is present.',
                    $path
                );
            } else {
                $findings[] = ModuleValidationFinding::failed(
                    'STRUCTURE_MANIFEST_KEY_MISSING_' . strtoupper($key),
                    'Manifest key missing: ' . $key,
                    'Required manifest key is missing or empty.',
                    ValidationSeverity::High,
                    $path,
                    'Manifest key present',
                    'Missing or empty',
                    'Add the missing key to module.json.'
                );
            }
        }

        if (! empty($manifest['provider'])) {
            $providerPath = $this->providerClassToPath($modulePath, (string) $manifest['provider']);
            if ($providerPath && is_file($providerPath)) {
                $findings[] = ModuleValidationFinding::passed(
                    'STRUCTURE_PROVIDER_FILE_EXISTS',
                    'Provider file exists',
                    'The provider referenced in module.json exists.',
                    $providerPath
                );
                $findings = array_merge($findings, $this->checkProviderContent($providerPath));
            } else {
                $findings[] = ModuleValidationFinding::failed(
                    'STRUCTURE_PROVIDER_FILE_MISSING',
                    'Provider file missing',
                    'The provider referenced in module.json could not be found.',
                    ValidationSeverity::Blocker,
                    $providerPath ?: $modulePath,
                    'Provider PHP file exists',
                    $manifest['provider'],
                    'Create the provider file or correct the provider namespace in module.json.'
                );
            }
        }

        if (isset($manifest['permissions']) && is_array($manifest['permissions'])) {
            $findings = array_merge($findings, $this->checkPermissions($path, $manifest['permissions']));
        }

        return $findings;
    }

    protected function checkProviderContent(string $providerPath): array
    {
        $content = file_get_contents($providerPath) ?: '';
        $checks = [
            'register' => 'public function register',
            'boot' => 'public function boot',
            'routes' => 'loadRoutesFrom',
            'views' => 'loadViewsFrom',
            'translations' => 'loadTranslationsFrom',
            'migrations' => 'loadMigrationsFrom',
        ];

        $findings = [];
        foreach ($checks as $key => $needle) {
            if (str_contains($content, $needle)) {
                $findings[] = ModuleValidationFinding::passed(
                    'STRUCTURE_PROVIDER_' . strtoupper($key),
                    'Provider handles ' . $key,
                    'Provider contains expected ' . $key . ' registration.',
                    $providerPath
                );
            } else {
                $findings[] = ModuleValidationFinding::warning(
                    'STRUCTURE_PROVIDER_MISSING_' . strtoupper($key),
                    'Provider may be missing ' . $key,
                    'Provider does not contain expected pattern: ' . $needle,
                    in_array($key, ['register', 'boot'], true) ? ValidationSeverity::High : ValidationSeverity::Medium,
                    $providerPath,
                    'Add the expected provider registration if applicable.'
                );
            }
        }
        return $findings;
    }

    protected function checkPermissions(string $manifestPath, array $permissions): array
    {
        $findings = [];
        $prefix = config('module-structure-validator.permission_prefix', 'permission_');

        if (empty($permissions)) {
            return [ModuleValidationFinding::warning(
                'STRUCTURE_PERMISSIONS_EMPTY',
                'Permissions are empty',
                'The manifest permissions array is empty.',
                ValidationSeverity::Medium,
                $manifestPath,
                'Define at least view/run/manage permissions when applicable.'
            )];
        }

        foreach ($permissions as $permission) {
            if (is_string($permission) && str_starts_with($permission, $prefix)) {
                $findings[] = ModuleValidationFinding::passed(
                    'STRUCTURE_PERMISSION_PREFIX_OK',
                    'Permission prefix valid',
                    'Permission uses the expected permission_* prefix.',
                    $manifestPath,
                    ['permission' => $permission]
                );
            } else {
                $findings[] = ModuleValidationFinding::failed(
                    'STRUCTURE_PERMISSION_PREFIX_INVALID',
                    'Permission prefix invalid',
                    'Permission does not use the expected permission_* prefix.',
                    ValidationSeverity::High,
                    $manifestPath,
                    $prefix . '*',
                    $permission,
                    'Rename permission to use permission_* prefix.'
                );
            }
        }

        return $findings;
    }

    protected function checkRootStructure(string $modulePath): array
    {
        $findings = [];
        $allowedFiles = config('module-structure-validator.safe_root_files', []);
        $allowedDirectories = config('module-structure-validator.allowed_root_directories', []);

        foreach (scandir($modulePath) ?: [] as $entry) {
            if (in_array($entry, ['.', '..'], true)) {
                continue;
            }

            $path = $modulePath . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path) && ! in_array($entry, $allowedDirectories, true)) {
                $findings[] = ModuleValidationFinding::warning(
                    'STRUCTURE_ROOT_DIR_NOT_STANDARD',
                    'Non-standard root directory',
                    'A root directory is not part of the current LSG standard.',
                    ValidationSeverity::Low,
                    $path,
                    'Confirm this directory is intentional or move it under a standard layer.'
                );
            }

            if (is_file($path) && ! in_array($entry, $allowedFiles, true) && ! str_ends_with(strtolower($entry), '.md')) {
                $findings[] = ModuleValidationFinding::warning(
                    'STRUCTURE_ROOT_FILE_NOT_STANDARD',
                    'Non-standard root file',
                    'A root file is not part of the current LSG standard.',
                    ValidationSeverity::Low,
                    $path,
                    'Confirm this file is intentional or move it under a standard layer.'
                );
            }
        }

        return $findings;
    }

    protected function checkMigrations(string $modulePath): array
    {
        $findings = [];
        $migrationPath = $modulePath . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations';
        if (! is_dir($migrationPath)) {
            return $findings;
        }

        $maxLength = (int) config('module-structure-validator.max_mysql_identifier_length', 64);
        foreach (glob($migrationPath . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $content = file_get_contents($file) ?: '';
            preg_match_all('/->(?:unique|index)\s*\([^;]*?\)/s', $content, $matches);
            foreach ($matches[0] ?? [] as $match) {
                if (preg_match('/["\']([a-zA-Z0-9_]{' . ($maxLength + 1) . ',})["\']/', $match, $nameMatch)) {
                    $findings[] = ModuleValidationFinding::failed(
                        'STRUCTURE_MIGRATION_INDEX_NAME_TOO_LONG',
                        'Migration index name too long',
                        'A migration index/constraint name appears to exceed MySQL identifier limits.',
                        ValidationSeverity::High,
                        $file,
                        'Identifier length <= ' . $maxLength,
                        $nameMatch[1],
                        'Use a shorter explicit index name.'
                    );
                }
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'STRUCTURE_MIGRATION_INDEX_NAMES_SAFE',
                'Migration index names look safe',
                'No obviously overlong explicit index names were found.',
                $migrationPath
            );
        }

        return $findings;
    }

    protected function providerClassToPath(string $modulePath, string $providerClass): ?string
    {
        $parts = explode('\\', trim($providerClass, '\\'));
        $moduleIndex = array_search('Modules', $parts, true);
        if ($moduleIndex === false) {
            return null;
        }

        $relativeParts = array_slice($parts, $moduleIndex + 2);
        if (empty($relativeParts)) {
            return null;
        }

        return $modulePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $relativeParts) . '.php';
    }

    protected function severityFromString(string $severity): ValidationSeverity
    {
        return ValidationSeverity::tryFrom($severity) ?? ValidationSeverity::Medium;
    }

    protected function buildResult(array $findings, array $metadata = []): ModuleValidationResult
    {
        $score = $this->scoreCalculator->calculate($findings);
        $status = ValidationStatus::Passed;

        foreach ($findings as $finding) {
            if ($finding->status === ValidationStatus::Failed) {
                $status = ValidationStatus::Failed;
                break;
            }
            if ($finding->status === ValidationStatus::Warning) {
                $status = ValidationStatus::Warning;
            }
        }

        return new ModuleValidationResult(
            validator: $this->key(),
            area: $this->area(),
            label: $this->label(),
            findings: $findings,
            score: $score,
            status: $status,
            metadata: $metadata,
        );
    }
}
