<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wt_investments_broker_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('broker')->default('ibkr'); // 'ibkr', 'ig', etc.
            $table->string('name');
            $table->string('external_account_id')->nullable(); // id na corretora
            $table->string('currency', 10)->default('EUR');
            $table->boolean('is_demo')->default(true);
            $table->decimal('balance', 18, 2)->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_investments_broker_accounts');
    }
};
