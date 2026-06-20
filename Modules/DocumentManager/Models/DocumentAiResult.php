<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAiResult extends Model
{
    protected $table = 'document_manager_ai_results';

    protected $guarded = [];

    protected $casts = [
        'preprocess' => 'boolean',
        'llm_ready' => 'boolean',
        'extracted_payload' => 'array',
        'raw_payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function version()
    {
        return $this->belongsTo(DocumentVersion::class, 'version_id');
    }
}
