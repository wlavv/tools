<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentCategory extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_core_categories';
    protected $guarded = [];

    protected $casts = [
        'rules' => 'array',
    ];
}
