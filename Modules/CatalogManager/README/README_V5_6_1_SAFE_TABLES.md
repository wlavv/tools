# CatalogManager V5.6.1 — Safe Tables

Esta versão corrige o erro 500 quando a instalação/migration está parcial.

## Problema corrigido

```text
SQLSTATE[42S02]: Base table or view not found: catalog_prestashop_sync_queue
```

## O que mudou

- Dashboard usa `Schema::hasTable()` antes de consultar tabelas.
- Sync page não quebra se `catalog_prestashop_sync_queue` ainda não existir.
- AI page não quebra se `catalog_ai_generations` ainda não existir.
- Painéis operacionais e painéis de ações pendentes passam a ser tolerantes a tabelas em falta.
- Adicionado helper `Modules\CatalogManager\Support\CatalogTable`.

## Ainda é necessário

Correr as migrations para criar as tabelas:

```bash
php artisan migrate
php artisan optimize:clear
```
