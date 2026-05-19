<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idealab_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('idealab_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description_raw')->nullable();
            $table->longText('description_refined')->nullable();
            $table->string('status')->default('draft')->index();
            $table->string('priority')->default('medium')->index();
            $table->string('source')->default('manual')->index();
            $table->unsignedTinyInteger('opportunity_score')->nullable();
            $table->unsignedTinyInteger('effort_score')->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->unsignedTinyInteger('strategic_score')->nullable();
            $table->unsignedTinyInteger('reusability_score')->nullable();
            $table->unsignedTinyInteger('monetization_score')->nullable();
            $table->decimal('final_score', 5, 2)->nullable()->index();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->unsignedBigInteger('converted_project_id')->nullable()->index();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_ideas');
    }
};
