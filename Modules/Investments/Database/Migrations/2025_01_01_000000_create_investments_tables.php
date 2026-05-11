<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wt_investments_broker_accounts')) {
            Schema::create('wt_investments_broker_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('broker')->default('ibkr');
                $table->string('name');
                $table->string('external_account_id')->nullable();
                $table->string('currency', 10)->default('EUR');
                $table->boolean('is_demo')->default(true);
                $table->decimal('balance', 18, 2)->default(0);
                $table->json('settings')->nullable();
                $table->string('connection_status')->nullable();
                $table->timestamp('last_sync_at')->nullable();
                $table->text('connection_error')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('wt_investments_broker_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('wt_investments_broker_accounts', 'connection_status')) {
                    $table->string('connection_status')->nullable()->after('settings');
                }
                if (!Schema::hasColumn('wt_investments_broker_accounts', 'last_sync_at')) {
                    $table->timestamp('last_sync_at')->nullable()->after('connection_status');
                }
                if (!Schema::hasColumn('wt_investments_broker_accounts', 'connection_error')) {
                    $table->text('connection_error')->nullable()->after('last_sync_at');
                }
            });
        }

        if (!Schema::hasTable('wt_investments_assets')) {
            Schema::create('wt_investments_assets', function (Blueprint $table) {
                $table->id();
                $table->string('symbol');
                $table->string('name');
                $table->string('broker')->default('ibkr');
                $table->string('external_instrument_id')->nullable();
                $table->string('type')->default('stock');
                $table->string('exchange')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique(['symbol', 'broker', 'exchange'], 'assets_symbol_broker_exchange_unique');
            });
        }

        if (!Schema::hasTable('wt_investments_positions')) {
            Schema::create('wt_investments_positions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('broker_account_id')->constrained('wt_investments_broker_accounts')->cascadeOnDelete();
                $table->foreignId('asset_id')->constrained('wt_investments_assets')->cascadeOnDelete();
                $table->enum('side', ['long', 'short'])->default('long');
                $table->decimal('quantity', 18, 4);
                $table->decimal('entry_price', 18, 4);
                $table->decimal('current_price', 18, 4)->nullable();
                $table->decimal('initial_stop_loss', 18, 4);
                $table->decimal('initial_stop_earn', 18, 4);
                $table->decimal('current_stop_loss', 18, 4);
                $table->decimal('current_stop_earn', 18, 4);
                $table->decimal('step_value', 18, 4);
                $table->boolean('auto_manage')->default(true);
                $table->string('status')->default('open');
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->decimal('closed_price', 18, 4)->nullable();
                $table->decimal('pnl', 18, 2)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wt_investments_stop_levels')) {
            Schema::create('wt_investments_stop_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('position_id')->constrained('wt_investments_positions')->cascadeOnDelete();
                $table->unsignedInteger('step_index');
                $table->decimal('stop_loss', 18, 4);
                $table->decimal('stop_earn', 18, 4);
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();

                $table->unique(['position_id', 'step_index']);
            });
        }

        if (!Schema::hasTable('wt_investments_position_events')) {
            Schema::create('wt_investments_position_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('position_id')->constrained('wt_investments_positions')->cascadeOnDelete();
                $table->string('type');
                $table->decimal('price', 18, 4)->nullable();
                $table->json('data')->nullable();
                $table->timestamp('event_time')->useCurrent();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_investments_position_events');
        Schema::dropIfExists('wt_investments_stop_levels');
        Schema::dropIfExists('wt_investments_positions');
        Schema::dropIfExists('wt_investments_assets');
        Schema::dropIfExists('wt_investments_broker_accounts');
    }
};
