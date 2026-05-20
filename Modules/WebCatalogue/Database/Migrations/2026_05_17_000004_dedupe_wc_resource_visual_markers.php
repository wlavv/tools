<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wc_resource_visual_markers')) {
            return;
        }

        DB::statement("
            DELETE older
            FROM wc_resource_visual_markers older
            INNER JOIN wc_resource_visual_markers newer
                ON older.id_resource = newer.id_resource
                AND older.algorithm = newer.algorithm
                AND older.id < newer.id
        ");

        if (!$this->indexExists()) {
            DB::statement('ALTER TABLE wc_resource_visual_markers ADD UNIQUE wc_resource_visual_markers_resource_algorithm_unique (id_resource, algorithm)');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('wc_resource_visual_markers') || !$this->indexExists()) {
            return;
        }

        DB::statement('ALTER TABLE wc_resource_visual_markers DROP INDEX wc_resource_visual_markers_resource_algorithm_unique');
    }

    private function indexExists(): bool
    {
        $indexes = DB::select('SHOW INDEX FROM wc_resource_visual_markers WHERE Key_name = ?', ['wc_resource_visual_markers_resource_algorithm_unique']);

        return !empty($indexes);
    }
};
