<?php

namespace Modules\ConfigInspector\DTOs;

class InspectionItem
{
    public string $severity;
    public string $title;
    public string $message;
    public array $meta;
    public ?string $suggestion;

    public function __construct(string $severity, string $title, string $message, array $meta = [], ?string $suggestion = null)
    {
        $this->severity = $severity;
        $this->title = $title;
        $this->message = $message;
        $this->meta = $meta;
        $this->suggestion = $suggestion;
    }

    public function toArray(): array
    {
        return [
            'severity' => $this->severity,
            'title' => $this->title,
            'message' => $this->message,
            'meta' => $this->meta,
            'suggestion' => $this->suggestion,
        ];
    }
}
