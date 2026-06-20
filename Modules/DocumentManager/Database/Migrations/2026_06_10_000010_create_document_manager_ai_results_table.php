<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_manager_ai_results')) {
            return;
        }

        Schema::create('document_manager_ai_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('version_id')->nullable();
            $table->string('operation', 80)->index();
            $table->string('status', 40)->default('pending')->index();
            $table->string('service', 120)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('language', 40)->nullable();
            $table->boolean('preprocess')->default(true);
            $table->longText('text')->nullable();
            $table->longText('raw_text')->nullable();
            $table->unsignedInteger('text_length')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->boolean('llm_ready')->default(false);
            $table->json('extracted_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->longText('raw_llm_response')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'operation', 'processed_at'], 'dm_ai_result_doc_op_processed_idx');
            $table->index(['document_id', 'status'], 'dm_ai_result_doc_status_idx');

            if (Schema::hasTable('document_core_documents')) {
                $table->foreign('document_id', 'dm_ai_result_doc_fk')
                    ->references('id')
                    ->on('document_core_documents')
                    ->cascadeOnDelete();
            }

            if (Schema::hasTable('document_core_versions')) {
                $table->foreign('version_id', 'dm_ai_result_ver_fk')
                    ->references('id')
                    ->on('document_core_versions')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_manager_ai_results');
    }
};
