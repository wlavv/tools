<?php

namespace Modules\Tasks\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FamilyPlannerThoughtService
{
    protected array $fallbackThoughts = [
        ['quote' => 'O sucesso é a soma de pequenos esforços repetidos dia após dia.', 'author' => 'Família LS'],
        ['quote' => 'Uma casa organizada é um abraço silencioso ao dia que começa.', 'author' => 'Family Planner'],
        ['quote' => 'Pequenas tarefas feitas com consistência criam grandes conquistas.', 'author' => 'LS Family'],
        ['quote' => 'A família cresce quando todos participam, mesmo nos detalhes.', 'author' => 'Planner'],
        ['quote' => 'Disciplina com carinho vale mais do que pressa sem direção.', 'author' => 'Family Hub'],
        ['quote' => 'Hoje não precisa ser perfeito. Precisa apenas avançar.', 'author' => 'LS Group'],
    ];

    public function today(): array
    {
        $today = now()->toDateString();
        $cacheKey = 'family_planner_thought:' . $today;

        return Cache::remember($cacheKey, now()->endOfDay(), function () use ($today) {
            $stored = $this->getStoredThoughtForDate($today);
            if ($stored !== null) {
                return $stored;
            }

            $apiThought = $this->fetchFromApi();
            if ($apiThought !== null) {
                $this->storeThoughtForDate($today, $apiThought);
                $this->appendFallbackPool($apiThought);

                return $apiThought;
            }

            $fallback = $this->fallbackForDate();
            $this->storeThoughtForDate($today, $fallback);

            return $fallback;
        });
    }

    protected function fetchFromApi(): ?array
    {
        $sources = [
            fn () => $this->fetchFromFavQs(),
            fn () => $this->fetchFromZenQuotes(),
        ];

        foreach ($sources as $source) {
            try {
                $thought = $source();

                if ($this->isValidThought($thought)) {
                    return $thought;
                }
            } catch (\Throwable $e) {
                Log::warning('FamilyPlannerThoughtService quote API failed', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    protected function fetchFromFavQs(): ?array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get('https://favqs.com/api/qotd');

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return $this->normalizeThought(
            data_get($data, 'quote.body'),
            data_get($data, 'quote.author'),
            'favqs'
        );
    }

    protected function fetchFromZenQuotes(): ?array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get('https://zenquotes.io/api/today');

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $first = is_array($data) ? ($data[0] ?? null) : null;

        return $this->normalizeThought(
            is_array($first) ? ($first['q'] ?? null) : null,
            is_array($first) ? ($first['a'] ?? null) : null,
            'zenquotes'
        );
    }

    protected function normalizeThought(?string $quote, ?string $author, string $source): ?array
    {
        $quote = trim((string) $quote);
        $author = trim((string) $author);

        if ($quote === '') {
            return null;
        }

        return [
            'quote' => $quote,
            'author' => $author !== '' ? $author : strtoupper($source),
            'source' => $source,
            'is_fallback' => false,
            'raw_quote' => null,
            'raw_language' => null,
            'translated_language' => null,
        ];
    }

    protected function fallbackForDate(): array
    {
        $pool = $this->getFallbackPool();
        $index = (int) now()->format('z') % count($pool);
        $selected = $pool[$index];

        return [
            'quote' => $selected['quote'],
            'author' => $selected['author'] ?? 'Family Hub',
            'source' => 'fallback',
            'is_fallback' => true,
            'raw_quote' => null,
            'raw_language' => null,
            'translated_language' => null,
        ];
    }

    protected function getFallbackPool(): array
    {
        $dynamicPool = Cache::get('family_planner_thought:fallback_pool', []);

        if (! is_array($dynamicPool)) {
            $dynamicPool = [];
        }

        $merged = array_merge($this->fallbackThoughts, $dynamicPool);
        $unique = [];
        $seen = [];

        foreach ($merged as $item) {
            if (! is_array($item)) {
                continue;
            }

            $quote = trim((string) ($item['quote'] ?? ''));
            $author = trim((string) ($item['author'] ?? ''));

            if ($quote === '') {
                continue;
            }

            $fingerprint = mb_strtolower($quote . '|' . $author);

            if (isset($seen[$fingerprint])) {
                continue;
            }

            $seen[$fingerprint] = true;
            $unique[] = [
                'quote' => $quote,
                'author' => $author !== '' ? $author : 'Family Hub',
            ];
        }

        return ! empty($unique) ? $unique : $this->fallbackThoughts;
    }

    protected function appendFallbackPool(array $thought): void
    {
        $pool = Cache::get('family_planner_thought:fallback_pool', []);

        if (! is_array($pool)) {
            $pool = [];
        }

        $pool[] = [
            'quote' => $thought['quote'],
            'author' => $thought['author'] ?? 'API',
        ];

        $normalized = [];
        $seen = [];

        foreach ($pool as $item) {
            $quote = trim((string) ($item['quote'] ?? ''));
            $author = trim((string) ($item['author'] ?? ''));

            if ($quote === '') {
                continue;
            }

            $fingerprint = mb_strtolower($quote . '|' . $author);

            if (isset($seen[$fingerprint])) {
                continue;
            }

            $seen[$fingerprint] = true;
            $normalized[] = [
                'quote' => $quote,
                'author' => $author !== '' ? $author : 'API',
            ];
        }

        Cache::forever('family_planner_thought:fallback_pool', array_slice($normalized, -500));
    }

    protected function getStoredThoughtForDate(string $date): ?array
    {
        if (! $this->canUseDatabaseStorage()) {
            return null;
        }

        $row = DB::table('wt_task_family_planner_thoughts')
            ->where('thought_date', $date)
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'quote' => (string) $row->quote,
            'author' => (string) ($row->author ?: 'Family Hub'),
            'source' => (string) ($row->source ?: 'database'),
            'is_fallback' => (bool) ($row->is_fallback ?? false),
            'raw_quote' => $row->raw_quote,
            'raw_language' => $row->raw_language,
            'translated_language' => $row->translated_language,
        ];
    }

    protected function storeThoughtForDate(string $date, array $thought): void
    {
        if (! $this->canUseDatabaseStorage()) {
            return;
        }

        DB::table('wt_task_family_planner_thoughts')->updateOrInsert(
            ['thought_date' => $date],
            [
                'quote' => $thought['quote'],
                'author' => $thought['author'] ?? 'Family Hub',
                'source' => $thought['source'] ?? 'unknown',
                'is_fallback' => (int) ($thought['is_fallback'] ?? false),
                'raw_quote' => $thought['raw_quote'] ?? null,
                'raw_language' => $thought['raw_language'] ?? null,
                'translated_language' => $thought['translated_language'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function canUseDatabaseStorage(): bool
    {
        try {
            return Schema::hasTable('wt_task_family_planner_thoughts');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function isValidThought(?array $thought): bool
    {
        return is_array($thought)
            && ! empty($thought['quote'])
            && is_string($thought['quote']);
    }
}
