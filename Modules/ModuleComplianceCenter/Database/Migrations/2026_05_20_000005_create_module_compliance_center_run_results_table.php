<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_compliance_center_run_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('run_validator_id')->nullable();
            $table->string('validator_key');
            $table->string('area')->nullable();
            $table->string('code');
            $table->string('status');
            $table->string('severity');
            $table->string('title');
            $table->longText('message')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->unsignedInteger('line_number')->nullable();
            $table->text('expected_value')->nullable();
            $table->text('actual_value')->nullable();
            $table->longText('recommendation')->nullable();
            $table->boolean('auto_fix_available')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('run_id', 'mcc_results_run_fk')
                ->references('id')->on('module_compliance_center_runs')
                ->cascadeOnDelete();
            $table->foreign('run_validator_id', 'mcc_results_rv_fk')
                ->references('id')->on('module_compliance_center_run_validators')
                ->nullOnDelete();
            $table->index(['run_id', 'severity'], 'mcc_results_run_sev_idx');
            $table->index(['validator_key', 'status'], 'mcc_results_key_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_compliance_center_run_results');
    }
};
