<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class UnmatchedProductLead extends Model
{
    protected $table = 'wc_unmatched_product_leads';
    protected $guarded = [];
    protected $casts = ['metadata' => 'array'];

    public function session(){ return $this->belongsTo(VisualRecognitionSession::class, 'id_session'); }
    public function store(){ return $this->belongsTo(Store::class, 'id_store'); }
}
