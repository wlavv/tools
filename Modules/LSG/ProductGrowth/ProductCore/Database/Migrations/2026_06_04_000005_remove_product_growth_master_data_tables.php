<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->remapProductMasterData();
        $this->dropProductForeignKeys();

        Schema::dropIfExists('lsg_catalog_store_product_categories');
        Schema::dropIfExists('lsg_catalog_store_categories');
        Schema::dropIfExists('lsg_catalog_core_suppliers');
        Schema::dropIfExists('lsg_catalog_core_brands');
    }

    public function down(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_brands')) {
            Schema::create('lsg_catalog_core_brands', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 180);
                $table->string('slug', 200)->unique();
                $table->string('external_reference', 120)->nullable()->index();
                $table->string('website', 255)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('lsg_catalog_core_suppliers')) {
            Schema::create('lsg_catalog_core_suppliers', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 180);
                $table->string('slug', 200)->unique();
                $table->string('vat_number', 60)->nullable()->index();
                $table->string('email', 180)->nullable();
                $table->string('website', 255)->nullable();
                $table->string('country', 80)->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('lsg_catalog_store_categories')) {
            Schema::create('lsg_catalog_store_categories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('lsg_sites')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('lsg_catalog_store_categories')->nullOnDelete();
                $table->string('name', 180);
                $table->string('slug', 200);
                $table->text('description')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['store_id', 'slug'], 'lsg_catalog_store_categories_unique');
            });
        }

        if (!Schema::hasTable('lsg_catalog_store_product_categories')) {
            Schema::create('lsg_catalog_store_product_categories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_product_id')->constrained('lsg_catalog_store_products')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('lsg_catalog_store_categories')->cascadeOnDelete();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->unique(['store_product_id', 'category_id'], 'lsg_store_product_category_unique');
            });
        }
    }

    private function remapProductMasterData(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_products')) {
            return;
        }

        if (
            Schema::hasColumn('lsg_catalog_core_products', 'brand_id')
            && Schema::hasTable('lsg_catalog_core_brands')
            && Schema::hasTable('catalog_core_manufacturers')
        ) {
            DB::table('lsg_catalog_core_products')
                ->whereNotNull('brand_id')
                ->orderBy('id')
                ->get(['id', 'brand_id'])
                ->each(function ($product): void {
                    $oldBrand = DB::table('lsg_catalog_core_brands')->where('id', $product->brand_id)->first();
                    if (!$oldBrand) {
                        return;
                    }

                    $manufacturerId = DB::table('catalog_core_manufacturers')
                        ->where('slug', $oldBrand->slug)
                        ->orWhere('name', $oldBrand->name)
                        ->value('id');

                    if ($manufacturerId) {
                        DB::table('lsg_catalog_core_products')->where('id', $product->id)->update(['brand_id' => $manufacturerId]);
                    }
                });
        }

        if (
            Schema::hasColumn('lsg_catalog_core_products', 'supplier_id')
            && Schema::hasTable('lsg_catalog_core_suppliers')
            && Schema::hasTable('catalog_core_suppliers')
        ) {
            DB::table('lsg_catalog_core_products')
                ->whereNotNull('supplier_id')
                ->orderBy('id')
                ->get(['id', 'supplier_id'])
                ->each(function ($product): void {
                    $oldSupplier = DB::table('lsg_catalog_core_suppliers')->where('id', $product->supplier_id)->first();
                    if (!$oldSupplier) {
                        return;
                    }

                    $supplierId = DB::table('catalog_core_suppliers')
                        ->where('code', Str::upper((string) $oldSupplier->slug))
                        ->orWhere('name', $oldSupplier->name)
                        ->value('id');

                    if ($supplierId) {
                        DB::table('lsg_catalog_core_products')->where('id', $product->id)->update(['supplier_id' => $supplierId]);
                    }
                });
        }
    }

    private function dropProductForeignKeys(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_products')) {
            return;
        }

        Schema::table('lsg_catalog_core_products', function (Blueprint $table): void {
            foreach (['brand_id', 'supplier_id'] as $column) {
                if (!Schema::hasColumn('lsg_catalog_core_products', $column)) {
                    continue;
                }

                try {
                    $table->dropForeign([$column]);
                } catch (Throwable) {
                    // The FK may already be absent in older installs.
                }
            }
        });
    }
};
