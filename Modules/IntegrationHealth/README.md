# Integration Health Module

Módulo Laravel para WebTools Manager / B.O. LSG.

## Objetivo

Centralizar a saúde operacional de integrações, APIs, queues, cron jobs, webhooks e sincronizações.

## Instalação

1. Copiar a pasta `IntegrationHealth` para `Modules/IntegrationHealth`.
2. Confirmar que o autoload modular encontra `Modules\\IntegrationHealth`.
3. Executar migrations:

```bash
php artisan migrate
```

4. Limpar cache se necessário:

```bash
php artisan optimize:clear
```

5. Aceder a:

```text
/integration-health
```

## Comando disponível

```bash
php artisan integration-health:evaluate
```

Pode ser chamado pelo scheduler/cron para avaliar heartbeats atrasados.

## Endpoints internos autenticados

### Heartbeat

`POST /integration-health/api/heartbeat`

```json
{
  "service_slug": "moloni",
  "status": "online",
  "response_time_ms": 312,
  "payload": {"source": "invoice-sync"}
}
```

### Event

`POST /integration-health/api/event`

```json
{
  "service_slug": "moloni",
  "severity": "error",
  "event_type": "invalid_customer",
  "title": "Moloni invalid customer",
  "message": "INVALID_CUSTOMER_ID returned by Moloni",
  "payload": {"order_id": 123}
}
```

### Metric

`POST /integration-health/api/metric`

```json
{
  "service_slug": "queue-worker",
  "metric": "pending_jobs",
  "value": 27,
  "unit": "jobs"
}
```

## Tabelas

- `integration_health_services`
- `integration_health_events`
- `integration_health_heartbeats`
- `integration_health_metrics`

## Notas

Este módulo foi preparado para integração futura com:

- Queue Monitor
- Audit Log Central
- Notifications
- Config Inspector
- System Tools / Recovery Center
