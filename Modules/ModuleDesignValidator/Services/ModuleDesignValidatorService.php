<?php

namespace Modules\ModuleDesignValidator\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleComplianceCore\DTO\ModuleValidationFinding;
use Modules\ModuleComplianceCore\DTO\ModuleValidationResult;
use Modules\ModuleComplianceCore\Enums\ValidationSeverity;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;
use Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator;

class ModuleDesignValidatorService implements ModuleValidatorInterface
{
    public function __construct(
        protected ComplianceScoreCalculator $scoreCalculator,
    ) {
    }

    public function key(): string
    {
        return 'design';
    }

    public function label(): string
    {
        return 'Module Design Validator';
    }

    public function area(): string
    {
        return 'design';
    }

    public function validate(ModuleValidationContext $context): ModuleValidationResult
    {
        $modulePath = rtrim($context->modulePath, DIRECTORY_SEPARATOR);
        $findings = [];

        if (! is_dir($modulePath)) {
            $findings[] = ModuleValidationFinding::failed(
                'DESIGN_MODULE_PATH_MISSING',
                'Module path missing',
                'The provided module path does not exist or is not a directory.',
                ValidationSeverity::Blocker,
                $modulePath,
                'Existing module directory',
                $modulePath,
                'Confirm the module path before running design validation.'
            );

            return $this->buildResult($findings, $context, []);
        }

        $viewFiles = $this->discoverBladeViews($modulePath);

        if (empty($viewFiles)) {
            $findings[] = ModuleValidationFinding::failed(
                'DESIGN_NO_BLADE_VIEWS_FOUND',
                'No Blade views found',
                'The validator could not find any Blade view files in the configured view paths.',
                $this->severity('missing_views'),
                $modulePath,
                'At least one Blade view under Resources/views',
                'No Blade files found',
                'Create module views under Resources/views or confirm this module has no UI.'
            );

            return $this->buildResult($findings, $context, $viewFiles);
        }

        $allContent = $this->readFiles($viewFiles);
        $combinedContent = implode("\n", array_values($allContent));
        $modulePhpFiles = $this->discoverModulePhpFiles($modulePath);
        $modulePhpContent = $this->readFiles($modulePhpFiles);
        $combinedModuleContent = implode("\n", array_merge(array_values($allContent), array_values($modulePhpContent)));

        $findings[] = ModuleValidationFinding::passed(
            'DESIGN_BLADE_VIEWS_FOUND',
            'Blade views found',
            count($viewFiles) . ' Blade view file(s) found for design validation.',
            $modulePath
        );

        $findings[] = $this->checkPatternGroup(
            'DESIGN_LAYOUT_BASE_USED',
            'Base layout usage',
            'Views appear to use a base layout or layout component.',
            'DESIGN_LAYOUT_BASE_MISSING',
            'Base layout not detected',
            'No expected LSG/base layout pattern was found in the module views.',
            config('module-design-validator.expected_layout_patterns', []),
            $combinedContent,
            $this->severity('missing_layout'),
            $modulePath,
            'Use the LSG base layout for all BO pages.'
        );

        $findings = array_merge($findings, $this->checkLayoutContract($combinedContent, $combinedModuleContent, $modulePath));
        $findings = array_merge($findings, $this->checkDuplicatePageActions($allContent, $combinedModuleContent, $modulePath));

        $findings[] = $this->checkPatternGroup(
            'DESIGN_CARDS_FOUND',
            'Card layout detected',
            'Card-related classes were found in the module views.',
            'DESIGN_CARDS_MISSING',
            'Card layout not detected',
            'No card layout pattern was found.',
            config('module-design-validator.card_patterns', []),
            $combinedContent,
            $this->severity('missing_cards'),
            $modulePath,
            'Wrap main BO sections in cards to keep the LSG layout coherent.'
        );

        $findings[] = $this->checkListTables($viewFiles, $allContent, $modulePath);
        $findings[] = $this->checkDestructiveActionsSweetAlert($combinedContent, $modulePath);
        $findings[] = $this->checkPatternGroup(
            'DESIGN_FONTAWESOME_FOUND',
            'Font Awesome detected',
            'Font Awesome icon patterns were found.',
            'DESIGN_FONTAWESOME_MISSING',
            'Font Awesome icons not detected',
            'No Font Awesome icon pattern was found.',
            config('module-design-validator.fontawesome_patterns', []),
            $combinedContent,
            $this->severity('missing_fontawesome'),
            $modulePath,
            'Use Font Awesome icons in action buttons according to the LSG convention.'
        );

        $findings[] = $this->checkEmptyState($combinedContent, $modulePath);
        $findings[] = $this->checkResponsivePatterns($combinedContent, $modulePath);
        $findings = array_merge($findings, $this->checkForbiddenViewClasses($allContent));
        $findings = array_merge($findings, $this->checkThemeContract($allContent));
        $findings = array_merge($findings, $this->checkTokenizedThemeColors($allContent));
        $findings = array_merge($findings, $this->checkColorContrast($allContent));
        $findings[] = $this->checkDropzoneForUploads($combinedContent, $modulePath);
        $findings = array_merge($findings, $this->checkButtonConventions($combinedModuleContent, $modulePath));
        $findings = array_merge($findings, $this->checkInlineStyles($allContent));

        return $this->buildResult($findings, $context, $viewFiles);
    }

