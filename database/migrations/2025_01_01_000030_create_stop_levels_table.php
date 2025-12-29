<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wt_investments_stop_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('step_index');     // 0,1,2,...
            $table->decimal('stop_loss', 18, 4);
            $table->decimal('stop_earn', 18, 4);

            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique(['position_id', 'step_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_investments_stop_levels');
    }
};
