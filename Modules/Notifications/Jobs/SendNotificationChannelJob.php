<?php

namespace Modules\Notifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Notifications\Services\NotificationManager;

class SendNotificationChannelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $notificationId,
        public int $recipientId,
        public string $channel,
        public array $channelPayload = []
    ) {
        $this->onQueue(config('notifications.default_queue', 'default'));
    }

    public function handle(NotificationManager $manager): void
    {
        $manager->deliverStoredChannel(
            $this->notificationId,
            $this->recipientId,
            $this->channel,
            $this->channelPayload
        );
    }
}
