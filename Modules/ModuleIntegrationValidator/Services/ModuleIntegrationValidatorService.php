<?php

namespace Modules\ModuleIntegrationValidator\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleComplianceCore\DTO\ModuleValidationFinding;
use Modules\ModuleComplianceCore\DTO\ModuleValidationResult;
use Modules\ModuleComplianceCore\Enums\ValidationSeverity;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;
use Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator;
use Throwable;

class ModuleIntegrationValidatorService implements ModuleValidatorInterface
{
    public function __construct(
        protected ComplianceScoreCalculator $scoreCalculator,
    ) {
    }

    public function key(): string
    {
        return 'integration';
    }

    public function label(): string
    {
        return 'Module Integration Validator';
    }

    public function area(): string
    {
        return 'integration';
    }

    public function validate(ModuleValidationContext $context): ModuleValidationResult
    {
        $findings = [];
        $modulePath = rtrim($context->modulePath, DIRECTORY_SEPARATOR);

        $manifest = $this->readManifest($modulePath, $findings);

        $this->validateManifestIntegration($modulePath, $manifest, $findings);
        $this->validateProviderIntegration($modulePath, $manifest, $findings);
        $this->validateRoutesIntegration($modulePath, $manifest, $findings);
        $this->validateViewNamespace($modulePath, $manifest, $findings);
        $this->validateTranslations($modulePath, $manifest, $findings);
        $this->validatePermissions($modulePath, $manifest, $findings);
        $this->validateMenuIntegration($modulePath, $manifest, $findings);
        $this->validateAssets($modulePath, $findings);
        $this->validateCoreIsolation($modulePath, $findings);
        $this->validateCompatibilityMetadata($manifest, $findings);

        $score = $this->scoreCalculator->calculate($findings);
        $status = $this->resolveStatus($findings);

        return new ModuleValidationResult(
            validator: $this->key(),
            area: $this->area(),
            label: $this->label(),
            findings: $findings,
            score: $score,
            status: $status,
            metadata: [
                'module_name' => $context->moduleName,
                'module_path' => $context->modulePath,
            ],
        );
    }

    protected function readManifest(string $modulePath, array &$findings): array
    {
        $path = $modulePath . DIRECTORY_SEPARATOR . 'module.json';

        if (! File::exists($path)) {
            $findings[] = $this->finding('INTEGRATION_MANIFEST_MISSING', ValidationStatus::Failed, ValidationSeverity::Blocker, 'Missing module.json.', 'The module cannot be integrated without a manifest.', $path, 'Create a valid module.json with name, slug, version, provider and permissions.');
            return [];
        }

        try {
            $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
            $findings[] = $this->finding('INTEGRATION_MANIFEST_READABLE', ValidationStatus::Passed, ValidationSeverity::Info, 'module.json is readable.', 'The integration manifest can be parsed.', $path);
            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            $findings[] = $this->finding('INTEGRATION_MANIFEST_INVALID_JSON', ValidationStatus::Failed, ValidationSeverity::Blocker, 'Invalid module.json.', $e->getMessage(), $path, 'Fix the JSON syntax before attempting integration.');
            return [];
        }
    }

    protected function validateManifestIntegration(string $modulePath, array $manifest, array &$findings): void
    {
        foreach (config('module-integration-validator.required_manifest_keys', []) as $key) {
            $status = Arr::has($manifest, $key) && filled(Arr::get($manifest, $key)) ? ValidationStatus::Passed : ValidationStatus::Failed;
            $findings[] = $this->finding(
                'INTEGRATION_MANIFEST_KEY_' . strtoupper(str_replace('.', '_', $key)),
                $status,
                $status === ValidationStatus::Passed ? ValidationSeverity::Info : ValidationSeverity::High,
                "Manifest key '{$key}'.",
                $status === ValidationStatus::Passed ? "Manifest contains {$key}." : "Manifest is missing {$key}.",
                $modulePath . DIRECTORY_SEPARATOR . 'module.json',
                $status === ValidationStatus::Passed ? null : "Add '{$key}' to module.json."
            );
        }

        $slug = Arr::get($manifest, 'slug');
        if ($slug && ! preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $findings[] = $this->finding('INTEGRATION_MANIFEST_SLUG_FORMAT', ValidationStatus::Warning, ValidationSeverity::Medium, 'Manifest slug format.', 'Slug should be lowercase kebab-case.', $modulePath . DIRECTORY_SEPARATOR . 'module.json', 'Use lowercase kebab-case, e.g. module-integration-validator.');
        } elseif ($slug) {
            $findings[] = $this->finding('INTEGRATION_MANIFEST_SLUG_FORMAT', ValidationStatus::Passed, ValidationSeverity::Info, 'Manifest slug format.', 'Slug follows lowercase kebab-case.', $modulePath . DIRECTORY_SEPARATOR . 'module.json');
        }
    }

