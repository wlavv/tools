# CatalogManager V5.6.2 — Safe Mode + Diagnostics

Esta versão adiciona:

- Logger próprio: `storage/logs/catalog-manager.log`
- Dashboard protegido por `try/catch`
- Página de crash própria
- Rota de diagnóstico independente do layout base:

```text
/catalog-manager/diagnostics
```

## Para testar

1. Substituir o módulo.
2. Limpar cache:

```bash
php artisan optimize:clear
```

3. Abrir:

```text
/catalog-manager/diagnostics
```

Esta página mostra:

- tabelas existentes/em falta
- rotas registadas
- últimas linhas do log próprio
