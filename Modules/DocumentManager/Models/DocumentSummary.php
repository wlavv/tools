<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentSummary extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_ai_summaries';
    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'keywords' => 'array',
        'entities' => 'array',
    ];
}
