<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_project_group_links')) {
            Schema::create('wt_project_group_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('roadmap_group_id');
                $table->timestamps();

                $table->unique(['project_id', 'roadmap_group_id'], 'uniq_project_group');
                $table->index('project_id', 'idx_project_id');
                $table->index('roadmap_group_id', 'idx_group_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_project_group_links');
    }
};
