# AssetLibrary module

Módulo simples para gestão centralizada de assets do WebCatalogue.

## Inclui
- listagem com pesquisa e filtros
- upload de ficheiros
- preview básico
- detalhe
- edição
- tabela `wt_assets`

## Layout
O módulo **herda o layout global do projeto**.  
Não define tema claro/escuro próprio.  
Os estilos do módulo são mínimos e neutros para respeitar o teu backoffice dark.

## Rotas
Prefixo:
- `/asset-library`

Names:
- `asset_library.index`
- `asset_library.create`
- `asset_library.store`
- `asset_library.show`
- `asset_library.edit`
- `asset_library.update`
- `asset_library.destroy`
- `asset_library.download`

## Storage
Usa o disk `public`.

Se ainda não estiver criado:
```bash
php artisan storage:link
```

## Migration
```bash
php artisan migrate --path=Modules/AssetLibrary/Database/Migrations
```
