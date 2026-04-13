# Notifications v4

Módulo autónomo de notificações para WebTools Manager.

## O que faz

- notificações internas no BO
- envio multi-canal: `email`, `whatsapp`, `discord`, `sms`, `webhook`
- dropdown/topbar bell para utilizador autenticado
- centro de notificações com filtros e logs
- página de configuração de providers
- consola de teste incluída no módulo
- integração por uma única função: `notifications_send([...])`

## Estrutura

O módulo vive integralmente em:

```text
Modules/Notifications/
```

Sem necessidade de espalhar ficheiros pelo resto do projeto, exceto o ponto opcional de renderização do sino no layout.

## Instalação

1. Copiar a pasta `Modules/Notifications` para dentro do teu projeto.
2. Garantir que o teu core carrega o `module.json` e o provider do módulo.
3. Executar migrações.
4. Integrar o sino no layout global, se quiseres notificações visíveis no topbar.

## Migrações

```bash
php artisan migrate
```

## Queue

Os canais externos são preparados para queue.

Para ambiente local sem worker, podes enviar logo sem queue ao passar:

```php
'queue' => false
```

Se quiseres usar queue:

```bash
php artisan queue:work
```

## Integração mínima em qualquer módulo

```php
notifications_send([
    'title' => 'Nova tarefa atribuída',
    'message' => 'Foi atribuída uma nova tarefa.',
    'category' => 'tasks',
    'type' => 'info',
    'priority' => 'normal',
    'source_module' => 'tasks',
    'channels' => ['internal', 'email'],
    'recipients' => [[
        'user_id' => 5,
        'name' => 'Bruno',
        'email' => 'bruno@example.com',
        'phone' => '+351900000000',
    ]],
    'email' => [
        'subject' => 'Nova tarefa atribuída',
        'body' => 'Tens uma nova tarefa à tua espera.'
    ]
]);
```

## Helpers disponíveis

```php
notifications_send([...]);
notifications_send_to_user($userId, [...]);
notifications_send_to_email('destino@dominio.com', [...]);
notifications_send_to_phone('+3519...', [...]);
notifications_task_assigned($task, $recipient, ['internal']);
notifications_calendar_reminder($event, $recipient, ['internal', 'email']);
```

## Como integrar no BO

Adicionar no layout/topbar:

```blade
<x-notifications-dropdown />
```

ou

```blade
<x-notifications-topbar-bell />
```

## URLs do módulo

- `/notifications`
- `/notifications/settings`
- `/notifications/test`

## Como testar

### 1. Internal

Vai a `/notifications/test`.
Seleciona apenas `internal`.
Envia.

Resultado esperado:
- aparece no centro de notificações
- aparece no dropdown do sino
- existe registo na tabela `notifications`

### 2. Email

Pré-requisito:
- `.env` do Laravel com mail configurado

Exemplo típico:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="WebTools Manager"
```

Teste:
- em `/notifications/test`, ativa `email`
- coloca email de destino
- envia

Resultado esperado:
- email recebido
- log `sent` em `notification_channel_logs`

### 3. SMS com Twilio

Em `/notifications/settings` criar provider:
- channel: `sms`
- provider: `twilio`
- enabled: `true`

JSON:

```json
{
  "account_sid": "ACxxxxxxxx",
  "auth_token": "xxxxxxxx",
  "from": "+1..."
}
```

Teste:
- em `/notifications/test`, ativa `sms`
- coloca telefone do destinatário em formato internacional
- envia

Resultado esperado:
- SMS enviado
- log com provider `twilio`

### 4. WhatsApp com Twilio

Em `/notifications/settings` criar provider:
- channel: `whatsapp`
- provider: `twilio`
- enabled: `true`

JSON:

```json
{
  "account_sid": "ACxxxxxxxx",
  "auth_token": "xxxxxxxx",
  "from": "+14155238886"
}
```

Notas:
- o driver acrescenta `whatsapp:` automaticamente
- no sandbox Twilio, o número de destino tem de estar autorizado

Teste:
- em `/notifications/test`, ativa `whatsapp`
- coloca telefone do destinatário
- envia

### 5. Discord

Podes usar config global ou webhook por destinatário.

Config global em `/notifications/settings`:
- channel: `discord`
- provider: `webhook`
- enabled: `true`

JSON:

```json
{
  "webhook_url": "https://discord.com/api/webhooks/..."
}
```

Teste:
- ativa `discord`
- opcionalmente preenche `discord_webhook_url`
- envia

### 6. Webhook genérico

Em `/notifications/test`:
- ativa `webhook`
- define URL, método, headers e payload JSON

## Como usar a partir de outros módulos

### Exemplo Tasks

```php
notifications_send([
    'title' => 'Task concluída',
    'message' => 'A task #' . $task->id . ' foi concluída.',
    'category' => 'tasks',
    'type' => 'success',
    'priority' => 'normal',
    'source_module' => 'tasks',
    'reference_type' => 'task',
    'reference_id' => $task->id,
    'channels' => ['internal', 'email'],
    'recipients' => [[
        'user_id' => $task->assigned_user_id,
        'email' => $task->assignedUser->email ?? null,
        'name' => $task->assignedUser->name ?? null,
    ]],
    'action_url' => route('tasks.show', $task->id),
    'action_label' => 'Ver task',
    'email' => [
        'subject' => 'Task concluída',
        'body' => 'A task foi concluída com sucesso.',
    ],
]);
```

### Exemplo Calendar

```php
notifications_calendar_reminder([
    'id' => $event->id,
    'title' => $event->title,
    'date' => $event->start_date,
    'url' => route('calendar.show', $event->id),
], [
    'user_id' => $event->user_id,
    'email' => $event->user->email ?? null,
    'name' => $event->user->name ?? null,
    'phone' => $event->user->phone ?? null,
], ['internal', 'email', 'sms']);
```

## Tabelas criadas

- `notifications`
- `notification_recipients`
- `notification_channel_logs`
- `notification_provider_configs`

## Notas de operação

- `internal` grava na base de dados e alimenta o centro de notificações
- `email` usa o mailer Laravel
- `sms` suporta `twilio` e `generic_webhook`
- `whatsapp` suporta `twilio` e `generic_webhook`
- `discord` usa webhook
- `webhook` é genérico

## Recomendação

Em produção, manter canais externos em queue e usar worker ativo.
Para troubleshooting, usar sempre primeiro a página `/notifications/test` e depois confirmar os registos dos logs.
