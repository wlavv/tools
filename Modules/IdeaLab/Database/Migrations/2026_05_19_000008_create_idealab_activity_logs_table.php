<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idealab_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->nullable()->constrained('idealab_ideas')->cascadeOnDelete();
            $table->string('event')->index();
            $table->string('level')->default('info')->index();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idealab_activity_logs');
    }
};
