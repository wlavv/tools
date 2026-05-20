<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceFingerprintProfile extends Model
{
    protected $table = 'wc_resource_fingerprint_profiles';

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'profile_json' => 'array',
    ];

    public function fingerprint(){return $this->belongsTo(ResourceFingerprint::class, 'id_fingerprint');}
    public function resource(){return $this->belongsTo(Resource::class, 'id_resource');}
}
