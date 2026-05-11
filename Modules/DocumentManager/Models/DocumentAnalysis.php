<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentAnalysis extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_ai_analysis';
    protected $guarded = [];

    protected $casts = [
        'classification' => 'array',
        'risk_flags' => 'array',
        'relation_suggestions' => 'array',
        'raw_payload' => 'array',
    ];
}
