<?php

namespace Modules\DatabaseExplorer\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseExplorerFinding extends Model
{
    protected $table = 'database_explorer_findings';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function snapshot()
    {
        return $this->belongsTo(DatabaseExplorerSnapshot::class, 'snapshot_id');
    }
}
