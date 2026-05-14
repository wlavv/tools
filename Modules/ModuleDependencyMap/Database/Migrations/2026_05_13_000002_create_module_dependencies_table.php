<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('module_dependencies')) {
            return;
        }

        Schema::create('module_dependencies', function (Blueprint $table): void {
            $table->id();
            $table->string('source_module')->index();
            $table->string('target_module')->index();
            $table->string('dependency_type', 40)->index();
            $table->text('file_path')->nullable();
            $table->unsignedInteger('line_number')->nullable();
            $table->text('reference')->nullable();
            $table->unsignedTinyInteger('confidence')->default(100);
            $table->char('evidence_hash', 40)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('first_detected_at')->nullable();
            $table->timestamp('last_detected_at')->nullable()->index();
            $table->unsignedBigInteger('latest_scan_id')->nullable()->index();
            $table->timestamps();

            $table->index(['source_module', 'target_module'], 'mdm_deps_source_target_idx');
            $table->index(['target_module', 'source_module'], 'mdm_deps_target_source_idx');
            $table->index(['source_module', 'is_active'], 'mdm_deps_source_active_idx');
            $table->index(['target_module', 'is_active'], 'mdm_deps_target_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_dependencies');
    }
};
