<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_todo')) {
            Schema::create('wt_todo', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_project');
                $table->string('title');
                $table->unsignedInteger('priority')->default(0);
                $table->string('status')->default('Pending');
                $table->timestamps();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('wt_todo'); }
};
