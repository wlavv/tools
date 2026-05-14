# Data Import Wizard

Módulo Laravel para importações CSV modulares, com contratos por model, dependências entre tabelas e dashboard de coerência.

## Objetivo

Permitir que cada model/módulo declare:

1. Os seus campos de importação.
2. As suas regras de validação.
3. As suas dependências obrigatórias/opcionais.
4. A forma de resolver ou criar essas dependências.
5. A chave usada para encontrar registos existentes.

Assim, ao importar `A`, o wizard pergunta a `A` os seus campos, pergunta a `A` quais são as dependências, depois pergunta a `B` e `C` quais os campos de que precisam.

Exemplo:

```txt
A depende de B e C
D depende apenas de B
```

O contrato de `B` é definido uma única vez e reutilizado por `A`, `D` ou qualquer outro model.

---

## Instalação

Copiar a pasta para:

```txt
Modules/DataImportWizard
```

Depois confirmar que o provider do módulo está carregado pelo teu sistema de módulos. Se usas `nwidart/laravel-modules`, o `module.json` já inclui o provider.

Executar migrations:

```bash
php artisan migrate
```

Opcionalmente publicar/configurar manualmente o ficheiro:

```txt
Modules/DataImportWizard/Config/config.php
```

---

## Configuração dos importáveis

Em `Config/config.php`:

```php
'importables' => [
    \Modules\Catalog\Models\Product::class,
    \Modules\Suppliers\Models\Supplier::class,
    \Modules\System\Models\Currency::class,
],
```

---

## Exemplo de model importável

```php
<?php

namespace Modules\Suppliers\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class Supplier extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'suppliers';

    protected $fillable = [
        'reference',
        'name',
        'email',
    ];

    public static function importKey(): string
    {
        return 'supplier';
    }

    public static function importLabel(): string
    {
        return 'Fornecedor';
    }

    public static function importFields(): array
    {
        return [
            'reference' => [
                'label' => 'Referência Fornecedor',
                'required' => true,
                'type' => 'string',
                'example' => 'SUP-001',
                'column' => 'reference',
                'lookup' => true,
            ],
            'name' => [
                'label' => 'Nome Fornecedor',
                'required' => true,
                'type' => 'string',
                'example' => 'Fornecedor XPTO',
                'column' => 'name',
            ],
            'email' => [
                'label' => 'Email',
                'required' => false,
                'type' => 'email',
                'example' => 'compras@example.test',
                'column' => 'email',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'reference' => 'required|string|max:64',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ];
    }

    public static function importLookupColumns(): array
    {
        return ['reference'];
    }
}
```

---

## Exemplo: A depende de B e C

```php
public static function importDependencies(): array
{
    return [
        'supplier' => [
            'class' => \Modules\Suppliers\Models\Supplier::class,
            'required' => true,
            'mode' => 'resolve_or_create',
            'prefix' => 'supplier',
            'foreign_key' => 'supplier_id',
        ],
        'currency' => [
            'class' => \Modules\System\Models\Currency::class,
            'required' => true,
            'mode' => 'resolve_only',
            'prefix' => 'currency',
            'foreign_key' => 'currency_id',
        ],
    ];
}
```

O CSV gerado para `Product` ficaria semelhante a:

```csv
supplier_reference,supplier_name,supplier_email,currency_iso,reference,name,price
```

Ou seja:

```txt
Campos de Supplier + Campos de Currency + Campos do Product
```

---

## Modos de dependência

| Modo | Comportamento |
|---|---|
| `resolve_only` | Procura o registo. Se não existir, falha. |
| `optional_resolve` | Procura o registo. Se não existir, continua com `null`. |
| `resolve_or_create` | Procura. Se não existir, cria. |
| `resolve_or_update` | Procura. Se existir, atualiza. Se não existir, cria. |
| `create_only` | Cria sempre novo. |

Modos para o model principal:

| Modo | Comportamento |
|---|---|
| `create` | Cria sempre novo. |
| `update` | Atualiza apenas se encontrar registo existente. |
| `upsert` | Atualiza se existir, cria se não existir. |
| `validate_only` | Apenas valida, não executa. |

---


## Resolver customizado opcional

Quando a chave de procura é mais complexa do que uma coluna simples, o model pode implementar um método estático opcional:

```php
public static function resolveImportRecord(array $attributes, array $context): ?\Illuminate\Database\Eloquent\Model
{
    return static::query()
        ->where('supplier_id', $attributes['supplier_id'] ?? null)
        ->where('reference', $attributes['reference'] ?? null)
        ->first();
}
```

O executor usa este método antes da resolução genérica por `importLookupColumns()`.

---
## Rotas

Por defeito:

```txt
/admin/data-import-wizard
```

Podes alterar em:

```php
'route_prefix' => 'admin/data-import-wizard',
'route_middleware' => ['web', 'auth'],
```

---

## Dashboard de coerência

O dashboard apresenta:

```txt
Total de módulos
Módulos com contratos de importação
Models importáveis registados
Perfis válidos
Perfis com erro
Perfis sem campos
Perfis com dependências
Módulos sem perfil
```

---

## Notas importantes

Este módulo não tenta importar qualquer tabela automaticamente. Ele usa contratos declarados pelos models para evitar imports perigosos em campos técnicos, chaves internas, passwords, tokens ou colunas sensíveis.

A descoberta automática da BD pode ser implementada como assistente técnico, mas a execução deve continuar baseada em contratos validados.
