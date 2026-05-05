<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wc_3d_generation_jobs')) {
            Schema::create('wc_3d_generation_jobs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_product')->index();
                $table->string('provider', 80)->default('manual_upload')->index();
                $table->string('provider_task_id', 160)->nullable()->index();
                $table->string('provider_status', 80)->nullable()->index();
                $table->unsignedTinyInteger('progress')->default(0);
                $table->string('input_mode', 80)->default('multi_image')->index();
                $table->string('status', 40)->default('draft')->index();
                $table->json('source_resource_ids')->nullable();
                $table->unsignedBigInteger('result_resource_id')->nullable()->index();
                $table->unsignedBigInteger('ar_resource_id')->nullable()->index();
                $table->unsignedBigInteger('vr_resource_id')->nullable()->index();
                $table->text('prompt')->nullable();
                $table->text('notes')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_3d_generation_jobs');
    }
};
