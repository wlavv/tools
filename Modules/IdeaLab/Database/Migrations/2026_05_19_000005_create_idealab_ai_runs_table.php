<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idealab_ai_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->constrained('idealab_ideas')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('idealab_ai_templates')->nullOnDelete();
            $table->string('run_type')->default('idea_deconstruction')->index();
            $table->string('status')->default('pending')->index();
            $table->json('prompt_payload')->nullable();
            $table->longText('prompt_text')->nullable();
            $table->json('response_payload')->nullable();
            $table->longText('response_text')->nullable();
            $table->longText('summary')->nullable();
            $table->json('scores')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_ai_runs');
    }
};
