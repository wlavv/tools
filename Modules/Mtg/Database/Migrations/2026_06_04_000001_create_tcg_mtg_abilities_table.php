<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tcg_mtg_abilities')) {
            return;
        }

        Schema::create('tcg_mtg_abilities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 60)->index();
            $table->boolean('is_official')->default(true);
            $table->boolean('is_evergreen')->default(false);
            $table->boolean('is_filterable')->default(false)->index();
            $table->boolean('is_commercial_tag')->default(false);
            $table->text('description')->nullable();
            $table->string('source')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcg_mtg_abilities');
    }
};
