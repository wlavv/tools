<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceValidator;

class ComplianceValidatorRegistry
{
    public function configured(): array
    {
        return (array) config('module-compliance-center.validators.validators', []);
    }

    public function all(): array
    {
        return collect($this->configured())
            ->mapWithKeys(fn (array $config, string $key) => [$key => $this->describe($key, $config)])
            ->all();
    }

    public function active(?array $keys = null): array
    {
        return collect($this->all())
            ->when($keys, fn ($items) => $items->only($keys))
            ->filter(fn (array $validator) => $validator['enabled'])
            ->values()
            ->all();
    }

    public function sync(): array
    {
        return collect($this->all())->map(function (array $validator) {
            return ComplianceValidator::updateOrCreate(
                ['validator_key' => $validator['key']],
                [
                    'name' => $validator['label'],
                    'module_name' => $validator['module'],
                    'service_class' => $validator['service'],
                    'status' => $validator['enabled'] ? ($validator['available'] ? 'available' : 'unavailable') : 'disabled',
                    'is_available' => $validator['available'],
                    'is_enabled' => $validator['enabled'],
                    'is_required' => $validator['required'],
                    'weight' => $validator['weight'],
                    'last_checked_at' => now(),
                    'metadata' => [
                        'implements_contract' => $validator['implements_contract'],
                    ],
                ]
            );
        })->all();
    }

    private function describe(string $key, array $config): array
    {
        $service = (string) ($config['service'] ?? '');
        $exists = $service !== '' && class_exists($service);
        $contract = \Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface::class;
        $implements = $exists && is_subclass_of($service, $contract);

        return [
            'key' => $key,
            'label' => (string) ($config['label'] ?? $key),
            'module' => (string) ($config['module'] ?? ''),
            'service' => $service,
            'required' => (bool) ($config['required'] ?? false),
            'weight' => (float) ($config['weight'] ?? 0),
            'enabled' => (bool) ($config['enabled'] ?? true),
            'available' => $exists && $implements,
            'implements_contract' => $implements,
        ];
    }
}
