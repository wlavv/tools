<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_budget_expense')) {
            return;
        }

        Schema::create('wt_budget_expense', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->string('category')->index();
            $table->string('sub_category')->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('forecast', 12, 2)->default(0);
            $table->unique(['year', 'month', 'category', 'sub_category'], 'wt_budget_expense_period_category_sub_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_budget_expense');
    }
};
