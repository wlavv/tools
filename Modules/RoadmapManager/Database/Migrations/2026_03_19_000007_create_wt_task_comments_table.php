<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_task_comments')) {
            Schema::create('wt_task_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->longText('content');
                $table->json('mentions')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('task_id', 'idx_task');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_comments');
    }
};
