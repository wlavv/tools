
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wt_system_logs')) {
            return;
        }

        Schema::create('wt_system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 50);
            $table->text('message');
            $table->json('context')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_system_logs');
    }
};
