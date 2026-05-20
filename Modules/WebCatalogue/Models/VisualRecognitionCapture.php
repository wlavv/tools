<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VisualRecognitionCapture extends Model
{
    protected $table = 'wc_visual_recognition_captures';
    protected $guarded = [];

    protected $fillable = [];
    protected $casts = ['metadata' => 'array'];

    public function session(){ return $this->belongsTo(VisualRecognitionSession::class, 'id_session'); }
    public function store(){ return $this->belongsTo(Store::class, 'id_store'); }
    public function product(){ return $this->belongsTo(Product::class, 'id_product'); }

    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->public_url) return $this->public_url;
        if ($this->file_path) return Storage::disk('public')->url($this->file_path);
        return null;
    }
}
