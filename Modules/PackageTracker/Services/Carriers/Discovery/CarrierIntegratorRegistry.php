<?php

namespace Modules\PackageTracker\Services\Carriers\Discovery;

use Illuminate\Support\Collection;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierIntegratorInterface;

class CarrierIntegratorRegistry
{
    /** @var array<string, CarrierIntegratorInterface>|null */
    private ?array $integrators = null;

    /**
     * @return array<string, CarrierIntegratorInterface>
     */
    public function all(): array
    {
        if ($this->integrators !== null) {
            return $this->integrators;
        }

        $before = get_declared_classes();

        foreach ($this->paths() as $path) {
            if (!is_dir($path)) {
                continue;
            }

            foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*Integrator.php') ?: [] as $file) {
                require_once $file;
            }
        }

        $after = get_declared_classes();
        $classes = array_values(array_diff($after, $before));

        // Include already autoloaded integrators too, useful during tests or optimized classmaps.
        foreach (get_declared_classes() as $class) {
            if (str_contains($class, '\\PackageTracker\\') && str_ends_with($class, 'Integrator')) {
                $classes[] = $class;
            }
        }

        $integrators = [];

foreach (array_unique($classes) as $class) {
            if (!is_subclass_of($class, CarrierIntegratorInterface::class)) {
                continue;
            }

            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $instance = app($class);
            $integrators[$instance->code()] = $instance;
        }

        ksort($integrators);

        return $this->integrators = $integrators;
    }

    public function get(string $code): ?CarrierIntegratorInterface
    {
        return $this->all()[$code] ?? null;
    }

    public function has(string $code): bool
    {
        return isset($this->all()[$code]);
    }

    public function codes(): array
    {
        return array_keys($this->all());
    }

    public function asCollection(): Collection
    {
        return collect($this->all());
    }

    private function paths(): array
    {
        $default = [dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Integrators'];

        return array_values(array_unique(array_filter(array_merge(
            $default,
            config('package_tracker.integrator_paths', [])
        ))));
    }
}
