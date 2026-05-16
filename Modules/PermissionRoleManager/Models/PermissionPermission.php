<?php

namespace Modules\PermissionRoleManager\Models;

use App\Support\Breadcrumbs\BreadcrumbRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PermissionPermission extends Model
{
    use SoftDeletes;

    protected $table = 'permission_permissions';

    protected $fillable = [
        'key', 'label', 'module', 'group', 'risk', 'description', 'is_system', 'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionRole::class,
            'permission_role_permission',
            'permission_permission_id',
            'permission_role_id'
        )->withTimestamps();
    }

    public function displayName(?string $contextModule = null): string
    {
        if ($routeName = $this->routeName()) {
            return (!$contextModule ? $this->translatedPermissionRouteName($routeName) : null)
                ?: $this->humanRouteName($routeName, $contextModule)
                ?: $this->translatedRouteName($routeName);
        }

        $label = trim((string) $this->label);

        return $label !== '' ? $label : (string) $this->key;
    }

    public function technicalName(): string
    {
        $parts = array_filter([
            (string) $this->key,
            trim((string) $this->label),
        ]);

        return implode(' | ', array_unique($parts));
    }

    public function routeName(): ?string
    {
        $key = (string) $this->key;

        if (! Str::startsWith($key, 'route.')) {
            return null;
        }

        return Str::after($key, 'route.');
    }

    private function translatedRouteName(string $routeName): ?string
    {
        foreach ($this->routeTranslationKeys($routeName) as $key) {
            $translated = __($key);

            if ($translated !== $key) {
                return $translated;
            }
        }

        try {
            $breadcrumb = app(BreadcrumbRegistry::class)->getAll()[$routeName]['label'] ?? null;

            if ($breadcrumb) {
                $translated = __($breadcrumb);

                if ($translated !== $breadcrumb) {
                    return $translated;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function routeTranslationKeys(string $routeName): array
    {
        $prefix = Str::before($routeName, '.');
        $moduleNamespace = str_replace('_', '-', $prefix);

        return [
            'page_titles.' . $routeName,
            $moduleNamespace . '::page_titles.' . $routeName,
        ];
    }

    private function translatedPermissionRouteName(string $routeName): ?string
    {
        return $this->permissionNameLine('routes.' . $routeName);
    }

    private function humanRouteName(string $routeName, ?string $contextModule = null): string
    {
        $parts = explode('.', $routeName);
        $action = array_pop($parts) ?: 'access';
        $target = implode('.', $parts);
        $contextualTarget = $this->contextualTarget($target, $contextModule);

        $actionLabel = $this->permissionNameLine('actions.' . $action)
            ?: $this->humanizeSegment($action);

        $targetLabel = $contextualTarget !== ''
            ? ($this->permissionNameLine('targets.' . $contextualTarget . '.' . $action)
                ?: $this->permissionNameLine('targets.' . $contextualTarget)
                ?: $this->permissionNameLine('targets.' . $target . '.' . $action)
                ?: $this->permissionNameLine('targets.' . $target)
                ?: $this->humanizeSegment($contextualTarget))
            : '';

        return trim($actionLabel . ' ' . $targetLabel);
    }

    private function contextualTarget(string $target, ?string $contextModule): string
    {
        if (!$contextModule) {
            return $target;
        }

        $segments = explode('.', $target);
        $firstSegment = $segments[0] ?? '';

        if ($this->normalizedPermissionSegment($firstSegment) === $this->normalizedPermissionSegment($contextModule)) {
            array_shift($segments);

            return implode('.', $segments);
        }

        return $target;
    }

    private function normalizedPermissionSegment(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function permissionNameLine(string $key): ?string
    {
        $translationKey = 'permission-role-manager::permission_names.' . $key;
        $translated = __($translationKey);

        return $translated !== $translationKey && is_string($translated) ? $translated : null;
    }

    private function humanizeSegment(string $value): string
    {
        return Str::of($value)
            ->replace(['.', '_', '-'], ' ')
            ->title()
            ->toString();
    }
}
