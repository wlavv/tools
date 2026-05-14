<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('module_dependency_scans')) {
            return;
        }

        Schema::create('module_dependency_scans', function (Blueprint $table): void {
            $table->id();
            $table->string('module_name')->index();
            $table->string('status', 20)->default('running')->index();
            $table->string('health_status', 20)->default('unknown')->index();
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->unsignedInteger('direct_dependencies_count')->default(0);
            $table->unsignedInteger('dependents_count')->default(0);
            $table->unsignedInteger('circular_dependencies_count')->default(0);
            $table->unsignedInteger('critical_dependencies_count')->default(0);
            $table->unsignedInteger('stale_dependencies_count')->default(0);
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->unsignedBigInteger('triggered_by')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['module_name', 'status', 'finished_at'], 'mdm_scans_module_status_finished_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_dependency_scans');
    }
};
