# WebCatalogue — inventário de dependências do BO agregador

Data: 2026-07-25

Estado: **Concluído**

## Resumo

O núcleo funcional do WebCatalogue está concentrado em `Modules/WebCatalogue` e pode ser destacado para um produto autónomo. A dependência mais forte não está nos modelos ou serviços de catálogo: está na shell administrativa do projeto atual.

Indicadores:

- 27 controllers WebCatalogue estendem `App\Http\Controllers\Controller`;
- 45 views administrativas estendem `layouts.app`;
- 7 views públicas usam o layout próprio `webcatalogue::front.layouts.app`;
- 33 modelos, 17 migrações e 94 views estão dentro do módulo;
- apenas uma dependência direta noutro módulo funcional: importação MTG temporária;
- três fluxos usam a função global `notifications_send`;
- autenticação e autorização dependem do middleware e PermissionRoleManager do BO agregador.

## Mapa de dependências

| Área | Dependência atual | Impacto na separação | Decisão recomendada |
|---|---|---:|---|
| Controllers | `App\Http\Controllers\Controller` | Alto | Criar controllers base próprios para `studio`, `control`, API e front público |
| Layout administrativo | `layouts.app` | Alto | Substituir por layouts WebCatalogue autónomos |
| Ações de página | `App\Support\Actions\ActionResolver` | Alto | Extrair comportamento necessário para a shell WebCatalogue |
| Breadcrumbs | `App\Support\Breadcrumbs\BreadcrumbRegistry` | Médio | Criar registry próprio ou navegação explícita |
| Títulos | resolução global/module config | Baixo | Manter configs dentro do WebCatalogue |
| Autenticação | middleware `auth` e modelo de utilizador central | Alto | Implementar autenticação própria e migrar/associar utilizadores |
| Autorização | `EnsureRoutePermission` e PermissionRoleManager | Alto | Criar roles/policies WebCatalogue por organização/store |
| Descoberta do módulo | `ModulesServiceProvider`, `ModuleManager`, `module.json` | Alto | Registar provider diretamente no produto autónomo |
| Menu agregador | metadata em `module.json` | Médio | Substituir por navegação `studio` e `control` |
| Notificações | helper global `notifications_send` | Médio | Introduzir contrato WebCatalogue e adapter Laravel Notifications |
| Utilizador em auditoria | `auth()->id()` em imports, publish, benchmark e review | Médio | Preservar IDs através de uma camada de actor/audit |
| Filas | Laravel Queue/ShouldQueue | Baixo | Manter Laravel Queue com configuração própria |
| Scheduler | Laravel Schedule | Baixo | Manter no provider/console kernel autónomo |
| Storage | disk Laravel `public` e paths WebCatalogue | Médio | Criar disk dedicado e política por tenant |
| Base de dados | tabelas `wc_*`, modelos e migrações no módulo | Baixo | Migrar como schema do produto e acrescentar tenant ownership |
| Sessões/cache | configuração global Laravel | Médio | Isolar cookies, sessão e cache em `studio`/`control` |
| Assets | assets em `public/modules/webcatalogue` | Baixo | Mover para pipeline Vite/CDN próprio |
| OpenCV | serviço HTTP externo | Baixo | Manter contrato e secrets próprios |
| Meshy | API externa | Baixo | Manter como provider opcional |
| IA central | Gateway LSG em funcionalidades futuras | Médio | Manter por interface; não acoplar o MVP ao BO agregador |
| MTG | `ImportTcgCollectorsSetCommand` | Alto e desnecessário | Remover endpoint temporário e transformar dados demo em import normal |
| SiteManager | nenhuma dependência direta ativa | Nenhum | Não incluir no produto autónomo |

## Dependências críticas

### 1. Controller global

O controller atual fornece:

- middleware `auth`;
- page titles;
- breadcrumbs;
- ações CRUD;
- dados partilhados com o layout;
- filtragem de ações através do PermissionRoleManager.

Não deve ser copiado integralmente. O produto autónomo deve ter:

