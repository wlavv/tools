<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_health_scan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('module_health_scans')->cascadeOnDelete();
            $table->string('module_name');
            $table->string('module_slug')->nullable();
            $table->string('module_path');
            $table->string('profile')->default('structural');
            $table->string('status')->default('unknown');
            $table->unsignedTinyInteger('completion')->default(0);
            $table->unsignedInteger('required_found')->default(0);
            $table->unsignedInteger('required_total')->default(0);
            $table->unsignedInteger('recommended_found')->default(0);
            $table->unsignedInteger('recommended_total')->default(0);
            $table->unsignedInteger('optional_found')->default(0);
            $table->unsignedInteger('optional_total')->default(0);
            $table->json('components')->nullable();
            $table->json('missing_required')->nullable();
            $table->json('missing_recommended')->nullable();
            $table->json('present_optional')->nullable();
            $table->json('recommendations')->nullable();
            $table->timestamps();

            $table->index(['module_name', 'status']);
            $table->index(['profile', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_health_scan_items');
    }
};
