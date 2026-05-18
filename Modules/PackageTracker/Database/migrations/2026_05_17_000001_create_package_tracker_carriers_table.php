<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_tracker_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('driver')->nullable();
            $table->string('api_base_url')->nullable();
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('supports_webhooks')->default(false);
            $table->json('settings')->nullable();
            $table->timestamp('last_health_check_at')->nullable();
            $table->string('last_health_status', 40)->nullable();
            $table->timestamps();
        });

        DB::table('package_tracker_carriers')->insert([
            [
                'code' => 'manual',
                'name' => 'Manual / Generic',
                'driver' => Modules\PackageTracker\Services\Carriers\ManualCarrierClient::class,
                'is_active' => true,
                'supports_webhooks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'mock',
                'name' => 'Mock Carrier',
                'driver' => Modules\PackageTracker\Services\Carriers\MockCarrierClient::class,
                'is_active' => true,
                'supports_webhooks' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tracker_carriers');
    }
};
