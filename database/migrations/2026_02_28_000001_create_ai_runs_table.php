<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ai_runs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('template_key')->default('module_scaffold_v1');
            $table->longText('prompt');
            $table->enum('status',['queued','running','integrating','done','failed'])->default('queued');
            $table->longText('final_answer')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ai_runs');
    }
};