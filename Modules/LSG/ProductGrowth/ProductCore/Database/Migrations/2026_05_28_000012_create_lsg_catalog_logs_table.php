<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('loggable');
            $table->string('event', 120)->index();
            $table->string('severity', 40)->default('info')->index();
            $table->string('title', 180);
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_logs'); }
};
