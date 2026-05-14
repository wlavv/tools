<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_log_id')->constrained('audit_logs')->cascadeOnDelete();
            $table->string('related_type', 150)->index();
            $table->string('related_id', 100)->index();
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_relations');
    }
};
