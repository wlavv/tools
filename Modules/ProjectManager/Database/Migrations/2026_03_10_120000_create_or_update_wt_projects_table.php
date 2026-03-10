<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'wt_projects';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('status')->default('New');
                $table->unsignedInteger('priority')->default(0);
                $table->unsignedBigInteger('id_parent')->default(0);
                $table->string('url', 500)->nullable();
                $table->string('logo', 1000)->nullable();
                $table->text('description')->nullable();
                $table->string('primary_color', 20)->nullable();
                $table->string('secondary_color', 20)->nullable();
                $table->string('accent_color', 20)->nullable();
                $table->string('font_family', 120)->nullable();
                $table->text('brand_notes')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone', 50)->nullable();
                $table->string('website', 500)->nullable();
                $table->string('social_facebook', 500)->nullable();
                $table->string('social_instagram', 500)->nullable();
                $table->string('social_linkedin', 500)->nullable();
                $table->string('social_youtube', 500)->nullable();
                $table->string('repository_url', 500)->nullable();
                $table->string('documentation_url', 500)->nullable();
                $table->text('team_notes')->nullable();
                $table->longText('team_json')->nullable();
                $table->text('structure_notes')->nullable();
                $table->text('documentation_notes')->nullable();
                $table->date('start_date')->nullable();
                $table->date('deadline')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $columns = [
                'slug' => fn () => $table->string('slug')->nullable(),
                'description' => fn () => $table->text('description')->nullable(),
                'primary_color' => fn () => $table->string('primary_color', 20)->nullable(),
                'secondary_color' => fn () => $table->string('secondary_color', 20)->nullable(),
                'accent_color' => fn () => $table->string('accent_color', 20)->nullable(),
                'font_family' => fn () => $table->string('font_family', 120)->nullable(),
                'brand_notes' => fn () => $table->text('brand_notes')->nullable(),
                'contact_name' => fn () => $table->string('contact_name')->nullable(),
                'contact_email' => fn () => $table->string('contact_email')->nullable(),
                'contact_phone' => fn () => $table->string('contact_phone', 50)->nullable(),
                'website' => fn () => $table->string('website', 500)->nullable(),
                'social_facebook' => fn () => $table->string('social_facebook', 500)->nullable(),
                'social_instagram' => fn () => $table->string('social_instagram', 500)->nullable(),
                'social_linkedin' => fn () => $table->string('social_linkedin', 500)->nullable(),
                'social_youtube' => fn () => $table->string('social_youtube', 500)->nullable(),
                'repository_url' => fn () => $table->string('repository_url', 500)->nullable(),
                'documentation_url' => fn () => $table->string('documentation_url', 500)->nullable(),
                'team_notes' => fn () => $table->text('team_notes')->nullable(),
                'team_json' => fn () => $table->longText('team_json')->nullable(),
                'structure_notes' => fn () => $table->text('structure_notes')->nullable(),
                'documentation_notes' => fn () => $table->text('documentation_notes')->nullable(),
                'start_date' => fn () => $table->date('start_date')->nullable(),
                'deadline' => fn () => $table->date('deadline')->nullable(),
                'created_at' => fn () => $table->timestamp('created_at')->nullable(),
                'updated_at' => fn () => $table->timestamp('updated_at')->nullable(),
            ];

            foreach ($columns as $name => $callback) {
                if (!Schema::hasColumn($this->table, $name)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
