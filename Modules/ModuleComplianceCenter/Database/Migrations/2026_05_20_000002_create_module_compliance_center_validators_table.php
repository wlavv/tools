<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_compliance_center_validators', function (Blueprint $table) {
            $table->id();
            $table->string('validator_key')->unique();
            $table->string('name');
            $table->string('module_name');
            $table->string('service_class', 500);
            $table->string('status')->default('unavailable');
            $table->boolean('is_available')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_required')->default(false);
            $table->decimal('weight', 6, 2)->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status', 'mcc_validators_status_idx');
            $table->index('is_enabled', 'mcc_validators_enabled_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_compliance_center_validators');
    }
};
