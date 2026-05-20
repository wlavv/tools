<?php

return [
    'view_paths' => [
        'Resources/views',
        'resources/views',
    ],

    'excluded_view_path_fragments' => [
        '/resources/views/front/',
        '/resources/views/public/',
        '/resources/views/diagnostics/',
    ],

    'expected_layout_patterns' => [
        '@extends',
        'layouts.',
        'x-app-layout',
        'layouts.app',
        'layouts.admin',
        'layouts.backoffice',
        'layouts.lsg',
    ],

    'breadcrumb_patterns' => [
        'breadcrumb',
        'breadcrumbs',
        'fa-angle-left',
        'route(',
    ],

    'layout_contract' => [
        'controller_title_patterns' => [
            'setPageTitle(',
            '$this->pageTitle',
        ],
        'controller_breadcrumb_patterns' => [
            'setBreadcrumbs(',
            'addBreadcrumb(',
        ],
        'controller_action_patterns' => [
            'setActions(',
            'addAction(',
            'replaceAction(',
            'Config/actions.php',
        ],
        'config_files' => [
            'Config/breadcrumbs.php',
            'Config/actions.php',
            'Config/page_titles.php',
            'config/breadcrumbs.php',
            'config/actions.php',
            'config/page_titles.php',
        ],
        'manual_breadcrumb_patterns' => [
            '<nav aria-label="breadcrumb"',
            'class="breadcrumb',
            "class='breadcrumb",
            '<ol class="breadcrumb',
            "<ol class='breadcrumb",
        ],
        'manual_action_patterns' => [
            'breadcrumbs-actions',
            'lsg-page-actions',
            'btn-toolbar',
            'role="toolbar"',
        ],
        'manual_title_patterns' => [
            '<h1',
            'class="h1',
            "class='h1",
        ],
        'duplicate_action_detection' => [
            'new' => [
                'global_patterns' => ["'key' => 'new'", '"key" => "new"', "'new' =>", '"new" =>', 'makeNewAction', 'actionLink(\'new\''],
                'view_patterns' => ['fa-plus', 'btn-outline-success', '>New<', '>Novo<', '>Add<', '>Adicionar<'],
            ],
            'back' => [
                'global_patterns' => ["'key' => 'back'", '"key" => "back"', "'back' =>", '"back" =>', 'makeBackAction', 'actionLink(\'back\''],
                'view_patterns' => ['fa-angle-left', 'fa-arrow-left', '>Back<', '>Voltar<'],
            ],
            'config' => [
                'global_patterns' => ["'key' => 'config'", '"key" => "config"', "'config' =>", '"config" =>', 'actionLink(\'config\''],
                'view_patterns' => ['fa-cog', 'fa-gear', '>Configure<', '>Settings<'],
            ],
            'report' => [
                'global_patterns' => ["'key' => 'report'", '"key" => "report"', "'report' =>", '"report" =>', 'actionLink(\'report\''],
                'view_patterns' => ['fa-file-lines', '>Report<', '>Relatório<'],
            ],
        ],
    ],

    'card_patterns' => [
        'card',
        'card-header',
        'card-body',
    ],

    'datatable_patterns' => [
        'DataTable(',
        'dataTable(',
        'datatable',
        'table-striped',
        'table-hover',
        'id="datatable',
        "id='datatable",
    ],

    'sweetalert_patterns' => [
        'Swal.fire',
        'sweetalert',
        'SweetAlert',
        'swal(',
    ],

    'fontawesome_patterns' => [
        'fa-',
        'fas ',
        'far ',
        'fa-solid',
        'fa-regular',
    ],

    'dropzone_patterns' => [
        'dropzone',
        'Dropzone',
        'dz-message',
    ],

    'empty_state_patterns' => [
        'empty-state',
        'no records',
        'sem registos',
        'nenhum',
        'no data',
        'datatable-empty',
        '@forelse',
    ],

    'responsive_patterns' => [
        'col-md-',
        'col-lg-',
        'row',
        'table-responsive',
        'd-flex',
        'flex-wrap',
    ],

    'forbidden_view_classes' => [
        'container-fluid' => [
            'severity' => 'medium',
            'message' => 'Module views must not add container-fluid. The B.O. layout already owns the content wrapper.',
            'recommendation' => 'Remove container-fluid from module Blade views and let layouts.app/app-content control page width and spacing.',
        ],
    ],

    'theme_contract' => [
        'min_contrast_ratio' => 4.5,
        'large_text_min_contrast_ratio' => 3.0,
        'allowed_css_variables' => [
            '--module-',
            '--idealab-',
        ],
        'forbidden_css_variables' => [
            '--bs-body',
            '--bs-primary',
            '--bs-secondary',
            '--bs-success',
            '--bs-danger',
            '--bs-warning',
            '--bs-info',
            '--bs-light',
            '--bs-dark',
            '--lsg-',
            '--app-',
            '--theme-',
        ],
        'forbidden_selectors' => [
            ':root',
            'body',
            'html',
            '[data-bs-theme',
            '[data-theme',
            '.dark',
            '.light',
        ],
        'forbidden_properties' => [
            'background',
            'background-color',
            'color',
            'border-color',
            'box-shadow',
        ],
        'forbidden_important_properties' => [
            'background',
            'background-color',
            'color',
            'border-color',
            'box-shadow',
        ],
        'contrast_ignored_selector_fragments' => [
            'icon',
            'logo',
            'thumb',
            'thumbnail',
            'image',
            'img',
            'media',
            'stage',
            'preview',
            'avatar',
            'swatch',
            'badge',
            'alert',
            'score',
            'risk',
            'status',
            'tag',
            'chip',
            'button',
            'btn',
            'nav',
            'tab',
            'metric',
            'counter',
            'suggestion',
            'progress',
        ],
        'tokenized_color_properties' => [
            'background',
            'background-color',
            'color',
            'border-color',
        ],
        'tokenized_color_required_selector_fragments' => [
            'panel',
            'card',
            'header',
            'section',
            'action',
            'button',
            'btn',
            'badge',
            'alert',
            'list',
            'item',
            'summary',
        ],
        'tokenized_color_ignored_selector_fragments' => [
            'icon',
            'logo',
            'image',
            'img',
            'thumb',
            'thumbnail',
            'avatar',
            'swatch',
            'progress',
            'donut',
            'chart',
        ],
    ],

    'button_rules' => [
        'new' => [
            'classes' => ['btn-outline-success'],
            'icons' => ['fa-plus'],
            'labels' => ['new', 'novo', 'create', 'criar', 'add', 'adicionar'],
        ],
        'show' => [
            'classes' => ['btn-outline-primary'],
            'icons' => ['fa-eye'],
            'labels' => ['show', 'ver', 'view', 'details', 'detalhe'],
        ],
        'edit' => [
            'classes' => ['btn-outline-warning'],
            'icons' => ['fa-pencil', 'fa-pen'],
            'labels' => ['edit', 'editar'],
        ],
        'delete' => [
            'classes' => ['btn-outline-danger'],
            'icons' => ['fa-trash'],
            'labels' => ['delete', 'apagar', 'remover', 'destroy'],
        ],
        'back' => [
            'classes' => ['btn-outline-primary'],
            'icons' => ['fa-angle-left', 'fa-arrow-left'],
            'labels' => ['back', 'voltar'],
        ],
        'save' => [
            'classes' => ['btn-outline-primary'],
            'icons' => ['fa-floppy-disk', 'fa-save'],
            'labels' => ['save', 'guardar'],
        ],
        'config' => [
            'classes' => ['btn-outline-primary'],
            'icons' => ['fa-cog', 'fa-gear'],
            'labels' => ['ações', 'actions'],
        ],
    ],

    'inline_style_threshold' => 8,

    'severity' => [
        'missing_views' => 'high',
        'missing_layout' => 'high',
        'missing_breadcrumbs' => 'medium',
        'missing_cards' => 'medium',
        'missing_datatables' => 'medium',
        'missing_sweetalerts' => 'medium',
        'missing_fontawesome' => 'low',
        'button_mismatch' => 'medium',
        'missing_empty_state' => 'low',
        'missing_responsive_patterns' => 'low',
        'excess_inline_styles' => 'medium',
        'missing_dropzone' => 'medium',
        'misplaced_page_chrome' => 'medium',
        'duplicate_page_action' => 'medium',
        'forbidden_view_class' => 'medium',
        'theme_override' => 'high',
        'contrast_issue' => 'high',
        'hardcoded_theme_color' => 'medium',
    ],
];
