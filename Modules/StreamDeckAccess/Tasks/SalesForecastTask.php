<?php

namespace Modules\StreamDeckAccess\Tasks;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use Modules\StreamDeckAccess\Contracts\StreamDeckTask;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;

class SalesForecastTask implements StreamDeckTask
{
    public function handle(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log): array
    {
        $command = (string) config('streamdeck-access.commands.sales_forecast');

        if ($command === '') {
            throw new InvalidArgumentException('streamdeck-access.commands.sales_forecast is not configured.');
        }

        $payload = $accessPoint->payload ?? [];
        $date = $this->resolveDate((string) Arr::get($payload, 'date', 'today'));
        $arguments = [
            '--date' => $date,
        ];

        if (Arr::has($payload, 'store_id')) {
            $arguments['--store-id'] = Arr::get($payload, 'store_id');
        }

        $exitCode = Artisan::call($command, $arguments);

        return [
            'command' => $command,
            'date' => $date,
            'store_id' => Arr::get($payload, 'store_id'),
            'exit_code' => $exitCode,
            'output' => mb_substr(Artisan::output(), 0, 4000),
            'generated_at' => now()->toISOString(),
        ];
    }

    protected function resolveDate(string $date): string
    {
        return match ($date) {
            'today' => Carbon::today(config('app.timezone'))->toDateString(),
            'tomorrow' => Carbon::tomorrow(config('app.timezone'))->toDateString(),
            default => Carbon::parse($date, config('app.timezone'))->toDateString(),
        };
    }
}
