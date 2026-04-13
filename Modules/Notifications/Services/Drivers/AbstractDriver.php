<?php

namespace Modules\Notifications\Services\Drivers;

use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationRecipient;

abstract class AbstractDriver
{
    abstract public function send(Notification $notification, NotificationRecipient $recipient, array $channelPayload = []): array;
}
