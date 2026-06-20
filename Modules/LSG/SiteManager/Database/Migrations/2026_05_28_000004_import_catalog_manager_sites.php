<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_stores') || !Schema::hasTable('lsg_sites')) {
            return;
        }

        $legacyToSite = [];
        $now = now();

        DB::table('catalog_stores')
            ->orderBy('id')
            ->get()
            ->each(function ($store) use (&$legacyToSite, $now) {
                $slug = $this->uniqueSlug((string) ($store->code ?: $store->name), (int) $store->id);
                $settings = $this->legacySettings($store);

                DB::table('lsg_sites')->updateOrInsert(
                    ['slug' => $slug],
                    [
                        'name' => $store->name ?: $store->code ?: 'Site ' . $store->id,
                        'site_type' => $this->mapSiteType($store->site_kind ?? $store->record_type ?? 'store'),
                        'domain' => $store->domain ?: null,
                        'public_url' => $this->publicUrl($store->domain ?? null),
                        'environment' => 'production',
                        'status' => !empty($store->active) ? 'active' : 'inactive',
                        'default_language' => $store->locale ?: 'pt',
                        'default_currency' => $store->currency ?: 'EUR',
                        'monitor_pagespeed' => true,
                        'monitor_availability' => true,
                        'settings' => json_encode($settings),
                        'notes' => 'Importado de CatalogManager.',
                        'updated_at' => $now,
                        'created_at' => $store->created_at ?: $now,
                    ]
                );

                $site = DB::table('lsg_sites')->where('slug', $slug)->first();
                if ($site) {
                    $legacyToSite[(int) $store->id] = (int) $site->id;
                }
            });

        $this->importPageSpeedRuns($legacyToSite, $now);
    }

    public function down(): void
    {
        if (!Schema::hasTable('lsg_sites')) {
            return;
        }

        DB::table('lsg_sites')
            ->where('notes', 'Importado de CatalogManager.')
            ->delete();
    }

    private function importPageSpeedRuns(array $legacyToSite, $now): void
    {
        if (empty($legacyToSite) || !Schema::hasTable('catalog_store_pagespeed_insights') || !Schema::hasTable('lsg_site_pagespeed_runs')) {
            return;
        }

        DB::table('catalog_store_pagespeed_insights')
            ->orderBy('id')
            ->get()
            ->each(function ($run) use ($legacyToSite, $now) {
                $siteId = $legacyToSite[(int) $run->store_id] ?? null;
                if (!$siteId) {
                    return;
                }

                DB::table('lsg_site_pagespeed_runs')->updateOrInsert(
                    [
                        'site_id' => $siteId,
                        'checked_on' => $run->checked_on,
                        'strategy' => $run->strategy ?: 'mobile',
                    ],
                    [
                        'url' => $run->url ?: null,
                        'status' => $run->status ?: 'completed',
                        'performance_score' => $run->performance_score,
                        'accessibility_score' => $run->accessibility_score ?? null,
                        'best_practices_score' => $run->best_practices_score ?? null,
                        'seo_score' => $run->seo_score ?? null,
                        'first_contentful_paint_ms' => $run->first_contentful_paint_ms,
                        'largest_contentful_paint_ms' => $run->largest_contentful_paint_ms,
                        'total_blocking_time_ms' => $run->total_blocking_time_ms,
                        'cumulative_layout_shift' => $run->cumulative_layout_shift,
                        'speed_index_ms' => $run->speed_index_ms,
                        'error_message' => $run->error_message,
                        'raw_summary' => $run->raw_summary,
                        'updated_at' => $now,
                        'created_at' => $run->created_at ?: $now,
                    ]
                );
            });
    }

    private function uniqueSlug(string $value, int $legacyId): string
    {
        $slug = Str::slug($value) ?: 'site-' . $legacyId;
        $existing = DB::table('lsg_sites')->where('slug', $slug)->first();

        if (!$existing) {
            return $slug;
        }

        $settings = json_decode((string) ($existing->settings ?? ''), true);
        if (($settings['legacy_catalog_store_id'] ?? null) === $legacyId) {
            return $slug;
        }

        return $slug . '-legacy-' . $legacyId;
    }

    private function legacySettings(object $store): array
    {
        $settings = json_decode((string) ($store->settings ?? ''), true);
        $settings = is_array($settings) ? $settings : [];

        return array_merge($settings, [
            'legacy_source' => 'catalog_manager',
            'legacy_catalog_store_id' => (int) $store->id,
            'legacy_code' => $store->code ?? null,
            'legacy_record_type' => $store->record_type ?? null,
            'legacy_site_kind' => $store->site_kind ?? null,
        ]);
    }

    private function mapSiteType(string $type): string
    {
        return match ($type) {
            'service', 'labs' => 'service',
            'showcase', 'group' => 'presentation',
            default => 'store',
        };
    }

    private function publicUrl(?string $domain): ?string
    {
        $domain = trim((string) $domain);
        if ($domain === '') {
            return null;
        }

        return Str::startsWith($domain, ['http://', 'https://']) ? $domain : 'https://' . $domain;
    }
};
