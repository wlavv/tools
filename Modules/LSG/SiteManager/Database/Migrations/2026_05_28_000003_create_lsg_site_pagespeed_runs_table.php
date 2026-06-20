<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lsg_site_pagespeed_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('lsg_sites')->cascadeOnDelete();
            $table->date('checked_on')->index();
            $table->string('strategy', 20)->default('mobile')->index();
            $table->string('url', 255)->nullable();
            $table->string('status', 40)->default('completed')->index();
            $table->unsignedTinyInteger('performance_score')->nullable();
            $table->unsignedTinyInteger('accessibility_score')->nullable();
            $table->unsignedTinyInteger('best_practices_score')->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->unsignedInteger('first_contentful_paint_ms')->nullable();
            $table->unsignedInteger('largest_contentful_paint_ms')->nullable();
            $table->unsignedInteger('total_blocking_time_ms')->nullable();
            $table->unsignedInteger('cumulative_layout_shift')->nullable();
            $table->unsignedInteger('speed_index_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->json('raw_summary')->nullable();
            $table->timestamps();
            $table->unique(['site_id', 'checked_on', 'strategy'], 'lsg_site_pagespeed_daily_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lsg_site_pagespeed_runs');
    }
};
