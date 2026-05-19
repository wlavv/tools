<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idealab_project_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->constrained('idealab_ideas')->cascadeOnDelete();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->string('status')->default('payload_created')->index();
            $table->json('conversion_payload')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('converted_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_project_conversions');
    }
};
