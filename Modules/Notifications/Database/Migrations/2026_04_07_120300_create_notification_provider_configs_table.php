<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_provider_configs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 40);
            $table->string('provider', 60);
            $table->boolean('enabled')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['channel', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_provider_configs');
    }
};
