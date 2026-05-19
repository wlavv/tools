<?php

use Illuminate\Database\Migrations\Migration;
use Modules\AIConsensus\Database\Seeders\AIConsensusCentralSeeder;

return new class extends Migration
{
    public function up(): void
    {
        app(AIConsensusCentralSeeder::class)->run();
    }

    public function down(): void
    {
        // Intentionally keep templates/providers. They are configuration data and may be edited in production.
    }
};
