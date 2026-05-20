<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentPermission extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_core_permissions';
    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'conditions' => 'array',
        'expires_at' => 'datetime',
    ];
}
