<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_investments_assets')) return;

        Schema::create('wt_investments_assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');        // AAPL, SPY, EUR.USD, etc.
            $table->string('name');
            $table->string('broker')->default('ibkr');
            $table->string('external_instrument_id')->nullable(); // conid / id IBKR
            $table->string('type')->default('stock'); // stock, etf, forex, crypto, etc.
            $table->string('exchange')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['symbol', 'broker', 'exchange'], 'assets_symbol_broker_exchange_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_investments_assets');
    }
};
