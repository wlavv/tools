<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_store_pagespeed_insights')) {
            return;
        }

        Schema::table('catalog_store_pagespeed_insights', function (Blueprint $table) {
            if (!Schema::hasColumn('catalog_store_pagespeed_insights', 'accessibility_score')) {
                $table->unsignedSmallInteger('accessibility_score')->nullable()->after('performance_score');
            }

            if (!Schema::hasColumn('catalog_store_pagespeed_insights', 'best_practices_score')) {
                $table->unsignedSmallInteger('best_practices_score')->nullable()->after('accessibility_score');
            }

            if (!Schema::hasColumn('catalog_store_pagespeed_insights', 'seo_score')) {
                $table->unsignedSmallInteger('seo_score')->nullable()->after('best_practices_score');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('catalog_store_pagespeed_insights')) {
            return;
        }

        Schema::table('catalog_store_pagespeed_insights', function (Blueprint $table) {
            foreach (['seo_score', 'best_practices_score', 'accessibility_score'] as $column) {
                if (Schema::hasColumn('catalog_store_pagespeed_insights', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
