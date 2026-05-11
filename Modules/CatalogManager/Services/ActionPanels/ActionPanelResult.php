<?php

namespace Modules\CatalogManager\Services\ActionPanels;

class ActionPanelResult
{
    public function __construct(
        public string $key,
        public string $title,
        public string $description = '',
        public string $icon = 'fa-solid fa-circle-info',
        public string $tone = 'primary',
        public int $count = 0,
        public array $items = [],
        public array $actions = [],
        public array $meta = [],
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
