<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 80)->index();
            $table->string('name', 120);
            $table->foreignId('from_status_id')->nullable()->constrained('erp_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->constrained('erp_statuses')->cascadeOnDelete();
            $table->string('action_key', 120)->index();
            $table->string('action_label', 160);
            $table->string('icon', 120)->nullable();
            $table->string('button_class', 120)->nullable();
            $table->boolean('requires_confirmation')->default(false);
            $table->boolean('requires_permission')->default(false);
            $table->string('permission_key', 160)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_workflows');
    }
};
