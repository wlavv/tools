# Error Center / Exception Viewer — Laravel Module MVP

Módulo Laravel para capturar exceções, agrupar erros por hash, registar ocorrências individuais, visualizar erros no painel administrativo e disparar alertas para o módulo `Notifications`.

## O que está incluído

- Migrations:
  - `error_events`
  - `error_occurrences`
  - `error_event_users`
- Models Eloquent:
  - `ErrorEvent`
  - `ErrorOccurrence`
  - `ErrorEventUser`
- Services:
  - `ErrorCenterService`
  - `ErrorContextSanitizer`
  - `ErrorHashGenerator`
  - `ErrorCenterNotificationDispatcher`
- Middleware automático:
  - `CaptureUnhandledExceptions`
- Controller administrativo:
  - `ErrorCenterController`
- Views Blade standalone:
  - listagem
  - detalhe
- Integração com Notifications:
  - evento `ErrorCenterNotificationRequested`
  - opção de service configurável
- Seeder opcional para Spatie Permission:
  - `error_center.view`
  - `error_center.manage`

---

## Instalação usando `nwidart/laravel-modules`

Copie a pasta para o seu projeto:

```bash
cp -R Modules/ErrorCenter /caminho/do/projeto/Modules/ErrorCenter
```

Depois execute:

```bash
composer dump-autoload
php artisan module:enable ErrorCenter
php artisan migrate
```

Se usa Spatie Permission:

```bash
php artisan db:seed --class="Modules\\ErrorCenter\\Database\\Seeders\\ErrorCenterPermissionsSeeder"
```

---

## Instalação como package/path repository

Também pode instalar como package local. No `composer.json` principal do projeto:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "Modules/ErrorCenter"
    }
  ],
  "require": {
    "implementacao-pendente/error-center": "*"
  }
}
```

Depois:

```bash
composer update implementacao-pendente/error-center
php artisan migrate
```

O package tem auto-discovery do Laravel configurado em `composer.json`.

---

## Instalação manual sem package discovery

Adicione o autoload no `composer.json` principal:

```json
{
  "autoload": {
    "psr-4": {
      "Modules\\ErrorCenter\\": "Modules/ErrorCenter/app/",
      "Modules\\ErrorCenter\\Database\\Seeders\\": "Modules/ErrorCenter/database/seeders/"
    }
  }
}
```

Depois:

```bash
composer dump-autoload
```

Registe o provider.

Laravel 11+ / 12+ em `bootstrap/providers.php`:

```php
return [
    // ...
    Modules\ErrorCenter\Providers\ErrorCenterServiceProvider::class,
];
```

Laravel <= 10 em `config/app.php`:

```php
'providers' => [
    // ...
    Modules\ErrorCenter\Providers\ErrorCenterServiceProvider::class,
],
```

Depois:

```bash
php artisan migrate
```

---

## Configuração

Opcionalmente publique a config:

```bash
php artisan vendor:publish --tag=error-center-config
```

Principais variáveis de ambiente:

```env
ERROR_CENTER_ENABLED=true
ERROR_CENTER_CAPTURE_ENABLED=true
ERROR_CENTER_AUTO_REGISTER_MIDDLEWARE=true
ERROR_CENTER_CAPTURE_MIDDLEWARE_GROUPS=web,api
ERROR_CENTER_ROUTE_PREFIX=admin/error-center
ERROR_CENTER_VIEW_MIDDLEWARE=web,auth
ERROR_CENTER_MANAGE_MIDDLEWARE=web,auth
ERROR_CENTER_NOTIFICATIONS_ENABLED=true
ERROR_CENTER_NOTIFICATION_ENVIRONMENTS=production
ERROR_CENTER_NOTIFICATION_COOLDOWN_MINUTES=30
```

Se usa Spatie Permission, recomenda-se:

```env
ERROR_CENTER_VIEW_MIDDLEWARE=web,auth,permission:error_center.view
ERROR_CENTER_MANAGE_MIDDLEWARE=web,auth,permission:error_center.manage
```

Se usa Gates do Laravel:

```env
ERROR_CENTER_VIEW_MIDDLEWARE=web,auth,can:error_center.view
ERROR_CENTER_MANAGE_MIDDLEWARE=web,auth,can:error_center.manage
```

---

## Rotas administrativas

Interface:

```text
GET /admin/error-center
GET /admin/error-center/{id}
```

API interna da UI:

```text
GET  /admin/error-center/api/stats
GET  /admin/error-center/api/events
GET  /admin/error-center/api/events/{id}
GET  /admin/error-center/api/events/{id}/occurrences
POST /admin/error-center/api/events/{id}/status
POST /admin/error-center/api/events/{id}/resolve
POST /admin/error-center/api/events/{id}/ignore
```

---

## Captura automática de exceções

Por padrão, o módulo injeta o middleware `CaptureUnhandledExceptions` nos grupos `web` e `api`.

Fluxo:

```text
Request
 ↓
