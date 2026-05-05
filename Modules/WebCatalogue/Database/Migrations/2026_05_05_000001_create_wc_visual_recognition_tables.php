<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wc_visual_recognition_sessions')) {
            Schema::create('wc_visual_recognition_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->unsignedBigInteger('id_catalogue')->nullable()->index();
                $table->unsignedBigInteger('id_product')->nullable()->index();
                $table->string('session_token', 120)->unique();
                $table->string('device_type', 60)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->string('status', 40)->default('started')->index();
                $table->decimal('matched_score', 8, 4)->nullable();
                $table->timestamp('matched_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_visual_recognition_captures')) {
            Schema::create('wc_visual_recognition_captures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_session')->index();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->unsignedBigInteger('id_product')->nullable()->index();
                $table->string('capture_type', 60)->default('object_photo')->index();
                $table->string('file_path')->nullable();
                $table->string('public_url')->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('status', 40)->default('stored')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_visual_recognition_matches')) {
            Schema::create('wc_visual_recognition_matches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_session')->index();
                $table->unsignedBigInteger('id_product')->index();
                $table->string('match_provider', 80)->default('manual_review');
                $table->decimal('score', 8, 4)->nullable();
                $table->unsignedInteger('rank')->default(1);
                $table->string('status', 40)->default('suggested')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_unmatched_product_leads')) {
            Schema::create('wc_unmatched_product_leads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_session')->nullable()->index();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->string('brand')->nullable()->index();
                $table->string('model')->nullable();
                $table->string('reference')->nullable();
                $table->text('description')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('label_photo_path')->nullable();
                $table->string('object_photo_path')->nullable();
                $table->string('status', 40)->default('new')->index();
                $table->unsignedInteger('lead_score')->default(0);
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wc_brand_prospect_leads')) {
            Schema::create('wc_brand_prospect_leads', function (Blueprint $table) {
                $table->id();
                $table->string('brand')->index();
                $table->string('matched_domain')->nullable();
                $table->unsignedInteger('total_requests')->default(0);
                $table->string('status', 40)->default('new')->index();
                $table->timestamp('last_requested_at')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_brand_prospect_leads');
        Schema::dropIfExists('wc_unmatched_product_leads');
        Schema::dropIfExists('wc_visual_recognition_matches');
        Schema::dropIfExists('wc_visual_recognition_captures');
        Schema::dropIfExists('wc_visual_recognition_sessions');
    }
};
