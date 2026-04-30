<?php

namespace Modules\Notifications\Services;

use App\Models\User;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Notifications\Jobs\SendNotificationChannelJob;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationChannelLog;
use Modules\Notifications\Models\NotificationRecipient;
use Modules\Notifications\Services\Drivers\DiscordDriver;
use Modules\Notifications\Services\Drivers\EmailDriver;
use Modules\Notifications\Services\Drivers\InternalDriver;
use Modules\Notifications\Services\Drivers\SmsDriver;
use Modules\Notifications\Services\Drivers\WebhookDriver;
use Modules\Notifications\Services\Drivers\WhatsappDriver;

class NotificationManager
{
    protected array $drivers = [];

    public function __construct(protected Container $app)
    {
        $this->drivers = [
            'internal' => $app->make(InternalDriver::class),
            'email' => $app->make(EmailDriver::class),
            'whatsapp' => $app->make(WhatsappDriver::class),
            'discord' => $app->make(DiscordDriver::class),
            'sms' => $app->make(SmsDriver::class),
            'webhook' => $app->make(WebhookDriver::class),
        ];
    }

    public function send(array $payload): Notification
    {
        return DB::transaction(function () use ($payload) {
            $notification = Notification::create([
                'uuid' => (string) Str::uuid(),
                'title' => $payload['title'] ?? 'Notification',
                'message' => $payload['message'] ?? '',
                'type' => $payload['type'] ?? 'info',
                'category' => $payload['category'] ?? 'general',
                'priority' => $payload['priority'] ?? 'normal',
                'status' => 'queued',
                'icon' => $payload['icon'] ?? null,
                'action_label' => $payload['action_label'] ?? null,
                'action_url' => $payload['action_url'] ?? null,
                'source_module' => $payload['source_module'] ?? null,
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'created_by' => $payload['created_by'] ?? auth()->id(),
                'meta' => $payload['meta'] ?? [],
                'scheduled_at' => isset($payload['scheduled_at']) ? Carbon::parse($payload['scheduled_at']) : null,
                'expires_at' => isset($payload['expires_at']) ? Carbon::parse($payload['expires_at']) : null,
            ]);

            $channels = $this->normalizeChannels($payload['channels'] ?? config('notifications.default_channels', ['internal']));
            $recipients = $this->normalizeRecipients($payload);

            foreach ($recipients as $recipientData) {
                $recipient = NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'user_id' => $recipientData['user_id'] ?? null,
                    'name' => $recipientData['name'] ?? null,
                    'email' => $recipientData['email'] ?? null,
                    'phone' => $recipientData['phone'] ?? null,
                    'discord_webhook_url' => $recipientData['discord_webhook_url'] ?? null,
                    'delivery_channels' => $channels,
                    'delivery_meta' => $recipientData['meta'] ?? [],
                ]);

                foreach ($channels as $channel) {
                    $channelPayload = (array) ($payload[$channel] ?? []);
                    if ($this->mustQueueChannel($channel, $payload)) {
                        SendNotificationChannelJob::dispatch($notification->id, $recipient->id, $channel, $channelPayload);
                        $this->logQueuedChannel($notification, $recipient, $channel, $channelPayload);
                    } else {
                        $this->deliverChannel($notification, $recipient, $channel, $channelPayload);
                    }
                }
            }

            $notification->update(['status' => 'processing']);

            return $notification->fresh(['recipients', 'logs']);
        });
    }

    public function deliverStoredChannel(int $notificationId, int $recipientId, string $channel, array $channelPayload = []): void
    {
        $notification = Notification::findOrFail($notificationId);
        $recipient = NotificationRecipient::findOrFail($recipientId);
        $this->deliverChannel($notification, $recipient, $channel, $channelPayload);

        if (!NotificationChannelLog::query()->where('notification_id', $notificationId)->where('status', 'queued')->exists()) {
            $notification->update(['status' => 'processed']);
        }
    }

    public function sendToUser(int $userId, array $payload): Notification
    {
        $payload['recipients'] = array_merge($payload['recipients'] ?? [], [['user_id' => $userId]]);
        return $this->send($payload);
    }

    public function sendTaskAssigned(array $task, array $recipient, array $channels = ['internal']): Notification
    {
        return $this->send([
            'title' => 'Nova tarefa atribuída',
            'message' => 'Foi atribuída a tarefa: ' . ($task['title'] ?? 'Tarefa'),
            'type' => 'info',
            'category' => 'tasks',
            'priority' => 'normal',
            'source_module' => 'tasks',
            'reference_type' => 'task',
            'reference_id' => $task['id'] ?? null,
            'channels' => $channels,
            'recipients' => [$recipient],
            'action_url' => $task['url'] ?? null,
            'action_label' => 'Ver tarefa',
        ]);
    }

    public function sendCalendarReminder(array $event, array $recipient, array $channels = ['internal']): Notification
    {
        return $this->send([
            'title' => 'Lembrete de calendário',
            'message' => 'Evento: ' . ($event['title'] ?? 'Evento') . ' em ' . ($event['date'] ?? 'breve'),
            'type' => 'warning',
            'category' => 'calendar',
            'priority' => 'high',
            'source_module' => 'calendar',
            'reference_type' => 'calendar_event',
            'reference_id' => $event['id'] ?? null,
            'channels' => $channels,
            'recipients' => [$recipient],
            'action_url' => $event['url'] ?? null,
            'action_label' => 'Ver evento',
        ]);
    }

    protected function mustQueueChannel(string $channel, array $payload): bool
    {
        if (array_key_exists('queue', $payload)) {
            return (bool) $payload['queue'];
        }

        return in_array($channel, config('notifications.external_channels', []), true);
    }

    public function deliverChannel(Notification $notification, NotificationRecipient $recipient, string $channel, array $channelPayload = []): void
    {
        $driver = $this->drivers[$channel] ?? null;

        if (!$driver) {
            $this->logChannelResult($notification, $recipient, $channel, [
                'status' => 'failed',
                'error_message' => 'Driver not registered for channel [' . $channel . '].',
            ], $channelPayload);
            $this->refreshNotificationStatus($notification);
            return;
        }

        try {
            $this->touchQueuedLogToProcessing($notification, $recipient, $channel);
            $result = $driver->send($notification, $recipient, $channelPayload);
            $this->logChannelResult($notification, $recipient, $channel, $result, $channelPayload);
        } catch (\Throwable $e) {
            $this->logChannelResult($notification, $recipient, $channel, [
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ], $channelPayload);
        }

        $this->refreshNotificationStatus($notification);
    }

    protected function refreshNotificationStatus(Notification $notification): void
    {
        $statuses = NotificationChannelLog::query()
            ->where('notification_id', $notification->id)
            ->pluck('status')
            ->filter()
            ->values()
            ->all();

        if (empty($statuses)) {
            $notification->update(['status' => 'queued']);
            return;
        }

        if (in_array('queued', $statuses, true) || in_array('processing', $statuses, true)) {
            $notification->update(['status' => 'processing']);
            return;
        }

        if (collect($statuses)->contains(fn ($status) => in_array($status, ['sent', 'stored'], true))) {
            $notification->update(['status' => 'processed']);
            return;
        }

        $notification->update(['status' => 'failed']);
    }

    protected function logQueuedChannel(Notification $notification, NotificationRecipient $recipient, string $channel, array $requestPayload = []): void
    {
        NotificationChannelLog::create([
            'notification_id' => $notification->id,
            'recipient_id' => $recipient->id,
            'channel' => $channel,
            'provider' => null,
            'status' => 'queued',
            'request_payload' => $requestPayload,
        ]);
    }

    protected function touchQueuedLogToProcessing(Notification $notification, NotificationRecipient $recipient, string $channel): void
    {
        NotificationChannelLog::query()
            ->where('notification_id', $notification->id)
            ->where('recipient_id', $recipient->id)
            ->where('channel', $channel)
            ->where('status', 'queued')
            ->latest('id')
            ->limit(1)
            ->update(['status' => 'processing']);
    }

    protected function logChannelResult(Notification $notification, NotificationRecipient $recipient, string $channel, array $result, array $requestPayload = []): void
    {
        NotificationChannelLog::create([
            'notification_id' => $notification->id,
            'recipient_id' => $recipient->id,
            'channel' => $channel,
            'provider' => $result['provider'] ?? null,
            'status' => $result['status'] ?? 'unknown',
            'external_id' => $result['external_id'] ?? null,
            'request_payload' => $requestPayload,
            'response_payload' => Arr::except($result, ['status', 'provider', 'external_id', 'error_message']),
            'error_message' => $result['error_message'] ?? null,
            'sent_at' => in_array($result['status'] ?? null, ['sent', 'stored']) ? now() : null,
        ]);
    }

    protected function normalizeChannels(array|string|null $channels): array
    {
        $channels = is_array($channels) ? $channels : [$channels ?: 'internal'];
        $channels = array_values(array_unique(array_filter($channels)));
        $supported = config('notifications.supported_channels', ['internal']);
        return array_values(array_filter($channels, fn ($channel) => in_array($channel, $supported, true)));
    }

    protected function normalizeRecipients(array $payload): array
    {
        if (!empty($payload['recipients']) && is_array($payload['recipients'])) {
            return $this->enrichRecipients($payload['recipients']);
        }

        $recipients = [];

        foreach (($payload['users'] ?? []) as $userId) {
            $recipients[] = ['user_id' => (int) $userId];
        }

        foreach (($payload['emails'] ?? []) as $email) {
            $recipients[] = ['email' => $email];
        }

        foreach (($payload['phones'] ?? []) as $phone) {
            $recipients[] = ['phone' => $phone];
        }

        if (empty($recipients)) {
            $recipients[] = [
                'user_id' => auth()->id(),
                'name' => auth()->user()->name ?? null,
                'email' => auth()->user()->email ?? null,
            ];
        }

        return $this->enrichRecipients($recipients);
    }

    protected function enrichRecipients(array $recipients): array
    {
        $userIds = collect($recipients)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $users = $userIds->isNotEmpty()
            ? User::query()->whereIn('id', $userIds)->get()->keyBy('id')
            : collect();

        return collect($recipients)
            ->map(function (array $recipient) use ($users) {
                $userId = isset($recipient['user_id']) ? (int) $recipient['user_id'] : null;
                $user = $userId ? $users->get($userId) : null;

                if ($user) {
                    $recipient['name'] = $recipient['name'] ?? $user->name ?? null;
                    $recipient['email'] = $recipient['email'] ?? $user->email ?? null;
                }

                return $recipient;
            })
            ->unique(function (array $recipient) {
                return implode('|', [
                    $recipient['user_id'] ?? '',
                    strtolower((string) ($recipient['email'] ?? '')),
                    (string) ($recipient['phone'] ?? ''),
                ]);
            })
            ->values()
            ->all();
    }

}
