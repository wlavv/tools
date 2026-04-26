<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_projects')) {
            Schema::create('wt_projects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('status')->default('New');
                $table->unsignedInteger('priority')->default(0);
                $table->unsignedBigInteger('id_parent')->default(0);
                $table->string('url',500)->nullable();
                $table->string('website',500)->nullable();
                $table->string('logo',1000)->nullable();
                $table->text('description')->nullable();
                $table->string('repository_url',500)->nullable();
                $table->string('documentation_url',500)->nullable();
                $table->string('staging_url',500)->nullable();
                $table->string('production_url',500)->nullable();
                $table->text('server_notes')->nullable();
                $table->text('deployment_notes')->nullable();
                $table->string('business_model')->nullable();
                $table->string('market_scope')->nullable();
                $table->string('owner_name')->nullable();
                $table->string('owner_email')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone',50)->nullable();
                $table->string('primary_color',20)->nullable();
                $table->string('secondary_color',20)->nullable();
                $table->string('accent_color',20)->nullable();
                $table->string('font_family',120)->nullable();
                $table->text('brand_notes')->nullable();
                $table->longText('team_json')->nullable();
                $table->text('team_notes')->nullable();
                $table->text('structure_notes')->nullable();
                $table->text('documentation_notes')->nullable();
                $table->text('goals')->nullable();
                $table->text('risks')->nullable();
                $table->text('next_steps')->nullable();
                $table->text('budget_notes')->nullable();
                $table->date('start_date')->nullable();
                $table->date('deadline')->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('wt_projects'); }
};
