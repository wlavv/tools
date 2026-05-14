<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_health_scans', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('completed');
            $table->unsignedInteger('modules_total')->default(0);
            $table->unsignedInteger('broken_total')->default(0);
            $table->unsignedInteger('incomplete_total')->default(0);
            $table->unsignedInteger('functional_total')->default(0);
            $table->unsignedInteger('enhanced_total')->default(0);
            $table->json('summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_health_scans');
    }
};
