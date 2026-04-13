<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_budget_categories')) {
            return;
        }

        Schema::create('wt_budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('type', 20)->index();
            $table->unsignedBigInteger('id_parent')->default(0)->index();
            $table->integer('forecast_year')->index();
            $table->decimal('forecast', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_budget_categories');
    }
};
