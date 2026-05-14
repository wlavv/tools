# StreamDeckAccess

Módulo no formato usado nos restantes módulos do B.O. (`Modules\...`), seguindo a estrutura do exemplo `PasswordManager`:

```text
StreamDeckAccess/
├── Config/
├── Database/
├── Http/
├── Jobs/
├── Models/
├── Providers/
├── Resources/
├── Routes/
├── Services/
├── Tasks/
└── module.json
```

## O que faz

Permite criar access points externos para botões do Stream Deck ou outras automações.

Cada access point gera um link público protegido por token:

```text
GET /api/streamdeck/{public_id}?token={plain_token}
POST /api/streamdeck/{public_id}?token={plain_token}
```

O token só é mostrado no momento de criação ou rotação. Na base de dados fica apenas `token_hash` e `token_hint`.

## Tipos suportados

### 1. Redirect

Abre uma página específica ou URL:

```text
/api/streamdeck/{public_id}?token=...
```

Depois redireciona para:

```text
/backoffice/encomendas
https://app.exemplo.pt/backoffice/encomendas
```

Para páginas do B.O., o utilizador continua a precisar da sessão autenticada normal.

### 2. Task em background

Envia uma tarefa para queue e devolve resposta imediata `202 queued`.

Tarefas incluídas:

| task_key | Função |
|---|---|
| `ping` | Teste simples da queue. |
| `pagespeed_google` | Executa Google PageSpeed Insights. |
| `check_external_links` | Verifica URLs externas permitidas por allow-list. |
| `sales_yesterday_report` | Executa o command configurado para relatório de vendas do dia anterior. |
| `sales_forecast` | Executa o command configurado para previsão de vendas. |

## Instalação

Colocar a pasta em:

```text
Modules/StreamDeckAccess
```

Garantir que o loader de módulos lê o `module.json` e regista:

```text
Modules\StreamDeckAccess\Providers\StreamDeckAccessServiceProvider
```

Depois correr:

```bash
php artisan migrate
php artisan queue:work --queue=streamdeck,reports,default
```

## Configuração útil no `.env`

```dotenv
STREAMDECK_PUBLIC_ROUTE_PREFIX=api/streamdeck
STREAMDECK_TOKEN_PARAMETER=token
STREAMDECK_RATE_LIMIT_PER_MINUTE=30
STREAMDECK_QUEUE=streamdeck

# Opcional: restrição global por IP
# STREAMDECK_ALLOWED_IPS=203.0.113.10,203.0.113.*,10.10.0.0/16

# Google PageSpeed Insights
GOOGLE_PAGESPEED_API_KEY=

# Segurança do verificador de links externos
STREAMDECK_LINK_CHECKER_ALLOWED_HOSTS=exemplo.pt,cdn.exemplo.pt
STREAMDECK_LINK_CHECKER_TIMEOUT=10
STREAMDECK_LINK_CHECKER_MAX_URLS=25

# Commands internos
STREAMDECK_SALES_YESTERDAY_COMMAND=reports:sales-yesterday
STREAMDECK_SALES_FORECAST_COMMAND=reports:sales-forecast
```

## Criar access point via B.O.

Acede a:

```text
/streamdeck-access
```

Cria um access point, copia o URL gerado e cola no botão do Stream Deck.

## Criar access point via JSON

```http
POST /streamdeck-access
Accept: application/json
Content-Type: application/json

{
  "name": "PageSpeed homepage mobile",
  "type": "task",
  "task_key": "pagespeed_google",
  "payload": {
    "url": "https://www.exemplo.pt/",
    "strategy": "mobile"
  },
  "cooldown_seconds": 300,
  "queue": "streamdeck"
}
```

Resposta:

```json
{
  "access_point": {},
  "plain_token": "...",
  "token_hint": "abc123",
  "streamdeck_url": "https://app.exemplo.pt/api/streamdeck/{public_id}?token=...",
  "warning": "Guarda este token agora. Só é apresentado uma vez; depois só podes rodar o token."
}
```

## Payloads de exemplo

### Google PageSpeed

```json
{
  "url": "https://www.exemplo.pt/",
  "strategy": "mobile"
}
```

### Verificação de links externos

```json
{
  "urls": [
    "https://www.exemplo.pt/",
    "https://cdn.exemplo.pt/app.js"
  ]
}
```

Configura `STREAMDECK_LINK_CHECKER_ALLOWED_HOSTS`; sem allow-list, a tarefa não chama hosts externos.

### Relatório de vendas do dia anterior

```json
{
  "store_id": 1
}
```

### Previsão de vendas

```json
{
  "store_id": 1,
  "date": "tomorrow"
}
```

`date` aceita `today`, `tomorrow` ou uma data parseável pelo Carbon.

## Criar uma tarefa nova

```php
<?php

namespace App\StreamDeckTasks;

use Modules\StreamDeckAccess\Contracts\StreamDeckTask;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;

class ReindexCatalogTask implements StreamDeckTask
{
    public function handle(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log): array
    {
        // dispatch(new ReindexCatalogJob());

        return [
            'message' => 'Reindex requested',
            'requested_at' => now()->toISOString(),
        ];
    }
}
```

Registar em `Config/config.php`:

```php
'tasks' => [
    'reindex_catalog' => App\StreamDeckTasks\ReindexCatalogTask::class,
],

'task_labels' => [
    'reindex_catalog' => 'Reindexar catálogo',
],
```

## Controlos de segurança

Cada access point tem:

- `enabled`
- `expires_at`
- `max_uses`
- `cooldown_seconds`
- `allowed_ips`
- `use_count`
- `last_used_at`
- logs de execução
- rotação de token
- token em hash HMAC

Usar sempre HTTPS. Tokens em URL podem aparecer em logs de proxy, histórico de browser ou ferramentas de analytics.
