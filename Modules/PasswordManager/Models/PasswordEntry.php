<?php

namespace Modules\PasswordManager\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordEntry extends Model
{
    protected $table = 'pm_password_entries';

    protected $guarded = [];

    protected $casts = [
        'is_favorite' => 'boolean',
        'last_used_at' => 'datetime',
    ];
}
