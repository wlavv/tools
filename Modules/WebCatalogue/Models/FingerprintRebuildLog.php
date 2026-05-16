<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class FingerprintRebuildLog extends Model
{
    protected $table = 'wc_fingerprint_rebuild_logs';

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'id_store');
    }
}
