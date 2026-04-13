<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_budget_expense_details')) {
            return;
        }

        Schema::create('wt_budget_expense_details', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index();
            $table->string('detail');
            $table->integer('year')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->decimal('amount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_budget_expense_details');
    }
};
