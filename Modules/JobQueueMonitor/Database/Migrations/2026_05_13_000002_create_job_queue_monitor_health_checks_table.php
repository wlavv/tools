<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_queue_monitor_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_key')->index();
            $table->string('label');
            $table->string('status', 40)->default('ok')->index();
            $table->string('severity', 40)->default('info')->index();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('checked_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_queue_monitor_health_checks');
    }
};
