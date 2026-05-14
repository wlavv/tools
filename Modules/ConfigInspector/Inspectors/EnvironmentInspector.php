<?php

namespace Modules\ConfigInspector\Inspectors;

class EnvironmentInspector extends BaseInspector
{
    public function key(): string { return 'environment'; }
    public function label(): string { return 'Environment'; }

    public function inspect(): array
    {
        $items = [];
        $env = config('app.env');
        $debug = (bool) config('app.debug');

        $items[] = $this->item('success', 'APP_ENV', 'Ambiente atual: ' . $env, ['APP_ENV' => $env]);
        $items[] = $this->item($debug ? 'warning' : 'success', 'APP_DEBUG', $debug ? 'Debug ativo.' : 'Debug inativo.', ['APP_DEBUG' => $debug], $debug ? 'Em produção, APP_DEBUG deve estar false.' : null);
        $items[] = $this->item(config('app.url') ? 'success' : 'warning', 'APP_URL', config('app.url') ?: 'APP_URL não definido.', ['APP_URL' => config('app.url')]);
        $items[] = $this->item('info', 'Cache driver', 'Driver: ' . config('cache.default'), ['CACHE_DRIVER' => config('cache.default')]);
        $items[] = $this->item('info', 'Session driver', 'Driver: ' . config('session.driver'), ['SESSION_DRIVER' => config('session.driver')]);
        $items[] = $this->item(config('queue.default') === 'sync' ? 'warning' : 'success', 'Queue connection', 'Connection: ' . config('queue.default'), ['QUEUE_CONNECTION' => config('queue.default')], config('queue.default') === 'sync' ? 'Para processos pesados, usar database/redis sempre que possível.' : null);
        $items[] = $this->item(config('mail.default') ? 'info' : 'warning', 'Mail transport', 'Mailer: ' . (config('mail.default') ?: 'not configured'), ['MAIL_MAILER' => config('mail.default')]);

        return $items;
    }
}
