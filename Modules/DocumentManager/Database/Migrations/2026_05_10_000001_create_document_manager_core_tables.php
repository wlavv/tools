<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_core_workspaces')) {
            Schema::create('document_core_workspaces', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_wsp_uuid_uq');
                $table->string('name');
                $table->string('slug')->unique('dm_wsp_slug_uq');
                $table->string('type')->default('operational')->index('dm_wsp_type_idx');
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->json('rules')->nullable();
                $table->json('automation_config')->nullable();
                $table->boolean('is_active')->default(true)->index('dm_wsp_active_idx');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('document_core_folders')) {
            Schema::create('document_core_folders', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_fld_uuid_uq');
                $table->unsignedBigInteger('workspace_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('path')->nullable();
                $table->unsignedInteger('depth')->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['workspace_id', 'parent_id'], 'dm_fld_tree_idx');
                $table->index(['workspace_id', 'slug'], 'dm_fld_slug_idx');

                $this->foreignIfExists($table, 'workspace_id', 'document_core_workspaces', 'dm_fld_wsp_fk');
                $this->foreignIfExists($table, 'parent_id', 'document_core_folders', 'dm_fld_parent_fk');
            });
        }

        if (!Schema::hasTable('document_core_categories')) {
            Schema::create('document_core_categories', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_cat_uuid_uq');
                $table->unsignedBigInteger('workspace_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('color', 24)->nullable();
                $table->string('icon')->nullable();
                $table->json('rules')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['workspace_id', 'slug'], 'dm_cat_slug_idx');

                $this->foreignIfExists($table, 'workspace_id', 'document_core_workspaces', 'dm_cat_wsp_fk');
                $this->foreignIfExists($table, 'parent_id', 'document_core_categories', 'dm_cat_parent_fk');
            });
        }

        if (!Schema::hasTable('document_core_tags')) {
            Schema::create('document_core_tags', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_tag_uuid_uq');
                $table->string('name');
                $table->string('slug')->unique('dm_tag_slug_uq');
                $table->string('type')->default('manual')->index('dm_tag_type_idx');
                $table->string('color', 24)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('document_core_documents')) {
            Schema::create('document_core_documents', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_doc_uuid_uq');
                $table->unsignedBigInteger('workspace_id')->nullable();
                $table->unsignedBigInteger('folder_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('current_version_id')->nullable();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->string('document_type')->nullable()->index('dm_doc_type_idx');
                $table->string('status')->default('draft')->index('dm_doc_status_idx');
                $table->string('workflow_state')->default('draft')->index('dm_doc_wf_idx');
                $table->string('visibility')->default('private')->index('dm_doc_vis_idx');
                $table->string('owner_type')->nullable();
                $table->unsignedBigInteger('owner_id')->nullable();
                $table->string('source_module')->nullable()->index('dm_doc_module_idx');
                $table->string('source_context')->nullable();
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('mime_type')->nullable()->index('dm_doc_mime_idx');
                $table->string('extension', 24)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->string('checksum_algorithm', 20)->default('sha256');
                $table->string('checksum', 128)->nullable()->index('dm_doc_checksum_idx');
                $table->boolean('has_file')->default(false);
                $table->boolean('has_preview')->default(false)->index('dm_doc_preview_idx');
                $table->boolean('has_ocr')->default(false)->index('dm_doc_ocr_idx');
                $table->boolean('has_embeddings')->default(false)->index('dm_doc_emb_idx');
                $table->boolean('is_immutable')->default(false);
                $table->boolean('is_locked')->default(false);
                $table->boolean('legal_hold')->default(false)->index('dm_doc_hold_idx');
                $table->timestamp('expires_at')->nullable()->index('dm_doc_exp_idx');
                $table->timestamp('retention_until')->nullable()->index('dm_doc_ret_idx');
                $table->timestamp('published_at')->nullable();
                $table->json('security_flags')->nullable();
                $table->json('metadata')->nullable();
                $table->longText('search_text')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['workspace_id', 'folder_id'], 'dm_doc_folder_idx');
                $table->index(['source_type', 'source_id'], 'dm_doc_source_idx');
                $table->index(['owner_type', 'owner_id'], 'dm_doc_owner_idx');
                $table->index(['created_at', 'status'], 'dm_doc_created_idx');

                $this->foreignIfExists($table, 'workspace_id', 'document_core_workspaces', 'dm_doc_wsp_fk');
                $this->foreignIfExists($table, 'folder_id', 'document_core_folders', 'dm_doc_fld_fk');
                $this->foreignIfExists($table, 'category_id', 'document_core_categories', 'dm_doc_cat_fk');
            });
        }

        if (!Schema::hasTable('document_core_versions')) {
            Schema::create('document_core_versions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_ver_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->unsignedInteger('version_number')->default(1);
                $table->string('label')->nullable();
                $table->string('disk')->default('local');
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('extension', 24)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->string('checksum_algorithm', 20)->default('sha256');
                $table->string('checksum', 128)->nullable();
                $table->string('processing_status')->default('uploaded')->index('dm_ver_proc_idx');
                $table->json('processing_trace')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['document_id', 'version_number'], 'dm_ver_doc_num_uq');
                $table->index(['document_id', 'processing_status'], 'dm_ver_doc_proc_idx');
                $table->index(['checksum'], 'dm_ver_checksum_idx');

                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_ver_doc_fk');
            });
        }

        if (!Schema::hasTable('document_core_document_tags')) {
            Schema::create('document_core_document_tags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('document_id');
                $table->unsignedBigInteger('tag_id');
                $table->string('source')->default('manual')->index('dm_dtag_src_idx');
                $table->decimal('confidence', 5, 4)->nullable();
                $table->timestamps();

                $table->unique(['document_id', 'tag_id'], 'dm_dtag_doc_tag_uq');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_dtag_doc_fk');
                $this->foreignIfExists($table, 'tag_id', 'document_core_tags', 'dm_dtag_tag_fk');
            });
        }

        if (!Schema::hasTable('document_core_metadata')) {
            Schema::create('document_core_metadata', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('document_id');
                $table->string('key');
                $table->longText('value')->nullable();
                $table->string('value_type')->default('string');
                $table->string('source')->default('manual')->index('dm_meta_src_idx');
                $table->boolean('is_searchable')->default(true);
                $table->timestamps();

                $table->unique(['document_id', 'key'], 'dm_meta_doc_key_uq');
                $table->index(['key', 'value_type'], 'dm_meta_key_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_meta_doc_fk');
            });
        }

        if (!Schema::hasTable('document_core_relations')) {
            Schema::create('document_core_relations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_rel_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->string('relation_type')->index('dm_rel_type_idx');
                $table->string('related_type');
                $table->unsignedBigInteger('related_id')->nullable();
                $table->unsignedBigInteger('related_document_id')->nullable();
                $table->string('source')->default('manual')->index('dm_rel_source_idx');
                $table->decimal('confidence', 5, 4)->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['document_id', 'relation_type'], 'dm_rel_doc_type_idx');
                $table->index(['related_type', 'related_id'], 'dm_rel_target_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_rel_doc_fk');
                $this->foreignIfExists($table, 'related_document_id', 'document_core_documents', 'dm_rel_rdoc_fk');
            });
        }

        if (!Schema::hasTable('document_core_permissions')) {
            Schema::create('document_core_permissions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_perm_uuid_uq');
                $table->unsignedBigInteger('document_id')->nullable();
                $table->unsignedBigInteger('folder_id')->nullable();
                $table->unsignedBigInteger('workspace_id')->nullable();
                $table->string('principal_type');
                $table->unsignedBigInteger('principal_id')->nullable();
                $table->string('permission');
                $table->string('effect')->default('allow');
                $table->timestamp('expires_at')->nullable();
                $table->json('conditions')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['document_id', 'principal_type', 'principal_id'], 'dm_perm_doc_pr_idx');
                $table->index(['workspace_id', 'permission'], 'dm_perm_wsp_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_perm_doc_fk');
                $this->foreignIfExists($table, 'folder_id', 'document_core_folders', 'dm_perm_fld_fk');
                $this->foreignIfExists($table, 'workspace_id', 'document_core_workspaces', 'dm_perm_wsp_fk');
            });
        }

        if (!Schema::hasTable('document_core_shares')) {
            Schema::create('document_core_shares', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique('dm_share_uuid_uq');
                $table->unsignedBigInteger('document_id');
                $table->string('token', 128)->unique('dm_share_token_uq');
                $table->string('share_type')->default('link')->index('dm_share_type_idx');
                $table->string('recipient_email')->nullable();
                $table->string('password_hash')->nullable();
                $table->boolean('can_download')->default(false);
                $table->unsignedInteger('max_accesses')->nullable();
                $table->unsignedInteger('access_count')->default(0);
                $table->timestamp('expires_at')->nullable()->index('dm_share_exp_idx');
                $table->timestamp('revoked_at')->nullable();
                $table->json('permissions')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['document_id', 'expires_at'], 'dm_share_doc_exp_idx');
                $this->foreignIfExists($table, 'document_id', 'document_core_documents', 'dm_share_doc_fk');
            });
        }

        if (Schema::hasTable('document_core_documents') && Schema::hasTable('document_core_versions')) {
            try {
                Schema::table('document_core_documents', function (Blueprint $table) {
                    $this->foreignIfExists($table, 'current_version_id', 'document_core_versions', 'dm_doc_cver_fk', 'id', false);
                });
            } catch (\Throwable $e) {
                // Keep migration tolerant in partially installed environments.
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'document_core_shares',
            'document_core_permissions',
            'document_core_relations',
            'document_core_metadata',
            'document_core_document_tags',
            'document_core_versions',
            'document_core_documents',
            'document_core_tags',
            'document_core_categories',
            'document_core_folders',
            'document_core_workspaces',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function foreignIfExists(Blueprint $table, string $column, string $foreignTable, string $name, string $foreignColumn = 'id', bool $cascade = true): void
    {
        if (!Schema::hasTable($foreignTable)) {
            return;
        }

        $foreign = $table->foreign($column, $name)->references($foreignColumn)->on($foreignTable);

        if ($cascade) {
            $foreign->cascadeOnDelete();
        } else {
            $foreign->nullOnDelete();
        }
    }
};
