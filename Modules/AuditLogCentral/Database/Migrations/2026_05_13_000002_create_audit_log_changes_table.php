<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_log_id')->constrained('audit_logs')->cascadeOnDelete();
            $table->string('field', 150)->index();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('change_type', 50)->default('updated')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_changes');
    }
};
