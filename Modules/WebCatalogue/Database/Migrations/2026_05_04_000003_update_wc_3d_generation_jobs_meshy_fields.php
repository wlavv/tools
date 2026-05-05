<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wc_3d_generation_jobs')) {
            return;
        }

        if (!Schema::hasColumn('wc_3d_generation_jobs', 'provider_task_id')) {
            Schema::table('wc_3d_generation_jobs', function (Blueprint $table) {
                $table->string('provider_task_id', 160)->nullable()->index()->after('provider');
            });
        }

        if (!Schema::hasColumn('wc_3d_generation_jobs', 'provider_status')) {
            Schema::table('wc_3d_generation_jobs', function (Blueprint $table) {
                $table->string('provider_status', 80)->nullable()->index()->after('provider_task_id');
            });
        }

        if (!Schema::hasColumn('wc_3d_generation_jobs', 'progress')) {
            Schema::table('wc_3d_generation_jobs', function (Blueprint $table) {
                $table->unsignedTinyInteger('progress')->default(0)->after('provider_status');
            });
        }

        if (!Schema::hasColumn('wc_3d_generation_jobs', 'started_at')) {
            Schema::table('wc_3d_generation_jobs', function (Blueprint $table) {
                $table->timestamp('started_at')->nullable()->after('error_message');
            });
        }

        if (!Schema::hasColumn('wc_3d_generation_jobs', 'completed_at')) {
            Schema::table('wc_3d_generation_jobs', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            });
        }

        if (!Schema::hasColumn('wc_3d_generation_jobs', 'failed_at')) {
            Schema::table('wc_3d_generation_jobs', function (Blueprint $table) {
                $table->timestamp('failed_at')->nullable()->after('completed_at');
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive for production systems.
    }
};
