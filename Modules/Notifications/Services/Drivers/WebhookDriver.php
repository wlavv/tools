<?php

namespace Modules\Notifications\Services\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationProviderConfig;
use Modules\Notifications\Models\NotificationRecipient;

class WebhookDriver extends AbstractDriver
{
    public function send(Notification $notification, NotificationRecipient $recipient, array $channelPayload = []): array
    {
        $provider = NotificationProviderConfig::query()->where('channel', 'webhook')->where('enabled', true)->first();
        $webhook = data_get($channelPayload, 'webhook_url') ?: data_get($provider?->settings, 'webhook_url');

        if (!$webhook) {
            return ['provider' => 'webhook', 'status' => 'failed', 'error_message' => 'Webhook URL not configured.'];
        }

        $response = Http::withHeaders(array_filter([
            'Authorization' => data_get($channelPayload, 'authorization') ?: data_get($provider?->settings, 'authorization'),
        ]))->post($webhook, [
            'title' => $notification->title,
            'message' => $notification->message,
            'recipient' => [
                'user_id' => $recipient->user_id,
                'name' => $recipient->name,
                'email' => $recipient->email,
                'phone' => $recipient->phone,
            ],
            'notification' => [
                'type' => $notification->type,
                'category' => $notification->category,
                'priority' => $notification->priority,
                'action_url' => $notification->action_url,
            ],
        ]);

        return ['provider' => 'webhook', 'status' => $response->successful() ? 'sent' : 'failed', 'response_code' => $response->status(), 'response_body' => $response->body()];
    }
}
