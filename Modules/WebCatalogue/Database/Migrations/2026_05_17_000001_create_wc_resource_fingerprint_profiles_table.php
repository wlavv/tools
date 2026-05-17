<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wc_resource_fingerprint_profiles')) {
            Schema::create('wc_resource_fingerprint_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_fingerprint')->index();
                $table->unsignedBigInteger('id_resource')->index();
                $table->string('algorithm', 120)->index();
                $table->json('profile_json')->nullable();
                $table->timestamps();

                $table->unique(['id_fingerprint'], 'wc_resource_fp_profiles_fingerprint_unique');
                $table->index(['id_resource', 'algorithm'], 'wc_resource_fp_profiles_resource_algorithm_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_resource_fingerprint_profiles');
    }
};
