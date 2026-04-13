<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('calendar_contexts')) {
            Schema::create('calendar_contexts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('color', 20)->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('calendar_categories')) {
            Schema::create('calendar_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('context_id')->nullable();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('color', 20)->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(1);
                $table->timestamps();

                $table->foreign('context_id')->references('id')->on('calendar_contexts')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('calendar_events')) {
            Schema::create('calendar_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('context_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->dateTime('start_at');
                $table->dateTime('end_at')->nullable();
                $table->boolean('all_day')->default(0);
                $table->string('status', 50)->default('active');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('context_id')->references('id')->on('calendar_contexts')->nullOnDelete();
                $table->foreign('category_id')->references('id')->on('calendar_categories')->nullOnDelete();

                $table->index(['context_id', 'start_at']);
                $table->index(['category_id', 'start_at']);
            });
        }

        if (Schema::hasTable('calendar_contexts')) {
            $defaults = [
                ['name' => 'Family', 'slug' => 'family', 'color' => '#4f46e5', 'icon' => 'fa-solid fa-house', 'sort_order' => 10, 'is_active' => 1],
                ['name' => 'Family Tasks', 'slug' => 'family-tasks', 'color' => '#0ea5e9', 'icon' => 'fa-solid fa-list-check', 'sort_order' => 20, 'is_active' => 1],
                ['name' => 'Professional', 'slug' => 'professional', 'color' => '#f59e0b', 'icon' => 'fa-solid fa-briefcase', 'sort_order' => 30, 'is_active' => 1],
                ['name' => 'Personal', 'slug' => 'personal', 'color' => '#10b981', 'icon' => 'fa-solid fa-user', 'sort_order' => 40, 'is_active' => 1],
                ['name' => 'School', 'slug' => 'school', 'color' => '#8b5cf6', 'icon' => 'fa-solid fa-school', 'sort_order' => 50, 'is_active' => 1],
                ['name' => 'Thesis', 'slug' => 'thesis', 'color' => '#ef4444', 'icon' => 'fa-solid fa-graduation-cap', 'sort_order' => 60, 'is_active' => 1],
                ['name' => 'General', 'slug' => 'general', 'color' => '#6b7280', 'icon' => 'fa-solid fa-calendar', 'sort_order' => 70, 'is_active' => 1],
            ];

            foreach ($defaults as $row) {
                $exists = DB::table('calendar_contexts')->where('slug', $row['slug'])->exists();

                if (! $exists) {
                    DB::table('calendar_contexts')->insert(array_merge($row, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calendar_events')) {
            Schema::dropIfExists('calendar_events');
        }

        if (Schema::hasTable('calendar_categories')) {
            Schema::dropIfExists('calendar_categories');
        }

        if (Schema::hasTable('calendar_contexts')) {
            Schema::dropIfExists('calendar_contexts');
        }
    }
};
