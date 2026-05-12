<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('title', 160);
            $table->string('icon', 120)->nullable();
            $table->string('component', 160);
            $table->string('zone', 40)->default('center')->index();
            $table->unsignedInteger('sort_order')->default(100);
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_dashboard_widgets');
    }
};
