# CatalogManager Complete V5.5 UI Optimized

Correções desta versão:

- Estilos LSG aplicados e otimizados.
- CSS próprio publicado em `public/modules/catalogmanager/css/catalogmanager.css`.
- JS próprio publicado em `public/modules/catalogmanager/js/catalogmanager.js`.
- Todas as listas/tabelas usam DataTables.
- Alertas de sessão e erros usam SweetAlert2.
- Confirmações podem usar `data-catalog-confirm`.
- Uploads preparados com Dropzone.
- Product show inclui área Dropzone preparada para media/uploads.

## Instalação

```bash
php artisan vendor:publish --tag=catalogmanager-assets --force
php artisan optimize:clear
```

Se a tua layout base não tiver `@stack('styles')` e `@stack('scripts')`, adiciona no layout principal.
