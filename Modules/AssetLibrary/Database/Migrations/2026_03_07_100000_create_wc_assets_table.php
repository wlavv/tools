<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wt_assets')) {
            return;
        }

        Schema::create('wt_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 190)->unique();
            $table->string('type', 50)->index();
            $table->string('status', 50)->default('draft')->index();
            $table->text('description')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('tags', 255)->nullable();
            $table->string('disk', 50)->default('public');
            $table->string('directory', 190)->nullable();
            $table->string('filename', 190);
            $table->string('original_filename', 190)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_path', 255);
            $table->string('preview_path', 255)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_assets');
    }
};