    protected function validateProviderIntegration(string $modulePath, array $manifest, array &$findings): void
    {
        $provider = Arr::get($manifest, 'provider');
        if (! $provider) {
            $findings[] = $this->finding('INTEGRATION_PROVIDER_DECLARED', ValidationStatus::Failed, ValidationSeverity::Blocker, 'Provider declaration missing.', 'module.json does not declare a provider.', $modulePath . DIRECTORY_SEPARATOR . 'module.json', 'Declare the module ServiceProvider.');
            return;
        }

        $providerPath = $this->providerClassToPath($modulePath, $provider);
        if (! File::exists($providerPath)) {
            $findings[] = $this->finding('INTEGRATION_PROVIDER_FILE_EXISTS', ValidationStatus::Failed, ValidationSeverity::Blocker, 'Provider file missing.', "Could not find provider file for {$provider}.", $providerPath, 'Create the provider file or fix the provider namespace in module.json.');
            return;
        }

        $contents = File::get($providerPath);
        $findings[] = $this->finding('INTEGRATION_PROVIDER_FILE_EXISTS', ValidationStatus::Passed, ValidationSeverity::Info, 'Provider file exists.', "Provider file found for {$provider}.", $providerPath);

        foreach (config('module-integration-validator.expected_provider_methods', []) as $method) {
            $ok = preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $contents) === 1;
            $findings[] = $this->finding(
                'INTEGRATION_PROVIDER_METHOD_' . strtoupper($method),
                $ok ? ValidationStatus::Passed : ValidationStatus::Failed,
                $ok ? ValidationSeverity::Info : ValidationSeverity::High,
                "Provider method {$method}().",
                $ok ? "Provider defines {$method}()." : "Provider does not define {$method}().",
                $providerPath,
                $ok ? null : "Add {$method}() to the provider."
            );
        }

