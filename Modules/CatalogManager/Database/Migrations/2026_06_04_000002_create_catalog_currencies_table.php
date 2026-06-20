<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_currencies')) {
            Schema::create('catalog_currencies', function (Blueprint $table): void {
                $table->id();
                $table->string('iso_code', 3)->unique();
                $table->string('name');
                $table->string('symbol', 8)->nullable();
                $table->decimal('conversion_rate_to_eur', 18, 8)->default(1);
                $table->boolean('active')->default(true);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }

        $now = now();
        $defaults = [
            ['iso_code' => 'EUR', 'name' => 'Euro', 'symbol' => 'EUR', 'conversion_rate_to_eur' => 1, 'position' => 1],
            ['iso_code' => 'USD', 'name' => 'US Dollar', 'symbol' => 'USD', 'conversion_rate_to_eur' => 1, 'position' => 2],
            ['iso_code' => 'GBP', 'name' => 'Pound Sterling', 'symbol' => 'GBP', 'conversion_rate_to_eur' => 1, 'position' => 3],
            ['iso_code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'conversion_rate_to_eur' => 1, 'position' => 4],
            ['iso_code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => 'JPY', 'conversion_rate_to_eur' => 1, 'position' => 5],
        ];

        foreach ($defaults as $currency) {
            DB::table('catalog_currencies')->updateOrInsert(
                ['iso_code' => $currency['iso_code']],
                array_merge($currency, ['active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_currencies');
    }
};
