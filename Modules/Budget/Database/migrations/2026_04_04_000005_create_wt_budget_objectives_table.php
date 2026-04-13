<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_budget_objectives')) {
            return;
        }

        Schema::create('wt_budget_objectives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('priority')->default(1);
            $table->string('category')->nullable()->index();
            $table->string('sub_category')->nullable()->index();
            $table->unsignedTinyInteger('type')->default(1)->index();
            $table->string('link')->nullable();
            $table->boolean('done')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_budget_objectives');
    }
};
