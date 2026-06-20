<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lsg_site_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('lsg_sites')->cascadeOnDelete();
            $table->string('integration_type', 80)->index();
            $table->string('name', 160);
            $table->string('status', 40)->default('active')->index();
            $table->json('config')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lsg_site_integrations');
    }
};
