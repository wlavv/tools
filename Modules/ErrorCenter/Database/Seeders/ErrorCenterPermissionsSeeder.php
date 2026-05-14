<?php

namespace Modules\ErrorCenter\Database\Seeders;

use Illuminate\Database\Seeder;

class ErrorCenterPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (! class_exists(\Spatie\Permission\Models\Permission::class)) {
            $this->command?->warn('spatie/laravel-permission not found. Skipping Error Center permissions.');
            return;
        }

        foreach (['error_center.view', 'error_center.manage'] as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission);
        }
    }
}
