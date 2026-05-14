# DatabaseExplorer

Módulo Laravel modular para analisar a estrutura e a health técnica da base de dados sem consultar dados reais das tabelas.

## Funcionalidades

- Estatísticas globais da base de dados.
- Estatísticas por schema.
- Lista de tabelas com tamanho, linhas estimadas, indexes, constraints e health.
- Detalhe de tabela com campos, indexes, constraints e relações.
- Health findings por severidade.
- Snapshots de crescimento/health.
- PostgreSQL como adapter inicial.

## Instalação

Copiar a pasta para:

```text
Modules/DatabaseExplorer
```

Garantir que o loader modular da plataforma regista o provider indicado em `module.json`:

```text
Modules\DatabaseExplorer\Providers\DatabaseExplorerServiceProvider
```

Depois executar:

```bash
php artisan migrate
```

## Configuração recomendada

```env
DB_EXPLORER_ENABLED=true
DB_EXPLORER_CONNECTION=pgsql
DB_EXPLORER_ALLOWED_SCHEMAS=public
DB_EXPLORER_ROUTE_PREFIX=database-explorer
DB_EXPLORER_SNAPSHOTS_ENABLED=true
```

Para permitir todos os schemas não-sistema:

```env
DB_EXPLORER_ALLOWED_SCHEMAS=*
```

## Rotas

```text
GET  /database-explorer
GET  /database-explorer/health
GET  /database-explorer/snapshots
POST /database-explorer/snapshots/collect
GET  /database-explorer/tables/{schemaName}/{tableName}
```

## Segurança

O módulo não disponibiliza SQL livre, preview de linhas, exportação de dados nem queries sobre colunas de negócio. Todas as leituras são feitas sobre catálogos técnicos PostgreSQL, `information_schema`, `pg_catalog`, `pg_stat_*` e funções de tamanho/estatística.

Para ambientes produtivos, usar uma connection Laravel dedicada com uma role PostgreSQL metadata-only.