- `StudioController` para clientes;
- `ControlController` para administração interna;
- `PublicController` sem autenticação administrativa;
- `ApiController` com respostas JSON e autenticação própria.

### 2. Layout e componentes do BO

As 45 views administrativas dependem de `layouts.app` e, por consequência, de:

- navegação do agregador;
- componentes e estilos globais;
- breadcrumbs e ações partilhadas;
- sessão/autenticação do projeto atual;
- convenções de permissões por rota.

Estas views devem ser migradas progressivamente para:

```text
webcatalogue::studio.layouts.app
webcatalogue::control.layouts.app
```

O front público já possui layout próprio e representa o ponto de partida mais desacoplado.

### 3. Autenticação, tenancy e autorização

O modelo atual usa autenticação Laravel global e guarda IDs do utilizador em:

- benchmarks;
- imports;
- publicação;
- revisão e ground truth;
- criação de produtos a partir de reconhecimento.

O novo modelo deve associar cada utilizador a organizações/stores e aplicar scopes em queries, policies e jobs. A resolução do tenant não pode depender de um `store_id` enviado pelo browser.

### 4. PermissionRoleManager

O middleware web global aplica `EnsureRoutePermission`, que depende de:

```text
Modules\PermissionRoleManager\Services\RoutePermissionAccessService
```

Para o produto autónomo, substituir por:

- roles WebCatalogue;
- policies Laravel;
- scopes por organização/store;
- permissões distintas para `studio` e `control`.

### 5. Notificações

Três áreas usam `notifications_send`:

- conclusão de geração 3D;
- produto reconhecido;
- lead de produto não encontrado.

Criar primeiro um contrato, por exemplo:

```text
WebCatalogueNotificationSender
```

Durante a transição, um adapter pode chamar o helper atual. No produto autónomo, o contrato usa Laravel Notifications e canais configurados pelo WebCatalogue.

### 6. Descoberta e arranque

O módulo é carregado pelo `ModulesServiceProvider` através de `module.json`. O produto autónomo deve:

- registar `WebCatalogueServiceProvider` diretamente;
- declarar o namespace `Modules\WebCatalogue` no autoload de produção, ou migrá-lo para `App\WebCatalogue`;
- carregar rotas, views, traduções, migrações, comandos e scheduler sem o ModuleManager;
- substituir metadata de menu por configuração própria da aplicação.

## Dependência temporária a remover

Existe uma rota pública temporária:

```text
/webcatalogue/temp/seed/tcg-collectors-mirrodin
```

Ela invoca diretamente:

```text
Modules\Mtg\Console\ImportTcgCollectorsSetCommand
```

Esta rota é incompatível com a autonomia e aumenta a superfície de ataque. Deve ser removida. Se os dados forem necessários para demonstração, usar um seeder/importador WebCatalogue executado apenas por CLI.

## Componentes que podem ser mantidos

- modelos e migrações `wc_*`;
- serviços de catálogo, publicação, pricing, imports, viewer e reconhecimento;
- jobs e comandos WebCatalogue;
- layout e views do front público;
- configuração própria do módulo;
- cliente OpenCV e microserviço;
- providers 3D;
- testes WebCatalogue;
- Laravel Queue, Scheduler, Storage, Cache e HTTP Client.

## Ordem recomendada de desacoplamento

1. Remover a rota temporária e dependência MTG.
2. Criar layouts e controllers base próprios.
3. Migrar o BO de clientes para `studio`.
4. Criar autenticação própria e modelo organização/store.
5. Substituir PermissionRoleManager por roles e policies.
6. Introduzir contratos para notificações e auditoria.
7. Registar o provider sem ModuleManager.
8. Isolar storage, filas, cache, sessão e configuração.
9. Criar o BO `control`.
10. Retirar as rotas WebCatalogue do BO agregador.

## Critério de conclusão do inventário

- [x] controllers e layouts identificados;
- [x] autenticação e permissões identificadas;
- [x] serviços globais e notificações identificados;
- [x] módulos externos identificados;
- [x] storage, filas, scheduler e base de dados classificados;
- [x] estratégia inicial de substituir/partilhar/extrair definida.
