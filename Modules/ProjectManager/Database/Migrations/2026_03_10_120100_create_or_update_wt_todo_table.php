<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'wt_todo';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_project');
                $table->string('title');
                $table->unsignedInteger('priority')->default(0);
                $table->string('status')->default('Pending');
                $table->date('start_date')->nullable();
                $table->unsignedInteger('expected_time')->nullable();
                $table->unsignedBigInteger('id_parent')->default(0);
                $table->text('comment')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            foreach (['created_at', 'updated_at'] as $name) {
                if (!Schema::hasColumn($this->table, $name)) {
                    $table->timestamp($name)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
