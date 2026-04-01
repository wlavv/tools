<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_roadmap_groups')) {
            Schema::create('wt_roadmap_groups', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->nullable();
                $table->string('name', 150);
                $table->string('slug', 150)->unique();
                $table->text('description')->nullable();
                $table->string('color', 7)->default('#6366f1');
                $table->string('icon', 50)->nullable();
                $table->enum('status', ['active', 'archived', 'planning'])->default('active');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_roadmap_groups');
    }
};
