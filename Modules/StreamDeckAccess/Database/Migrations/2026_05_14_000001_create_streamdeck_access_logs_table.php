<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->tableExists()) {
            $this->ensureIndexes();
            return;
        }

        try {
            Schema::create('streamdeck_access_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('request_id')->unique();
                $table->foreignId('streamdeck_access_point_id')
                    ->nullable()
                    ->constrained('streamdeck_access_points')
                    ->nullOnDelete();
                $table->string('status', 40)->default('received');
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('ip', 64)->nullable();
                $table->string('user_agent', 512)->nullable();
                $table->string('referer', 2048)->nullable();
                $table->json('payload_snapshot')->nullable();
                $table->json('response')->nullable();
                $table->text('error')->nullable();
                $table->unsignedInteger('response_ms')->nullable();
                $table->timestamps();

                $table->index(['streamdeck_access_point_id', 'created_at'], 'sda_logs_point_created_idx');
                $table->index(['status', 'created_at'], 'sda_logs_status_created_idx');
            });
        } catch (QueryException $exception) {
            if (!$this->tableExists()) {
                throw $exception;
            }

            $this->ensureIndexes();
        }
    }

    private function tableExists(): bool
    {
        return !empty(DB::select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1',
            [DB::getDatabaseName(), 'streamdeck_access_logs']
        ));
    }

    private function ensureIndexes(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM streamdeck_access_logs'))
            ->pluck('Key_name')
            ->all();

        Schema::table('streamdeck_access_logs', function (Blueprint $table) use ($indexes) {
            if (!in_array('sda_logs_point_created_idx', $indexes, true)) {
                $table->index(['streamdeck_access_point_id', 'created_at'], 'sda_logs_point_created_idx');
            }

            if (!in_array('sda_logs_status_created_idx', $indexes, true)) {
                $table->index(['status', 'created_at'], 'sda_logs_status_created_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streamdeck_access_logs');
    }
};
