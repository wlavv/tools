<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class VisualRecognitionMatch extends Model
{
    protected $table = 'wc_visual_recognition_matches';
    protected $guarded = [];

    protected $fillable = [];
    protected $casts = ['metadata' => 'array'];

    public function session(){ return $this->belongsTo(VisualRecognitionSession::class, 'id_session'); }
    public function product(){ return $this->belongsTo(Product::class, 'id_product'); }
}
