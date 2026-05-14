<?php

namespace Modules\StreamDeckAccess\Tasks;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use Modules\StreamDeckAccess\Contracts\StreamDeckTask;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;

class SalesYesterdayReportTask implements StreamDeckTask
{
    public function handle(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log): array
    {
        $command = (string) config('streamdeck-access.commands.sales_yesterday_report');

        if ($command === '') {
            throw new InvalidArgumentException('streamdeck-access.commands.sales_yesterday_report is not configured.');
        }

        $payload = $accessPoint->payload ?? [];
        $date = Carbon::yesterday(config('app.timezone'))->toDateString();
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
}
