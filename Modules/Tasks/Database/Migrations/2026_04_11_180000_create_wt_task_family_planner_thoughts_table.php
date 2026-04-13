<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wt_task_family_planner_thoughts', function (Blueprint $table) {
            $table->id();
            $table->date('thought_date')->unique();
            $table->text('quote');
            $table->string('author')->nullable();
            $table->string('source')->nullable();
            $table->boolean('is_fallback')->default(false);
            $table->text('raw_quote')->nullable();
            $table->string('raw_language', 8)->nullable();
            $table->string('translated_language', 8)->nullable()->default('pt');
            $table->timestamps();

            $table->index(['thought_date', 'source'], 'wt_task_fp_thoughts_date_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_family_planner_thoughts');
    }
};
