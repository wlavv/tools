# Audit Log Central

Módulo Laravel modular para centralizar eventos de auditoria no WebTools Manager / B.O. LSG.

## Instalação

1. Copiar a pasta `AuditLogCentral` para `Modules/AuditLogCentral`.
2. Garantir que o autoload/loader de módulos carrega o provider indicado em `module.json`.
3. Executar migrations:

```bash
php artisan migrate
```

4. Abrir:

```text
/audit-log-central
/audit-log-central/logs
```

## Uso rápido noutros módulos

```php
use Modules\AuditLogCentral\Support\Facades\AuditLog;

AuditLog::log([
    'module' => 'oms',
    'event' => 'invoice.price_updated',
    'action' => 'update_price',
    'severity' => 'warning',
    'auditable_type' => 'product',
    'auditable_id' => 4521,
    'changes' => [
        'wholesale_price' => [
            'old' => 12.50,
            'new' => 13.90,
            'type' => 'updated',
        ],
    ],
    'tags' => ['OMS', 'PRICE'],
    'relations' => [
        ['type' => 'invoice', 'id' => 854, 'label' => 'OMS Invoice'],
    ],
    'metadata' => [
        'reason' => 'invoice line confirmed',
    ],
]);
```

## Trait opcional

```php
use Modules\AuditLogCentral\Support\Traits\HasAuditLog;

class Product extends Model
{
    use HasAuditLog;
}

$product->audit('stock.changed', [
    'module' => 'housing_tool',
    'changes' => [
        'stock' => ['old' => 5, 'new' => 8],
    ],
]);
```

## Estrutura

- `audit_logs`: evento principal
- `audit_log_changes`: before/after por campo
- `audit_log_tags`: tags como OMS, SECURITY, PRICE
- `audit_log_relations`: relações com invoices, produtos, clientes, fornecedores, etc.

## Notas

- Dados sensíveis são mascarados automaticamente por chave (`password`, `token`, `secret`, etc.).
- A UI usa estilo LSG simples com cards, badges por severidade, filtros laterais e timeline por entidade.
- Este módulo não altera módulos existentes; serve como base incremental e não invasiva.
