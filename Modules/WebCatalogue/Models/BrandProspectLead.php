<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class BrandProspectLead extends Model
{
    protected $table = 'wc_brand_prospect_leads';
    protected $guarded = [];
    protected $casts = [
        'metadata' => 'array',
        'last_requested_at' => 'datetime',
    ];
}
