<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentWorkflowState extends Model
{
    use UsesDocumentUuid;

    protected $table = 'document_workflow_states';
    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'context' => 'array',
    ];
}
