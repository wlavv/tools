<?php

namespace Modules\ModuleSecurityValidator\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleComplianceCore\DTO\ModuleValidationFinding;
use Modules\ModuleComplianceCore\DTO\ModuleValidationResult;
use Modules\ModuleComplianceCore\Enums\ValidationSeverity;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;
use Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator;

class ModuleSecurityValidatorService implements ModuleValidatorInterface
{
    public function __construct(
        protected ComplianceScoreCalculator $scoreCalculator,
    ) {
    }

    public function key(): string
    {
        return 'security';
    }

    public function label(): string
    {
        return 'Module Security Validator';
    }

    public function area(): string
    {
        return 'security';
    }

    public function validate(ModuleValidationContext $context): ModuleValidationResult
    {
        $modulePath = rtrim($context->modulePath, DIRECTORY_SEPARATOR);
        $findings = [];

        if (! is_dir($modulePath)) {
            $findings[] = ModuleValidationFinding::failed(
                'SECURITY_MODULE_PATH_MISSING',
                'Module path missing',
                'The provided module path does not exist or is not a directory.',
                $this->severity('module_path_missing'),
                $modulePath,
                'Existing module directory',
                $modulePath,
                'Confirm the module path before running security validation.'
            );

            return $this->buildResult($findings, $context, []);
        }

        $files = $this->discoverScannableFiles($modulePath);
        if (empty($files)) {
            $findings[] = ModuleValidationFinding::warning(
                'SECURITY_NO_SCANNABLE_FILES',
                'No scannable files found',
                'The validator did not find PHP, Blade or JS files to scan.',
                ValidationSeverity::Medium,
                $modulePath,
                'Add module implementation files or confirm this is an empty module.'
            );

            return $this->buildResult($findings, $context, $files);
        }

        $findings[] = ModuleValidationFinding::passed(
            'SECURITY_SCANNABLE_FILES_FOUND',
            'Scannable files found',
            count($files) . ' file(s) found for security validation.',
            $modulePath
        );

        $contents = $this->readFiles($files);
        $combined = implode("
", array_values($contents));

        $findings = array_merge($findings, $this->checkDangerousFunctions($contents));
        $findings = array_merge($findings, $this->checkEnvWrites($contents));
        $findings = array_merge($findings, $this->checkPathTraversalRisks($contents));
        $findings = array_merge($findings, $this->checkRouteProtection($modulePath));
        $findings = array_merge($findings, $this->checkUploadValidation($contents));
        $findings = array_merge($findings, $this->checkCoreWriteAttempts($contents));
        $findings = array_merge($findings, $this->checkRawSqlRisks($contents));
        $findings = array_merge($findings, $this->checkCsrfDisabled($contents));
        $findings = array_merge($findings, $this->checkDebugCode($contents));
        $findings = array_merge($findings, $this->checkMassAssignment($contents));

        return $this->buildResult($findings, $context, $files);
    }

    protected function checkDangerousFunctions(array $contents): array
    {
        $findings = [];
        $functions = config('module-security-validator.dangerous_php_functions', []);

        foreach ($contents as $file => $content) {
            foreach ($functions as $function) {
                if (preg_match('/(?<![A-Za-z0-9_])' . preg_quote($function, '/') . '\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $findings[] = ModuleValidationFinding::failed(
                        'SECURITY_DANGEROUS_FUNCTION_' . strtoupper($function),
                        'Dangerous PHP function detected',
                        "The function {$function}() was detected. Shell/process execution must be explicitly reviewed and avoided in generated modules.",
                        $this->severity('shell_execution'),
                        $file,
                        'No direct shell/process execution',
                        $function . '()',
                        'Replace shell execution with a controlled service, queue job, or whitelisted internal command. Require manual review if unavoidable.'
                    );
                }
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_NO_DANGEROUS_FUNCTIONS',
                'No dangerous PHP functions detected',
                'No direct shell/process execution functions were detected.'
            );
        }

        return $findings;
    }

    protected function checkEnvWrites(array $contents): array
    {
        $findings = [];

        foreach ($contents as $file => $content) {
            if ($this->writesToPath($content, '.env')) {
                $findings[] = ModuleValidationFinding::failed(
                    'SECURITY_ENV_WRITE_ATTEMPT',
                    '.env write attempt detected',
                    'The module appears to write to or modify .env. This is not allowed by LSG standards.',
                    $this->severity('env_write'),
                    $file,
                    'No write operation against .env',
                    'Potential .env write',
                    'Move configuration to module config/database and require manual server configuration for environment changes.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_NO_ENV_WRITES',
                'No .env write attempts detected',
                'No direct .env write pattern was detected.'
            );
        }

        return $findings;
    }

    protected function checkPathTraversalRisks(array $contents): array
    {
        $findings = [];
        $requestPatterns = config('module-security-validator.request_path_patterns', []);

        foreach ($contents as $file => $content) {
            if (str_contains($file, DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR)
                || str_contains($file, DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $hasWrite = $this->containsWriteOperation($content);
            $hasRequestPath = $this->containsAny($content, $requestPatterns);
            $hasPathSanitizer = str_contains($content, 'basename(')
                || str_contains($content, 'realpath(')
                || str_contains($content, 'Str::slug')
                || str_contains($content, 'Storage::path')
                || str_contains($content, 'validated()')
                || str_contains($content, '->validate(')
                || preg_match('/\b[A-Za-z0-9_]*Request\s+\$request\b/', $content) === 1;

            if ($hasWrite && $hasRequestPath && ! $hasPathSanitizer) {
                $findings[] = ModuleValidationFinding::failed(
                    'SECURITY_POTENTIAL_PATH_TRAVERSAL',
                    'Potential path traversal risk',
                    'The file appears to combine request input with filesystem writes without an obvious sanitizer.',
                    $this->severity('path_traversal'),
                    $file,
                    'Sanitized paths and constrained storage roots',
                    'Request input used near filesystem operations',
                    'Constrain writes to a fixed disk/path, validate filenames, use basename/slugging, and never trust request-provided paths.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_NO_PATH_TRAVERSAL_PATTERN',
                'No obvious path traversal pattern detected',
                'No direct request-input-to-filesystem-write pattern was detected.'
            );
        }

        return $findings;
    }

    protected function checkRouteProtection(string $modulePath): array
    {
        $findings = [];
        $routesPath = $modulePath . DIRECTORY_SEPARATOR . 'routes';

        if (! is_dir($routesPath)) {
            $findings[] = ModuleValidationFinding::warning(
                'SECURITY_ROUTES_FOLDER_MISSING',
                'Routes folder not found',
                'No routes folder was found. Skipping route protection validation.',
                ValidationSeverity::Low,
                $routesPath,
                'Confirm whether the module exposes routes.'
            );
            return $findings;
        }

        $routeFiles = glob($routesPath . DIRECTORY_SEPARATOR . '*.php') ?: [];
        foreach ($routeFiles as $routeFile) {
            $content = file_get_contents($routeFile) ?: '';
            if (trim($content) === '') {
                continue;
            }

            $hasRoutes = str_contains($content, 'Route::');
            $hasProtection = $this->containsAny($content, config('module-security-validator.route_protection_patterns', []));
            $hasApiPrefix = str_contains($routeFile, 'api.php') || str_contains($content, "prefix('api") || str_contains($content, 'prefix("api');

            if ($hasRoutes && ! $hasProtection) {
                $findings[] = ModuleValidationFinding::failed(
                    'SECURITY_ROUTES_WITHOUT_PROTECTION',
                    'Routes without protection detected',
                    'A routes file defines routes without obvious auth/permission middleware.',
                    $this->severity('unprotected_route'),
                    $routeFile,
                    'Routes protected by web/auth/permission middleware',
                    'Routes defined without middleware',
                    $hasApiPrefix
                        ? 'Add auth/token middleware and explicit permissions for API routes.'
                        : 'Wrap BO routes in web, auth and permission middleware.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_ROUTES_PROTECTED',
                'Route protection detected',
                'Routes appear to include middleware/protection patterns.'
            );
        }

        return $findings;
    }

    protected function checkUploadValidation(array $contents): array
    {
        $findings = [];

        foreach ($contents as $file => $content) {
            if (str_contains($file, DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR)
                || str_contains($file, DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $isBackendFile = str_ends_with($file, '.php') && ! str_ends_with($file, '.blade.php');
            if (! $isBackendFile) {
                continue;
            }

            $hasUpload = preg_match('/->hasFile\s*\(|->file\s*\(|request\(\)->file\s*\(/', $content) === 1;
            $hasValidation = $this->containsAny($content, config('module-security-validator.upload_validation_patterns', []))
                || str_contains($content, 'validated()')
                || preg_match('/\b[A-Za-z0-9_]*Request\s+\$request\b/', $content) === 1;

            if ($hasUpload && ! $hasValidation) {
                $findings[] = ModuleValidationFinding::failed(
                    'SECURITY_UPLOAD_WITHOUT_VALIDATION',
                    'Upload without clear validation',
                    'The module appears to handle uploads without clear file type/size validation.',
                    $this->severity('unsafe_upload'),
                    $file,
                    'Upload validation with mime/type/size constraints',
                    'Upload handling without validation pattern',
                    'Add Request validation rules for file type, size, extension and storage path. Convert images server-side when needed.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_UPLOAD_VALIDATION_OK',
                'No unsafe upload pattern detected',
                'No upload handler without validation was detected.'
            );
        }

        return $findings;
    }

    protected function checkCoreWriteAttempts(array $contents): array
    {
        $findings = [];
        $corePaths = config('module-security-validator.core_forbidden_paths', []);

        foreach ($contents as $file => $content) {
            foreach ($corePaths as $corePath) {
                if ($this->writesToPath($content, $corePath)) {
                    $findings[] = ModuleValidationFinding::failed(
                        'SECURITY_CORE_WRITE_ATTEMPT',
                        'Potential core write detected',
                        "The module appears to write to a core path: {$corePath}",
                        $this->severity('core_write'),
                        $file,
                        'Writes constrained to module/storage/upload locations',
                        $corePath,
                        'Move writes to module-owned paths, storage or configured upload directories. Core changes require manual review.'
                    );
                }
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_NO_CORE_WRITES',
                'No core write attempts detected',
                'No obvious write operation to forbidden core paths was detected.'
            );
        }

        return $findings;
    }

    protected function checkRawSqlRisks(array $contents): array
    {
        $findings = [];

        foreach ($contents as $file => $content) {
            $hasRawSql = preg_match('/DB::(select|statement|unprepared|raw)\s*\(/', $content);
            $scanContent = $this->stripCommentsAndStrings($content);
            $hasConcatenation = preg_match('/DB::(select|statement|unprepared)\s*\([^;]*(\.|\$request|request\(\))/', $scanContent);

            if ($hasRawSql && $hasConcatenation) {
                $findings[] = ModuleValidationFinding::warning(
                    'SECURITY_RAW_SQL_WITH_DYNAMIC_INPUT',
                    'Raw SQL with possible dynamic input',
                    'Raw SQL appears to be built with dynamic input. This may create SQL injection risk.',
                    $this->severity('raw_sql'),
                    $file,
                    'Expected query builder or parameter bindings. Actual raw SQL with dynamic content. Use query builder, Eloquent, or bound parameters. Avoid concatenating request data into SQL.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_RAW_SQL_OK',
                'No obvious raw SQL injection pattern detected',
                'No raw SQL concatenation pattern was detected.'
            );
        }

        return $findings;
    }

    protected function checkCsrfDisabled(array $contents): array
    {
        $findings = [];

        foreach ($contents as $file => $content) {
            $scanContent = $this->stripCommentsAndStrings($content);
            if (str_contains($scanContent, 'withoutMiddleware') && str_contains($scanContent, 'VerifyCsrfToken')) {
                $findings[] = ModuleValidationFinding::warning(
                    'SECURITY_CSRF_DISABLED',
                    'CSRF middleware disabled',
                    'The module appears to disable CSRF middleware.',
                    $this->severity('csrf_disabled'),
                    $file,
                    'Expected CSRF enabled for BO web routes. Actual withoutMiddleware(VerifyCsrfToken). Avoid disabling CSRF except for signed webhooks or protected API endpoints.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_CSRF_OK',
                'No CSRF disable pattern detected',
                'No CSRF middleware disabling pattern was detected.'
            );
        }

        return $findings;
    }

    protected function checkDebugCode(array $contents): array
    {
        $findings = [];
        $patterns = [
            'dd' => '/(?<![A-Za-z0-9_$])dd\s*\(/',
            'dump' => '/(?<![A-Za-z0-9_$])dump\s*\(/',
            'var_dump' => '/(?<![A-Za-z0-9_$])var_dump\s*\(/',
            'print_r' => '/(?<![A-Za-z0-9_$])print_r\s*\(/',
            'die' => '/(?<![A-Za-z0-9_$])die\s*;/',
            'exit' => '/(?<![A-Za-z0-9_$])exit\s*;/',
        ];

        foreach ($contents as $file => $content) {
            $scanContent = $this->stripCommentsAndStrings($content);
            foreach ($patterns as $name => $pattern) {
                if (preg_match($pattern, $scanContent)) {
                    $findings[] = ModuleValidationFinding::warning(
                        'SECURITY_DEBUG_CODE_' . strtoupper($name),
                        'Debug code detected',
                        "Debug pattern {$name} was detected.",
                        $this->severity('debug_code'),
                        $file,
                        "Expected no debug/dump code in committed module files. Actual pattern: {$name}. Remove debug statements before validation approval."
                    );
                }
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_NO_DEBUG_CODE',
                'No debug code detected',
                'No obvious dump/die/debug statements were detected.'
            );
        }

        return $findings;
    }

    protected function checkMassAssignment(array $contents): array
    {
        $findings = [];

        foreach ($contents as $file => $content) {
            $isModel = str_contains($file, DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR) && str_ends_with($file, '.php');
            if (! $isModel) {
                continue;
            }

            $hasGuardedEmpty = str_contains($content, 'protected $guarded = []') || str_contains($content, 'protected $guarded=[]');
            $hasFillable = str_contains($content, 'protected $fillable');

            if ($hasGuardedEmpty && ! $hasFillable) {
                $findings[] = ModuleValidationFinding::warning(
                    'SECURITY_MODEL_GUARDED_EMPTY',
                    'Model uses unguarded mass assignment',
                    'A model appears to use protected $guarded = [] without explicit fillable fields.',
                    $this->severity('mass_assignment'),
                    $file,
                    'Expected explicit $fillable fields or reviewed guarded policy. Actual $guarded = []. Prefer explicit $fillable for module models unless there is a documented reason.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'SECURITY_MASS_ASSIGNMENT_OK',
                'No obvious mass-assignment risk detected',
                'No unguarded model pattern was detected.'
            );
        }

        return $findings;
    }

    protected function discoverScannableFiles(string $modulePath): array
    {
        $extensions = config('module-security-validator.scan_extensions', ['php', 'blade.php', 'js']);
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulePath));

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            foreach ($extensions as $extension) {
                if (str_ends_with($path, '.' . $extension) || str_ends_with($path, $extension)) {
                    $files[] = $path;
                    break;
                }
            }
        }

        sort($files);
        return $files;
    }

    protected function readFiles(array $files): array
    {
        $contents = [];
        foreach ($files as $file) {
            $contents[$file] = file_get_contents($file) ?: '';
        }
        return $contents;
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

    protected function containsWriteOperation(string $content): bool
    {
        return preg_match('/(file_put_contents|fopen|fwrite|mkdir|rename|unlink|Storage::put|->put\s*\(|->store\s*\(|->storeAs\s*\(|->makeDirectory\s*\()/', $content) === 1;
    }

    protected function writesToPath(string $content, string $path): bool
    {
        $quotedPath = preg_quote($path, '/');
        $normalizedPath = preg_quote(str_replace('/', '\\\\', $path), '/');

        $patterns = [
            '/file_put_contents\s*\([^;]*(?:[\'"]' . $quotedPath . '[\'"]|[\'"]' . $normalizedPath . '[\'"]|base_path\s*\(\s*[\'"]' . $quotedPath . '[\'"])/s',
            '/fopen\s*\([^;]*(?:[\'"]' . $quotedPath . '[\'"]|[\'"]' . $normalizedPath . '[\'"]|base_path\s*\(\s*[\'"]' . $quotedPath . '[\'"])/s',
            '/Storage::put\s*\(\s*[\'"]' . $quotedPath . '[\'"]/s',
            '/Storage::disk\s*\([^;]*->put\s*\(\s*[\'"]' . $quotedPath . '[\'"]/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
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

    protected function severity(string $key): ValidationSeverity
    {
        $value = config('module-security-validator.severity.' . $key, 'medium');

        return match ($value) {
            'blocker' => ValidationSeverity::Blocker,
            'critical' => ValidationSeverity::Critical,
            'high' => ValidationSeverity::High,
            'low' => ValidationSeverity::Low,
            'info' => ValidationSeverity::Info,
            default => ValidationSeverity::Medium,
        };
    }

    protected function buildResult(array $findings, ModuleValidationContext $context, array $files): ModuleValidationResult
    {
        $score = $this->scoreCalculator->calculate($findings);
        $status = collect($findings)->contains(fn ($finding) => $finding->status === ValidationStatus::Failed)
            ? ValidationStatus::Failed
            : (collect($findings)->contains(fn ($finding) => $finding->status === ValidationStatus::Warning)
                ? ValidationStatus::Warning
                : ValidationStatus::Passed);

        return new ModuleValidationResult(
            validator: $this->key(),
            label: $this->label(),
            area: $this->area(),
            findings: $findings,
            score: $score,
            status: $status,
            metadata: [
                'module_name' => $context->moduleName,
                'module_path' => $context->modulePath,
                'scanned_files' => count($files),
            ],
        );
    }
}
