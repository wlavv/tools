<?php

namespace Modules\ConfigInspector\Inspectors;

class SecurityInspector extends BaseInspector
{
    public function key(): string { return 'security'; }
    public function label(): string { return 'Security'; }

    public function inspect(): array
    {
        $items = [];
        $isProduction = config('app.env') === 'production';
        $debug = (bool) config('app.debug');

        $items[] = $this->item($isProduction && $debug ? 'critical' : 'success', 'Debug exposure', $isProduction && $debug ? 'APP_DEBUG está ativo em production.' : 'Sem exposição crítica de debug detetada.', [], $isProduction && $debug ? 'Definir APP_DEBUG=false.' : null);
        $items[] = $this->item(config('app.key') ? 'success' : 'critical', 'APP_KEY', config('app.key') ? 'APP_KEY definido.' : 'APP_KEY ausente.', [], config('app.key') ? null : 'Gerar APP_KEY antes de operar a aplicação.');
        $items[] = $this->item(file_exists(public_path('.env')) ? 'critical' : 'success', '.env public exposure', file_exists(public_path('.env')) ? '.env encontrado em public.' : '.env não encontrado em public.', ['public_env' => public_path('.env')]);
        $items[] = $this->item(app()->isDownForMaintenance() ? 'warning' : 'success', 'Maintenance mode', app()->isDownForMaintenance() ? 'Aplicação em maintenance mode.' : 'Aplicação operacional.');

        return $items;
    }
}
