<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_export_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('profile_key')->index();
            $table->string('profile_type')->default('model');
            $table->string('profile_class')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('format')->default('csv');
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->string('download_name')->nullable();
            $table->unsignedInteger('rows_count')->default(0);
            $table->longText('query_sql')->nullable();
            $table->string('query_hash', 64)->nullable()->index();
            $table->json('filters')->nullable();
            $table->json('context')->nullable();
            $table->foreignId('report_template_id')->nullable()->constrained('data_export_report_templates')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->json('errors')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['profile_key', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_export_batches');
    }
};
