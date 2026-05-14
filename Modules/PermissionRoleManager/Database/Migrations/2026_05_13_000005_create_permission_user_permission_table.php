<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_user_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreignId('permission_permission_id')->constrained('permission_permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'permission_permission_id'], 'pup_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user_permission');
    }
};
