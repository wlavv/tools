<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wc_recognition_scans')) {
            Schema::create('wc_recognition_scans', function (Blueprint $table) {
                $table->id();
                $table->uuid('scan_uuid')->unique();
                $table->unsignedBigInteger('id_session')->nullable()->index();
                $table->unsignedBigInteger('id_capture')->nullable()->index();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->string('recognition_profile', 80)->default('default')->index();
                $table->string('product_scope', 80)->default('global')->index();
                $table->string('status', 40)->default('started')->index();
                $table->string('decision_reason')->nullable();
                $table->string('rejection_reason')->nullable();
                $table->unsignedInteger('input_image_width')->nullable();
                $table->unsignedInteger('input_image_height')->nullable();
                $table->unsignedBigInteger('input_image_size')->nullable();
                $table->decimal('quality_score', 8, 4)->nullable()->index();
                $table->decimal('blur_score', 8, 4)->nullable();
                $table->decimal('brightness_score', 8, 4)->nullable();
                $table->decimal('glare_score', 8, 4)->nullable();
                $table->decimal('card_area_score', 8, 4)->nullable();
                $table->decimal('object_area_score', 8, 4)->nullable();
                $table->decimal('perspective_score', 8, 4)->nullable();
                $table->unsignedInteger('number_of_candidates_initial')->nullable();
                $table->unsignedInteger('number_of_candidates_after_hash')->nullable();
                $table->unsignedInteger('number_of_candidates_after_ocr')->nullable();
                $table->unsignedInteger('number_of_candidates_after_orb')->nullable();
                $table->unsignedBigInteger('top_1_product_id')->nullable()->index();
                $table->json('top_3_candidates')->nullable();
                $table->decimal('score_final', 8, 4)->nullable()->index();
                $table->json('comparator_scores')->nullable();
                $table->unsignedBigInteger('expected_product_id')->nullable()->index();
                $table->string('expected_card_id', 120)->nullable()->index();
                $table->string('scenario_label', 80)->nullable()->index();
                $table->boolean('top_1_correct')->nullable()->index();
                $table->boolean('top_3_correct')->nullable()->index();
                $table->boolean('false_positive')->nullable()->index();
                $table->boolean('false_negative')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_recognition_scan_candidates')) {
            Schema::create('wc_recognition_scan_candidates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_scan')->index();
                $table->unsignedBigInteger('id_product')->nullable()->index();
                $table->unsignedBigInteger('id_resource')->nullable()->index();
                $table->unsignedInteger('rank')->default(1)->index();
                $table->decimal('score_final', 8, 4)->nullable()->index();
                $table->decimal('weighted_score', 8, 4)->nullable();
                $table->decimal('quality_modifier', 6, 4)->nullable();
                $table->json('scores')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['id_scan', 'rank'], 'wc_recognition_scan_candidates_rank_unique');
            });
        }

        if (!Schema::hasTable('wc_recognition_scan_timings')) {
            Schema::create('wc_recognition_scan_timings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_scan')->unique();
                $table->unsignedInteger('total_processing_time_ms')->nullable();
                $table->unsignedInteger('input_preparation_time_ms')->nullable();
                $table->unsignedInteger('quality_check_time_ms')->nullable();
                $table->unsignedInteger('contour_detection_time_ms')->nullable();
                $table->unsignedInteger('perspective_correction_time_ms')->nullable();
                $table->unsignedInteger('hash_generation_time_ms')->nullable();
                $table->unsignedInteger('hash_search_time_ms')->nullable();
                $table->unsignedInteger('color_comparison_time_ms')->nullable();
                $table->unsignedInteger('ocr_time_ms')->nullable();
                $table->unsignedInteger('orb_time_ms')->nullable();
                $table->unsignedInteger('scoring_time_ms')->nullable();
                $table->unsignedInteger('database_time_ms')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_recognition_scan_timings');
        Schema::dropIfExists('wc_recognition_scan_candidates');
        Schema::dropIfExists('wc_recognition_scans');
    }
};
