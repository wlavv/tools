<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaAiTemplate extends Model
{
    protected $table = 'idealab_ai_templates';
    protected $guarded = ['id'];
    protected $casts = ['expected_schema' => 'array', 'supports_chat' => 'boolean', 'is_active' => 'boolean'];

}
