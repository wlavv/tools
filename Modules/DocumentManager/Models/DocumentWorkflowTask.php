<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentWorkflowTask extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_workflow_tasks';
    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
