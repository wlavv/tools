<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class VisualRecognitionSession extends Model
{
    protected $table = 'wc_visual_recognition_sessions';
    protected $guarded = [];

    protected $fillable = [];
    protected $casts = [
        'metadata' => 'array',
        'matched_at' => 'datetime',
    ];

    public function store(){ return $this->belongsTo(Store::class, 'id_store'); }
    public function product(){ return $this->belongsTo(Product::class, 'id_product'); }
    public function captures(){ return $this->hasMany(VisualRecognitionCapture::class, 'id_session'); }
    public function matches(){ return $this->hasMany(VisualRecognitionMatch::class, 'id_session'); }
    public function lead(){ return $this->hasOne(UnmatchedProductLead::class, 'id_session'); }
}
