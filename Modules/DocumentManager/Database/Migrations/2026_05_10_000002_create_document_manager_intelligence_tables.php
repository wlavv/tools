<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_ai_ocr')) {
            Schema::create('document_ai_ocr', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_ocr_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->unsignedBigInteger('version_id')->nullable();
                $table->string('provider')->default('stub')->index('dm_ocr_provider_idx');
                $table->string('status')->default('pending')->index('dm_ocr_status_idx');
                $table->decimal('confidence', 5, 4)->nullable();
                $table->longText('extracted_text')->nullable();
                $table->json('structured_blocks')->nullable();
                $table->json('raw_response')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'status'], 'dm_ocr_doc_status_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_ocr_doc_fk');
                $this->foreignIfExists($table, 'version_id', 'document_core_versions', 'dm_ocr_ver_fk');
            });
        }

        if (!Schema::hasTable('document_ai_embeddings')) {
            Schema::create('document_ai_embeddings', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_emb_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->unsignedBigInteger('version_id')->nullable();
                $table->string('provider')->default('stub')->index('dm_emb_provider_idx');
                $table->string('model')->nullable();
                $table->string('vector_store')->nullable()->index('dm_emb_store_idx');
                $table->string('vector_id')->nullable()->index('dm_emb_vector_idx');
                $table->unsignedInteger('dimensions')->nullable();
                $table->longText('embedding_payload')->nullable();
                $table->string('content_hash', 128)->nullable()->index('dm_emb_hash_idx');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'provider'], 'dm_emb_doc_provider_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_emb_doc_fk');
                $this->foreignIfExists($table, 'version_id', 'document_core_versions', 'dm_emb_ver_fk');
            });
        }

        if (!Schema::hasTable('document_ai_summaries')) {
            Schema::create('document_ai_summaries', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_sum_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->string('provider')->default('stub')->index('dm_sum_provider_idx');
                $table->string('model')->nullable();
                $table->string('summary_type')->default('executive')->index('dm_sum_type_idx');
                $table->longText('summary')->nullable();
                $table->json('keywords')->nullable();
                $table->json('entities')->nullable();
                $table->decimal('confidence', 5, 4)->nullable();
                $table->timestamps();

                $table->index(['document_id', 'summary_type'], 'dm_sum_doc_type_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_sum_doc_fk');
            });
        }

        if (!Schema::hasTable('document_ai_analysis')) {
            Schema::create('document_ai_analysis', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_an_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->string('provider')->default('stub')->index('dm_an_provider_idx');
                $table->string('model')->nullable();
                $table->string('analysis_type')->index('dm_an_type_idx');
                $table->string('status')->default('pending')->index('dm_an_status_idx');
                $table->decimal('confidence', 5, 4)->nullable();
                $table->json('classification')->nullable();
                $table->json('risk_flags')->nullable();
                $table->json('relation_suggestions')->nullable();
                $table->json('raw_payload')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'analysis_type'], 'dm_an_doc_type_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_an_doc_fk');
            });
        }

        if (!Schema::hasTable('document_logs_ai')) {
            Schema::create('document_logs_ai', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_ailog_uuid_uq');
                $table->unsignedBigInteger('document_id')->nullable();
                $table->string('provider')->nullable()->index('dm_ailog_provider_idx');
                $table->string('operation')->index('dm_ailog_op_idx');
                $table->string('status')->default('info')->index('dm_ailog_status_idx');
                $table->unsignedInteger('latency_ms')->nullable();
                $table->unsignedInteger('tokens_in')->nullable();
                $table->unsignedInteger('tokens_out')->nullable();
                $table->json('context')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'operation'], 'dm_ailog_doc_op_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_ailog_doc_fk');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'document_logs_ai',
            'document_ai_analysis',
            'document_ai_summaries',
            'document_ai_embeddings',
            'document_ai_ocr',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function foreignIfExists(Blueprint $table, string $column, string $foreignTable, string $name): void
    {
        if (Schema::hasTable($foreignTable)) {
            $table->foreign($column, $name)->references('id')->on($foreignTable)->cascadeOnDelete();
        }
    }
};
