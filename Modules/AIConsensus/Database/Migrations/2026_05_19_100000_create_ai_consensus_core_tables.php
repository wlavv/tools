<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_consensus_templates')) {
            Schema::create('ai_consensus_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_key', 150)->unique();
                $table->string('name', 180);
                $table->text('description')->nullable();
                $table->string('module_scope', 80)->nullable()->index('aict_scope_idx');
                $table->string('category', 80)->nullable()->index('aict_cat_idx');
                $table->longText('system_prompt')->nullable();
                $table->longText('user_prompt_template');
                $table->json('expected_output_schema')->nullable();
                $table->string('default_output_type', 80)->nullable();
                $table->json('default_options')->nullable();
                $table->string('version', 30)->default('1.0.0');
                $table->boolean('is_active')->default(true)->index('aict_active_idx');
                $table->unsignedBigInteger('created_by')->nullable()->index('aict_user_idx');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_consensus_providers')) {
            Schema::create('ai_consensus_providers', function (Blueprint $table) {
                $table->id();
                $table->string('provider_key', 80)->unique();
                $table->string('name', 120);
                $table->string('driver', 80);
                $table->string('model', 150)->nullable();
                $table->boolean('is_active')->default(true)->index('aicp_active_idx');
                $table->integer('priority')->default(100)->index('aicp_prio_idx');
                $table->decimal('weight', 8, 4)->default(1);
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_consensus_runs')) {
            Schema::create('ai_consensus_runs', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('source_module', 80)->index('aicr_src_mod_idx');
                $table->string('source_type', 80)->index('aicr_src_type_idx');
                $table->string('source_id', 120)->nullable()->index('aicr_src_id_idx');
                $table->foreignId('template_id')->nullable()->constrained('ai_consensus_templates')->nullOnDelete();
                $table->string('output_type', 80)->index('aicr_out_idx');
                $table->string('status', 40)->default('pending')->index('aicr_status_idx');
                $table->string('title', 180)->nullable();
                $table->json('input_payload');
                $table->json('context_payload')->nullable();
                $table->json('options')->nullable();
                $table->longText('final_output')->nullable();
                $table->decimal('final_score', 8, 2)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->text('error_message')->nullable();
                $table->unsignedBigInteger('requested_by')->nullable()->index('aicr_user_idx');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_consensus_messages')) {
            Schema::create('ai_consensus_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('ai_consensus_runs')->cascadeOnDelete();
                $table->string('role', 30)->index('aicm_role_idx');
                $table->longText('message');
                $table->json('payload')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index('aicm_user_idx');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_consensus_provider_responses')) {
            Schema::create('ai_consensus_provider_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('ai_consensus_runs')->cascadeOnDelete();
                $table->foreignId('provider_id')->nullable()->constrained('ai_consensus_providers')->nullOnDelete();
                $table->string('status', 40)->default('pending')->index('aicpr_status_idx');
                $table->json('input_payload')->nullable();
                $table->longText('raw_response')->nullable();
                $table->json('normalized_response')->nullable();
                $table->decimal('score', 8, 2)->nullable();
                $table->decimal('cost_estimate', 12, 6)->nullable();
                $table->integer('tokens_input')->nullable();
                $table->integer('tokens_output')->nullable();
                $table->integer('latency_ms')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_consensus_outputs')) {
            Schema::create('ai_consensus_outputs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('ai_consensus_runs')->cascadeOnDelete();
                $table->string('output_type', 80)->index('aico_type_idx');
                $table->string('format', 40)->default('json');
                $table->longText('content')->nullable();
                $table->json('json_payload')->nullable();
                $table->boolean('schema_valid')->default(false);
                $table->json('validation_errors')->nullable();
                $table->unsignedBigInteger('approved_by_user')->nullable()->index('aico_appr_user_idx');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_consensus_contexts')) {
            Schema::create('ai_consensus_contexts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('ai_consensus_runs')->cascadeOnDelete();
                $table->string('context_key', 80)->index('aicc_key_idx');
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_consensus_logs')) {
            Schema::create('ai_consensus_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->nullable()->constrained('ai_consensus_runs')->nullOnDelete();
                $table->string('level', 30)->default('info')->index('aicl_level_idx');
                $table->string('event', 120)->index('aicl_event_idx');
                $table->text('message')->nullable();
                $table->json('context')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_consensus_logs');
        Schema::dropIfExists('ai_consensus_contexts');
        Schema::dropIfExists('ai_consensus_outputs');
        Schema::dropIfExists('ai_consensus_provider_responses');
        Schema::dropIfExists('ai_consensus_messages');
        Schema::dropIfExists('ai_consensus_runs');
        Schema::dropIfExists('ai_consensus_providers');
        Schema::dropIfExists('ai_consensus_templates');
    }
};
