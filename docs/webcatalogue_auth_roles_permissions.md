# WebCatalogue — autenticação, papéis e permissões

Data: 2026-07-25

Estado: **Aprovado**

## Princípios

- autenticação própria do WebCatalogue no MVP;
- registo apenas por convite;
- menor privilégio por defeito;
- separação entre clientes, administração interna, API e público;
- autorização aplicada no servidor por policies;
- nenhuma autorização baseada apenas em esconder menus ou botões;
- MFA obrigatório para administração interna;
- SSO preparado como evolução, mas fora do MVP.

## Superfícies de acesso

| Superfície | Endereço | Autenticação |
|---|---|---|
| Frontoffice institucional | `ar-webcatalogue.com` | Pública |
| Catálogo da store | `{store}.ar-webcatalogue.com` | Pública, salvo catálogos privados |
| BO dos clientes | `studio.ar-webcatalogue.com` | Sessão própria do WebCatalogue |
| BO administrativo | `control.ar-webcatalogue.com` | Sessão administrativa + MFA |
| Documentação da API | `api.ar-webcatalogue.com` | Pública |
| API v1 | `api.ar-webcatalogue.com/v1` | Credencial por store |

## Contas de utilizador

Tabela base: `users`, adaptada para o produto autónomo.

Campos essenciais:

- `id`;
- `public_id` ULID/UUID;
- `name`;
- `email`, único e normalizado;
- `password`;
- `email_verified_at`;
- `status`: `invited`, `active`, `suspended`, `closed`;
- `last_login_at`;
- `last_login_ip`;
- `password_changed_at`;
- `mfa_enabled_at`;
- timestamps e soft delete.

Não guardar passwords, tokens de recuperação ou segredos MFA em claro.

## Fluxos de autenticação

### Convite

1. Owner/admin indica email, organização, stores e papel.
2. Sistema cria convite com token aleatório, hash persistido e validade limitada.
3. Destinatário recebe link de uso único.
4. Define nome e password e verifica o email.
5. Membership passa de `invited` para `active`.
6. Token é invalidado e o evento fica auditado.

Convites devem permitir revogação, reenvio com rate limit e expiração.

### Login no studio

1. Email e password.
2. Rate limit por IP e conta.
3. Seleção da organização quando existir mais de uma.
4. Seleção da store entre acessos autorizados.
5. Regeneração do ID de sessão.
6. Registo de login e atualização do último acesso.

### Login no control

- tabela de utilizadores comum, mas papel interno separado;
- MFA obrigatório;
- sessão e cookie separados do `studio`;
- timeout de inatividade mais curto;
- ações cross-tenant auditadas com motivo quando forem sensíveis.

### Recuperação

- token de uso único e curta duração;
- resposta indistinguível para emails existentes/inexistentes;
- invalidação das sessões existentes após alteração de password;
- notificação de alteração;
- rate limiting.

## Sessões e cookies

### Studio

```text
Nome: wc_studio_session
Domain: studio.ar-webcatalogue.com
Secure: true
HttpOnly: true
SameSite: Lax
```

### Control

```text
Nome: wc_control_session
Domain: control.ar-webcatalogue.com
Secure: true
HttpOnly: true
SameSite: Strict ou Lax quando necessário
```

Não usar `.ar-webcatalogue.com` como domínio comum do cookie. Stores públicas, API, studio e control não devem partilhar sessão.

## Papéis de organização

### Owner

- controlo total da organização;
- gerir administradores e memberships;
- criar, suspender e eliminar stores;
- configurações da organização;
- credenciais e integrações de todas as stores;
- faturação futura;
- transferir ownership;
- encerrar organização.

Deve existir sempre pelo menos um owner ativo.

### Admin

- gerir stores;
- convidar e gerir members, exceto transferir ownership;
- configurar integrações;
- consultar auditoria da organização;
- acesso operacional a todas as stores quando `store_access=all`.

### Billing

- consultar e gerir faturação futura;
- sem acesso a produtos, reconhecimento ou credenciais por defeito.

### Member

- acesso apenas às stores e papéis explicitamente atribuídos.

## Papéis de store

### Manager

- configuração da store;
- membros da store;
- catálogos, produtos, recursos e preços;
- integrações e credenciais;
- preview, publicação e remoção de publicação;
- reconhecimento e relatórios.

### Editor

- catálogos, produtos, recursos, preços e promoções;
- imports;
- preview;
- sem publicação final;
- sem membros, credenciais ou configurações críticas.

### Reviewer

- sessões de reconhecimento;
- associação manual;
- ground truth;
- leads;
- benchmarks;
- consulta de produtos necessária à revisão.

### Integrator

- documentação privada/sandbox;
- conectores;
- credenciais API;
- webhooks;
- sincronizações, logs e retries;
- consulta limitada dos dados sincronizados;
- sem gestão de equipa ou publicação manual.

### Viewer

- consulta do BO;
- relatórios permitidos;
- sem alterações.

## Matriz de permissões

Legenda: `✓` permitido, `L` leitura, `—` não permitido.

