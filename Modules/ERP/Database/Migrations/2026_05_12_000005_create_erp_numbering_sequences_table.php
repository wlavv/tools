<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_numbering_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type_code', 60)->index();
            $table->string('prefix', 40)->nullable();
            $table->string('pattern', 120);
            $table->unsignedInteger('year')->nullable()->index();
            $table->unsignedBigInteger('current_number')->default(0);
            $table->unsignedInteger('padding')->default(5);
            $table->boolean('reset_yearly')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['document_type_code', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_numbering_sequences');
    }
};
