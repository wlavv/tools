<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $storeTables = [
        'lsg_catalog_store_products' => 'cascade',
        'lsg_catalog_store_categories' => 'cascade',
        'lsg_catalog_product_assets' => 'null',
        'lsg_catalog_import_batches' => 'null',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('lsg_sites')) {
            return;
        }

        $mapping = Schema::hasTable('lsg_catalog_stores')
            ? $this->migrateCatalogStoresToSites()
            : [];

        $this->remapStoreReferences($mapping);
        $this->dropStoreForeignKeys();
        $this->addSiteForeignKeys();

        Schema::dropIfExists('lsg_catalog_stores');
    }

    public function down(): void
    {
        if (!Schema::hasTable('lsg_catalog_stores')) {
            Schema::create('lsg_catalog_stores', function (Blueprint $table) {
                $table->id();
                $table->string('name', 180);
                $table->string('slug', 200)->unique();
                $table->string('domain', 180)->nullable()->index();
                $table->string('store_code', 80)->nullable()->index();
                $table->string('default_language', 10)->default('pt');
                $table->string('default_currency', 3)->default('EUR');
                $table->boolean('is_active')->default(true)->index();
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('lsg_sites')) {
            DB::table('lsg_sites')
                ->where('site_type', 'store')
                ->orderBy('id')
                ->chunkById(100, function ($sites): void {
                    foreach ($sites as $site) {
                        DB::table('lsg_catalog_stores')->updateOrInsert(
                            ['id' => $site->id],
                            [
                                'name' => $site->name,
                                'slug' => $site->slug,
                                'domain' => $site->domain,
                                'store_code' => strtoupper(str_replace('-', '_', (string) $site->slug)),
                                'default_language' => $site->default_language ?: 'pt',
                                'default_currency' => $site->default_currency ?: 'EUR',
                                'is_active' => $site->status === 'active',
                                'settings' => $site->settings,
                                'created_at' => $site->created_at,
                                'updated_at' => $site->updated_at,
                                'deleted_at' => $site->deleted_at,
                            ]
                        );
                    }
                });
        }

        $this->dropStoreForeignKeys();
        $this->addCatalogStoreForeignKeys();
    }

    private function migrateCatalogStoresToSites(): array
    {
        $mapping = [];

        DB::table('lsg_catalog_stores')
            ->orderBy('id')
            ->chunkById(100, function ($stores) use (&$mapping): void {
                foreach ($stores as $store) {
                    $query = DB::table('lsg_sites')->where('slug', $store->slug);

                    if (!empty($store->domain)) {
                        $query->orWhere('domain', $store->domain);
                    }

                    $site = $query->first();

                    if (!$site) {
                        $siteId = DB::table('lsg_sites')->insertGetId([
                            'name' => $store->name,
                            'slug' => $store->slug,
                            'site_type' => 'store',
                            'domain' => $store->domain,
                            'public_url' => $store->domain ? $this->normalisePublicUrl($store->domain) : null,
                            'environment' => 'production',
                            'status' => $store->is_active ? 'active' : 'inactive',
                            'default_language' => $store->default_language ?: 'pt',
                            'default_currency' => $store->default_currency ?: 'EUR',
                            'settings' => $store->settings,
                            'created_at' => $store->created_at,
                            'updated_at' => $store->updated_at,
                            'deleted_at' => $store->deleted_at ?? null,
                        ]);
                    } else {
                        $siteId = $site->id;
                    }

                    $mapping[(int) $store->id] = (int) $siteId;
                }
            });

        return $mapping;
    }

    private function remapStoreReferences(array $mapping): void
    {
        if ($mapping === []) {
            return;
        }

        foreach (array_keys($this->storeTables) as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'store_id')) {
                continue;
            }

            foreach ($mapping as $oldId => $siteId) {
                DB::table($table)->where('store_id', $oldId)->update(['store_id' => $siteId]);
            }
        }
    }

    private function dropStoreForeignKeys(): void
    {
        foreach (array_keys($this->storeTables) as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'store_id')) {
                continue;
            }

            try {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign(['store_id']));
            } catch (Throwable) {
                // The foreign key may already have been removed in a previous deploy.
            }
        }
    }

    private function addSiteForeignKeys(): void
    {
        foreach ($this->storeTables as $table => $deleteMode) {
            $this->addForeignKey($table, 'lsg_sites', $deleteMode);
        }
    }

    private function addCatalogStoreForeignKeys(): void
    {
        foreach ($this->storeTables as $table => $deleteMode) {
            $this->addForeignKey($table, 'lsg_catalog_stores', $deleteMode);
        }
    }

    private function addForeignKey(string $table, string $targetTable, string $deleteMode): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($targetTable) || !Schema::hasColumn($table, 'store_id')) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($targetTable, $deleteMode): void {
                $foreign = $blueprint->foreign('store_id')->references('id')->on($targetTable);

                $deleteMode === 'null'
                    ? $foreign->nullOnDelete()
                    : $foreign->cascadeOnDelete();
            });
        } catch (Throwable) {
            // Avoid blocking deployments if the FK already exists with the expected target.
        }
    }

    private function normalisePublicUrl(string $domain): string
    {
        return str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')
            ? $domain
            : 'https://' . $domain;
    }
};
