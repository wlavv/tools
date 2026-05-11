<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentWorkspace extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_core_workspaces';
    protected $guarded = [];

    protected $casts = [
        'rules' => 'array',
        'automation_config' => 'array',
        'is_active' => 'boolean',
    ];
}
