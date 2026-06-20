<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lsg_catalog_core_characteristic_values')) {
            return;
        }

        Schema::create('lsg_catalog_core_characteristic_values', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('characteristic_id');
            $table->string('value', 180);
            $table->string('label', 180);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();

            $table->unique(['characteristic_id', 'value'], 'lsg_char_values_unique');
            $table->index('characteristic_id', 'lsg_char_values_char_idx');

            if (Schema::hasTable('lsg_catalog_core_characteristics')) {
                $table->foreign('characteristic_id', 'lsg_char_values_char_fk')
                    ->references('id')
                    ->on('lsg_catalog_core_characteristics')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lsg_catalog_core_characteristic_values');
    }
};
