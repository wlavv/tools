# Data Export Center

Módulo Laravel para exportação de dados com três modos principais:

1. **Export por contrato/model**, reutilizando a árvore de dependências do `DataImportWizard`.
2. **Export por query SQL `SELECT`**, com guard para impedir `INSERT`, `UPDATE`, `DELETE`, `DROP`, múltiplos statements, etc.
3. **Export por builder dinâmico**, baseado em JSON e com whitelist de tabelas.

Inclui ainda geração de relatórios `HTML` e `PDF` com templates próprios, header/footer por loja, plataforma, módulo ou perfil.

---

## Instalação

Copiar a pasta para:

```txt
Modules/DataExportCenter
```

Confirmar que o provider é carregado pelo sistema de módulos. Se usas `nwidart/laravel-modules`, o `module.json` já inclui:

```txt
Modules\DataExportCenter\Providers\DataExportCenterServiceProvider
```

Executar migrations:

```bash
php artisan migrate
```

Opcionalmente publicar config:

```bash
php artisan vendor:publish --tag=data-export-center-config
```

---

## Rotas

Por defeito:

```txt
/admin/data-export-center
```

Configuração:

```php
'route_prefix' => 'admin/data-export-center',
'route_middleware' => ['web', 'auth'],
```

---

## 1. Export usando a árvore do Import Wizard

Se um model já implementa `ImportableContract`, pode ser registado diretamente:

```php
// Modules/DataExportCenter/Config/config.php
'exportables' => [
    \Modules\Catalog\Models\Product::class,
],
```

O Export Center consegue reutilizar:

- `importKey()`
- `importLabel()`
- `importFields()`
- `importDependencies()`

Ou seja, se `Product` depende de `Supplier` e `Currency`, o export automático faz joins com base em:

```php
'foreign_key' => 'supplier_id',
'owner_key' => 'id',
'prefix' => 'supplier',
```

Resultado esperado em CSV:

```csv
reference,name,price,status,supplier_reference,supplier_name,supplier_email,currency_iso,currency_name
```

Por defeito, dependências usam `leftJoin`, mesmo quando são obrigatórias, para evitar esconder linhas por dados inconsistentes. Se quiseres inner join para dependências obrigatórias:

```php
'dependencies' => [
    'required_dependencies_as_inner_join' => true,
],
```

---

## 2. Contrato de export próprio

Quando o export deve ser diferente do import, o model pode implementar `ExportableContract` e usar `HasExportContract`:

```php
use Modules\DataExportCenter\Contracts\ExportableContract;
use Modules\DataExportCenter\Traits\HasExportContract;

class Product extends Model implements ImportableContract, ExportableContract
{
    use HasImportContract;
    use HasExportContract;

    public static function exportFields(): array
    {
        return array_merge(static::importFields(), [
            'status' => [
                'label' => 'Estado',
                'type' => 'string',
                'column' => 'status',
            ],
        ]);
    }

    public static function exportFilters(): array
    {
        return [
            'status' => [
                'label' => 'Estado',
                'operator' => '=',
                'column' => 'status',
            ],
            'name' => [
                'label' => 'Nome contém',
                'operator' => 'like',
                'column' => 'name',
            ],
        ];
    }
}
```

Também podes customizar a query final:

```php
public static function modifyExportQuery(\Illuminate\Database\Query\Builder $query, array $context = [], array $schema = [])
{
    if (! empty($context['shop_id'])) {
        $query->where('root.shop_id', $context['shop_id']);
    }

    return $query;
}
```

---

## 3. Export por SQL SELECT

Perfis SQL são guardados em `data_export_profiles`:

```php
DataExportProfile::create([
    'key' => 'active-products-sql',
    'type' => 'sql',
    'label' => 'Produtos ativos',
    'query_sql' => 'select reference, name, price from products where status = ?',
    'query_bindings' => ['active'],
    'default_format' => 'csv',
]);
```

