<?php

namespace Modules\Notifications\Services\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationProviderConfig;
use Modules\Notifications\Models\NotificationRecipient;

class SmsDriver extends AbstractDriver
{
    public function send(Notification $notification, NotificationRecipient $recipient, array $channelPayload = []): array
    {
        if (!$recipient->phone) {
            return ['provider' => 'sms', 'status' => 'failed', 'error_message' => 'Recipient phone missing.'];
        }

        $provider = NotificationProviderConfig::query()->where('channel', 'sms')->where('enabled', true)->first();
        $providerName = data_get($channelPayload, 'provider') ?: $provider?->provider ?: 'generic_webhook';

        if ($providerName === 'twilio') {
            $settings = array_merge((array)($provider?->settings ?? []), $channelPayload);
            $sid = data_get($settings, 'account_sid');
            $token = data_get($settings, 'auth_token');
            $from = data_get($settings, 'from');
            if (!$sid || !$token || !$from) {
                return ['provider' => 'twilio', 'status' => 'failed', 'error_message' => 'Twilio SMS credentials missing.'];
            }
            $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/Messages.json';
            $response = Http::asForm()->withBasicAuth($sid, $token)->post($url, [
                'To' => $recipient->phone,
                'From' => $from,
                'Body' => $channelPayload['message'] ?? ($notification->title . ' - ' . $notification->message),
            ]);
            return ['provider' => 'twilio', 'status' => $response->successful() ? 'sent' : 'failed', 'external_id' => $response->json('sid'), 'response_code' => $response->status(), 'response_body' => $response->json()];
        }

        $webhook = data_get($channelPayload, 'webhook_url') ?: data_get($provider?->settings, 'webhook_url');
        if (!$webhook) {
            return ['provider' => 'generic_webhook', 'status' => 'failed', 'error_message' => 'SMS webhook not configured.'];
        }

        $response = Http::withHeaders(array_filter([
            'Authorization' => data_get($channelPayload, 'authorization') ?: data_get($provider?->settings, 'authorization'),
        ]))->post($webhook, [
            'to' => $recipient->phone,
            'message' => $channelPayload['message'] ?? $notification->message,
            'title' => $notification->title,
        ]);

        return ['provider' => 'generic_webhook', 'status' => $response->successful() ? 'sent' : 'failed', 'response_code' => $response->status()];
    }
}
