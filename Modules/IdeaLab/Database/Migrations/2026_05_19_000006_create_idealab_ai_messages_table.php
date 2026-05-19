<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idealab_ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->constrained('idealab_ideas')->cascadeOnDelete();
            $table->foreignId('ai_run_id')->nullable()->constrained('idealab_ai_runs')->nullOnDelete();
            $table->string('role')->index(); // user, assistant, system, tool
            $table->longText('content');
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_ai_messages');
    }
};
