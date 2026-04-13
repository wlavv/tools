<?php

namespace Modules\Notifications\Services\Drivers;

use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationRecipient;

class InternalDriver extends AbstractDriver
{
    public function send(Notification $notification, NotificationRecipient $recipient, array $channelPayload = []): array
    {
        return [
            'provider' => 'database',
            'status' => 'stored',
        ];
    }
}
