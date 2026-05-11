# CatalogManager Complete V5.4 LSG CRUD

Correções principais:

- Layout LSG interno aplicado ao módulo.
- Breadcrumbs próprios do módulo, resolvidos via `Config/breadcrumbs.php`.
- Actions próprias do módulo, resolvidas via `Config/actions.php`.
- CRUDs básicos completos:
  - Products
  - Manufacturers / Brands
  - Suppliers
  - Stores
  - Store Categories
- Painéis operacionais e painéis de ações pendentes.
- Migration idempotente para evitar falhas quando a instalação anterior ficou parcial.

## Instalação

1. Copiar `CatalogManager` para `Modules/CatalogManager`.
2. Publicar CSS:

```bash
php artisan vendor:publish --tag=catalogmanager-assets --force
```

3. Correr migrations:

```bash
php artisan migrate
```

4. Limpar cache:

```bash
php artisan optimize:clear
```

## Nota

A sync PrestaShop continua segura/fila. Não altera tabelas `ps_*` automaticamente.
