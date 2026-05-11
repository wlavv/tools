<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_core_manufacturers')) {
            Schema::create('catalog_core_manufacturers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable()->index();
                $table->string('website')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_core_suppliers')) {
            Schema::create('catalog_core_suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable()->index();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('currency', 3)->default('EUR');
                $table->unsignedInteger('lead_time_days')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_core_products')) {
            Schema::create('catalog_core_products', function (Blueprint $table) {
                $table->id();
                $table->string('internal_sku')->nullable()->index();
                $table->string('reference')->nullable()->index();
                $table->string('ean13')->nullable()->index();
                $table->string('name')->nullable();
                $table->unsignedBigInteger('manufacturer_id')->nullable()->index();
                $table->string('type')->default('simple');
                $table->string('status')->default('draft')->index();
                $table->decimal('weight', 10, 3)->nullable();
                $table->decimal('width', 10, 3)->nullable();
                $table->decimal('height', 10, 3)->nullable();
                $table->decimal('depth', 10, 3)->nullable();
                $table->string('housing')->nullable()->index();
                $table->text('internal_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('catalog_core_product_suppliers')) {
            Schema::create('catalog_core_product_suppliers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->string('supplier_reference')->nullable();
                $table->decimal('cost', 12, 4)->nullable();
                $table->string('currency', 3)->default('EUR');
                $table->unsignedInteger('moq')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_stores')) {
            Schema::create('catalog_stores', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('domain')->nullable();
                $table->string('locale', 8)->default('pt');
                $table->string('currency', 3)->default('EUR');
                $table->boolean('active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_store_products')) {
            Schema::create('catalog_store_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('store_id')->index();
                $table->string('status')->default('draft')->index();
                $table->boolean('active')->default(false);
                $table->boolean('visible')->default(false);
                $table->boolean('available_for_order')->default(false);
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'store_id'], 'cat_sp_product_store_unique');
            });
        }

        if (!Schema::hasTable('catalog_store_product_lang')) {
            Schema::create('catalog_store_product_lang', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_product_id')->index();
                $table->string('locale', 8)->default('pt');
                $table->string('name')->nullable();
                $table->text('description_short')->nullable();
                $table->longText('description')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('link_rewrite')->nullable();
                $table->text('keywords')->nullable();
                $table->timestamps();
                $table->unique(['store_product_id', 'locale'], 'cat_sp_lang_unique');
            });
        }

        if (!Schema::hasTable('catalog_store_categories')) {
            Schema::create('catalog_store_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->string('code')->nullable()->index();
                $table->boolean('active')->default(true);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_store_category_lang')) {
            Schema::create('catalog_store_category_lang', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_category_id')->index();
                $table->string('locale', 8)->default('pt');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('link_rewrite')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();
                $table->unique(['store_category_id', 'locale'], 'cat_sc_lang_unique');
            });
        }

        if (!Schema::hasTable('catalog_store_product_categories')) {
            Schema::create('catalog_store_product_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_product_id')->index();
                $table->unsignedBigInteger('store_category_id')->index();
                $table->boolean('is_default')->default(false);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
                $table->unique(['store_product_id', 'store_category_id'], 'cat_spc_unique');
            });
        }

        if (!Schema::hasTable('catalog_store_prices')) {
            Schema::create('catalog_store_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_product_id')->index();
                $table->decimal('price', 12, 4)->nullable();
                $table->decimal('sale_price', 12, 4)->nullable();
                $table->decimal('cost_snapshot', 12, 4)->nullable();
                $table->string('currency', 3)->default('EUR');
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_store_visibility_rules')) {
            Schema::create('catalog_store_visibility_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_product_id')->index();
                $table->boolean('visible')->default(false);
                $table->boolean('searchable')->default(false);
                $table->boolean('available_for_order')->default(false);
                $table->boolean('show_price')->default(true);
                $table->timestamp('scheduled_from')->nullable();
                $table->timestamp('scheduled_to')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_prestashop_store_mapping')) {
            Schema::create('catalog_prestashop_store_mapping', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->index();
                $table->unsignedInteger('ps_id_shop');
                $table->unsignedInteger('ps_id_shop_group')->nullable();
                $table->timestamps();
                $table->unique(['store_id', 'ps_id_shop'], 'cat_ps_store_unique');
            });
        }

        if (!Schema::hasTable('catalog_prestashop_product_mapping')) {
            Schema::create('catalog_prestashop_product_mapping', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedInteger('ps_id_product');
                $table->timestamps();
                $table->unique(['product_id', 'ps_id_product'], 'cat_ps_product_unique');
            });
        }

        if (!Schema::hasTable('catalog_prestashop_category_mapping')) {
            Schema::create('catalog_prestashop_category_mapping', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_category_id')->index();
                $table->unsignedInteger('ps_id_category');
                $table->timestamps();
                $table->unique(['store_category_id', 'ps_id_category'], 'cat_ps_cat_unique');
            });
        }

        if (!Schema::hasTable('catalog_prestashop_sync_queue')) {
            Schema::create('catalog_prestashop_sync_queue', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type')->index();
                $table->unsignedBigInteger('entity_id')->nullable()->index();
                $table->string('operation')->default('update');
                $table->string('status')->default('pending')->index();
                $table->json('payload')->nullable();
                $table->longText('last_error')->nullable();
                $table->unsignedInteger('attempts')->default(0);
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_logs_sync')) {
            Schema::create('catalog_logs_sync', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type')->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('operation')->nullable();
                $table->string('status')->nullable();
                $table->json('payload')->nullable();
                $table->longText('message')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_logs_activity')) {
            Schema::create('catalog_logs_activity', function (Blueprint $table) {
                $table->id();
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('action')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_ai_generations')) {
            Schema::create('catalog_ai_generations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->unsignedBigInteger('store_product_id')->nullable()->index();
                $table->string('type')->index();
                $table->string('status')->default('generated')->index();
                $table->json('input_payload')->nullable();
                $table->json('output_payload')->nullable();
                $table->boolean('applied')->default(false);
                $table->timestamp('applied_at')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_ai_store_settings')) {
            Schema::create('catalog_ai_store_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->index();
                $table->string('tone')->nullable();
                $table->string('brand_voice')->nullable();
                $table->string('language', 8)->default('pt');
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_ai_store_settings');
        Schema::dropIfExists('catalog_ai_generations');
        Schema::dropIfExists('catalog_logs_activity');
        Schema::dropIfExists('catalog_logs_sync');
        Schema::dropIfExists('catalog_prestashop_sync_queue');
        Schema::dropIfExists('catalog_prestashop_category_mapping');
        Schema::dropIfExists('catalog_prestashop_product_mapping');
        Schema::dropIfExists('catalog_prestashop_store_mapping');
        Schema::dropIfExists('catalog_store_visibility_rules');
        Schema::dropIfExists('catalog_store_prices');
        Schema::dropIfExists('catalog_store_product_categories');
        Schema::dropIfExists('catalog_store_category_lang');
        Schema::dropIfExists('catalog_store_categories');
        Schema::dropIfExists('catalog_store_product_lang');
        Schema::dropIfExists('catalog_store_products');
        Schema::dropIfExists('catalog_stores');
        Schema::dropIfExists('catalog_core_product_suppliers');
        Schema::dropIfExists('catalog_core_products');
        Schema::dropIfExists('catalog_core_suppliers');
        Schema::dropIfExists('catalog_core_manufacturers');
    }
};
