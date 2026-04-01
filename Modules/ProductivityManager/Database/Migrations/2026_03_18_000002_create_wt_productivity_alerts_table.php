<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wt_productivity_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('source')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_productivity_alerts');
    }
};
