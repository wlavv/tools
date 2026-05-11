<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentActivityLog extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_logs_activity';
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];
}
