# ProjectManager

Módulo de gestão de projetos para o Backoffice Laravel com arquitetura modular própria.

## Objetivo
Gerir projetos, identidade gráfica, contactos, redes sociais, estrutura, equipa, documentação e tasks.

## Estrutura resumida
- Config
- Http/Controllers
- Http/Requests
- Models
- Services
- Database/Migrations
- Resources/views
- Routes
- Providers

## Dependências
Sem packages externas de modularização.

## Instalação
Copiar `Modules/ProjectManager` para o projeto e correr:

```bash
composer dump-autoload
php artisan optimize:clear
php artisan modules:list
php artisan modules:check
php artisan migrate --path=Modules/ProjectManager/Database/Migrations
```

## Rota principal
`/project-manager`

## Tabelas
- `wt_projects`
- `wt_todo`

## Notas
- O módulo herda `layouts.app`
- CSS e JS são complementares e neutros
- Não inclui seeder inicial por não ser necessário
