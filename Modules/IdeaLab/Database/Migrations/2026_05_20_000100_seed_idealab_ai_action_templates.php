<?php

use Illuminate\Database\Migrations\Migration;
use Modules\IdeaLab\Database\Seeders\IdeaLabDatabaseSeeder;

return new class extends Migration
{
    public function up(): void
    {
        app(IdeaLabDatabaseSeeder::class)->run();
    }

    public function down(): void
    {
        // Keep action templates because they can be edited in production.
    }
};
