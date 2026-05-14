<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('data_export_report_templates')) {
            if (! Schema::hasIndex('data_export_report_templates', 'dert_profile_scope_idx')) {
                Schema::table('data_export_report_templates', function (Blueprint $table) {
                    $table->index(['profile_key', 'scope_type', 'scope_key'], 'dert_profile_scope_idx');
                });
            }

            if (! Schema::hasIndex('data_export_report_templates', 'dert_scope_default_idx')) {
                Schema::table('data_export_report_templates', function (Blueprint $table) {
                    $table->index(['scope_type', 'scope_key', 'is_default'], 'dert_scope_default_idx');
                });
            }

            return;
        }

        Schema::create('data_export_report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('profile_key')->nullable()->index();
            $table->string('name');
            $table->string('scope_type')->default('global')->index();
            $table->string('scope_key')->nullable()->index();
            $table->boolean('is_default')->default(false)->index();
            $table->string('engine')->default('blade');
            $table->string('title_template')->nullable();
            $table->longText('header_html')->nullable();
            $table->longText('footer_html')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('css')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['profile_key', 'scope_type', 'scope_key'], 'dert_profile_scope_idx');
            $table->index(['scope_type', 'scope_key', 'is_default'], 'dert_scope_default_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_export_report_templates');
    }
};
