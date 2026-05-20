<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class Document extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_core_documents';

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'security_flags' => 'array',
        'metadata' => 'array',
        'has_file' => 'boolean',
        'has_preview' => 'boolean',
        'has_ocr' => 'boolean',
        'has_embeddings' => 'boolean',
        'is_immutable' => 'boolean',
        'is_locked' => 'boolean',
        'legal_hold' => 'boolean',
        'expires_at' => 'datetime',
        'retention_until' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class, 'document_id');
    }

    public function currentVersion()
    {
        return $this->belongsTo(DocumentVersion::class, 'current_version_id');
    }

    public function workspace()
    {
        return $this->belongsTo(DocumentWorkspace::class, 'workspace_id');
    }

    public function folder()
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(DocumentTag::class, 'document_core_document_tags', 'document_id', 'tag_id')
            ->withPivot(['source', 'confidence'])
            ->withTimestamps();
    }
}
