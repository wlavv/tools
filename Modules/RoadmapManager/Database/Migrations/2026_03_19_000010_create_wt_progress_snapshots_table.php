<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_progress_snapshots')) {
            Schema::create('wt_progress_snapshots', function (Blueprint $table) {
                $table->id();
                $table->enum('entity_type', ['group','project','milestone','task']);
                $table->unsignedBigInteger('entity_id');
                $table->date('snapshot_date');
                $table->unsignedTinyInteger('progress')->default(0);
                $table->unsignedSmallInteger('completed_tasks')->default(0);
                $table->unsignedSmallInteger('total_tasks')->default(0);
                $table->decimal('logged_hours', 8, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->unique(['entity_type', 'entity_id', 'snapshot_date'], 'unique_snapshot');
                $table->index(['entity_type', 'entity_id'], 'idx_entity');
                $table->index('snapshot_date', 'idx_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_progress_snapshots');
    }
};
