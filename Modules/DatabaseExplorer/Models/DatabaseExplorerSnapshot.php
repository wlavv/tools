<?php

namespace Modules\DatabaseExplorer\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseExplorerSnapshot extends Model
{
    protected $table = 'database_explorer_snapshots';

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'schema_count' => 'integer',
        'table_count' => 'integer',
        'view_count' => 'integer',
        'index_count' => 'integer',
        'total_size_bytes' => 'integer',
        'estimated_rows' => 'integer',
        'health_score' => 'integer',
    ];

    public function tables()
    {
        return $this->hasMany(DatabaseExplorerTableSnapshot::class, 'snapshot_id');
    }

    public function findings()
    {
        return $this->hasMany(DatabaseExplorerFinding::class, 'snapshot_id');
    }
}
