<?php

namespace Modules\ModuleHealth\Support;

class ModuleComponentRegistry
{
    public static function checks(): array
    {
        return [
            'module_json' => ['label' => 'module.json', 'paths' => ['module.json']],
            'service_provider' => ['label' => 'Service Provider', 'paths' => ['Providers/*ServiceProvider.php']],
            'web_routes' => ['label' => 'Web Routes', 'paths' => ['Routes/web.php', 'routes/web.php']],
            'api_routes' => ['label' => 'API Routes', 'paths' => ['Routes/api.php', 'routes/api.php']],
            'controllers' => ['label' => 'Controllers', 'paths' => ['Http/Controllers/*.php', 'Http/Controllers/**/*.php']],
            'models' => ['label' => 'Models', 'paths' => ['Models/*.php', 'Entities/*.php']],
            'views' => ['label' => 'Views', 'paths' => ['Resources/views/*.blade.php', 'Resources/views/**/*.blade.php']],
            'migrations' => ['label' => 'Migrations', 'paths' => ['Database/Migrations/*.php', 'database/migrations/*.php']],
            'seeders' => ['label' => 'Seeders', 'paths' => ['Database/Seeders/*.php', 'database/seeders/*.php']],
            'config' => ['label' => 'Config', 'paths' => ['Config/config.php', 'Config/*.php', 'config/*.php']],
            'actions' => ['label' => 'Actions Config', 'paths' => ['Config/actions.php']],
            'breadcrumbs' => ['label' => 'Breadcrumbs Config', 'paths' => ['Config/breadcrumbs.php']],
            'page_titles' => ['label' => 'Page Titles Config', 'paths' => ['Config/page_titles.php']],
            'permissions' => ['label' => 'Permissions Config', 'paths' => ['Config/permissions.php', 'Config/acl.php']],
            'translations' => ['label' => 'Translations', 'paths' => ['Resources/lang/*/*.php', 'Resources/lang/*.php', 'lang/*/*.php', 'lang/*.php']],
            'diagnostics' => ['label' => 'Diagnostics', 'paths' => ['Config/diagnostics.php', 'Services/*Diagnostic*.php', 'Http/Controllers/*Diagnostic*.php']],
            'audit_logs' => ['label' => 'Audit Logs', 'paths' => ['Models/*Audit*.php', 'Services/*Audit*.php', 'Database/Migrations/*audit*.php']],
            'notifications' => ['label' => 'Notifications', 'paths' => ['Notifications/*.php', 'Services/*Notification*.php']],
            'jobs' => ['label' => 'Jobs', 'paths' => ['Jobs/*.php', 'Jobs/**/*.php']],
            'events' => ['label' => 'Events', 'paths' => ['Events/*.php', 'Listeners/*.php']],
            'commands' => ['label' => 'Console Commands', 'paths' => ['Console/Commands/*.php']],
            'scheduler' => ['label' => 'Scheduler', 'paths' => ['Console/Kernel.php', 'Config/schedule.php']],
            'exports' => ['label' => 'Exports', 'paths' => ['Exports/*.php', 'Services/*Export*.php']],
            'imports' => ['label' => 'Imports', 'paths' => ['Imports/*.php', 'Services/*Import*.php']],
            'webhooks' => ['label' => 'Webhooks', 'paths' => ['Http/Controllers/*Webhook*.php', 'Routes/webhooks.php']],
            'tests' => ['label' => 'Tests', 'paths' => ['Tests/*.php', 'Tests/**/*.php']],
            'billing' => ['label' => 'Billing', 'paths' => ['Services/*Billing*.php', 'Models/*Subscription*.php']],
            'multi_tenant' => ['label' => 'Multi-tenant Layer', 'paths' => ['Services/*Tenant*.php', 'Models/*Tenant*.php', 'Config/tenancy.php']],
            'public_assets' => ['label' => 'Public Assets', 'paths' => ['Resources/assets/*', 'public/*']],
            'sdk' => ['label' => 'SDK', 'paths' => ['SDK/*.php', 'Sdk/*.php']],
            'documentation' => ['label' => 'Documentation', 'paths' => ['README.md', 'Docs/*.md', 'docs/*.md']],
        ];
    }
}
