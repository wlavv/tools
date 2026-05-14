<?php

namespace Modules\ConfigInspector\Inspectors;

use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseInspector extends BaseInspector
{
    public function key(): string { return 'database'; }
    public function label(): string { return 'Database'; }

    public function inspect(): array
    {
        $items = [];
        foreach (['mysql', 'mysql2'] as $connection) {
            try {
                DB::connection($connection)->getPdo();
                $db = DB::connection($connection)->getDatabaseName();
                $items[] = $this->item('success', 'Connection ' . $connection, 'Ligação ativa: ' . $db, ['connection' => $connection, 'database' => $db]);
            } catch (Throwable $e) {
                $items[] = $this->item($connection === 'mysql' ? 'critical' : 'warning', 'Connection ' . $connection, 'Falha na ligação: ' . $e->getMessage(), ['connection' => $connection], $connection === 'mysql2' ? 'Confirmar se este ambiente precisa de PrestaShop/DB2.' : 'Corrigir ligação principal da aplicação.');
            }
        }

        try {
            $count = DB::table('migrations')->count();
            $items[] = $this->item('success', 'Migrations table', 'Tabela migrations acessível. Total: ' . $count, ['count' => $count]);
        } catch (Throwable $e) {
            $items[] = $this->item('error', 'Migrations table', 'Não foi possível consultar migrations: ' . $e->getMessage());
        }

        return $items;
    }
}