        foreach (config('module-integration-validator.expected_provider_calls', []) as $call) {
            $ok = str_contains($contents, $call);
            $findings[] = $this->finding(
                'INTEGRATION_PROVIDER_CALL_' . strtoupper($call),
                $ok ? ValidationStatus::Passed : ValidationStatus::Failed,
                $ok ? ValidationSeverity::Info : ValidationSeverity::High,
                "Provider call {$call}.",
                $ok ? "Provider uses {$call}." : "Provider does not use {$call}.",
                $providerPath,
                $ok ? null : "Add {$call} to provider boot/register as appropriate."
            );
        }
    }

    protected function validateRoutesIntegration(string $modulePath, array $manifest, array &$findings): void
    {
        $routesPath = $modulePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        if (! File::exists($routesPath)) {
            $findings[] = $this->finding('INTEGRATION_ROUTES_WEB_EXISTS', ValidationStatus::Failed, ValidationSeverity::High, 'routes/web.php missing.', 'The module has no web routes file.', $routesPath, 'Create routes/web.php and load it from the provider.');
            return;
        }

        $contents = $this->readRouteContents($routesPath);
        $findings[] = $this->finding('INTEGRATION_ROUTES_WEB_EXISTS', ValidationStatus::Passed, ValidationSeverity::Info, 'routes/web.php exists.', 'Web routes file found.', $routesPath);

        $hasName = str_contains($contents, "->name(") || str_contains($contents, "'as'") || str_contains($contents, '"as"');
        $findings[] = $this->finding(
            'INTEGRATION_ROUTES_NAMED',
            $hasName ? ValidationStatus::Passed : ValidationStatus::Warning,
            $hasName ? ValidationSeverity::Info : ValidationSeverity::Medium,
            'Named routes.',
            $hasName ? 'Routes appear to use names.' : 'Routes may not be named.',
            $routesPath,
            $hasName ? null : config('module-integration-validator.route_name_recommendation')
        );

        $hasMiddleware = str_contains($contents, 'middleware') || str_contains($contents, 'auth') || str_contains($contents, 'permission');
        $findings[] = $this->finding(
            'INTEGRATION_ROUTES_MIDDLEWARE',
            $hasMiddleware ? ValidationStatus::Passed : ValidationStatus::Warning,
            $hasMiddleware ? ValidationSeverity::Info : ValidationSeverity::High,
            'Route middleware.',
            $hasMiddleware ? 'Routes appear to declare middleware.' : 'Routes do not visibly declare middleware/auth/permission.',
            $routesPath,
            $hasMiddleware ? null : 'Protect BO routes with auth and permission middleware.'
        );

        $slug = Arr::get($manifest, 'slug');
        if ($slug) {
            $usesSlug = str_contains($contents, $slug) || str_contains($contents, Str::camel($slug)) || str_contains($contents, Str::studly($slug));
            $findings[] = $this->finding(
                'INTEGRATION_ROUTES_PREFIX_MATCH_MODULE',
                $usesSlug ? ValidationStatus::Passed : ValidationStatus::Warning,
                $usesSlug ? ValidationSeverity::Info : ValidationSeverity::Low,
                'Route prefix/name matches module.',
                $usesSlug ? 'Routes appear to reference the module slug/name.' : 'Routes may not reference the module slug/name.',
                $routesPath,
                $usesSlug ? null : 'Use a stable route prefix and name based on the module slug.'
            );
        }
    }

    protected function validateViewNamespace(string $modulePath, array $manifest, array &$findings): void
    {
        $viewsPath = $modulePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views';
        if (! File::isDirectory($viewsPath)) {
            $findings[] = $this->finding('INTEGRATION_VIEWS_DIRECTORY_EXISTS', ValidationStatus::Failed, ValidationSeverity::High, 'Views directory missing.', 'Resources/views does not exist.', $viewsPath, 'Create Resources/views and load it from the provider.');
            return;
        }

        $findings[] = $this->finding('INTEGRATION_VIEWS_DIRECTORY_EXISTS', ValidationStatus::Passed, ValidationSeverity::Info, 'Views directory exists.', 'Resources/views found.', $viewsPath);

        $bladeFiles = File::allFiles($viewsPath);
        if (count($bladeFiles) === 0) {
            $findings[] = $this->finding('INTEGRATION_VIEWS_NOT_EMPTY', ValidationStatus::Warning, ValidationSeverity::Medium, 'Views directory is empty.', 'No Blade views were found.', $viewsPath, 'Add at least the module dashboard/list/detail views.');
        } else {
            $findings[] = $this->finding('INTEGRATION_VIEWS_NOT_EMPTY', ValidationStatus::Passed, ValidationSeverity::Info, 'Views found.', count($bladeFiles) . ' view file(s) found.', $viewsPath);
        }
    }

    protected function readRouteContents(string $routesPath): string
    {
        $contents = File::get($routesPath);

        if (preg_match_all('/require\s+__DIR__\s*\.\s*[\'"]([^\'"]+)[\'"]\s*;/', $contents, $matches)) {
            foreach ($matches[1] as $relativePath) {
                $includedPath = dirname($routesPath) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
                if (File::exists($includedPath)) {
                    $contents .= "\n" . File::get($includedPath);
                }
            }
        }

        return $contents;
    }

    protected function validateTranslations(string $modulePath, array $manifest, array &$findings): void
    {
        foreach (['pt', 'en'] as $locale) {
            $path = $modulePath . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $locale;
            $resourcesPath = $modulePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $locale;
            $ok = (File::isDirectory($path) && count(File::files($path)) > 0)
                || (File::isDirectory($resourcesPath) && count(File::files($resourcesPath)) > 0);
            $findings[] = $this->finding(
                'INTEGRATION_TRANSLATIONS_' . strtoupper($locale),
                $ok ? ValidationStatus::Passed : ValidationStatus::Warning,
                $ok ? ValidationSeverity::Info : ValidationSeverity::Medium,
                "Translations {$locale}.",
                $ok ? "Translations for {$locale} found." : "Translations for {$locale} are missing or empty.",
                File::isDirectory($path) ? $path : $resourcesPath,
                $ok ? null : "Create lang/{$locale}/ or Resources/lang/{$locale}/ files and ensure provider calls loadTranslationsFrom."
            );
        }
    }

    protected function validatePermissions(string $modulePath, array $manifest, array &$findings): void
    {
        $permissions = Arr::get($manifest, 'permissions', []);
        if (! is_array($permissions) || count($permissions) === 0) {
            $findings[] = $this->finding('INTEGRATION_PERMISSIONS_DECLARED', ValidationStatus::Failed, ValidationSeverity::High, 'Permissions missing.', 'No permissions declared in module.json.', $modulePath . DIRECTORY_SEPARATOR . 'module.json', 'Declare module permissions with permission_* prefix.');
            return;
        }

        $findings[] = $this->finding('INTEGRATION_PERMISSIONS_DECLARED', ValidationStatus::Passed, ValidationSeverity::Info, 'Permissions declared.', count($permissions) . ' permission(s) declared.', $modulePath . DIRECTORY_SEPARATOR . 'module.json');

        foreach ($permissions as $permission) {
            $ok = is_string($permission) && str_starts_with($permission, config('module-integration-validator.permission_prefix', 'permission_'));
            $findings[] = $this->finding(
                'INTEGRATION_PERMISSION_PREFIX_' . strtoupper(Str::slug((string) $permission, '_')),
                $ok ? ValidationStatus::Passed : ValidationStatus::Failed,
                $ok ? ValidationSeverity::Info : ValidationSeverity::High,
                "Permission {$permission}.",
                $ok ? 'Permission uses expected prefix.' : 'Permission does not use permission_* prefix.',
                $modulePath . DIRECTORY_SEPARATOR . 'module.json',
                $ok ? null : 'Rename permission using permission_* prefix.'
            );
        }
    }

    protected function validateMenuIntegration(string $modulePath, array $manifest, array &$findings): void
    {
        $menu = Arr::get($manifest, 'menu');
        if (! $menu) {
            $findings[] = $this->finding('INTEGRATION_MENU_METADATA', ValidationStatus::Warning, ValidationSeverity::Low, 'Menu metadata missing.', 'module.json has no menu metadata.', $modulePath . DIRECTORY_SEPARATOR . 'module.json', 'Add menu metadata if this module should appear in the B.O. navigation.');
            return;
        }

        $findings[] = $this->finding('INTEGRATION_MENU_METADATA', ValidationStatus::Passed, ValidationSeverity::Info, 'Menu metadata exists.', 'module.json contains menu metadata.', $modulePath . DIRECTORY_SEPARATOR . 'module.json');

        foreach (['label', 'route', 'icon'] as $key) {
            $ok = filled(Arr::get($menu, $key));
            $findings[] = $this->finding(
                'INTEGRATION_MENU_' . strtoupper($key),
                $ok ? ValidationStatus::Passed : ValidationStatus::Warning,
                $ok ? ValidationSeverity::Info : ValidationSeverity::Low,
                "Menu {$key}.",
                $ok ? "Menu {$key} is defined." : "Menu {$key} is missing.",
                $modulePath . DIRECTORY_SEPARATOR . 'module.json',
                $ok ? null : "Add menu.{$key} to module.json."
            );
        }
    }

    protected function validateAssets(string $modulePath, array &$findings): void
    {
        $viewsPath = $modulePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views';
        if (! File::isDirectory($viewsPath)) {
            return;
        }

        $patterns = config('module-integration-validator.asset_patterns', []);
        $assetReferences = 0;
        foreach (File::allFiles($viewsPath) as $file) {
            $contents = File::get($file->getRealPath());
            foreach ($patterns as $pattern) {
                if (str_contains($contents, $pattern)) {
                    $assetReferences++;
                    break;
                }
            }
        }

        $findings[] = $this->finding(
            'INTEGRATION_ASSET_LOADING_PATTERN',
            $assetReferences > 0 ? ValidationStatus::Passed : ValidationStatus::Warning,
            $assetReferences > 0 ? ValidationSeverity::Info : ValidationSeverity::Low,
            'Asset loading pattern.',
            $assetReferences > 0 ? "Found asset loading references in {$assetReferences} view(s)." : 'No explicit asset loading pattern found in views.',
            $viewsPath,
            $assetReferences > 0 ? null : 'Use @push, @section, Vite/Mix, asset() or the module asset helper when the module needs CSS/JS.'
        );
    }

    protected function validateCoreIsolation(string $modulePath, array &$findings): void
    {
        $violations = [];
        $writePatterns = [
            'file_put_contents',
            'fopen',
            'fwrite',
            'Storage::put',
            'File::put',
            'copy(',
            'rename(',
            'unlink(',
        ];

        foreach (File::allFiles($modulePath) as $file) {
            $contents = File::get($file->getRealPath());
            $scanContents = $this->stripCommentsAndStrings($contents);

            if (! $this->containsAny($scanContents, $writePatterns)) {
                continue;
            }

            foreach (config('module-integration-validator.core_write_forbidden_patterns', []) as $pattern) {
                if (str_contains($scanContents, $pattern)) {
                    $violations[] = [$file->getRealPath(), $pattern];
                }
            }
        }

        if (count($violations) === 0) {
            $findings[] = $this->finding('INTEGRATION_CORE_ISOLATION', ValidationStatus::Passed, ValidationSeverity::Info, 'Core isolation.', 'No obvious core write/reference pattern detected.', $modulePath);
            return;
        }

        foreach ($violations as [$filePath, $pattern]) {
            $findings[] = $this->finding('INTEGRATION_CORE_ISOLATION_PATTERN', ValidationStatus::Warning, ValidationSeverity::Medium, 'Possible core modification reference.', "Detected reference to {$pattern}.", $filePath, 'Confirm the module is not modifying core files directly. Prefer provider-based registration and isolated module files.');
        }
    }

    protected function validateCompatibilityMetadata(array $manifest, array &$findings): void
    {
        $lsg = Arr::get($manifest, 'lsg', []);
        if (! is_array($lsg) || count($lsg) === 0) {
            $findings[] = $this->finding('INTEGRATION_LSG_METADATA', ValidationStatus::Warning, ValidationSeverity::Low, 'LSG metadata missing.', 'module.json has no lsg metadata block.', null, 'Add lsg metadata with type, standard and compatible_with when applicable.');
            return;
        }

        $findings[] = $this->finding('INTEGRATION_LSG_METADATA', ValidationStatus::Passed, ValidationSeverity::Info, 'LSG metadata exists.', 'module.json contains lsg metadata.');
    }

    protected function providerClassToPath(string $modulePath, string $provider): string
    {
        $parts = explode('\\', $provider);
        $moduleIndex = array_search('Modules', $parts, true);
        if ($moduleIndex !== false) {
            $relative = array_slice($parts, $moduleIndex + 2);
        } else {
            $relative = array_slice($parts, -2);
        }

        return $modulePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $relative) . '.php';
    }

    protected function containsAny(string $content, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern !== '' && str_contains($content, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function stripCommentsAndStrings(string $content): string
    {
        $withoutStrings = preg_replace('/(["\'])(?:\\\\.|(?!\1).)*\1/s', '""', $content);

        return preg_replace([
            '/\/\*.*?\*\//s',
            '/\/\/[^\r\n]*/',
        ], '', $withoutStrings ?? $content) ?? $content;
    }

    protected function finding(string $code, ValidationStatus $status, ValidationSeverity $severity, string $title, string $message, ?string $filePath = null, ?string $recommendation = null): ModuleValidationFinding
    {
        return new ModuleValidationFinding(
            code: $code,
            status: $status,
            severity: $severity,
            title: $title,
            message: $message,
            filePath: $filePath,
            recommendation: $recommendation
        );
    }

    protected function resolveStatus(array $findings): ValidationStatus
    {
        $status = ValidationStatus::Passed;

        foreach ($findings as $finding) {
            if ($finding->status === ValidationStatus::Failed) {
                return ValidationStatus::Failed;
            }

            if ($finding->status === ValidationStatus::Warning) {
                $status = ValidationStatus::Warning;
            }
        }

        return $status;
    }
}