| Ação | Owner | Admin | Manager | Editor | Reviewer | Integrator | Viewer |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Gerir organização | ✓ | L | — | — | — | — | — |
| Transferir ownership | ✓ | — | — | — | — | — | — |
| Criar/suspender stores | ✓ | ✓ | — | — | — | — | — |
| Gerir membros da organização | ✓ | ✓ | — | — | — | — | — |
| Configurar store | ✓ | ✓ | ✓ | L | L | L | L |
| Gerir membros da store | ✓ | ✓ | ✓ | — | — | — | — |
| Gerir produtos/catálogos | ✓ | ✓ | ✓ | ✓ | L | L | L |
| Gerir recursos/preços | ✓ | ✓ | ✓ | ✓ | L | L | L |
| Importar dados | ✓ | ✓ | ✓ | ✓ | — | ✓ | L |
| Preview | ✓ | ✓ | ✓ | ✓ | L | L | L |
| Publicar/unpublish | ✓ | ✓ | ✓ | — | — | — | — |
| Rever reconhecimento | ✓ | ✓ | ✓ | L | ✓ | L | L |
| Guardar ground truth | ✓ | ✓ | ✓ | — | ✓ | — | — |
| Gerir leads | ✓ | ✓ | ✓ | — | ✓ | — | L |
| Executar benchmarks | ✓ | ✓ | ✓ | — | ✓ | — | L |
| Gerir conectores | ✓ | ✓ | ✓ | — | — | ✓ | L |
| Criar/revogar API clients | ✓ | ✓ | ✓ | — | — | ✓ | — |
| Repetir sincronização | ✓ | ✓ | ✓ | — | — | ✓ | L |
| Consultar auditoria | ✓ | ✓ | L | — | L | L | — |

Owner/admin só recebem acesso às stores de acordo com `store_access`. O papel de organização não deve contornar uma restrição explícita sem policy.

## Administração interna

Papéis exclusivos do `control`:

- `platform_owner`: configuração global e gestão de administradores;
- `platform_admin`: clientes, stores e operação global;
- `support_agent`: suporte com acesso temporário e auditado;
- `security_auditor`: leitura de segurança/auditoria;
- `operations`: saúde, filas, integrações e incidentes.

O suporte não deve abrir dados de uma store sem:

- ticket/motivo;
- duração limitada;
- evento de auditoria;
- indicação visual de impersonation/suporte;
- impossibilidade de ver secrets existentes.

## Policies e abilities

Abilities iniciais:

```text
organization.view
organization.manage
organization.members.manage
organization.ownership.transfer
store.view
store.manage
store.members.manage
catalogue.manage
product.manage
resource.manage
pricing.manage
import.run
publish.preview
publish.execute
recognition.review
recognition.ground_truth
recognition.benchmark
lead.manage
integration.manage
api_client.manage
sync.retry
audit.view
```

Cada policy recebe o utilizador, o recurso e o `TenantContext`. Não autorizar apenas pelo nome da rota.

## API

- secrets apresentados apenas uma vez;
- guardar apenas hash;
- scopes mínimos por client;
- rotação com período de sobreposição controlado;
- revogação imediata;
- expiração opcional/obrigatória conforme risco;
- rate limit por client e store;
- logs sem secret;
- autenticação de webhooks por assinatura e timestamp.

Scopes iniciais:

```text
stores:read
catalogues:read
catalogues:write
products:read
products:write
prices:read
prices:write
stock:read
stock:write
media:read
media:write
publish:read
publish:write
sync:read
webhooks:manage
```

## Segurança

- Argon2id ou bcrypt com custo adequado;
- CSRF em studio/control;
- proteção contra session fixation;
- rate limits em login, recuperação, convites e MFA;
- confirmação de password para ações críticas;
- MFA obrigatório em control e recomendado para owner;
- sessões revogáveis por dispositivo;
- alertas para login suspeito e alteração de credenciais;
- secrets fora do Git e cifrados quando reversibilidade for indispensável;
- auditoria append-only para ações críticas.

## SSO futuro

Preparar tabela de identidades externas:

```text
user_id
provider
provider_subject
organization_id
metadata
```

Não implementar SSO no MVP. A conta local continua a ser a identidade principal até existir requisito empresarial.

## Testes obrigatórios

- utilizador sem membership recebe 403/404;
- papel não autorizado não altera dados;
- editor não publica;
- reviewer não edita produtos;
- integrator não gere membros;
- viewer não faz writes;
- owner não remove o último owner;
- sessão studio não autentica em control;
- sessão control não é enviada para stores públicas;
- MFA é exigido em control;
- convite expirado/revogado não funciona;
- API client não sai da store nem excede scopes;
- suporte cross-tenant exige motivo e gera auditoria.

## Decisões propostas para aprovação

- [x] autenticação local própria no MVP;
- [x] acesso ao studio apenas por convite;
- [x] MFA obrigatório no control;
- [x] MFA recomendado para owners;
- [x] cookies separados por subdomínio;
- [x] roles de organização: owner, admin, billing e member;
- [x] roles de store: manager, editor, reviewer, integrator e viewer;
- [x] papéis internos separados dos papéis de clientes;
- [x] autorização através de policies e abilities;
- [x] credenciais API com scopes e limitadas a uma store;
- [x] SSO apenas como evolução futura.

Modelo aprovado pelo responsável do projeto em 2026-07-25.
