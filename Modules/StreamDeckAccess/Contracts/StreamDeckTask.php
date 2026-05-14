<?php

namespace Modules\StreamDeckAccess\Contracts;

use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;

interface StreamDeckTask
{
    /**
     * Executa uma tarefa registada para Stream Deck.
     *
     * Devolve apenas metadados compactos e seguros para auditoria.
     * Relatórios grandes devem ser guardados noutro local e referenciados aqui.
     *
     * @return array<string, mixed>
     */
    public function handle(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log): array;
}
