<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idealab_ai_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('entrypoint_type')->default('idea_discussion')->index();
            $table->text('description')->nullable();
            $table->longText('system_prompt')->nullable();
            $table->longText('user_prompt_template');
            $table->json('expected_schema')->nullable();
            $table->boolean('supports_chat')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_ai_templates');
    }
};
