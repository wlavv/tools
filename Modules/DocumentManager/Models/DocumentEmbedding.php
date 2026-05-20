<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentEmbedding extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_ai_embeddings';
    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
