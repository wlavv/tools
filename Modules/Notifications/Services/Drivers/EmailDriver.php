<?php

namespace Modules\Notifications\Services\Drivers;

use Illuminate\Support\Facades\Mail;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationRecipient;

class EmailDriver extends AbstractDriver
{
    public function send(Notification $notification, NotificationRecipient $recipient, array $channelPayload = []): array
    {
        if (!$recipient->email) {
            return ['provider' => 'laravel_mail', 'status' => 'failed', 'error_message' => 'Recipient email missing.'];
        }

        Mail::raw($channelPayload['body'] ?? $notification->message, function ($message) use ($recipient, $channelPayload, $notification) {
            $message->to($recipient->email, $recipient->name)->subject($channelPayload['subject'] ?? $notification->title);
        });

        return ['provider' => 'laravel_mail', 'status' => 'sent'];
    }
}
