<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wc_recognition_benchmark_calls')) {
            return;
        }

        Schema::create('wc_recognition_benchmark_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_run')->index();
            $table->unsignedBigInteger('id_result')->index();
            $table->unsignedBigInteger('id_session')->index();
            $table->unsignedBigInteger('id_capture')->nullable()->index();
            $table->string('flow_key', 80)->index();
            $table->string('flow_stage')->nullable()->index();
            $table->string('endpoint_key', 80)->index();
            $table->string('method', 12)->default('POST');
            $table->string('url_path')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->boolean('ok')->default(false)->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('request_bytes')->nullable();
            $table->unsignedInteger('response_bytes')->nullable();
            $table->unsignedInteger('client_time_ms')->nullable();
            $table->unsignedInteger('server_time_ms')->nullable();
            $table->unsignedInteger('gateway_time_ms')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('headers')->nullable();
            $table->json('metadata')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_recognition_benchmark_calls');
    }
};
