<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureProjects();
        $this->ensureModules();
        $this->ensureRoadmapGroups();
        $this->ensureRoadmapItems();
        $this->ensureTasks();
        $this->ensureTaskDependencies();
        $this->ensureTaskBlocks();
        $this->ensureSprints();
        $this->ensureSprintTasks();
        $this->ensureDocumentation();
        $this->ensureDecisions();
        $this->ensureNotes();
        $this->ensureActivityLogs();
        $this->ensureSectionTables();
    }

    public function down(): void
    {
        // Non-destructive compatibility migration. Project data is intentionally preserved.
    }

    private function ensureProjects(): void
    {
        if (!Schema::hasTable('wt_projects')) {
            Schema::create('wt_projects', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->nullable();
                $table->unsignedInteger('id_parent')->default(0);
                $table->unsignedBigInteger('group_id')->nullable();
                $table->boolean('have_details')->default(false);
                $table->string('name', 180);
                $table->string('status', 50)->default('active');
                $table->integer('priority')->nullable();
                $table->string('url', 500)->nullable();
                $table->string('logo', 500)->nullable();
                $table->string('slogan', 180)->nullable();
                $table->string('theme', 80)->nullable();
                $table->timestamps();
            });
        }

        $this->addColumns('wt_projects', [
            'uuid' => fn (Blueprint $table) => $table->string('uuid', 36)->nullable(),
            'slug' => fn (Blueprint $table) => $table->string('slug', 180)->nullable()->index('wtp_slug_idx'),
            'code' => fn (Blueprint $table) => $table->string('code', 50)->nullable(),
            'project_type' => fn (Blueprint $table) => $table->string('project_type', 80)->default('software'),
            'client_name' => fn (Blueprint $table) => $table->string('client_name', 180)->nullable(),
            'version' => fn (Blueprint $table) => $table->string('version', 50)->nullable(),
            'environment_status' => fn (Blueprint $table) => $table->string('environment_status', 50)->nullable(),
            'description' => fn (Blueprint $table) => $table->longText('description')->nullable(),
            'primary_color' => fn (Blueprint $table) => $table->string('primary_color', 30)->nullable(),
            'secondary_color' => fn (Blueprint $table) => $table->string('secondary_color', 30)->nullable(),
            'accent_color' => fn (Blueprint $table) => $table->string('accent_color', 30)->nullable(),
            'font_family' => fn (Blueprint $table) => $table->string('font_family', 120)->nullable(),
            'brand_notes' => fn (Blueprint $table) => $table->longText('brand_notes')->nullable(),
            'design_status' => fn (Blueprint $table) => $table->string('design_status', 50)->default('draft'),
            'contact_name' => fn (Blueprint $table) => $table->string('contact_name', 180)->nullable(),
            'contact_email' => fn (Blueprint $table) => $table->string('contact_email', 180)->nullable(),
            'contact_phone' => fn (Blueprint $table) => $table->string('contact_phone', 80)->nullable(),
            'website' => fn (Blueprint $table) => $table->string('website', 500)->nullable(),
            'staging_url' => fn (Blueprint $table) => $table->string('staging_url', 500)->nullable(),
            'production_url' => fn (Blueprint $table) => $table->string('production_url', 500)->nullable(),
            'repository_url' => fn (Blueprint $table) => $table->string('repository_url', 500)->nullable(),
            'repository_branch' => fn (Blueprint $table) => $table->string('repository_branch', 120)->nullable(),
            'documentation_url' => fn (Blueprint $table) => $table->string('documentation_url', 500)->nullable(),
            'team_notes' => fn (Blueprint $table) => $table->longText('team_notes')->nullable(),
            'team_json' => fn (Blueprint $table) => $table->json('team_json')->nullable(),
            'structure_notes' => fn (Blueprint $table) => $table->longText('structure_notes')->nullable(),
            'technical_status' => fn (Blueprint $table) => $table->string('technical_status', 50)->default('draft'),
            'documentation_notes' => fn (Blueprint $table) => $table->longText('documentation_notes')->nullable(),
            'start_date' => fn (Blueprint $table) => $table->date('start_date')->nullable(),
            'deadline' => fn (Blueprint $table) => $table->date('deadline')->nullable(),
            'progress_percent' => fn (Blueprint $table) => $table->decimal('progress_percent', 5, 2)->default(0),
            'health_status' => fn (Blueprint $table) => $table->string('health_status', 50)->default('normal'),
            'current_focus' => fn (Blueprint $table) => $table->string('current_focus', 255)->nullable(),
            'next_step' => fn (Blueprint $table) => $table->string('next_step', 255)->nullable(),
            'is_pinned' => fn (Blueprint $table) => $table->boolean('is_pinned')->default(false),
            'archived_at' => fn (Blueprint $table) => $table->timestamp('archived_at')->nullable(),
            'deleted_at' => fn (Blueprint $table) => $table->timestamp('deleted_at')->nullable(),
        ]);
    }

    private function ensureModules(): void
    {
        if (!Schema::hasTable('wt_project_modules')) {
            Schema::create('wt_project_modules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('name', 180);
                $table->string('slug', 180);
                $table->string('namespace', 180)->nullable();
                $table->string('route_prefix', 180)->nullable();
                $table->string('route_name_prefix', 180)->nullable();
                $table->text('description')->nullable();
                $table->longText('technical_notes')->nullable();
                $table->string('status', 50)->default('planned');
                $table->integer('priority')->default(2);
                $table->integer('execution_order')->default(0);
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
                $table->index(['project_id', 'status'], 'wtpm_proj_status_idx');
            });
        }
    }

    private function ensureRoadmapGroups(): void
    {
        if (!Schema::hasTable('wt_project_roadmap_groups')) {
            Schema::create('wt_project_roadmap_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('name', 180);
                $table->text('description')->nullable();
                $table->string('status', 50)->default('active');
                $table->integer('execution_order')->default(0);
                $table->timestamps();
            });
        }

        $this->addColumns('wt_project_roadmap_groups', [
            'parent_id' => fn (Blueprint $table) => $table->unsignedBigInteger('parent_id')->nullable(),
            'target_version' => fn (Blueprint $table) => $table->string('target_version', 80)->nullable(),
            'planned_start_date' => fn (Blueprint $table) => $table->date('planned_start_date')->nullable(),
            'planned_end_date' => fn (Blueprint $table) => $table->date('planned_end_date')->nullable(),
            'progress_percent' => fn (Blueprint $table) => $table->decimal('progress_percent', 5, 2)->default(0),
        ]);
    }

    private function ensureRoadmapItems(): void
    {
        if (!Schema::hasTable('wt_project_roadmap_items')) {
            Schema::create('wt_project_roadmap_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('roadmap_group_id')->nullable();
                $table->unsignedBigInteger('project_module_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('phase', 120)->nullable();
                $table->string('title', 180);
                $table->text('description')->nullable();
                $table->string('status', 50)->default('pending');
                $table->integer('priority')->default(2);
                $table->date('planned_start_date')->nullable();
                $table->date('planned_end_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('depends_on_id')->nullable();
                $table->unsignedBigInteger('depends_on_item_id')->nullable();
                $table->decimal('progress_percent', 5, 2)->default(0);
                $table->integer('execution_order')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index(['project_id', 'status'], 'wtpri_proj_status_idx');
            });
        }

        $this->addColumns('wt_project_roadmap_items', [
            'phase' => fn (Blueprint $table) => $table->string('phase', 120)->nullable(),
            'depends_on_id' => fn (Blueprint $table) => $table->unsignedBigInteger('depends_on_id')->nullable(),
            'depends_on_item_id' => fn (Blueprint $table) => $table->unsignedBigInteger('depends_on_item_id')->nullable(),
        ]);
    }

    private function ensureTasks(): void
    {
        if (!Schema::hasTable('wt_project_tasks')) {
            Schema::create('wt_project_tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('roadmap_group_id')->nullable();
                $table->unsignedBigInteger('project_module_id')->nullable();
                $table->unsignedBigInteger('roadmap_item_id')->nullable();
                $table->unsignedBigInteger('sprint_id')->nullable();
                $table->unsignedBigInteger('owner_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('type', 50)->default('task');
                $table->string('title', 180);
                $table->longText('description')->nullable();
                $table->integer('priority')->default(3);
                $table->string('status', 50)->default('pending');
                $table->string('depends_status', 50)->default('none');
                $table->integer('execution_order')->default(0);
                $table->date('start_date')->nullable();
                $table->date('scheduled_for')->nullable();
                $table->date('deadline')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->integer('expected_time')->nullable();
                $table->integer('actual_time')->nullable();
                $table->decimal('progress_percent', 5, 2)->default(0);
                $table->longText('comment')->nullable();
                $table->longText('review_notes')->nullable();
                $table->longText('acceptance_criteria')->nullable();
                $table->longText('technical_notes')->nullable();
                $table->string('source', 80)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->longText('blocked_reason')->nullable();
                $table->string('block_type', 80)->nullable();
                $table->string('blocked_by', 180)->nullable();
                $table->timestamp('blocked_at')->nullable();
                $table->tinyInteger('importance')->nullable()->default(3);
                $table->tinyInteger('urgency')->nullable()->default(3);
                $table->integer('priority_score')->nullable();
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
                $table->index(['project_id', 'status', 'execution_order'], 'wtpt_proj_stat_ord_idx');
            });
            return;
        }

        $this->addColumns('wt_project_tasks', [
            'project_module_id' => fn (Blueprint $table) => $table->unsignedBigInteger('project_module_id')->nullable(),
            'roadmap_item_id' => fn (Blueprint $table) => $table->unsignedBigInteger('roadmap_item_id')->nullable(),
            'sprint_id' => fn (Blueprint $table) => $table->unsignedBigInteger('sprint_id')->nullable(),
            'owner_id' => fn (Blueprint $table) => $table->unsignedBigInteger('owner_id')->nullable(),
            'depends_status' => fn (Blueprint $table) => $table->string('depends_status', 50)->default('none'),
            'review_notes' => fn (Blueprint $table) => $table->longText('review_notes')->nullable(),
            'acceptance_criteria' => fn (Blueprint $table) => $table->longText('acceptance_criteria')->nullable(),
            'technical_notes' => fn (Blueprint $table) => $table->longText('technical_notes')->nullable(),
            'actual_time' => fn (Blueprint $table) => $table->integer('actual_time')->nullable(),
            'progress_percent' => fn (Blueprint $table) => $table->decimal('progress_percent', 5, 2)->default(0),
            'created_by' => fn (Blueprint $table) => $table->unsignedBigInteger('created_by')->nullable(),
            'updated_by' => fn (Blueprint $table) => $table->unsignedBigInteger('updated_by')->nullable(),
            'block_type' => fn (Blueprint $table) => $table->string('block_type', 80)->nullable(),
            'blocked_by' => fn (Blueprint $table) => $table->string('blocked_by', 180)->nullable(),
            'blocked_at' => fn (Blueprint $table) => $table->timestamp('blocked_at')->nullable(),
            'importance' => fn (Blueprint $table) => $table->tinyInteger('importance')->nullable()->default(3),
            'urgency' => fn (Blueprint $table) => $table->tinyInteger('urgency')->nullable()->default(3),
            'priority_score' => fn (Blueprint $table) => $table->integer('priority_score')->nullable(),
            'deleted_at' => fn (Blueprint $table) => $table->timestamp('deleted_at')->nullable(),
        ]);
    }

    private function ensureTaskDependencies(): void
    {
        if (!Schema::hasTable('wt_project_task_dependencies')) {
            Schema::create('wt_project_task_dependencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id')->nullable();
                $table->unsignedBigInteger('project_task_id')->nullable();
                $table->unsignedBigInteger('depends_on_project_task_id')->nullable();
                $table->unsignedBigInteger('task_id')->nullable();
                $table->unsignedBigInteger('depends_on_task_id')->nullable();
                $table->string('dependency_type', 50)->default('finish_to_start');
                $table->string('status', 50)->default('active');
                $table->string('reason', 255)->nullable();
                $table->longText('notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureTaskBlocks(): void
    {
        if (!Schema::hasTable('wt_project_task_blocks')) {
            Schema::create('wt_project_task_blocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id')->nullable();
                $table->unsignedBigInteger('project_task_id')->nullable();
                $table->string('block_type', 80)->default('other');
                $table->string('title', 180);
                $table->longText('reason')->nullable();
                $table->longText('description')->nullable();
                $table->string('status', 50)->default('open');
                $table->string('blocked_by', 180)->nullable();
                $table->timestamp('blocked_at')->nullable();
                $table->string('resolved_by', 180)->nullable();
                $table->longText('resolved_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureSprints(): void
    {
        if (!Schema::hasTable('wt_project_sprints')) {
            Schema::create('wt_project_sprints', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->string('name', 180);
                $table->longText('description')->nullable();
                $table->string('goal', 255)->nullable();
                $table->string('status', 50)->default('planned');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->longText('review_notes')->nullable();
                $table->longText('retrospective_notes')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureSprintTasks(): void
    {
        if (!Schema::hasTable('wt_project_sprint_tasks')) {
            Schema::create('wt_project_sprint_tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('sprint_id');
                $table->unsignedBigInteger('project_task_id');
                $table->integer('execution_order')->default(0);
                $table->timestamps();
            });
        }
    }

    private function ensureDocumentation(): void
    {
        if (!Schema::hasTable('wt_project_documentation_sections')) {
            Schema::create('wt_project_documentation_sections', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('project_module_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('type', 80)->default('other');
                $table->string('title', 180);
                $table->string('summary', 500)->nullable();
                $table->longText('content')->nullable();
                $table->json('content_json')->nullable();
                $table->string('status', 50)->default('active');
                $table->boolean('is_pinned')->default(false);
                $table->integer('execution_order')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureDecisions(): void
    {
        if (!Schema::hasTable('wt_project_decisions')) {
            Schema::create('wt_project_decisions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('project_module_id')->nullable();
                $table->string('title', 180);
                $table->longText('context')->nullable();
                $table->longText('decision')->nullable();
                $table->longText('reason')->nullable();
                $table->longText('impact')->nullable();
                $table->string('status', 50)->default('accepted');
                $table->unsignedBigInteger('decided_by')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureNotes(): void
    {
        if (!Schema::hasTable('wt_project_notes')) {
            Schema::create('wt_project_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('project_module_id')->nullable();
                $table->unsignedBigInteger('project_task_id')->nullable();
                $table->string('type', 80)->default('general_note');
                $table->string('title', 180);
                $table->longText('content')->nullable();
                $table->string('visibility', 50)->default('internal');
                $table->boolean('is_pinned')->default(false);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureActivityLogs(): void
    {
        if (!Schema::hasTable('wt_project_activity_logs')) {
            Schema::create('wt_project_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_id')->nullable();
                $table->string('entity_type', 80)->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('action', 80)->nullable();
                $table->string('event_type', 80)->nullable();
                $table->string('title', 180)->nullable();
                $table->longText('description')->nullable();
                $table->json('old_values_json')->nullable();
                $table->json('new_values_json')->nullable();
                $table->longText('old_values')->nullable();
                $table->longText('new_values')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name', 180)->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    private function ensureSectionTables(): void
    {
        $this->createProjectChildTable('wt_project_design_profiles', [
            'name' => 'string', 'status' => 'string', 'brand_positioning' => 'longText', 'visual_language' => 'longText',
            'layout_rules' => 'longText', 'component_rules' => 'longText', 'button_rules' => 'longText', 'card_rules' => 'longText',
            'table_rules' => 'longText', 'form_rules' => 'longText', 'icon_rules' => 'longText', 'logo_rules' => 'longText',
            'accessibility_rules' => 'longText', 'notes' => 'longText', 'is_default' => 'boolean',
        ]);
        $this->createProjectChildTable('wt_project_design_tokens', [
            'design_profile_id' => 'unsignedBigInteger', 'group' => 'string', 'token_key' => 'string', 'token_label' => 'string',
            'token_value' => 'longText', 'css_variable' => 'string', 'description' => 'longText', 'usage_notes' => 'longText',
            'execution_order' => 'integer', 'is_active' => 'boolean',
        ]);
        $this->createProjectChildTable('wt_project_assets', [
            'design_profile_id' => 'unsignedBigInteger', 'project_module_id' => 'unsignedBigInteger', 'type' => 'string', 'name' => 'string',
            'variant' => 'string', 'language' => 'string', 'file_path' => 'string', 'public_url' => 'string', 'mime_type' => 'string',
            'file_size' => 'unsignedBigInteger', 'width' => 'integer', 'height' => 'integer', 'description' => 'longText',
            'usage_rules' => 'longText', 'version' => 'string', 'is_primary' => 'boolean', 'execution_order' => 'integer',
        ]);
        $this->createProjectChildTable('wt_project_technical_stack', [
            'project_module_id' => 'unsignedBigInteger', 'category' => 'string', 'name' => 'string', 'version' => 'string',
            'purpose' => 'longText', 'notes' => 'longText', 'documentation_url' => 'string', 'is_required' => 'boolean',
            'execution_order' => 'integer',
        ]);
        $this->createProjectChildTable('wt_project_environments', [
            'name' => 'string', 'type' => 'string', 'url' => 'string', 'repository_branch' => 'string', 'database_name' => 'string',
            'php_version' => 'string', 'node_version' => 'string', 'notes' => 'longText', 'credential_reference' => 'string',
            'is_active' => 'boolean', 'execution_order' => 'integer',
        ]);
        $this->createProjectChildTable('wt_project_guidelines', [
            'project_module_id' => 'unsignedBigInteger', 'category' => 'string', 'title' => 'string', 'content' => 'longText',
            'importance' => 'string', 'status' => 'string', 'execution_order' => 'integer',
        ]);
        $this->createProjectChildTable('wt_project_links', [
            'project_module_id' => 'unsignedBigInteger', 'type' => 'string', 'label' => 'string', 'url' => 'string',
            'description' => 'longText', 'is_primary' => 'boolean', 'execution_order' => 'integer',
        ]);
        $this->createProjectChildTable('wt_project_blocks', [
            'type' => 'string', 'title' => 'string', 'summary' => 'longText', 'content' => 'longText', 'status' => 'string',
            'execution_order' => 'integer', 'is_pinned' => 'boolean',
        ]);
        $this->createProjectChildTable('wt_project_contacts', [
            'name' => 'string', 'role' => 'string', 'company' => 'string', 'email' => 'string', 'phone' => 'string',
            'notes' => 'longText', 'is_primary' => 'boolean', 'execution_order' => 'integer',
        ]);
        $this->createProjectChildTable('wt_project_external_dependencies', [
            'name' => 'string', 'type' => 'string', 'owner' => 'string', 'status' => 'string', 'description' => 'longText',
            'risk_level' => 'string', 'needed_by' => 'date', 'resolved_at' => 'timestamp',
        ]);
    }

    private function createProjectChildTable(string $tableName, array $columns): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($columns) {
                $table->id();
                $table->unsignedInteger('project_id')->nullable();
                foreach ($columns as $column => $type) {
                    $this->addColumnByType($table, $column, $type);
                }
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
            });
            return;
        }

        $this->addColumns($tableName, collect($columns)->mapWithKeys(fn ($type, $column) => [
            $column => fn (Blueprint $table) => $this->addColumnByType($table, $column, $type),
        ])->all());
    }

    private function addColumns(string $tableName, array $columns): void
    {
        foreach ($columns as $column => $definition) {
            if (Schema::hasColumn($tableName, $column)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    private function addColumnByType(Blueprint $table, string $column, string $type): void
    {
        match ($type) {
            'unsignedBigInteger' => $table->unsignedBigInteger($column)->nullable(),
            'integer' => $table->integer($column)->nullable()->default(0),
            'boolean' => $table->boolean($column)->default(false),
            'longText' => $table->longText($column)->nullable(),
            'date' => $table->date($column)->nullable(),
            'timestamp' => $table->timestamp($column)->nullable(),
            default => $table->string($column, 255)->nullable(),
        };
    }
};
