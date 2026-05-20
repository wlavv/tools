<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_compliance_center_run_validators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->string('validator_key');
            $table->string('validator_name');
            $table->string('validator_module');
            $table->string('status')->default('pending');
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('weight', 6, 2)->default(0);
            $table->unsignedInteger('findings_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('warning_count')->default(0);
            $table->unsignedInteger('blocker_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->longText('error_message')->nullable();
            $table->json('raw_result')->nullable();
            $table->timestamps();

            $table->foreign('run_id', 'mcc_run_validators_run_fk')
                ->references('id')->on('module_compliance_center_runs')
                ->cascadeOnDelete();
            $table->index(['run_id', 'validator_key'], 'mcc_rv_run_key_idx');
            $table->index('status', 'mcc_rv_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_compliance_center_run_validators');
    }
};
