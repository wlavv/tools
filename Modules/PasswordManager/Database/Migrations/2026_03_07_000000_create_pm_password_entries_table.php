<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_password_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title', 150);
            $table->string('category', 100)->nullable();
            $table->string('url')->nullable();
            $table->string('account_email', 150)->nullable();
            $table->string('login_username', 150)->nullable();
            $table->longText('encrypted_password');
            $table->longText('encrypted_secret')->nullable();
            $table->longText('encrypted_notes')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'title']);
            $table->index(['user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_password_entries');
    }
};
