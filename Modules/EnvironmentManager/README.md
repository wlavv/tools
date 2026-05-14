# Environment Manager

Módulo Laravel read-only para consulta de ambiente e configurações, seguindo a mesma lógica estrutural dos módulos existentes em `Modules/*`.

## Estrutura

```txt
EnvironmentManager/
  Config/
  Http/Controllers/
  Providers/
  Resources/views/
  Routes/
  Services/
  Support/
  module.json
```

## O que consulta

- Ficheiro `.env`, quando existir e for legível.
- Runtime env do processo PHP.
- Configuração efetiva do Laravel via `config()`.
- `module.json` e `Config/*.php` de cada módulo em `Modules/*`.
- Opcionalmente, configs de módulos guardadas em tabelas do B.O., se existirem.

## Segurança

O módulo é apenas de consulta. Não existem rotas `POST`, `PUT`, `PATCH` ou `DELETE`.

Valores sensíveis são mascarados automaticamente, incluindo chaves que contenham termos como:

```txt
APP_KEY, SECRET, PASSWORD, TOKEN, PRIVATE_KEY, API_KEY, DATABASE_URL, CONNECTION_STRING
```

## Instalação

Copiar a pasta `EnvironmentManager` para:

```txt
Modules/EnvironmentManager
```

Garantir que o loader de módulos do projeto regista o provider definido em `module.json`:

```json
"provider": "Modules\\EnvironmentManager\\Providers\\EnvironmentManagerServiceProvider"
```

## Rotas

Por defeito:

```txt
GET /environment-manager
GET /environment-manager/env
GET /environment-manager/config
GET /environment-manager/modules
GET /environment-manager/modules/{moduleKey}
GET /environment-manager/effective
```

As rotas usam:

```php
config('environment-manager.middleware', ['web', 'auth'])
config('environment-manager.route_prefix', 'environment-manager')
```

Podes alterar em:

```txt
Modules/EnvironmentManager/Config/config.php
```

## Configs de módulos criados no B.O.

O módulo tenta consultar tabelas comuns como:

```txt
modules
bo_modules
module_configs
module_settings
bo_module_configs
bo_module_settings
```

Se as tabelas ou colunas não existirem, são ignoradas sem erro. A configuração está em `Config/config.php`, na chave `bo_module_configs`.

## Ficheiros B.O.

Inclui os ficheiros usados pelo B.O. nos módulos existentes:

```txt
Config/actions.php
Config/breadcrumbs.php
Config/page_titles.php
Config/ui.php
Resources/lang/en/breadcrumbs.php
Resources/lang/pt/breadcrumbs.php
```
