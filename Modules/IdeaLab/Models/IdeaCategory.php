<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaCategory extends Model
{
    protected $table = 'idealab_categories';
    protected $guarded = ['id'];
    protected $casts = [];

}
