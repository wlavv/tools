<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_task_attachments')) {
            Schema::create('wt_task_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('disk', 50)->default('public');
                $table->string('path', 500);
                $table->string('filename', 255);
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->timestamps();

                $table->index('task_id', 'idx_task');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_attachments');
    }
};
