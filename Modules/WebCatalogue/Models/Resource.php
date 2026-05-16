<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $table = 'wc_resources';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'raw_payload' => 'array',
        'vr_scene_config' => 'array',
        'ar_scene_config' => 'array',
        'is_default' => 'boolean',
        'is_featured' => 'boolean',
        'is_main' => 'boolean',
        'show_prices' => 'boolean',
        'show_promotions' => 'boolean',
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function store(){return $this->belongsTo(Store::class, 'id_store');}
    public function product(){return $this->belongsTo(Product::class, 'id_product');}
    public function catalogue(){return $this->belongsTo(Catalogue::class, 'id_catalogue');}
    public function fingerprints(){return $this->hasMany(ResourceFingerprint::class, 'id_resource');}

    public function getResolvedUrlAttribute(): ?string
    {
        if (!empty($this->public_url)) {
            return $this->public_url;
        }

        if (!empty($this->file_path)) {
            return asset('storage/' . ltrim($this->file_path, '/'));
        }

        if (!empty($this->source_url)) {
            return $this->source_url;
        }

        return null;
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/') || in_array($this->resource_type, ['image', 'gallery_image', 'thumbnail', 'cover', 'logo', 'favicon', 'environment_background'], true);
    }

    public function getIconAttribute(): string
    {
        return match ($this->resource_type) {
            'image', 'gallery_image', 'thumbnail', 'cover', 'logo', 'favicon', 'environment_background' => 'fa-solid fa-image',
            'video' => 'fa-solid fa-video',
            'audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track' => 'fa-solid fa-volume-high',
            'model_3d' => 'fa-solid fa-cube',
            'ar_file' => 'fa-solid fa-vr-cardboard',
            'vr_file', 'vr_scene' => 'fa-solid fa-headset',
            'manual', 'datasheet', 'assembly_instructions', 'download' => 'fa-solid fa-file-lines',
            'external_link' => 'fa-solid fa-link',
            default => 'fa-solid fa-paperclip',
        };
    }

}
