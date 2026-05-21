<?php

namespace Modules\WebCatalogue\Services\Recognition;

class RecognitionTimingTracker
{
    private float $startedAt;
    private float $lastMarkAt;
    private array $timings = [];

    public function __construct()
    {
        $this->startedAt = microtime(true);
        $this->lastMarkAt = $this->startedAt;
    }

    public function mark(string $key): int
    {
        $now = microtime(true);
        $elapsed = (int) round(($now - $this->lastMarkAt) * 1000);
        $this->timings[$key] = ($this->timings[$key] ?? 0) + $elapsed;
        $this->lastMarkAt = $now;

        return $elapsed;
    }

    public function measure(string $key, callable $callback): mixed
    {
        $before = microtime(true);

        try {
            return $callback();
        } finally {
            $this->timings[$key] = ($this->timings[$key] ?? 0) + (int) round((microtime(true) - $before) * 1000);
            $this->lastMarkAt = microtime(true);
        }
    }

    public function total(): int
    {
        return (int) round((microtime(true) - $this->startedAt) * 1000);
    }

    public function all(): array
    {
        return array_merge($this->timings, [
            'total_processing_time_ms' => $this->total(),
        ]);
    }
}
