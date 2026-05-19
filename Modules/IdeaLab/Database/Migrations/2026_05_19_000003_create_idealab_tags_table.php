<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idealab_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('idealab_idea_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->constrained('idealab_ideas')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('idealab_tags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['idea_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_idea_tag');
        Schema::dropIfExists('idealab_tags');
    }
};
