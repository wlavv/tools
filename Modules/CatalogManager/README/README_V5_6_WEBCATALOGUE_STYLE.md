# CatalogManager Complete V5.6 — WebCatalogue Style Loader

Esta versão corrige o problema dos estilos não carregarem.

## Alteração principal

O módulo agora carrega estilos/scripts com:

```blade
@include('catalogmanager::Includes.css')
```

dentro de:

```text
Resources/views/layouts/module.blade.php
```

Isto replica o padrão do módulo WebCatalogue e não depende de:

```blade
@stack('styles')
@stack('scripts')
```

## Inclui

- CSS inline LSG / WebCatalogue-like
- DataTables em todas as listas
- SweetAlert2 para alerts
- Dropzone preparado
- Breadcrumbs próprios
- CRUDs operacionais
- Painéis operacionais e ações pendentes

## Nota

Como os estilos são inline via partial, não é obrigatório publicar assets para a UI aparecer.
