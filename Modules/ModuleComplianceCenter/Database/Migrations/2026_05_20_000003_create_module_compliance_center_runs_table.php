<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_compliance_center_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('managed_module_id')->nullable();
            $table->string('module_name');
            $table->string('module_path', 500);
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('final_status')->nullable();
            $table->decimal('structure_score', 5, 2)->nullable();
            $table->decimal('design_score', 5, 2)->nullable();
            $table->decimal('security_score', 5, 2)->nullable();
            $table->decimal('integration_score', 5, 2)->nullable();
            $table->decimal('health_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->unsignedInteger('total_findings')->default(0);
            $table->unsignedInteger('failed_findings')->default(0);
            $table->unsignedInteger('warning_findings')->default(0);
            $table->unsignedInteger('blocker_findings')->default(0);
            $table->json('options')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->longText('error_message')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->longText('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('managed_module_id', 'mcc_runs_module_fk')
                ->references('id')->on('module_compliance_center_modules')
                ->nullOnDelete();
            $table->index('status', 'mcc_runs_status_idx');
            $table->index('final_status', 'mcc_runs_final_status_idx');
            $table->index('created_at', 'mcc_runs_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_compliance_center_runs');
    }
};
