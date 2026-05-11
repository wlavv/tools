<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_store_pagespeed_insights')) {
            Schema::create('catalog_store_pagespeed_insights', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->index();
                $table->date('checked_on')->index();
                $table->string('strategy', 24)->default('mobile');
                $table->string('url')->nullable();
                $table->string('status', 32)->default('pending')->index();
                $table->unsignedSmallInteger('performance_score')->nullable();
                $table->unsignedInteger('first_contentful_paint_ms')->nullable();
                $table->unsignedInteger('largest_contentful_paint_ms')->nullable();
                $table->unsignedInteger('total_blocking_time_ms')->nullable();
                $table->unsignedInteger('cumulative_layout_shift')->nullable();
                $table->unsignedInteger('speed_index_ms')->nullable();
                $table->text('error_message')->nullable();
                $table->json('raw_summary')->nullable();
                $table->timestamps();

                $table->unique(['store_id', 'checked_on', 'strategy'], 'cat_store_psi_daily_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_store_pagespeed_insights');
    }
};
