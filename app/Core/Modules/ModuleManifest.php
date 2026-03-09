<?php

namespace App\Core\Modules;

use InvalidArgumentException;

class ModuleManifest
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly bool $enabled,
        public readonly string $version,
        public readonly string $description,
        public readonly string $provider,
        public readonly array $dependencies,
        public readonly array $raw = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        foreach (['name', 'slug', 'enabled', 'version', 'description', 'provider'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Missing module manifest key [{$key}].");
            }
        }

        $dependencies = $data['dependencies'] ?? [
            'composer' => [],
            'npm' => [],
            'internal_modules' => [],
        ];

        return new self(
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            enabled: (bool) $data['enabled'],
            version: (string) $data['version'],
            description: (string) $data['description'],
            provider: (string) $data['provider'],
            dependencies: [
                'composer' => array_values($dependencies['composer'] ?? []),
                'npm' => array_values($dependencies['npm'] ?? []),
                'internal_modules' => array_values($dependencies['internal_modules'] ?? []),
            ],
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'enabled' => $this->enabled,
            'version' => $this->version,
            'description' => $this->description,
            'provider' => $this->provider,
            'dependencies' => $this->dependencies,
        ];
    }
}
