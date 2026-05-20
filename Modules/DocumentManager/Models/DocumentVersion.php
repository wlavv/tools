<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentVersion extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_core_versions';
    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'processing_trace' => 'array',
        'metadata' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
