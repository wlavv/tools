<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lsg_sites', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('site_type', 40)->default('store')->index();
            $table->string('domain', 180)->nullable()->index();
            $table->string('public_url', 255)->nullable();
            $table->string('environment', 40)->default('production')->index();
            $table->string('status', 40)->default('active')->index();
            $table->string('default_language', 10)->default('pt');
            $table->string('default_currency', 3)->default('EUR');
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->boolean('monitor_pagespeed')->default(true)->index();
            $table->boolean('monitor_availability')->default(true)->index();
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lsg_sites');
    }
};
