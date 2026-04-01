<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wt_productivity_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('project')->default('General');
            $table->enum('status', ['todo', 'doing', 'blocked', 'done'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->date('due_date')->nullable();
            $table->text('blocked_reason')->nullable();
            $table->string('blocked_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('project');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_productivity_tasks');
    }
};
