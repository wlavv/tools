<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_export_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('type')->default('model');
            $table->string('class_name')->nullable();
            $table->string('module')->nullable()->index();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->longText('query_sql')->nullable();
            $table->json('query_bindings')->nullable();
            $table->json('builder_definition')->nullable();
            $table->string('default_format')->default('csv');
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_export_profiles');
    }
};
