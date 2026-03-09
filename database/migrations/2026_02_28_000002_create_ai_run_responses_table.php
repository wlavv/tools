<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        if (Schema::hasTable('ai_run_responses')) return;

        Schema::create('ai_run_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_run_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->enum('status',['queued','running','done','failed'])->default('queued');
            $table->longText('raw_output')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ai_run_responses');
    }
};