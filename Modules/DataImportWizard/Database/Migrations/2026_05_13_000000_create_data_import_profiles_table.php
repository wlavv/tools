<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_import_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('class_name');
            $table->string('module')->nullable();
            $table->string('label');
            $table->string('status')->default('valid');
            $table->json('metadata')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_import_profiles');
    }
};
