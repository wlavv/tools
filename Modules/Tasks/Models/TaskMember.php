<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskMember extends Model
{
    use HasFactory;

    protected $table = 'wt_task_members';

    protected $fillable = [
        'name',
        'slug',
        'task_type',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'task_type' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'member_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public static function bootstrapDefaults(): void
    {
        if (static::query()->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'Márcia', 'slug' => 'marcia', 'task_type' => 1, 'color' => '#0d6efd', 'sort_order' => 10, 'is_active' => 1],
            ['name' => 'Bruno',  'slug' => 'bruno',  'task_type' => 1, 'color' => '#198754', 'sort_order' => 20, 'is_active' => 1],
            ['name' => 'Inês',   'slug' => 'ines',   'task_type' => 2, 'color' => '#6f42c1', 'sort_order' => 30, 'is_active' => 1],
            ['name' => 'Eva',    'slug' => 'eva',    'task_type' => 2, 'color' => '#fd7e14', 'sort_order' => 40, 'is_active' => 1],
        ];

        foreach ($defaults as $row) {
            static::query()->create($row);
        }
    }
}
