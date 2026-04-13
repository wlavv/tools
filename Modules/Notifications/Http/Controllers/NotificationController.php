<?php

namespace Modules\Notifications\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationProviderConfig;
use Modules\Notifications\Models\NotificationRecipient;

class NotificationController extends BaseNotificationController
{
    public function index(Request $request)
    {
        $this->addAction(route('notifications.settings'), 'Config', '<i class="fa-solid fa-cog"></i>');
        if (config('notifications.test_route_enabled', true)) {
            $this->addAction(route('notifications.test'), 'Testar', '<i class="fa-solid fa-flask"></i>');
        }

        $userId = auth()->id();

        $notifications = Notification::query()
            ->with(['recipients' => function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', $userId);
                });
            }, 'logs'])
            ->whereHas('recipients', function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', $userId);
                });
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->string('scope')->toString() === 'unread', function ($q) use ($userId) {
                $q->whereHas('recipients', function ($rq) use ($userId) {
                    $rq->where(function ($w) use ($userId) {
                        $w->whereNull('user_id')->orWhere('user_id', $userId);
                    })->whereNull('read_at')->whereNull('dismissed_at');
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return View::make('notifications::index')->with($this->viewData([
            'notifications' => $notifications,
            'categories' => Notification::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
        ]));
    }

    public function show(Notification $notification)
    {
        $this->addBreadcrumb($notification->title);
        $notification->load(['recipients', 'logs']);

        return View::make('notifications::show')->with($this->viewData([
            'notification' => $notification,
        ]));
    }

    public function settings()
    {
        $this->addBreadcrumb('Settings');

        return View::make('notifications::settings.index')->with($this->viewData([
            'configs' => NotificationProviderConfig::query()->orderBy('channel')->orderBy('provider')->get(),
        ]));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'max:50'],
            'provider' => ['required', 'string', 'max:100'],
            'enabled' => ['nullable', 'boolean'],
            'settings_json' => ['nullable', 'string'],
        ]);

        $settings = [];
        if (!empty($validated['settings_json'])) {
            $decoded = json_decode($validated['settings_json'], true);
            if (!is_array($decoded)) {
                return redirect()->back()->withErrors(['settings_json' => 'O JSON é inválido.'])->withInput();
            }
            $settings = $decoded;
        }

        NotificationProviderConfig::updateOrCreate(
            ['channel' => $validated['channel'], 'provider' => $validated['provider']],
            ['enabled' => (bool) ($validated['enabled'] ?? false), 'settings' => $settings]
        );

        return redirect()->route('notifications.settings')->with('success', 'Configuração guardada com sucesso.');
    }

    public function test()
    {
        abort_unless(config('notifications.test_route_enabled', true), 404);

        $this->addBreadcrumb('Testar');

        return View::make('notifications::test')->with($this->viewData([
            'supportedChannels' => config('notifications.supported_channels', ['internal']),
            'configs' => NotificationProviderConfig::query()->orderBy('channel')->orderBy('provider')->get()->groupBy('channel'),
        ]));
    }

    public function sendTest(Request $request): RedirectResponse
    {
        abort_unless(config('notifications.test_route_enabled', true), 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string'],
            'user_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'discord_webhook_url' => ['nullable', 'url'],
            'queue' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'max:20'],
            'priority' => ['nullable', 'string', 'max:20'],
            'category' => ['nullable', 'string', 'max:80'],
            'email_subject' => ['nullable', 'string', 'max:255'],
            'email_body' => ['nullable', 'string'],
            'sms_message' => ['nullable', 'string'],
            'whatsapp_message' => ['nullable', 'string'],
            'discord_message' => ['nullable', 'string'],
            'webhook_url' => ['nullable', 'url'],
            'webhook_method' => ['nullable', 'string', 'max:10'],
            'webhook_headers_json' => ['nullable', 'string'],
            'webhook_payload_json' => ['nullable', 'string'],
        ]);

        $headers = [];
        if (!empty($validated['webhook_headers_json'])) {
            $headers = json_decode($validated['webhook_headers_json'], true);
            if (!is_array($headers)) {
                return back()->withErrors(['webhook_headers_json' => 'JSON inválido para headers.'])->withInput();
            }
        }

        $body = [];
        if (!empty($validated['webhook_payload_json'])) {
            $body = json_decode($validated['webhook_payload_json'], true);
            if (!is_array($body)) {
                return back()->withErrors(['webhook_payload_json' => 'JSON inválido para payload.'])->withInput();
            }
        }

        notifications_send([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'category' => $validated['category'] ?? 'tests',
            'type' => $validated['type'] ?? 'info',
            'priority' => $validated['priority'] ?? 'normal',
            'source_module' => 'notifications',
            'channels' => $validated['channels'],
            'queue' => (bool) ($validated['queue'] ?? false),
            'recipients' => [[
                'user_id' => $validated['user_id'] ?? auth()->id(),
                'name' => $validated['name'] ?? auth()->user()->name ?? null,
                'email' => $validated['email'] ?? auth()->user()->email ?? null,
                'phone' => $validated['phone'] ?? null,
                'discord_webhook_url' => $validated['discord_webhook_url'] ?? null,
            ]],
            'email' => [
                'subject' => $validated['email_subject'] ?? $validated['title'],
                'body' => $validated['email_body'] ?? $validated['message'],
            ],
            'sms' => [
                'message' => $validated['sms_message'] ?? $validated['message'],
            ],
            'whatsapp' => [
                'message' => $validated['whatsapp_message'] ?? $validated['message'],
            ],
            'discord' => [
                'message' => $validated['discord_message'] ?? ($validated['title'] . "\n" . $validated['message']),
            ],
            'webhook' => [
                'url' => $validated['webhook_url'] ?? null,
                'method' => $validated['webhook_method'] ?? 'POST',
                'headers' => $headers,
                'body' => $body,
            ],
        ]);

        return redirect()->route('notifications.test')->with('success', 'Notificação de teste enviada. Verifica os logs do módulo.');
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        $notification->recipients()
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->update(['seen_at' => now(), 'read_at' => now()]);

        return back()->with('success', 'Notificação marcada como lida.');
    }

    public function markAllRead(): RedirectResponse
    {
        NotificationRecipient::query()
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->whereNull('read_at')
            ->whereNull('dismissed_at')
            ->update(['seen_at' => now(), 'read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    public function dismiss(Notification $notification): RedirectResponse
    {
        $notification->recipients()
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->update(['dismissed_at' => now()]);

        return back()->with('success', 'Notificação ocultada.');
    }

    public function dropdownData(): JsonResponse
    {
        $userId = auth()->id();

        $rows = Notification::query()
            ->whereHas('recipients', function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', $userId);
                })->whereNull('dismissed_at');
            })
            ->with(['recipients' => function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', $userId);
                });
            }])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(function ($notification) {
                $recipient = $notification->recipients->first();

                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'url' => route('notifications.show', $notification),
                    'read' => (bool) optional($recipient)->read_at,
                    'created_at' => optional($notification->created_at)->diffForHumans(),
                ];
            });

        $unread = NotificationRecipient::query()
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->whereNull('read_at')
            ->whereNull('dismissed_at')
            ->count();

        return response()->json([
            'unread' => $unread,
            'items' => $rows,
            'index_url' => route('notifications.index'),
        ]);
    }
}
