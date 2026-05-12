<?php

namespace Modules\ERP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ERPWorkflow extends Model
{
    protected $table = 'erp_workflows';

    protected $guarded = [];

    protected $casts = [
        'conditions' => 'array',
        'requires_confirmation' => 'boolean',
        'requires_permission' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(ERPStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(ERPStatus::class, 'to_status_id');
    }
}
