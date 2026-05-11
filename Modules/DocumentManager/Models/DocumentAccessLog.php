<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentAccessLog extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_logs_access';
    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
    ];
}
