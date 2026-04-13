<?php

namespace Modules\Notifications\Services\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationProviderConfig;
use Modules\Notifications\Models\NotificationRecipient;

class DiscordDriver extends AbstractDriver
{
    public function send(Notification $notification, NotificationRecipient $recipient, array $channelPayload = []): array
    {
        $provider = NotificationProviderConfig::query()->where('channel', 'discord')->where('enabled', true)->first();
        $webhook = $recipient->discord_webhook_url ?: data_get($channelPayload, 'webhook_url') ?: data_get($provider?->settings, 'webhook_url');

        if (!$webhook) {
            return ['provider' => 'discord', 'status' => 'failed', 'error_message' => 'Discord webhook not configured.'];
        }

        $response = Http::post($webhook, [
            'content' => $notification->title . "\n" . $notification->message,
        ]);

        return ['provider' => 'discord', 'status' => $response->successful() ? 'sent' : 'failed', 'response_code' => $response->status(), 'response_body' => $response->body()];
    }
}
