<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tcg_mtg_card_ability')) {
            return;
        }

        Schema::create('tcg_mtg_card_ability', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('ability_id');
            $table->string('source')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['card_id', 'ability_id'], 'tcg_mtg_card_ability_unique');
            $table->index('card_id', 'tcg_mtg_card_ability_card_idx');
            $table->index('ability_id', 'tcg_mtg_card_ability_ability_idx');

            $table->foreign('ability_id', 'tcg_mtg_card_ability_ability_fk')
                ->references('id')
                ->on('tcg_mtg_abilities')
                ->cascadeOnDelete();

            // Keep card_id indexed. Link this to the canonical MTG cards table later once the card schema is normalized.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcg_mtg_card_ability');
    }
};
