<?php

use Modules\Notifications\Services\NotificationManager;

if (!function_exists('notifications_manager')) {
    function notifications_manager(): NotificationManager
    {
        return app(NotificationManager::class);
    }
}

if (!function_exists('notifications_send')) {
    function notifications_send(array $payload)
    {
        return notifications_manager()->send($payload);
    }
}

if (!function_exists('notifications_send_to_user')) {
    function notifications_send_to_user(int $userId, array $payload)
    {
        return notifications_manager()->sendToUser($userId, $payload);
    }
}

if (!function_exists('notifications_send_to_email')) {
    function notifications_send_to_email(string $email, array $payload)
    {
        $payload['recipients'] = array_merge($payload['recipients'] ?? [], [[
            'email' => $email,
        ]]);

        return notifications_manager()->send($payload);
    }
}

if (!function_exists('notifications_send_to_phone')) {
    function notifications_send_to_phone(string $phone, array $payload)
    {
        $payload['recipients'] = array_merge($payload['recipients'] ?? [], [[
            'phone' => $phone,
        ]]);

        return notifications_manager()->send($payload);
    }
}

if (!function_exists('notifications_task_assigned')) {
    function notifications_task_assigned(array $task, array $recipient, array $channels = ['internal'])
    {
        return notifications_manager()->sendTaskAssigned($task, $recipient, $channels);
    }
}

if (!function_exists('notifications_calendar_reminder')) {
    function notifications_calendar_reminder(array $event, array $recipient, array $channels = ['internal'])
    {
        return notifications_manager()->sendCalendarReminder($event, $recipient, $channels);
    }
}
