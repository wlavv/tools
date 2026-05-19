<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('idealab_idea_ai_runs')) {
            return;
        }

        Schema::create('idealab_idea_ai_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->constrained('idealab_ideas')->cascadeOnDelete();
            $table->foreignId('ai_consensus_run_id')->constrained('ai_consensus_runs')->cascadeOnDelete();
            $table->string('purpose', 80)->index('iliair_purpose_idx');
            $table->timestamps();

            $table->unique(['idea_id', 'ai_consensus_run_id'], 'iliair_idea_run_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_idea_ai_runs');
    }
};
