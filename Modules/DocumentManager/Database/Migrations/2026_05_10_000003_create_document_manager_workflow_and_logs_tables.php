<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_workflow_states')) {
            Schema::create('document_workflow_states', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_wfs_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->string('from_state')->nullable();
                $table->string('to_state')->index('dm_wfs_to_idx');
                $table->text('reason')->nullable();
                $table->json('context')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'created_at'], 'dm_wfs_doc_time_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_wfs_doc_fk');
            });
        }

        if (!Schema::hasTable('document_workflow_approvals')) {
            Schema::create('document_workflow_approvals', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_apr_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->string('approval_type')->default('review')->index('dm_apr_type_idx');
                $table->string('status')->default('pending')->index('dm_apr_status_idx');
                $table->unsignedBigInteger('requested_by')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamp('due_at')->nullable()->index('dm_apr_due_idx');
                $table->timestamp('completed_at')->nullable();
                $table->text('comment')->nullable();
                $table->json('context')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['document_id', 'status'], 'dm_apr_doc_status_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_apr_doc_fk');
            });
        }

        if (!Schema::hasTable('document_workflow_tasks')) {
            Schema::create('document_workflow_tasks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_task_uuid_uq');
                $table->unsignedBigInteger('document_id')->nullable();
                $table->string('task_type')->default('manual')->index('dm_task_type_idx');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('open')->index('dm_task_status_idx');
                $table->string('priority')->default('normal')->index('dm_task_pri_idx');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('due_at')->nullable()->index('dm_task_due_idx');
                $table->timestamp('completed_at')->nullable();
                $table->json('context')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['document_id', 'status'], 'dm_task_doc_status_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_task_doc_fk');
            });
        }

        if (!Schema::hasTable('document_logs_activity')) {
            Schema::create('document_logs_activity', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_act_uuid_uq');
                $table->unsignedBigInteger('document_id')->nullable();
                $table->string('event')->index('dm_act_event_idx');
                $table->string('actor_type')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->string('user_agent')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'created_at'], 'dm_act_doc_time_idx');
                $table->index(['actor_type', 'actor_id'], 'dm_act_actor_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_act_doc_fk');
            });
        }

        if (!Schema::hasTable('document_logs_access')) {
            Schema::create('document_logs_access', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_acc_uuid_uq');
                $table->unsignedBigInteger('document_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('access_type')->index('dm_acc_type_idx');
                $table->string('result')->default('allowed')->index('dm_acc_result_idx');
                $table->string('ip_address', 64)->nullable();
                $table->string('user_agent')->nullable();
                $table->json('context')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'created_at'], 'dm_acc_doc_time_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_acc_doc_fk');
            });
        }

        if (!Schema::hasTable('document_logs_downloads')) {
            Schema::create('document_logs_downloads', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_dwl_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->unsignedBigInteger('version_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('channel')->default('web')->index('dm_dwl_channel_idx');
                $table->string('ip_address', 64)->nullable();
                $table->string('user_agent')->nullable();
                $table->json('context')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'created_at'], 'dm_dwl_doc_time_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_dwl_doc_fk');
                $this->foreignIfExists($table, 'version_id', 'document_core_versions', 'dm_dwl_ver_fk');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'document_logs_downloads',
            'document_logs_access',
            'document_logs_activity',
            'document_workflow_tasks',
            'document_workflow_approvals',
            'document_workflow_states',
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
