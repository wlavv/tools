<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentOcr extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_ai_ocr';
    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'structured_blocks' => 'array',
        'raw_response' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
