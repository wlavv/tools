<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentTag extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_core_tags';
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
