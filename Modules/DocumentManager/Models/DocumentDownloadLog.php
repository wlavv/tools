<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentDownloadLog extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_logs_downloads';
    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
    ];
}
