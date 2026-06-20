<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wc_recognition_benchmark_runs')) {
            Schema::create('wc_recognition_benchmark_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_session')->index();
                $table->unsignedBigInteger('id_capture')->nullable()->index();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->unsignedBigInteger('expected_product_id')->nullable()->index();
                $table->string('scenario_label')->nullable()->index();
                $table->string('status', 40)->default('created')->index();
                $table->string('triggered_by', 40)->default('manual')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->json('summary')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_recognition_benchmark_results')) {
            Schema::create('wc_recognition_benchmark_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_run')->index();
                $table->unsignedBigInteger('id_session')->index();
                $table->unsignedBigInteger('id_capture')->nullable()->index();
                $table->string('flow_key', 80)->index();
                $table->string('flow_label')->nullable();
                $table->string('flow_stage')->nullable()->index();
                $table->string('base_url')->nullable();
                $table->string('status', 40)->default('pending')->index();
                $table->boolean('ok')->default(false)->index();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->unsignedInteger('total_time_ms')->nullable();
                $table->unsignedInteger('quality_time_ms')->nullable();
                $table->unsignedInteger('normalize_time_ms')->nullable();
                $table->unsignedInteger('markers_time_ms')->nullable();
                $table->unsignedInteger('identifiers_time_ms')->nullable();
                $table->decimal('quality_score', 8, 4)->nullable();
                $table->decimal('normalize_confidence', 8, 4)->nullable();
                $table->unsignedInteger('marker_count')->nullable();
                $table->unsignedInteger('identifier_count')->nullable();
                $table->string('normalized_path')->nullable();
                $table->string('debug_path')->nullable();
                $table->text('error')->nullable();
                $table->json('metrics')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_recognition_benchmark_results');
        Schema::dropIfExists('wc_recognition_benchmark_runs');
    }
};
