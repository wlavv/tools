<?php

namespace Modules\AssetLibrary\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLibrary extends Model
{
    protected $table = 'wt_assets';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'description',
        'alt_text',
        'tags',
        'disk',
        'directory',
        'filename',
        'original_filename',
        'extension',
        'mime_type',
        'file_size',
        'file_path',
        'preview_path',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'file_size' => 'integer',
    ];
}
