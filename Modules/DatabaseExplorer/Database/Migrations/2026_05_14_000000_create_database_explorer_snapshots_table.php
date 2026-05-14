<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_explorer_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('database_name')->nullable();
            $table->string('engine', 100)->nullable();
            $table->text('engine_version')->nullable();
            $table->unsignedInteger('schema_count')->default(0);
            $table->unsignedInteger('table_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('index_count')->default(0);
            $table->unsignedBigInteger('total_size_bytes')->default(0);
            $table->unsignedBigInteger('estimated_rows')->default(0);
            $table->unsignedSmallInteger('health_score')->default(0);
            $table->string('health_status', 50)->nullable();
            $table->timestamps();

            $table->index(['database_name', 'created_at'], 'dbexp_snap_database_created_idx');
            $table->index(['health_status', 'created_at'], 'dbexp_snap_health_created_idx');
        });

        Schema::create('database_explorer_table_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('database_explorer_snapshots')->cascadeOnDelete();
            $table->string('schema_name');
            $table->string('table_name');
            $table->string('table_type', 100)->nullable();
            $table->unsignedBigInteger('estimated_rows')->default(0);
            $table->unsignedBigInteger('total_size_bytes')->default(0);
            $table->unsignedBigInteger('data_size_bytes')->default(0);
            $table->unsignedBigInteger('index_size_bytes')->default(0);
            $table->unsignedInteger('column_count')->default(0);
            $table->unsignedInteger('index_count')->default(0);
            $table->unsignedInteger('foreign_key_count')->default(0);
            $table->boolean('has_primary_key')->default(false);
            $table->timestamp('last_analyzed_at')->nullable();
            $table->timestamp('last_maintenance_at')->nullable();
            $table->unsignedSmallInteger('health_score')->default(0);
            $table->string('health_status', 50)->nullable();
            $table->timestamps();

            $table->index(['snapshot_id', 'schema_name', 'table_name'], 'dbexp_table_snap_snapshot_schema_table_idx');
            $table->index(['schema_name', 'table_name'], 'dbexp_table_snap_schema_table_idx');
            $table->index(['health_status', 'health_score'], 'dbexp_table_snap_health_idx');
        });

        Schema::create('database_explorer_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->nullable()->constrained('database_explorer_snapshots')->nullOnDelete();
            $table->string('schema_name')->nullable();
            $table->string('table_name')->nullable();
            $table->string('column_name')->nullable();
            $table->string('index_name')->nullable();
            $table->string('severity', 50);
            $table->string('code', 100);
            $table->text('message');
            $table->text('recommendation')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['severity', 'code'], 'dbexp_findings_severity_code_idx');
            $table->index(['schema_name', 'table_name'], 'dbexp_findings_schema_table_idx');
            $table->index(['snapshot_id', 'severity'], 'dbexp_findings_snapshot_severity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_explorer_findings');
        Schema::dropIfExists('database_explorer_table_snapshots');
        Schema::dropIfExists('database_explorer_snapshots');
    }
};
