# Module Dependency Map

Módulo Laravel para B.O. que lista os módulos existentes, executa uma verificação de dependências por módulo, guarda os resultados em base de dados e mostra o estado de frescura/risco sem precisar de correr o scanner a cada visualização.

## O que inclui

- Lista de todos os módulos existentes em `base_path('Modules')`.
- Botão `Run` por módulo.
- Botão opcional `Run all`.
- Persistência em `module_dependency_scans` e `module_dependencies`.
- View de detalhe com dependências usadas pelo módulo.
- View de detalhe com módulos impactados por alterações nesse módulo.
- Cores por data do último scan com sucesso:
  - sem background: nunca testado;
  - verde: verificado hoje;
  - amarelo: verificado nos últimos 15 dias;
  - vermelho: verificado há mais de 15 dias.
- Health status baseado em risco de alteração:
  - `unknown`, `healthy`, `warning`, `risky`, `critical`.
- Comando Artisan: `php artisan module-dependency-map:scan {module?} {--all}`.

## Instalação

1. Copiar a pasta para:

```text
Modules/ModuleDependencyMap
```

2. Garantir que o autoload do projeto reconhece `Modules\\`.

No `composer.json` principal do B.O., normalmente deverá existir algo equivalente a:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "Modules/"
    }
}
```

Depois executar:

```bash
composer dump-autoload
```

3. Registar o provider, se o teu loader de módulos não o fizer automaticamente:

```php
Modules\ModuleDependencyMap\Providers\ModuleDependencyMapServiceProvider::class,
```

4. Executar as migrations:

```bash
php artisan migrate
```

5. Aceder ao módulo:

```text
/module-dependency-map
```

## Configuração

O ficheiro de configuração está em:

```text
Modules/ModuleDependencyMap/Config/module-dependency-map.php
```

Opcionalmente, podes publicar para `config/module-dependency-map.php`:

```bash
php artisan vendor:publish --tag=module-dependency-map-config
```

Campos principais:

- `modules_path`: diretoria onde estão os módulos.
- `namespace_prefix`: prefixo PHP usado pelos módulos. Por defeito: `Modules`.
- `fresh_days`: número de dias para considerar um scan recente. Por defeito: `15`.
- `critical_modules`: módulos críticos que aumentam o risco quando são dependências.
- `ignored_modules`: módulos que não devem aparecer no mapa.
- `layout`: layout Blade usado no B.O.
- `middleware`: middlewares da rota.

## Como o scanner deteta dependências

O scanner percorre os ficheiros configurados, por defeito `*.php`, dentro do módulo de origem e procura referências a namespaces de outros módulos:

```text
Modules\OutroModulo\...
```

Cada evidência é guardada com:

- módulo de origem;
- módulo de destino;
- tipo de dependência;
- ficheiro;
- linha;
- referência encontrada;
- hash único da evidência;
- data de primeira e última deteção.

## Health status

O risco é calculado com base em:

- número de dependências diretas;
- número de módulos que dependem deste módulo;
- dependências circulares;
- dependências para módulos críticos;
- dependências cujo módulo alvo está sem scan recente.

A fórmula e os thresholds podem ser ajustados no ficheiro de configuração.

## Nota de integração

O módulo não cria foreign keys para a tabela `users`, para ser mais portátil entre B.O.s. O campo `triggered_by` guarda apenas o ID do utilizador autenticado quando disponível.
