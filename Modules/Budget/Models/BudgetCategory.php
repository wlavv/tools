<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $table = 'wt_budget_categories';
    protected $fillable = ['name'];
    public $timestamps = false;
}
