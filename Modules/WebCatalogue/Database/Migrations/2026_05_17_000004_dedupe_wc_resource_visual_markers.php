<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $table = 'wc_resource_visual_markers';
    private string $index = 'wc_resource_visual_markers_resource_algorithm_unique';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        DB::statement("
            DELETE older
            FROM {$this->table} older
            INNER JOIN {$this->table} newer
                ON older.id_resource = newer.id_resource
                AND older.algorithm = newer.algorithm
                AND older.id < newer.id
        ");

        if (!$this->indexExists()) {
            DB::statement("ALTER TABLE {$this->table} ADD UNIQUE {$this->index} (id_resource, algorithm)");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table) || !$this->indexExists()) {
            return;
        }

        DB::statement("ALTER TABLE {$this->table} DROP INDEX {$this->index}");
    }

    private function indexExists(): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$this->table} WHERE Key_name = ?", [$this->index]);

        return !empty($indexes);
    }
};
