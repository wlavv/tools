<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_investments_positions')) return;

        Schema::create('wt_investments_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();

            $table->enum('side', ['long', 'short'])->default('long');
            $table->decimal('quantity', 18, 4);
            $table->decimal('entry_price', 18, 4);
            $table->decimal('current_price', 18, 4)->nullable();

            $table->decimal('initial_stop_loss', 18, 4);
            $table->decimal('initial_stop_earn', 18, 4);

            $table->decimal('current_stop_loss', 18, 4);
            $table->decimal('current_stop_earn', 18, 4);

            $table->decimal('step_value', 18, 4); // step em preço

            $table->boolean('auto_manage')->default(true);

            $table->string('status')->default('open'); // open, closed
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->decimal('closed_price', 18, 4)->nullable();
            $table->decimal('pnl', 18, 2)->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_investments_positions');
    }
};