O SQL guard é aplicado antes da execução. Apenas queries que começam por `SELECT` ou `WITH` são aceites, e palavras como `UPDATE`, `DELETE`, `DROP`, `ALTER`, `TRUNCATE` e múltiplos statements são bloqueados.

---

## 4. Export por builder dinâmico

Exemplo de perfil:

```php
DataExportProfile::create([
    'key' => 'products-builder',
    'type' => 'builder',
    'label' => 'Produtos por builder',
    'builder_definition' => [
        'from' => ['table' => 'products', 'alias' => 'p'],
        'select' => [
            ['column' => 'p.reference', 'alias' => 'reference'],
            ['column' => 'p.name', 'alias' => 'name'],
            ['column' => 's.name', 'alias' => 'supplier_name'],
        ],
        'joins' => [
            [
                'type' => 'left',
                'table' => 'suppliers',
                'alias' => 's',
                'left' => 'p.supplier_id',
                'operator' => '=',
                'right' => 's.id',
            ],
        ],
        'filters' => [
            'status' => ['column' => 'p.status', 'operator' => '='],
        ],
        'order' => [
            ['column' => 'p.reference', 'direction' => 'asc'],
        ],
    ],
]);
```

Deve ser configurada uma whitelist de tabelas:

```php
'dynamic_builder' => [
    'allowed_tables' => ['products', 'suppliers', 'currencies'],
],
```

---

## 5. Relatórios com header/footer por loja, plataforma ou módulo

Os templates ficam em `data_export_report_templates`.

Ordem de resolução recomendada:

1. Template específico do perfil + loja.
2. Template específico do perfil + plataforma.
3. Template específico do perfil + módulo.
4. Template específico do perfil + global.
5. Template genérico da loja/plataforma/módulo.
6. Template global default.

Exemplo para `webtools-manager`:

```php
DataExportReportTemplate::create([
    'key' => 'webtools-manager-default',
    'profile_key' => null,
    'name' => 'Webtools Manager Default',
    'scope_type' => 'platform',
    'scope_key' => 'webtools-manager',
    'is_default' => true,
    'title_template' => '{{ $title }}',
    'header_html' => '<div><strong>Webtools Manager</strong><br>{{ $title }}</div>',
    'footer_html' => '<div>Webtools Manager · {{ $generated_at->format("Y-m-d H:i") }}</div>',
    'css' => 'body { font-family: Arial, sans-serif; }',
]);
```

Exemplo para uma loja:

```php
DataExportReportTemplate::create([
    'key' => 'shop-123-default',
    'name' => 'Loja 123',
    'scope_type' => 'shop',
    'scope_key' => '123',
    'is_default' => true,
    'header_html' => '<div><strong>{{ $context["shop_name"] ?? "Loja" }}</strong></div>',
    'footer_html' => '<div>{{ $context["shop_name"] ?? "Loja" }} · {{ $rows_count }} linhas</div>',
]);
```

Ao executar export em formato `html` ou `pdf`, envia contexto:

```php
app(ExportExecutorService::class)->executeByKey(
    profileKey: 'product',
    filters: ['status' => 'active'],
    context: [
        'platform' => 'webtools-manager',
        'shop_id' => 123,
        'shop_name' => 'Loja Demo',
    ],
    format: 'html'
);
```

---

## Formatos suportados

- `csv`
- `json`
- `html`
- `pdf`, se existir `barryvdh/laravel-dompdf` ou renderer compatível configurado

---

## Segurança recomendada

- Não expor perfis SQL a utilizadores sem permissão administrativa.
- Manter `dynamic_builder.allowed_tables` explícito.
- Não permitir raw selects no builder dinâmico, exceto em perfis técnicos validados.
- Criar políticas/permissions separadas: `export.view`, `export.run`, `export.sql.manage`, `export.templates.manage`.
- Guardar `query_hash`, filtros, contexto e utilizador para auditoria.

---

## Diagnóstico

```bash
php artisan data-export-center:diagnose
```

Mostra perfis válidos, inválidos, número de campos e erros de configuração.