    protected function discoverBladeViews(string $modulePath): array
    {
        $files = [];
        foreach (config('module-design-validator.view_paths', []) as $relativePath) {
            $path = $modulePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (! is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    if ($this->isExcludedViewPath($file->getPathname())) {
                        continue;
                    }

                    $files[strtolower($file->getPathname())] = $file->getPathname();
                }
            }
        }

        $files = array_values($files);
        sort($files);

        return $files;
    }

    protected function isExcludedViewPath(string $path): bool
    {
        $normalized = strtolower(str_replace('\\', '/', $path));

        foreach (config('module-design-validator.excluded_view_path_fragments', []) as $fragment) {
            if (is_string($fragment) && $fragment !== '' && str_contains($normalized, strtolower($fragment))) {
                return true;
            }
        }

        return false;
    }

    protected function discoverModulePhpFiles(string $modulePath): array
    {
        $files = [];
        $directories = ['Http', 'Providers', 'Config', 'config'];

        foreach ($directories as $directory) {
            $path = $modulePath . DIRECTORY_SEPARATOR . $directory;
            if (! is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                    $files[] = $file->getPathname();
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
            $contents[$file] = (string) file_get_contents($file);
        }
        return $contents;
    }

    protected function checkPatternGroup(
        string $passCode,
        string $passTitle,
        string $passMessage,
        string $failCode,
        string $failTitle,
        string $failMessage,
        array $patterns,
        string $content,
        ValidationSeverity $severity,
        string $filePath,
        string $recommendation
    ): ModuleValidationFinding {
        foreach ($patterns as $pattern) {
            if ($pattern !== '' && stripos($content, $pattern) !== false) {
                return ModuleValidationFinding::passed(
                    $passCode,
                    $passTitle,
                    $passMessage . ' Matched pattern: ' . $pattern,
                    $filePath
                );
            }
        }

        return ModuleValidationFinding::warning(
            $failCode,
            $failTitle,
            $failMessage,
            $severity,
            $filePath,
            $recommendation
        );
    }

    protected function checkLayoutContract(string $viewContent, string $moduleContent, string $modulePath): array
    {
        $findings = [];
        $config = config('module-design-validator.layout_contract', []);
        $hasControllerTitle = $this->containsAny($moduleContent, $config['controller_title_patterns'] ?? []);
        $hasControllerBreadcrumbs = $this->containsAny($moduleContent, $config['controller_breadcrumb_patterns'] ?? []);
        $hasControllerActions = $this->containsAny($moduleContent, $config['controller_action_patterns'] ?? []);
        $hasConfigBreadcrumbs = $this->hasConfiguredLayoutFile($modulePath, ['Config/breadcrumbs.php', 'config/breadcrumbs.php']);
        $hasConfigActions = $this->hasConfiguredLayoutFile($modulePath, ['Config/actions.php', 'config/actions.php']);
        $hasConfigTitles = $this->hasConfiguredLayoutFile($modulePath, ['Config/page_titles.php', 'config/page_titles.php']);
        $hasManualBreadcrumbs = $this->containsAny($viewContent, $config['manual_breadcrumb_patterns'] ?? []);
        $hasManualActions = $this->containsAny($viewContent, $config['manual_action_patterns'] ?? []);
        $hasManualTitle = $this->containsAny($viewContent, $config['manual_title_patterns'] ?? []);

        if ($hasControllerTitle || $hasConfigTitles) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_PAGE_TITLE_LAYOUT_CONTRACT_OK',
                'Page title uses LSG layout contract',
                'The module provides page title metadata through controller/config instead of relying only on Blade content.',
                $modulePath
            );
        } elseif ($hasManualTitle) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_PAGE_TITLE_HARDCODED_IN_VIEW',
                'Page title appears hardcoded in view',
                'A top-level title was found in Blade. This is accepted for legacy modules without topbar metadata.',
                $modulePath
            );
        } else {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_PAGE_TITLE_LAYOUT_CONTRACT_MISSING',
                'Page title layout contract missing',
                'No page title metadata was detected in controller/config. This is accepted when the module has no page-level topbar contract.',
                $modulePath
            );
        }

        if ($hasControllerBreadcrumbs || $hasConfigBreadcrumbs) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_BREADCRUMBS_LAYOUT_CONTRACT_OK',
                'Breadcrumbs use LSG layout contract',
                'Breadcrumb metadata is provided through controller/config for the global B.O. breadcrumb area.',
                $modulePath
            );
        } elseif ($hasManualBreadcrumbs) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_BREADCRUMBS_HARDCODED_IN_VIEW',
                'Breadcrumbs appear hardcoded in view',
                'Breadcrumb markup was found inside Blade content. This is accepted for legacy modules without global breadcrumb metadata.',
                $modulePath
            );
        } else {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_BREADCRUMBS_MISSING',
                'Breadcrumbs not detected',
                'No breadcrumb metadata or breadcrumb pattern was found. This is accepted for modules without navigational pages.',
                $modulePath
            );
        }

        if ($hasControllerActions || $hasConfigActions) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_ACTIONS_LAYOUT_CONTRACT_OK',
                'Actions use LSG layout contract',
                'Page actions are provided through controller/config for the global B.O. action toolbar.',
                $modulePath
            );
        } elseif ($hasManualActions) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_ACTIONS_HARDCODED_IN_VIEW',
                'Actions appear hardcoded in view',
                'Action toolbar markup was found inside Blade content. This is accepted for legacy modules without global action metadata.',
                $modulePath
            );
        } else {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_ACTIONS_LAYOUT_CONTRACT_MISSING',
                'Actions layout contract missing',
                'No page action metadata was detected in controller/config. This is accepted when the page exposes no global actions.',
                $modulePath
            );
        }

        if (($hasControllerTitle || $hasConfigTitles) && ($hasControllerBreadcrumbs || $hasConfigBreadcrumbs) && ($hasManualBreadcrumbs || $hasManualActions)) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_LAYOUT_CONTRACT_MISPLACED_PAGE_CHROME',
                'Page chrome appears duplicated or misplaced',
                'The module uses the LSG layout contract and contains manual breadcrumb/action chrome. This is accepted for transitional legacy views.',
                $modulePath
            );
        } else {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_LAYOUT_CONTRACT_PAGE_CHROME_PLACEMENT_OK',
                'Page chrome placement OK',
                'No conflicting hardcoded topbar/breadcrumb/action chrome was detected.',
                $modulePath
            );
        }

        return $findings;
    }

    protected function checkDuplicatePageActions(array $viewContents, string $moduleContent, string $modulePath): array
    {
        $findings = [];
        $rules = config('module-design-validator.layout_contract.duplicate_action_detection', []);

        foreach ($rules as $action => $rule) {
            $globalActionRoutes = $this->globalActionRoutes($moduleContent, (string) $action);

            if (empty($globalActionRoutes) && ! $this->containsAny($moduleContent, $rule['global_patterns'] ?? [])) {
                continue;
            }

            foreach ($viewContents as $file => $viewContent) {
                $pageLevelContent = $this->withoutRowLevelActionAreas($viewContent);
                $hasViewAction = $this->containsAny($pageLevelContent, $rule['view_patterns'] ?? []);

                if (! $hasViewAction) {
                    continue;
                }

                $viewRoutes = $this->viewRouteNames($pageLevelContent);
                $sameTarget = empty($globalActionRoutes) || empty($viewRoutes)
                    ? false
                    : count(array_intersect($globalActionRoutes, $viewRoutes)) > 0;

                if (! $sameTarget) {
                    continue;
                }

                $findings[] = ModuleValidationFinding::warning(
                    'DESIGN_DUPLICATE_PAGE_ACTION_' . strtoupper((string) $action) . '_' . $this->codeFromPath($file),
                    'Duplicate page action: ' . $action,
                    'The action is declared in the global LSG action toolbar and also appears as a button inside the same Blade view.',
                    $this->severity('duplicate_page_action'),
                    $file,
                    'Keep page-level actions in setActions()/Config/actions.php and remove duplicate buttons from the view content.'
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_DUPLICATE_PAGE_ACTIONS_ABSENT',
                'No duplicate page actions detected',
                'No duplicated global/page-content actions were detected.',
                $modulePath
            );
        }

        return $findings;
    }

    protected function globalActionRoutes(string $content, string $action): array
    {
        $routes = [];
        $quotedAction = preg_quote($action, '/');

        if (preg_match_all('/actionLink\s*\(\s*[\'"]' . $quotedAction . '[\'"][\s\S]{0,260}?[\'"]([A-Za-z0-9_\-]+\.[A-Za-z0-9_\.\-]+)[\'"]/i', $content, $matches)) {
            $routes = array_merge($routes, $matches[1]);
        }

        if (preg_match_all('/[\'"]key[\'"]\s*=>\s*[\'"]' . $quotedAction . '[\'"][\s\S]{0,360}?[\'"]route[\'"]\s*=>\s*[\'"]([A-Za-z0-9_\-]+\.[A-Za-z0-9_\.\-]+)[\'"]/i', $content, $matches)) {
            $routes = array_merge($routes, $matches[1]);
        }

        if (preg_match_all('/[\'"]key[\'"]\s*=>\s*[\'"]' . $quotedAction . '[\'"][\s\S]{0,360}?route\s*\(\s*[\'"]([A-Za-z0-9_\-]+\.[A-Za-z0-9_\.\-]+)[\'"]/i', $content, $matches)) {
            $routes = array_merge($routes, $matches[1]);
        }

        return array_values(array_unique($routes));
    }

    protected function viewRouteNames(string $content): array
    {
        if (! preg_match_all('/route\s*\(\s*[\'"]([A-Za-z0-9_\-]+\.[A-Za-z0-9_\.\-]+)[\'"]/i', $content, $matches)) {
            return [];
        }

        return array_values(array_unique($matches[1]));
    }

    protected function withoutRowLevelActionAreas(string $content): string
    {
        $content = preg_replace('/<table\b[\s\S]*?<\/table>/i', '', $content) ?? $content;
        $content = preg_replace('/<tbody\b[\s\S]*?<\/tbody>/i', '', $content) ?? $content;

        return $content;
    }

    protected function checkListTables(array $viewFiles, array $contents, string $modulePath): ModuleValidationFinding
    {
        $hasTable = false;
        foreach ($contents as $content) {
            if (stripos($content, '<table') !== false) {
                $hasTable = true;
                break;
            }
        }

        if (! $hasTable) {
            return ModuleValidationFinding::passed(
                'DESIGN_NO_TABLES_DETECTED',
                'No table/listing detected',
                'No HTML table was detected. This may be valid for modules without listings.',
                $modulePath
            );
        }

        $combinedContent = implode("\n", array_values($contents));
        foreach (config('module-design-validator.datatable_patterns', []) as $pattern) {
            if ($pattern !== '' && stripos($combinedContent, $pattern) !== false) {
                return ModuleValidationFinding::passed(
                    'DESIGN_DATATABLES_FOUND',
                    'DataTables detected',
                    'A listing table appears to use DataTables or DataTables-ready markup.',
                    $modulePath
                );
            }
        }

        return ModuleValidationFinding::passed(
            'DESIGN_DATATABLES_MISSING',
            'DataTables not detected',
            'One or more tables were found without an explicit DataTables marker. This is accepted for legacy/simple tables when no layout break is detected.',
            $modulePath
        );
    }

    protected function checkDestructiveActionsSweetAlert(string $content, string $modulePath): ModuleValidationFinding
    {
        $hasDelete = $this->containsAny($content, ['delete', 'destroy', 'apagar', 'remover', 'fa-trash', "method('DELETE')", '@method(']);
        if (! $hasDelete) {
            return ModuleValidationFinding::passed(
                'DESIGN_NO_DESTRUCTIVE_ACTIONS_DETECTED',
                'No destructive action detected',
                'No delete/destroy action was detected in the views.',
                $modulePath
            );
        }

        if ($this->containsAny($content, config('module-design-validator.sweetalert_patterns', []))) {
            return ModuleValidationFinding::passed(
                'DESIGN_SWEETALERT_FOUND',
                'SweetAlert detected for destructive actions',
                'Destructive actions exist and SweetAlert-related code was detected.',
                $modulePath
            );
        }

        return ModuleValidationFinding::passed(
            'DESIGN_SWEETALERT_MISSING',
            'SweetAlert missing for destructive actions',
            'Delete/destroy patterns were found without SweetAlert. This is accepted when the module uses its own confirmation layer.',
            $modulePath
        );
    }

    protected function checkEmptyState(string $content, string $modulePath): ModuleValidationFinding
    {
        if ($this->containsAny($content, config('module-design-validator.empty_state_patterns', []))) {
            return ModuleValidationFinding::passed(
                'DESIGN_EMPTY_STATE_FOUND',
                'Empty state detected',
                'An empty state or @forelse pattern was found.',
                $modulePath
            );
        }

        return ModuleValidationFinding::passed(
            'DESIGN_EMPTY_STATE_MISSING',
            'Empty state not detected',
            'No empty state pattern was found. This is acceptable for modules without record-listing screens.',
            $modulePath
        );
    }

    protected function checkResponsivePatterns(string $content, string $modulePath): ModuleValidationFinding
    {
        if ($this->containsAny($content, config('module-design-validator.responsive_patterns', []))) {
            return ModuleValidationFinding::passed(
                'DESIGN_RESPONSIVE_PATTERNS_FOUND',
                'Responsive layout patterns detected',
                'Bootstrap/flex responsive classes were found.',
                $modulePath
            );
        }

        return ModuleValidationFinding::warning(
            'DESIGN_RESPONSIVE_PATTERNS_MISSING',
            'Responsive patterns not detected',
            'No obvious responsive classes were found.',
            $this->severity('missing_responsive_patterns'),
            $modulePath,
            'Use row/col/table-responsive/d-flex/flex-wrap where applicable.'
        );
    }

    protected function checkDropzoneForUploads(string $content, string $modulePath): ModuleValidationFinding
    {
        $hasUpload = $this->containsAny($content, ['type="file"', "type='file'", 'multipart/form-data', 'upload', 'ficheiro', 'imagem']);
        if (! $hasUpload) {
            return ModuleValidationFinding::passed(
                'DESIGN_NO_UPLOAD_UI_DETECTED',
                'No upload UI detected',
                'No upload-related form was detected.',
                $modulePath
            );
        }

        if ($this->containsAny($content, config('module-design-validator.dropzone_patterns', []))) {
            return ModuleValidationFinding::passed(
                'DESIGN_DROPZONE_FOUND',
                'Dropzone detected',
                'Upload UI exists and Dropzone-related patterns were detected.',
                $modulePath
            );
        }

        return ModuleValidationFinding::passed(
            'DESIGN_DROPZONE_MISSING',
            'Dropzone not detected for upload UI',
            'Upload-related fields were found without Dropzone. Simple validated uploads are acceptable.',
            $modulePath
        );
    }

    protected function checkForbiddenViewClasses(array $contents): array
    {
        $findings = [];
        $rules = config('module-design-validator.forbidden_view_classes', []);

        foreach ($contents as $file => $content) {
            foreach ($rules as $class => $rule) {
                if (! $this->containsCssClass($content, (string) $class)) {
                    continue;
                }

                $findings[] = ModuleValidationFinding::warning(
                    'DESIGN_FORBIDDEN_VIEW_CLASS_' . strtoupper(str_replace('-', '_', (string) $class)) . '_' . $this->codeFromPath($file),
                    'Forbidden layout class used: ' . $class,
                    (string) ($rule['message'] ?? 'A forbidden layout class was found in a module view.'),
                    $this->severity((string) ($rule['severity_key'] ?? 'forbidden_view_class')),
                    $file,
                    (string) ($rule['recommendation'] ?? 'Remove the forbidden class from the view.')
                );
            }
        }

        if (empty($findings)) {
            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_FORBIDDEN_VIEW_CLASSES_ABSENT',
                'No forbidden layout classes detected',
                'No forbidden layout wrapper classes were found in module views.'
            );
        }

        return $findings;
    }

    protected function containsCssClass(string $content, string $class): bool
    {
        return preg_match('/class\s*=\s*([\"\\\'])(?:(?!\1).)*\b' . preg_quote($class, '/') . '\b(?:(?!\1).)*\1/is', $content) === 1;
    }

    protected function checkButtonConventions(string $content, string $modulePath): array
    {
        $findings = [];
        $rules = config('module-design-validator.button_rules', []);

        foreach ($rules as $action => $rule) {
            $hasLayoutActionMetadata = $this->containsActionMetadata($content, $action, $rule);
            $actionDetected = $hasLayoutActionMetadata
                || $this->containsCssTokenAny($content, $rule['icons'] ?? [])
                || $this->containsButtonLabel($content, $rule['labels'] ?? []);

            if (! $actionDetected) {
                $findings[] = ModuleValidationFinding::passed(
                    'DESIGN_BUTTON_ACTION_NOT_REQUIRED_' . strtoupper($action),
                    'Action button not required: ' . $action,
                    'No obvious ' . $action . ' action was detected. This is acceptable when the page does not need that action.',
                    $modulePath
                );
                continue;
            }

            $hasClass = $this->containsCssTokenAny($content, $rule['classes'] ?? []);
            $hasIcon = $this->containsCssTokenAny($content, $rule['icons'] ?? []);

            if (($hasClass && $hasIcon) || $hasLayoutActionMetadata) {
                $findings[] = ModuleValidationFinding::passed(
                    'DESIGN_BUTTON_CONVENTION_OK_' . strtoupper($action),
                    'Button convention OK: ' . $action,
                    $hasLayoutActionMetadata
                        ? 'Detected LSG action metadata for action: ' . $action
                        : 'Detected expected class and icon for action: ' . $action,
                    $modulePath
                );
                continue;
            }

            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_BUTTON_CONVENTION_MISMATCH_' . strtoupper($action),
                'Button convention mismatch: ' . $action,
                'The action was detected with a custom or legacy button style. It is accepted when the module otherwise follows the LSG layout contract.',
                $modulePath
            );
        }

        return $findings;
    }

    protected function containsCssTokenAny(string $content, array $tokens): bool
    {
        foreach ($tokens as $token) {
            if (! is_string($token) || $token === '') {
                continue;
            }

            if (preg_match('/class\s*=\s*([\"\\\'])(?:(?!\1).)*\b' . preg_quote($token, '/') . '\b(?:(?!\1).)*\1/is', $content)) {
                return true;
            }
        }

        return false;
    }

    protected function checkThemeContract(array $contents): array
    {
        $findings = [];
        $config = config('module-design-validator.theme_contract', []);

        foreach ($contents as $file => $content) {
            $issues = [];

            foreach ($this->extractStyleBlocks($content) as $styleBlock) {
                foreach ($this->extractCssRules($styleBlock) as $rule) {
                    $hasGlobalThemeSelector = $this->hasForbiddenThemeSelector($rule['selector'], $config['forbidden_selectors'] ?? []);
                    $body = $rule['body'];

                    if ($hasGlobalThemeSelector) {
                        $issues[] = 'Global selector override: ' . trim($rule['selector']);
                    }

                    if ($hasGlobalThemeSelector) {
                        if (preg_match_all('/(--[A-Za-z0-9_-]+)\s*:/', $body, $matches)) {
                            foreach (array_unique($matches[1]) as $variable) {
                                if (! $this->isAllowedCssVariable($variable, $config['allowed_css_variables'] ?? [])
                                    && $this->matchesPrefix($variable, $config['forbidden_css_variables'] ?? [])) {
                                    $issues[] = 'Forbidden global theme variable override: ' . $variable;
                                }
                            }
                        }

                        foreach ($config['forbidden_properties'] ?? [] as $property) {
                            if ($this->extractCssDeclaration($body, (string) $property) !== null) {
                                $issues[] = 'Global styling property override: ' . trim($rule['selector']) . ' {' . $property . '}';
                            }
                        }
                    }
                }
            }

            if (empty($issues)) {
                $findings[] = ModuleValidationFinding::passed(
                    'DESIGN_THEME_CONTRACT_OK_' . $this->codeFromPath($file),
                    'No theme override detected',
                    'The view does not appear to override global light/dark color or styling tokens.',
                    $file
                );
                continue;
            }

            $findings[] = ModuleValidationFinding::warning(
                'DESIGN_THEME_OVERRIDE_' . $this->codeFromPath($file),
                'Module overrides global theme styling',
                implode(' | ', array_values(array_unique(array_slice($issues, 0, 8)))),
                $this->severity('theme_override'),
                $file,
                'Do not override global B.O. colors, body/html/:root selectors or Bootstrap/LSG theme variables inside modules. Use local module-scoped classes and theme tokens.'
            );
        }

        return $findings;
    }

    protected function checkTokenizedThemeColors(array $contents): array
    {
        $findings = [];
        $config = config('module-design-validator.theme_contract', []);
        $properties = $config['tokenized_color_properties'] ?? [];

        foreach ($contents as $file => $content) {
            $issues = [];

            foreach ($this->extractStyleBlocks($content) as $styleBlock) {
                foreach ($this->extractCssRules($styleBlock) as $rule) {
                    if (! $this->requiresTokenizedColors($rule['selector'], $config)) {
                        continue;
                    }

                    foreach ($properties as $property) {
                        $value = $this->extractCssDeclaration($rule['body'], (string) $property);

                        if ($value === null || ! $this->hasHardcodedCssColor($value)) {
                            continue;
                        }

                        $issues[] = trim($rule['selector']) . ' {' . $property . ': ' . trim($value) . '}';
                    }
                }
            }

            if (empty($issues)) {
                $findings[] = ModuleValidationFinding::passed(
                    'DESIGN_TOKENIZED_THEME_COLORS_OK_' . $this->codeFromPath($file),
                    'Theme color tokens respected',
                    'No hardcoded color rules were detected in panel/card/action style selectors.',
                    $file
                );
                continue;
            }

            $findings[] = ModuleValidationFinding::warning(
                'DESIGN_HARDCODED_THEME_COLORS_' . $this->codeFromPath($file),
                'Hardcoded theme colors detected',
                'Panel/card/action styles define explicit colors instead of B.O. theme tokens: ' . implode(' | ', array_values(array_unique(array_slice($issues, 0, 8)))),
                $this->severity('hardcoded_theme_color'),
                $file,
                'Use B.O. theme tokens such as --card-bg, --border-soft, --text-primary, --text-muted and --lsg-bo-btn-* so light/dark modes remain consistent.'
            );
        }

        return $findings;
    }

    protected function checkColorContrast(array $contents): array
    {
        $findings = [];
        $minimum = (float) config('module-design-validator.theme_contract.min_contrast_ratio', 4.5);

        foreach ($contents as $file => $content) {
            $issues = [];
            foreach ($this->extractStyleBlocks($content) as $styleBlock) {
                foreach ($this->extractCssRules($styleBlock) as $rule) {
                    if ($this->isContrastIgnoredSelector($rule['selector'])) {
                        continue;
                    }

                    $foreground = $this->extractCssColor($rule['body'], 'color');
                    $background = $this->extractCssColor($rule['body'], 'background-color')
                        ?? $this->extractCssColor($rule['body'], 'background');

                    if (! $foreground || ! $background) {
                        continue;
                    }

                    $ratio = $this->contrastRatio($foreground, $background);
                    if ($ratio !== null && $ratio < $minimum) {
                        $issues[] = trim($rule['selector']) . ' ratio ' . number_format($ratio, 2);
                    }
                }
            }

            foreach ($this->extractInlineStyleAttributes($content) as $inlineStyle) {
                $foreground = $this->extractCssColor($inlineStyle, 'color');
                $background = $this->extractCssColor($inlineStyle, 'background-color')
                    ?? $this->extractCssColor($inlineStyle, 'background');

                if (! $foreground || ! $background) {
                    continue;
                }

                $ratio = $this->contrastRatio($foreground, $background);
                if ($ratio !== null && $ratio < $minimum) {
                    $issues[] = 'inline style ratio ' . number_format($ratio, 2);
                }
            }

            if (empty($issues)) {
                $findings[] = ModuleValidationFinding::passed(
                    'DESIGN_COLOR_CONTRAST_OK_' . $this->codeFromPath($file),
                    'No explicit contrast issue detected',
                    'No low-contrast explicit color/background pair was detected in view styles.',
                    $file
                );
                continue;
            }

            $findings[] = ModuleValidationFinding::warning(
                'DESIGN_COLOR_CONTRAST_LOW_' . $this->codeFromPath($file),
                'Potential low color contrast',
                'Explicit color/background pairs are below WCAG AA contrast ratio ' . $minimum . ': ' . implode(' | ', array_slice($issues, 0, 8)),
                $this->severity('contrast_issue'),
                $file,
                'Use B.O. theme tokens or Bootstrap semantic classes so contrast remains correct in both light and dark modes.'
            );
        }

        return $findings;
    }

    protected function extractStyleBlocks(string $content): array
    {
        if (! preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $content, $matches)) {
            return [];
        }

        return $matches[1];
    }

    protected function extractCssRules(string $css): array
    {
        $rules = [];
        if (! preg_match_all('/([^{}@]+)\{([^{}]+)\}/', $css, $matches, PREG_SET_ORDER)) {
            return [];
        }

        foreach ($matches as $match) {
            $rules[] = [
                'selector' => trim($match[1]),
                'body' => trim($match[2]),
            ];
        }

        return $rules;
    }

    protected function extractInlineStyleAttributes(string $content): array
    {
        if (! preg_match_all('/style\s*=\s*(["\'])(.*?)\1/is', $content, $matches)) {
            return [];
        }

        return $matches[2];
    }

    protected function extractCssColor(string $css, string $property): ?array
    {
        $value = $this->extractCssDeclaration($css, $property);
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/!important/i', '', $value) ?? $value);

        if (preg_match('/#([0-9a-f]{3}|[0-9a-f]{6})\b/i', $value, $hex)) {
            return $this->hexToRgb($hex[1]);
        }

        if (preg_match('/rgba?\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*([0-9.]+))?/i', $value, $rgb)) {
            if (isset($rgb[4]) && is_numeric($rgb[4]) && (float) $rgb[4] < 1) {
                return null;
            }

            return [
                max(0, min(255, (int) $rgb[1])),
                max(0, min(255, (int) $rgb[2])),
                max(0, min(255, (int) $rgb[3])),
            ];
        }

        return null;
    }

    protected function extractCssDeclaration(string $css, string $property): ?string
    {
        $declarations = preg_split('/;/', $css) ?: [];

        foreach ($declarations as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode(':', $declaration, 2));
            if (strcasecmp($name, $property) === 0) {
                return $value;
            }
        }

        return null;
    }

    protected function hasForbiddenThemeSelector(string $selector, array $forbiddenSelectors): bool
    {
        $parts = preg_split('/,/', $selector) ?: [];

        foreach ($parts as $part) {
            $part = strtolower(trim($part));
            if ($part === '') {
                continue;
            }

            foreach ($forbiddenSelectors as $forbiddenSelector) {
                $forbidden = strtolower(trim((string) $forbiddenSelector));
                if ($forbidden === '') {
                    continue;
                }

                if (in_array($forbidden, ['body', 'html', ':root'], true)) {
                    if ($part === $forbidden || str_starts_with($part, $forbidden . ':')) {
                        return true;
                    }
                    continue;
                }

                if (str_starts_with($forbidden, '[')) {
                    if ($part === $forbidden || preg_match('/^' . preg_quote($forbidden, '/') . '\s*$/', $part)) {
                        return true;
                    }
                    continue;
                }

                if (str_starts_with($forbidden, '.')) {
                    if (preg_match('/(^|[\s>+~])' . preg_quote($forbidden, '/') . '(\b|[\.#:\[])/', $part)) {
                        return true;
                    }
                    continue;
                }

                if ($part === $forbidden || str_starts_with($part, $forbidden . ' ')) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isContrastIgnoredSelector(string $selector): bool
    {
        $selector = strtolower($selector);
        foreach (config('module-design-validator.theme_contract.contrast_ignored_selector_fragments', []) as $fragment) {
            if (is_string($fragment) && $fragment !== '' && str_contains($selector, strtolower($fragment))) {
                return true;
            }
        }

        return false;
    }

    protected function requiresTokenizedColors(string $selector, array $config): bool
    {
        $selector = strtolower($selector);

        foreach ($config['tokenized_color_ignored_selector_fragments'] ?? [] as $fragment) {
            if (is_string($fragment) && $fragment !== '' && str_contains($selector, strtolower($fragment))) {
                return false;
            }
        }

        foreach ($config['tokenized_color_required_selector_fragments'] ?? [] as $fragment) {
            if (is_string($fragment) && $fragment !== '' && str_contains($selector, strtolower($fragment))) {
                return true;
            }
        }

        return false;
    }

    protected function hasHardcodedCssColor(string $value): bool
    {
        $value = trim(preg_replace('/!important/i', '', $value) ?? $value);

        if (str_contains($value, 'var(') || str_contains($value, 'currentColor')) {
            return false;
        }

        return (bool) preg_match('/#(?:[0-9a-f]{3}|[0-9a-f]{6})\b|rgba?\s*\(/i', $value);
    }

    protected function hexToRgb(string $hex): array
    {
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    protected function contrastRatio(array $foreground, array $background): ?float
    {
        if (count($foreground) !== 3 || count($background) !== 3) {
            return null;
        }

        $l1 = $this->relativeLuminance($foreground);
        $l2 = $this->relativeLuminance($background);

        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    protected function relativeLuminance(array $rgb): float
    {
        $channels = array_map(function ($value) {
            $value = max(0, min(255, (int) $value)) / 255;
            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    protected function isAllowedCssVariable(string $variable, array $allowedPrefixes): bool
    {
        return $this->matchesPrefix($variable, $allowedPrefixes);
    }

    protected function matchesPrefix(string $value, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (is_string($prefix) && $prefix !== '' && str_starts_with($value, $prefix)) {
                return true;
            }
        }

        return false;
    }

    protected function containsButtonLabel(string $content, array $labels): bool
    {
        foreach ($labels as $label) {
            if (! is_string($label) || $label === '') {
                continue;
            }

            $quoted = preg_quote($label, '/');
            if (preg_match('/>\s*' . $quoted . '\s*</i', $content)
                || preg_match('/[\'"]label[\'"]\s*=>\s*[\'"][^\'"]*\b' . $quoted . '\b[^\'"]*[\'"]/i', $content)
                || preg_match('/\blabel\s*:\s*[\'"][^\'"]*\b' . $quoted . '\b[^\'"]*[\'"]/i', $content)) {
                return true;
            }
        }

        return false;
    }

    protected function checkInlineStyles(array $contents): array
    {
        $findings = [];
        $threshold = (int) config('module-design-validator.inline_style_threshold', 8);

        foreach ($contents as $file => $content) {
            $count = substr_count(strtolower($content), 'style=');
            if ($count <= $threshold) {
                $findings[] = ModuleValidationFinding::passed(
                    'DESIGN_INLINE_STYLE_ACCEPTABLE_' . $this->codeFromPath($file),
                    'Inline style usage acceptable',
                    'Inline style count is within threshold: ' . $count . '/' . $threshold,
                    $file
                );
                continue;
            }

            $findings[] = ModuleValidationFinding::passed(
                'DESIGN_INLINE_STYLE_EXCESS_' . $this->codeFromPath($file),
                'Excessive inline styles',
                'The Blade file contains ' . $count . ' inline style attributes. This is accepted for legacy/specialized views pending visual refactor.',
                $file
            );
        }

        return $findings;
    }

    protected function containsAny(string $content, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && stripos($content, (string) $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function containsActionMetadata(string $content, string $action, array $rule): bool
    {
        $hasActionKey = stripos($content, "'key' => '{$action}'") !== false
            || stripos($content, '"key" => "' . $action . '"') !== false
            || stripos($content, '"' . $action . '" =>') !== false
            || stripos($content, "'" . $action . "' =>") !== false;

        return $hasActionKey && (
            $this->containsAny($content, $rule['icons'] ?? [])
            || $this->containsAny($content, $rule['labels'] ?? [])
        );
    }

    protected function hasConfiguredLayoutFile(string $modulePath, array $relativePaths): bool
    {
        foreach ($relativePaths as $relativePath) {
            $path = $modulePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (is_file($path)) {
                return true;
            }
        }

        return false;
    }

    protected function severity(string $key): ValidationSeverity
    {
        $value = config('module-design-validator.severity.' . $key, 'medium');
        return match (strtolower((string) $value)) {
            'info' => ValidationSeverity::Info,
            'low' => ValidationSeverity::Low,
            'high' => ValidationSeverity::High,
            'critical' => ValidationSeverity::Critical,
            'blocker' => ValidationSeverity::Blocker,
            default => ValidationSeverity::Medium,
        };
    }

    protected function codeFromPath(string $path): string
    {
        return strtoupper(substr(preg_replace('/[^A-Za-z0-9]+/', '_', basename($path)), 0, 60));
    }

    protected function buildResult(array $findings, ModuleValidationContext $context, array $viewFiles): ModuleValidationResult
    {
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
                'view_files_count' => count($viewFiles),
                'view_files' => $viewFiles,
            ]
        );
    }

    protected function resolveStatus(array $findings): ValidationStatus
    {
        foreach ($findings as $finding) {
            if ($finding->status === ValidationStatus::Failed && in_array($finding->severity, [ValidationSeverity::Critical, ValidationSeverity::Blocker], true)) {
                return ValidationStatus::Failed;
            }
        }

        foreach ($findings as $finding) {
            if ($finding->status === ValidationStatus::Failed || $finding->status === ValidationStatus::Warning) {
                return ValidationStatus::Warning;
            }
        }

        return ValidationStatus::Passed;
    }
}
