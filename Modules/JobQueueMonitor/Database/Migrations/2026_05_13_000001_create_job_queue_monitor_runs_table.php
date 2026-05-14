<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_queue_monitor_runs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->index();
            $table->string('connection')->nullable()->index();
            $table->string('queue')->nullable()->index();
            $table->string('job_name')->nullable()->index();
            $table->string('status', 40)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('exception_message')->nullable();
            $table->string('exception_file')->nullable();
            $table->unsignedInteger('exception_line')->nullable();
            $table->longText('exception_trace')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'queue']);
            $table->index(['job_name', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_queue_monitor_runs');
    }
};
