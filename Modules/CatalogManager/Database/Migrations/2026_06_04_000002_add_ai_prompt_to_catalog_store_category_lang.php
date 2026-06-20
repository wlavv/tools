<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_store_category_lang') || Schema::hasColumn('catalog_store_category_lang', 'ai_prompt')) {
            return;
        }

        Schema::table('catalog_store_category_lang', function (Blueprint $table): void {
            $table->longText('ai_prompt')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('catalog_store_category_lang') || !Schema::hasColumn('catalog_store_category_lang', 'ai_prompt')) {
            return;
        }

        Schema::table('catalog_store_category_lang', function (Blueprint $table): void {
            $table->dropColumn('ai_prompt');
        });
    }
};
