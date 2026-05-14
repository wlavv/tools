<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_log_id')->constrained('audit_logs')->cascadeOnDelete();
            $table->string('tag', 80)->index();
            $table->timestamps();

            $table->unique(['audit_log_id', 'tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_tags');
    }
};
