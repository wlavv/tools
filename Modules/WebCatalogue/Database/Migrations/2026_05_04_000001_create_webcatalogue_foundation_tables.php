<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wc_stores')) {
            Schema::create('wc_stores', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('code', 50)->unique();
                $table->string('domain')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_store_themes')) {
            Schema::create('wc_store_themes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->string('name');
                $table->string('slug')->index();
                $table->boolean('is_default')->default(false)->index();
                $table->unsignedBigInteger('logo_resource_id')->nullable()->index();
                $table->unsignedBigInteger('favicon_resource_id')->nullable()->index();
                $table->string('font_family')->nullable();
                $table->string('heading_font_family')->nullable();
                $table->string('primary_color', 20)->nullable();
                $table->string('secondary_color', 20)->nullable();
                $table->string('accent_color', 20)->nullable();
                $table->string('background_color', 20)->nullable();
                $table->string('text_color', 20)->nullable();
                $table->string('button_style', 50)->nullable();
                $table->string('card_style', 50)->nullable();
                $table->string('border_radius', 20)->nullable();
                $table->longText('custom_css')->nullable();
                $table->json('metadata')->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->timestamps();
                $table->unique(['id_store', 'slug']);
            });
        }

        if (!Schema::hasTable('wc_store_environments')) {
            Schema::create('wc_store_environments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->string('name');
                $table->string('slug')->index();
                $table->boolean('is_default')->default(false)->index();
                $table->string('environment_type', 50)->default('showroom')->index();
                $table->string('background_type', 50)->nullable();
                $table->string('background_color', 20)->nullable();
                $table->unsignedBigInteger('background_resource_id')->nullable()->index();
                $table->unsignedBigInteger('skybox_resource_id')->nullable()->index();
                $table->unsignedBigInteger('floor_resource_id')->nullable()->index();
                $table->string('lighting_preset', 80)->nullable();
                $table->string('camera_preset', 80)->nullable();
                $table->json('vr_scene_config')->nullable();
                $table->json('ar_scene_config')->nullable();
                $table->json('metadata')->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->timestamps();
                $table->unique(['id_store', 'slug']);
            });
        }

        if (!Schema::hasTable('wc_catalogues')) {
            Schema::create('wc_catalogues', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->string('name');
                $table->string('slug')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('cover_resource_id')->nullable()->index();
                $table->string('catalogue_type', 50)->default('showcase')->index();
                $table->boolean('show_prices')->default(false);
                $table->boolean('show_promotions')->default(false);
                $table->string('price_mode', 50)->default('hidden')->index();
                $table->string('visibility', 50)->default('private')->index();
                $table->string('status', 30)->default('draft')->index();
                $table->timestamp('published_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['id_store', 'slug']);
            });
        }

        if (!Schema::hasTable('wc_products')) {
            Schema::create('wc_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->string('external_id')->nullable()->index();
                $table->string('external_source')->nullable()->index();
                $table->string('reference')->index();
                $table->string('sku')->nullable()->index();
                $table->string('ean13')->nullable()->index();
                $table->string('name');
                $table->string('slug')->index();
                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();
                $table->string('brand')->nullable()->index();
                $table->string('category')->nullable()->index();
                $table->decimal('price', 15, 4)->nullable();
                $table->string('currency', 3)->default('EUR');
                $table->decimal('stock', 15, 4)->nullable();
                $table->string('status', 30)->default('draft')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['id_store', 'reference']);
                $table->unique(['id_store', 'slug']);
            });
        }

        if (!Schema::hasTable('wc_catalogue_products')) {
            Schema::create('wc_catalogue_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_catalogue')->index();
                $table->unsignedBigInteger('id_product')->index();
                $table->integer('sort_order')->default(0)->index();
                $table->boolean('is_featured')->default(false)->index();
                $table->string('status', 30)->default('active')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['id_catalogue', 'id_product']);
            });
        }

        if (!Schema::hasTable('wc_product_prices')) {
            Schema::create('wc_product_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_product')->index();
                $table->string('price_type', 50)->default('standard')->index();
                $table->string('currency', 3)->default('EUR')->index();
                $table->decimal('regular_price', 15, 4)->nullable();
                $table->decimal('sale_price', 15, 4)->nullable();
                $table->boolean('tax_included')->default(true);
                $table->decimal('tax_rate', 8, 4)->nullable();
                $table->timestamp('valid_from')->nullable()->index();
                $table->timestamp('valid_until')->nullable()->index();
                $table->string('status', 30)->default('active')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['id_store', 'id_product', 'price_type']);
            });
        }

        if (!Schema::hasTable('wc_promotions')) {
            Schema::create('wc_promotions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->string('name');
                $table->string('slug')->index();
                $table->text('description')->nullable();
                $table->string('promotion_type', 50)->default('campaign')->index();
                $table->string('badge_label')->nullable();
                $table->string('discount_type', 50)->nullable();
                $table->decimal('discount_value', 15, 4)->nullable();
                $table->timestamp('starts_at')->nullable()->index();
                $table->timestamp('ends_at')->nullable()->index();
                $table->string('status', 30)->default('draft')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['id_store', 'slug']);
            });
        }

        if (!Schema::hasTable('wc_promotion_products')) {
            Schema::create('wc_promotion_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_promotion')->index();
                $table->unsignedBigInteger('id_product')->index();
                $table->string('custom_badge_label')->nullable();
                $table->decimal('custom_sale_price', 15, 4)->nullable();
                $table->integer('sort_order')->default(0)->index();
                $table->string('status', 30)->default('active')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['id_promotion', 'id_product']);
            });
        }

        if (!Schema::hasTable('wc_resources')) {
            Schema::create('wc_resources', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->unsignedBigInteger('id_product')->nullable()->index();
                $table->string('resource_owner_type', 80)->nullable()->index();
                $table->unsignedBigInteger('resource_owner_id')->nullable()->index();
                $table->string('resource_type', 80)->index();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('source_type', 50)->default('upload')->index();
                $table->text('source_url')->nullable();
                $table->string('file_path')->nullable();
                $table->string('public_url')->nullable();
                $table->string('filename')->nullable();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('extension', 20)->nullable()->index();
                $table->boolean('is_main')->default(false)->index();
                $table->integer('sort_order')->default(0)->index();
                $table->string('status', 30)->default('active')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['resource_owner_type', 'resource_owner_id', 'resource_type'], 'wc_resources_owner_type_id_type_idx');
            });
        }

        if (!Schema::hasTable('wc_hotspots')) {
            Schema::create('wc_hotspots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->unsignedBigInteger('id_product')->index();
                $table->unsignedBigInteger('id_resource')->nullable()->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('hotspot_type', 50)->default('info')->index();
                $table->decimal('position_x', 12, 6)->nullable();
                $table->decimal('position_y', 12, 6)->nullable();
                $table->decimal('position_z', 12, 6)->nullable();
                $table->decimal('rotation_x', 12, 6)->nullable();
                $table->decimal('rotation_y', 12, 6)->nullable();
                $table->decimal('rotation_z', 12, 6)->nullable();
                $table->unsignedBigInteger('target_resource_id')->nullable()->index();
                $table->text('target_url')->nullable();
                $table->integer('sort_order')->default(0)->index();
                $table->string('status', 30)->default('active')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_import_batches')) {
            Schema::create('wc_import_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->string('source_type', 50)->default('csv')->index();
                $table->string('filename')->nullable();
                $table->string('file_path')->nullable();
                $table->string('status', 30)->default('pending')->index();
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('created_rows')->default(0);
                $table->unsignedInteger('updated_rows')->default(0);
                $table->unsignedInteger('failed_rows')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_import_batch_rows')) {
            Schema::create('wc_import_batch_rows', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_batch')->index();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->unsignedBigInteger('id_product')->nullable()->index();
                $table->unsignedInteger('row_number')->index();
                $table->string('reference')->nullable()->index();
                $table->string('status', 30)->default('pending')->index();
                $table->text('message')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_settings')) {
            Schema::create('wc_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->string('key')->index();
                $table->longText('value')->nullable();
                $table->string('type', 50)->default('string');
                $table->string('group', 80)->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['id_store', 'key']);
            });
        }

        if (!Schema::hasTable('wc_public_links')) {
            Schema::create('wc_public_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->unsignedBigInteger('id_product')->nullable()->index();
                $table->string('token', 120)->unique();
                $table->string('link_type', 50)->default('catalogue')->index();
                $table->string('title')->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_public_links');
        Schema::dropIfExists('wc_settings');
        Schema::dropIfExists('wc_import_batch_rows');
        Schema::dropIfExists('wc_import_batches');
        Schema::dropIfExists('wc_hotspots');
        Schema::dropIfExists('wc_resources');
        Schema::dropIfExists('wc_promotion_products');
        Schema::dropIfExists('wc_promotions');
        Schema::dropIfExists('wc_product_prices');
        Schema::dropIfExists('wc_catalogue_products');
        Schema::dropIfExists('wc_products');
        Schema::dropIfExists('wc_catalogues');
        Schema::dropIfExists('wc_store_environments');
        Schema::dropIfExists('wc_store_themes');
        Schema::dropIfExists('wc_stores');
    }
};
