<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentMetadata extends Model
{
    protected $table = 'document_core_metadata';
    protected $guarded = [];

    protected $fillable = [];
}
