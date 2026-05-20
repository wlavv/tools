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
                    $files[strtolower($file->getPathname())] = $file->getPathname();
                }
            }
        }

        $files = array_values($files);
        sort($files);

        return $files;
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
