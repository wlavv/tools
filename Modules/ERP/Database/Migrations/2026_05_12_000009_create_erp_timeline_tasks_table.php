<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_timeline_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('step_key', 120)->index();
            $table->string('task_key', 160)->index();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->string('icon', 120)->nullable();
            $table->string('status', 60)->default('pending')->index();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(100);
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->unique(['step_key', 'task_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_timeline_tasks');
    }
};
