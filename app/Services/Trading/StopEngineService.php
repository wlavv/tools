<?php

namespace App\Services\Trading;

use App\Models\Position;
use App\Models\PositionEvent;

class StopEngineService
{
    public function evaluate(Position $position, float $currentPrice): void
    {
        if (! $position->isOpen() || ! $position->auto_manage) {
            return;
        }

        $originalSl = $position->current_stop_loss;
        $originalSe = $position->current_stop_earn;
        $step       = $position->step_value;

        // 1. Stop Loss atingido → fechar posição
        if ($position->side === 'long' && $currentPrice <= $position->current_stop_loss) {
            $this->closeByStopLoss($position, $currentPrice);
            return;
        }

        // 2. Stop Earnings atingido → mover patamares
        if ($position->side === 'long' && $currentPrice >= $position->current_stop_earn) {
            $this->moveStepUp($position, $currentPrice, $step, $originalSl, $originalSe);
        }

        // Nota: para posições short terias de inverter a lógica
    }

    protected function closeByStopLoss(Position $position, float $price): void
    {
        $position->status       = 'closed';
        $position->closed_at    = now();
        $position->closed_price = $price;
        $position->pnl          = ($price - $position->entry_price) * $position->quantity;
        $position->save();

        PositionEvent::create([
            'position_id' => $position->id,
            'type'        => 'stop_hit',
            'price'       => $price,
            'data'        => ['reason' => 'stop_loss'],
            'event_time'  => now(),
        ]);
    }

    protected function moveStepUp(
        Position $position,
        float $currentPrice,
        float $step,
        float $previousSl,
        float $previousSe
    ): void {
        $newSl = $previousSe - $step;
        $newSe = $previousSe + $step;

        $position->current_stop_loss = $newSl;
        $position->current_stop_earn = $newSe;
        $position->current_price     = $currentPrice;
        $position->save();

        $index = $position->stopLevels()->max('step_index') ?? 0;
        $position->stopLevels()->create([
            'step_index'   => $index + 1,
            'stop_loss'    => $newSl,
            'stop_earn'    => $newSe,
            'activated_at' => now(),
        ]);

        PositionEvent::create([
            'position_id' => $position->id,
            'type'        => 'step_moved',
            'price'       => $currentPrice,
            'data'        => [
                'previous_sl' => $previousSl,
                'previous_se' => $previousSe,
                'new_sl'      => $newSl,
                'new_se'      => $newSe,
            ],
            'event_time'  => now(),
        ]);
    }
}