Exceção não tratada
 ↓
CaptureUnhandledExceptions
 ↓
ErrorCenterService::captureException()
 ↓
error_events + error_occurrences
 ↓
Laravel continua o tratamento normal da exceção
```

O middleware captura o erro e depois relança a exceção original, preservando o comportamento padrão do Laravel.

---

## Captura manual

Para capturar uma exceção manualmente:

```php
use Modules\ErrorCenter\Services\ErrorCenterService;

try {
    // código crítico
} catch (Throwable $throwable) {
    app(ErrorCenterService::class)->captureException($throwable, [
        'module' => 'payments',
        'source' => 'job',
        'environment' => app()->environment(),
        'user_id' => $userId ?? null,
        'tenant_id' => $tenantId ?? null,
        'request_id' => (string) Str::uuid(),
        'status_code' => 500,
        'payload' => [
            'payment_id' => $paymentId,
        ],
        'extra' => [
            'job' => static::class,
        ],
    ]);

    throw $throwable;
}
```

---

## Sanitização

O módulo mascara automaticamente campos sensíveis em payloads, headers, query params e contexto.

Campos mascarados por padrão:

```text
password
password_confirmation
token
access_token
refresh_token
authorization
api_key
secret
client_secret
cookie
session
csrf
xsrf
credit_card
card_number
cvv
iban
private_key
```

Exemplo:

```json
{
  "email": "user@example.com",
  "password": "[REDACTED]",
  "authorization": "[REDACTED]"
}
```

---

## Agrupamento por hash

O hash é calculado com:

```text
environment + source + module + error_type + normalized_message + stack_origin
```

A mensagem é normalizada para remover variações como:

```text
UUIDs
emails
números
IDs
datas
query strings
```

Assim, erros como:

```text
User 123 not found
User 987 not found
```

ficam agrupados no mesmo `error_event`.

---

## Reabertura automática

Quando um erro com status `resolved` volta a ocorrer:

```text
status = new
resolved_at = null
resolved_by = null
```

O módulo dispara o evento de notificação:

```text
error_center.resolved_reopened
```

---

## Integração com o módulo Notifications

O Error Center não envia e-mail, Slack, WhatsApp ou push diretamente.

Ele emite este evento Laravel:

```php
Modules\ErrorCenter\Events\ErrorCenterNotificationRequested
```

Payload do evento:

```php
$event->notification
```

Exemplo de listener:

```php
use Illuminate\Support\Facades\Event;
use Modules\ErrorCenter\Events\ErrorCenterNotificationRequested;

Event::listen(ErrorCenterNotificationRequested::class, function (ErrorCenterNotificationRequested $event): void {
    app(\Modules\Notifications\Services\NotificationService::class)->create($event->notification);
});
```

Também pode configurar um service diretamente:

```env
ERROR_CENTER_NOTIFICATIONS_SERVICE="Modules\\Notifications\\Services\\NotificationService"
```

O service pode expor:

```php
create(array $payload): void
```

ou:

```php
send(array $payload): void
```

### Eventos enviados no MVP

```text
error_center.critical_created
error_center.resolved_reopened
```

Eventos preparados para evolução:

```text
error_center.error_created
error_center.threshold_reached
```

---

## Critérios de aceite cobertos

- Captura exceções não tratadas.
- Cria `error_events`.
- Cria `error_occurrences`.
- Agrupa erros repetidos por hash.
- Incrementa `occurrence_count`.
- Conta usuários afetados sem duplicidade.
- Sanitiza dados sensíveis.
- Exibe listagem administrativa.
- Exibe detalhe com stack trace e contexto.
- Permite alterar status.
- Reabre erro resolvido quando volta a ocorrer.
- Dispara evento para Notifications em erro crítico de produção.
- Dispara evento para Notifications quando erro resolvido volta.

---

## Próximas evoluções sugeridas

- Comentários internos no erro.
- Atribuição de responsável.
- Regras de silenciamento.
- Threshold de recorrência.
- Captura frontend.
- Exportação CSV/JSON.
- Integração com releases/deployments.
- Métricas temporais.
