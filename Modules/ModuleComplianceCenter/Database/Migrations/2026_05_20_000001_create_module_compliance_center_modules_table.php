<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_compliance_center_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->string('module_slug')->unique();
            $table->string('module_path', 500);
            $table->string('module_version')->nullable();
            $table->text('module_description')->nullable();
            $table->json('manifest_payload')->nullable();
            $table->unsignedBigInteger('last_run_id')->nullable();
            $table->string('last_status')->nullable();
            $table->decimal('last_score', 5, 2)->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('module_name', 'mcc_modules_name_idx');
            $table->index('last_status', 'mcc_modules_last_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_compliance_center_modules');
    }
};
