<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wc_store_environments')) {
            return;
        }

        Schema::table('wc_store_environments', function (Blueprint $table) {
            if (!Schema::hasColumn('wc_store_environments', 'id_catalogue')) {
                $table->unsignedBigInteger('id_catalogue')->nullable()->after('id_store')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('wc_store_environments') || !Schema::hasColumn('wc_store_environments', 'id_catalogue')) {
            return;
        }

        Schema::table('wc_store_environments', function (Blueprint $table) {
            $table->dropColumn('id_catalogue');
        });
    }
};
