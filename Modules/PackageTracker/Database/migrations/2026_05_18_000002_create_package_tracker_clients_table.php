<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_tracker_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_key', 120)->unique();
            $table->string('name');
            $table->string('contact_email')->nullable()->index();
            $table->string('public_token', 80)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->json('theme')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tracker_clients');
    }
};
