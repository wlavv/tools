# JobQueueMonitor

Módulo Laravel para monitorizar jobs/queues no WebTools Manager.

## Inclui

- Manifest completo: `name`, `slug`, `enabled`, `version`, `provider`
- ServiceProvider sem dependências externas
- Dashboard de saúde
- Histórico de execuções
- Captura automática de eventos Laravel Queue:
  - JobProcessing
  - JobProcessed
  - JobFailed
- Registo de falhas com payload, exception e stack trace resumido
- Integração defensiva com tabela `notifications`
- Envio de email em falhas
- Health checks básicos
- Views Blade estilo LSG

## Instalação

1. Copiar a pasta `JobQueueMonitor` para `Modules/JobQueueMonitor`
2. Confirmar que o autoload/modules loader do projeto carrega o provider:
   `Modules\\JobQueueMonitor\\Providers\\JobQueueMonitorServiceProvider`
3. Executar:

```bash
php artisan optimize:clear
php artisan migrate
```

4. Configurar `.env` se necessário:

```env
JOB_QUEUE_MONITOR_EMAIL_ENABLED=true
JOB_QUEUE_MONITOR_EMAIL_TO=admin@example.com
JOB_QUEUE_MONITOR_NOTIFICATIONS_ENABLED=true
JOB_QUEUE_MONITOR_NOTIFICATIONS_TABLE=notifications
JOB_QUEUE_MONITOR_STALE_PROCESSING_MINUTES=30
JOB_QUEUE_MONITOR_CRITICAL_FAILURES_THRESHOLD=5
JOB_QUEUE_MONITOR_CRITICAL_FAILURES_WINDOW_MINUTES=30
```

## URL

`/job-queue-monitor`

## Notas

A integração com `notifications` foi feita de forma defensiva. Se a tabela não existir ou tiver estrutura diferente, o worker não quebra.
