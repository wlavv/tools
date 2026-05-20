<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_compliance_center_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->string('title');
            $table->longText('summary')->nullable();
            $table->string('final_status');
            $table->decimal('final_score', 5, 2)->nullable();
            $table->json('report_payload');
            $table->json('recommendations')->nullable();
            $table->unsignedBigInteger('ai_consensus_run_id')->nullable();
            $table->json('project_tasks_payload')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('run_id', 'mcc_reports_run_fk')
                ->references('id')->on('module_compliance_center_runs')
                ->cascadeOnDelete();
            $table->index('final_status', 'mcc_reports_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_compliance_center_reports');
    }
};
