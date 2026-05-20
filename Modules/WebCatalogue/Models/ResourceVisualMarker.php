<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceVisualMarker extends Model
{
    protected $table = 'wc_resource_visual_markers';

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'keypoints_json' => 'array',
        'descriptors_json' => 'array',
        'metadata' => 'array',
    ];

    public function store(){return $this->belongsTo(Store::class, 'id_store');}
    public function product(){return $this->belongsTo(Product::class, 'id_product');}
    public function resource(){return $this->belongsTo(Resource::class, 'id_resource');}
}
