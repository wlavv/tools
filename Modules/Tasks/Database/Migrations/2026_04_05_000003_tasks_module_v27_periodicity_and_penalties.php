<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wt_tasks')) {
            Schema::table('wt_tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('wt_tasks', 'frequency')) {
                    $table->string('frequency', 20)->default('daily')->after('days_mask');
                }
                if (! Schema::hasColumn('wt_tasks', 'monthly_day')) {
                    $table->unsignedTinyInteger('monthly_day')->nullable()->after('frequency');
                }
                if (! Schema::hasColumn('wt_tasks', 'counts_for_completion')) {
                    $table->boolean('counts_for_completion')->default(1)->after('monthly_day');
                }
                if (! Schema::hasColumn('wt_tasks', 'value_mode')) {
                    $table->string('value_mode', 20)->default('add')->after('counts_for_completion');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wt_tasks')) {
            Schema::table('wt_tasks', function (Blueprint $table) {
                foreach (['value_mode', 'counts_for_completion', 'monthly_day', 'frequency'] as $col) {
                    if (Schema::hasColumn('wt_tasks', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
