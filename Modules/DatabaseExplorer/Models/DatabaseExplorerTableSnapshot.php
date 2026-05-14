<?php

namespace Modules\DatabaseExplorer\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseExplorerTableSnapshot extends Model
{
    protected $table = 'database_explorer_table_snapshots';

    protected $guarded = [];

    protected $casts = [
        'estimated_rows' => 'integer',
        'total_size_bytes' => 'integer',
        'data_size_bytes' => 'integer',
        'index_size_bytes' => 'integer',
        'column_count' => 'integer',
        'index_count' => 'integer',
        'foreign_key_count' => 'integer',
        'has_primary_key' => 'boolean',
        'last_analyzed_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'health_score' => 'integer',
    ];

    public function snapshot()
    {
        return $this->belongsTo(DatabaseExplorerSnapshot::class, 'snapshot_id');
    }
}
