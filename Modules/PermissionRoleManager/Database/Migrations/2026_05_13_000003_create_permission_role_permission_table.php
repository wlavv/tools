<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_role_id')->constrained('permission_roles')->cascadeOnDelete();
            $table->foreignId('permission_permission_id')->constrained('permission_permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['permission_role_id', 'permission_permission_id'], 'prp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role_permission');
    }
};
