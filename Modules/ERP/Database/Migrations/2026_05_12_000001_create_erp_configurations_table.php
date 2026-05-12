<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('group', 80)->default('general')->index();
            $table->string('key', 120)->index();
            $table->json('value')->nullable();
            $table->string('type', 40)->default('string');
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_configurations');
    }
};
