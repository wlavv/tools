<?php

namespace Modules\ERP\Models;

use Illuminate\Database\Eloquent\Model;

class ERPStatus extends Model
{
    protected $table = 'erp_statuses';

    protected $guarded = [];

    protected $casts = [

        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'is_initial' => 'boolean',
        'is_final' => 'boolean',
        'requires_supplier' => 'boolean',
        'affects_stock' => 'boolean',
        'affects_prices' => 'boolean',
        'is_financial' => 'boolean',
        'reset_yearly' => 'boolean',
        'free_shipping' => 'boolean',
        'is_required' => 'boolean',
        'min_amount' => 'decimal:4',
        'max_amount' => 'decimal:4',
        'discount_percent' => 'decimal:4',
    ];
}
