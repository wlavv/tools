<?php

namespace App\Services\Backoffice;

use App\Models\BackofficeAcknowledgement;
use Illuminate\Support\Collection;

class AcknowledgementService
{
    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    public function sourceHash(string $sourceType, string|int $sourceId): string
    {
        return sha1($sourceType . ':' . (string) $sourceId);
    }

    public function userId(?int $userId = null): ?int
    {
        return $userId ?? auth()->id();
    }

    public function acknowledgedIds(string $sourceType, iterable $sourceIds, ?int $userId = null): array
    {
        $ids = collect($sourceIds)
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return BackofficeAcknowledgement::query()
            ->where('source_type', $sourceType)
            ->where('status', self::STATUS_ACKNOWLEDGED)
            ->whereIn('source_id', $ids)
            ->where('user_id', $this->userId($userId))
            ->pluck('source_id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function isAcknowledged(string $sourceType, string|int $sourceId, ?int $userId = null): bool
    {
        return BackofficeAcknowledgement::query()
            ->where('source_type', $sourceType)
            ->where('source_hash', $this->sourceHash($sourceType, $sourceId))
            ->where('status', self::STATUS_ACKNOWLEDGED)
            ->where('user_id', $this->userId($userId))
            ->exists();
    }

    public function acknowledge(string $sourceType, string|int $sourceId, array $context = [], ?int $userId = null): BackofficeAcknowledgement
    {
        $sourceId = (string) $sourceId;
        $resolvedUserId = $this->userId($userId);

        return BackofficeAcknowledgement::query()->updateOrCreate(
            [
                'user_id' => $resolvedUserId,
                'source_hash' => $this->sourceHash($sourceType, $sourceId),
            ],
            [
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'status' => self::STATUS_ACKNOWLEDGED,
                'acknowledged_at' => now(),
                'context' => $context ?: null,
            ]
        );
    }

    public function unread(Collection $items, string $sourceType, ?int $userId = null): Collection
    {
        $acknowledged = $this->acknowledgedIds($sourceType, $items->pluck('id'), $userId);

        return $items->reject(fn ($item) => in_array((string) $item->id, $acknowledged, true))->values();
    }
}
