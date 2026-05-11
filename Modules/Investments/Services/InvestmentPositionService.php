<?php

namespace Modules\Investments\Services;

use Illuminate\Support\Carbon;
use Modules\Investments\Models\Position;

class InvestmentPositionService
{
    public function create(array $data): Position
    {
        $position = Position::create([
            ...$data,
            'current_price' => $data['entry_price'],
            'current_stop_loss' => $data['initial_stop_loss'],
            'current_stop_earn' => $data['initial_stop_earn'],
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->recordStopLevel($position, 0);
        $this->recordEvent($position, 'opened', (float) $data['entry_price'], [
            'side' => $data['side'],
            'quantity' => $data['quantity'],
        ]);

        return $position;
    }

    public function simulatePrice(Position $position, float $currentPrice): Position
    {
        if (!$position->isOpen()) {
            return $position;
        }

        $position->current_price = $currentPrice;
        $this->recordEvent($position, 'price_update', $currentPrice);

        if ($position->auto_manage) {
            $this->advanceStops($position, $currentPrice);
        }

        $position->save();

        return $position->refresh();
    }

    public function close(Position $position, ?float $price = null): Position
    {
        if (!$position->isOpen()) {
            return $position;
        }

        $closedPrice = $price ?? (float) ($position->current_price ?: $position->entry_price);
        $direction = $position->side === 'short' ? -1 : 1;
        $pnl = ($closedPrice - (float) $position->entry_price) * (float) $position->quantity * $direction;

        $position->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_price' => $closedPrice,
            'pnl' => $pnl,
        ]);

        $this->recordEvent($position, 'manual_close', $closedPrice, ['pnl' => $pnl]);

        return $position->refresh();
    }

    protected function advanceStops(Position $position, float $currentPrice): void
    {
        $entry = (float) $position->entry_price;
        $step = max((float) $position->step_value, 0.0001);
        $direction = $position->side === 'short' ? -1 : 1;
        $movement = ($currentPrice - $entry) * $direction;

        if ($movement < $step) {
            return;
        }

        $stepIndex = (int) floor($movement / $step);
        $latestStep = (int) ($position->stopLevels()->max('step_index') ?? 0);

        if ($stepIndex <= $latestStep) {
            return;
        }

        $lossDistance = abs((float) $position->entry_price - (float) $position->initial_stop_loss);
        $earnDistance = abs((float) $position->initial_stop_earn - (float) $position->entry_price);
        $anchor = $entry + ($step * $stepIndex * $direction);

        $position->current_stop_loss = $anchor - ($lossDistance * $direction);
        $position->current_stop_earn = $anchor + ($earnDistance * $direction);

        $this->recordStopLevel($position, $stepIndex);
        $this->recordEvent($position, 'step_moved', $currentPrice, [
            'step_index' => $stepIndex,
            'stop_loss' => $position->current_stop_loss,
            'stop_earn' => $position->current_stop_earn,
        ]);
    }

    protected function recordStopLevel(Position $position, int $stepIndex): void
    {
        $position->stopLevels()->updateOrCreate(
            ['step_index' => $stepIndex],
            [
                'stop_loss' => $position->current_stop_loss,
                'stop_earn' => $position->current_stop_earn,
                'activated_at' => Carbon::now(),
            ]
        );
    }

    protected function recordEvent(Position $position, string $type, ?float $price = null, array $data = []): void
    {
        $position->events()->create([
            'type' => $type,
            'price' => $price,
            'data' => $data ?: null,
            'event_time' => now(),
        ]);
    }
}
