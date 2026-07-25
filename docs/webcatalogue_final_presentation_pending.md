# WebCatalogue — pendentes para a apresentação final

Data da auditoria: 2026-07-25

## Critério de prioridade

- **P0 — bloqueador:** impede demonstrar ou defender o estado atual com confiança.
- **P1 — necessário:** deve ficar fechado antes da apresentação.
- **P2 — desejável:** melhora a apresentação, mas pode ser declarado como limitação.
- **Futuro:** não faz parte do fecho do MVP; deve aparecer no roadmap.

## P0 — bloqueadores

- [ ] **Separar o WebCatalogue do backoffice agregador atual.**
  - Criar um backoffice próprio, servido no domínio/subdomínio do WebCatalogue, com layout, navegação, autenticação e identidade visual próprios.
  - Manter a separação lógica entre o WebCatalogue e os restantes projetos, sem duplicar desnecessariamente serviços partilhados.
  - Definir estratégia de utilizadores: autenticação própria, SSO ou federação com o BO atual.
  - Rever rotas, middleware, permissões, menus, assets, links, emails e redirecionamentos para que não dependam da shell do BO agregador.
  - Preparar configuração por ambiente, TLS, cookies/sessões, CORS, filas, storage e deploy no novo domínio.
  - Critério de fecho: um utilizador entra diretamente no domínio WebCatalogue, autentica-se e realiza todas as tarefas do projeto sem navegar pelo BO agregador.

- [ ] **Criar o frontoffice institucional/comercial do WebCatalogue.**
  - Landing page pública que explique o problema, a proposta de valor, o funcionamento e os principais casos de uso.
  - Incluir demonstração visual, funcionalidades, integrações, segurança/privacidade, perguntas frequentes e chamada à ação.
  - Distinguir claramente a landing institucional dos catálogos públicos gerados para cada loja.
  - Definir os percursos “Pedir demonstração”, “Experimentar”, “Entrar” e “Integrar”, incluindo formulários e tratamento dos contactos.
  - Garantir responsividade, acessibilidade, SEO, analytics consentido, política de privacidade e páginas legais.
  - Critério de fecho: um visitante sem contexto compreende o produto, vê como funciona e consegue iniciar o próximo passo sem entrar no backoffice.

- [ ] **Criar a base da API pública de integração.**
  - Definir versão inicial (`v1`), convenções, autenticação, autorização por store, rate limits e política de compatibilidade.
  - Cobrir inicialmente stores, catálogos, produtos, preços, stock, media/recursos, publicação e estado de sincronização.
  - Incluir importação incremental, idempotência, paginação, filtros, erros normalizados e webhooks.
  - Criar credenciais por integração com rotação/revogação, scopes e auditoria.
  - Publicar especificação OpenAPI, exemplos, coleção de testes e ambiente sandbox.
  - Critério de fecho: uma loja custom consegue criar/atualizar um produto, associá-lo a um catálogo, publicar recursos e receber o resultado da sincronização usando apenas a documentação pública.

- [ ] **Criar módulos de integração com plataformas de e-commerce.**
  - Construir um núcleo comum de conectores para evitar regras duplicadas entre plataformas.
  - Primeira prioridade proposta: WooCommerce, Shopify, PrestaShop e Magento/Adobe Commerce; confirmar a ordem com base nos utilizadores-alvo.
  - Para cada conector, definir autenticação, mapeamento de produtos/variantes, preços, stock, categorias, media, idiomas, impostos e estados.
  - Suportar sincronização inicial, incremental, manual e agendada, com retries, idempotência, logs e resolução de conflitos.
  - Definir claramente a origem de verdade por campo e o comportamento em eliminações/arquivamentos.
  - Criar dashboard de estado, último sync, erros e ações de recuperação.
  - Critério de fecho por conector: instalação documentada, importação inicial, atualização incremental, webhook, recuperação de erro e desinstalação segura validados numa store sandbox.

- [ ] **Criar um processo guiado de teste da plataforma por utilizadores reais.**
  - Definir perfis: gestor de loja, operador de catálogo, integrador técnico e visitante/cliente final.
  - Criar tarefas orientadas, sem ensinar antecipadamente a solução, para medir se os fluxos são compreendidos.
  - Recolher consentimento, sucesso por tarefa, tempo, erros, abandonos, classificação de facilidade e comentário aberto.
  - Incluir observação do primeiro acesso, configuração da store, importação, publicação, consulta pública, scan/reconhecimento e resolução de um erro.
  - Registar feedback de forma estruturada e associá-lo à versão testada, dispositivo e perfil, sem recolher dados pessoais desnecessários.
  - Fechar cada ciclo com triagem por severidade, decisão, correção e novo teste.
  - Critério de fecho: piloto completo com utilizadores representativos, relatório de resultados e backlog priorizado por evidência.

- [ ] **Consolidar o trabalho local ainda não versionado.**
  - Existem alterações em WebCatalogue, SiteManager, OpenCV, vistas públicas e benchmarks, além de ficheiros novos.
  - Rever o diff funcional, remover artefactos gerados (`__pycache__`/`.pyc`), separar commits por tema e garantir que o repositório fica limpo.
  - Critério de fecho: `git status` limpo e commits identificáveis/reversíveis.

- [ ] **Corrigir a configuração CSRF da suite WebCatalogue e voltar a executá-la.**
  - Com o MySQL ativo, os quatro testes executam, mas todos os pedidos POST/DELETE falham com HTTP 419 antes de validarem o comportamento esperado.
  - Rever o ambiente `testing`, o middleware CSRF aplicado e a forma como os pedidos são construídos nos testes.
  - Critério de fecho: base de dados de teste isolada e `php artisan test --filter=WebCatalogue` sem falhas.

- [ ] **Validar ponta a ponta o percurso que será demonstrado.**
  - Loja pública → catálogo/filtros → produto → galeria/recursos/3D/AR/VR → scan → resultado → revisão no backoffice.
  - Incluir publicação, preview e remoção de publicação.
  - Critério de fecho: roteiro completo executado numa sessão limpa, em desktop e telemóvel, sem erros de consola ou servidor.

- [ ] **Fechar o estado do serviço OpenCV usado na apresentação.**
  - Confirmar ambiente efetivo (VPS antiga, Rise-S base ou Rise-S incremental), URL, token, health check e rollback.
  - Não expor segredos, IPs internos ou tokens nos materiais.
  - Critério de fecho: `/health` e uma chamada real funcionam; ambiente e rollback estão documentados.

- [ ] **Executar e guardar um benchmark final reproduzível.**
  - A documentação atual ainda assinala a fase P5 como pendente de benchmark online após redeploy.
  - Medir pelo menos top-1, top-3, falsos positivos e latência sobre um dataset fechado com ground truth.
  - Critério de fecho: relatório final identifica versão/pipeline, dataset, data, ambiente e resultados medidos.

## P1 — necessários

- [ ] **Definir formalmente o âmbito apresentado.**
  - Separar “MVP concluído”, “limitação conhecida” e “trabalho futuro”.
  - OCR, Qwen2.5-VL e decisão textual por IA devem ficar no roadmap futuro, salvo se forem requisitos explícitos da avaliação.

- [ ] **Definir arquitetura e plano de transição para o produto autónomo.**
  - Inventariar dependências atuais do WebCatalogue em `App`, SiteManager, permissões, utilizadores, layouts, notificações e ferramentas centrais.
  - Classificar cada dependência como partilhada, extraída para pacote/serviço ou substituída no WebCatalogue.
  - Planear a migração por etapas para manter o sistema utilizável durante a separação.

- [ ] **Definir modelo multi-tenant e onboarding.**
  - Formalizar isolamento por organização/store, papéis, convites, limites, credenciais de integração e ciclo de vida da conta.
  - Criar onboarding guiado: conta → store → origem de dados → primeiro catálogo → preview → publicação.

- [ ] **Rever as alterações recentes do front público.**
  - Validar nova homepage TCG, página de produtos da loja, filtros, responsividade, imagens promocionais e estados sem resultados.
  - Confirmar que outras lojas não-TCG não sofreram regressões.

- [ ] **Rever as alterações recentes do benchmark.**
  - Validar os três fluxos `legacy`, `rise_s_base` e `rise_s_incremental`.
  - Garantir que o baseline fica congelado e que cada resultado mostra claramente fluxo, versão e etapa incremental.

- [ ] **Fechar a integração WebCatalogue–SiteManager.**
  - O conteúdo simples de sites e as rotas/vistas associadas estão ainda por versionar.
  - Confirmar autorização, edição, persistência, apresentação pública e comportamento quando não há conteúdo.

- [ ] **Eliminar ou classificar placeholders visíveis.**
  - Ainda existem uma vista pública antiga de catálogo, CSS/JS de viewer/AR/VR e componentes marcados como placeholder.
  - Confirmar quais são código morto, fallback deliberado ou funcionalidade incompleta.
  - Remover do percurso de demonstração tudo o que não represente funcionalidade real.

- [ ] **Validar geração e visualização 3D.**
  - O modo mock gera um cubo placeholder e o suporte USDZ/iOS aparece como trabalho posterior.
  - Usar Meshy/ativos reais na demo ou declarar explicitamente a limitação.

- [ ] **Verificar segurança e privacidade.**
  - Rever permissões do backoffice, validação de uploads, exposição de ficheiros, rate limiting do scan e retenção de capturas.
  - Confirmar que logs e relatórios não contêm tokens, dados pessoais ou imagens indevidas.

- [ ] **Preparar dados de demonstração estáveis.**
  - Loja, catálogo, produtos, preços, recursos, marcadores/fingerprints e scans conhecidos.
  - Evitar depender de dados mutáveis de produção durante a apresentação.

- [ ] **Preparar contingência offline.**
  - Guardar screenshots/vídeo curto e resultados de benchmark para o caso de falha de rede, câmara, Gateway ou OpenCV.

## P2 — desejáveis

- [ ] Aumentar a cobertura de testes para filtros públicos, páginas de loja/produto, permissões, erro do OpenCV e benchmark multi-flow.
- [ ] Executar revisão de acessibilidade e compatibilidade nos browsers/dispositivos usados na apresentação.
- [ ] Rever textos, idioma, terminologia, estados vazios e mensagens de erro.
- [ ] Confirmar tempos de carregamento e otimização das imagens/ativos da homepage e galerias.
- [ ] Documentar instalação, migrações, filas/scheduler, build de assets, deploy, health checks e rotina pós-deploy.
- [ ] Alinhar README, tese, diagramas e screenshots com o comportamento efetivamente demonstrado.

## Roadmap futuro — não bloquear o MVP

Os seguintes blocos do roadmap existente devem ser apresentados como evolução, salvo alteração formal do âmbito:

- OCR com PaddleOCR e Tesseract;
- validação visual com Qwen2.5-VL;
- endpoint de decisão por IA e respetivos guardrails;
- auditoria avançada de OCR/IA no backoffice;
- alertas e health dashboard completo;
- encerramento definitivo da VPS antiga, após período de observação.

## Verificações já realizadas nesta auditoria

- Os ficheiros PHP alterados passam a verificação de sintaxe.
- `git diff --check` não reportou erros de whitespace.
- A branch local está dois commits à frente de `origin/main`.
- Existem alterações locais relevantes ainda não consolidadas.
- Com o MySQL ativo, a suite WebCatalogue executa quatro testes; os quatro falham com HTTP 419/CSRF (4 asserções falhadas).
- O roadmap técnico contém 20 itens concluídos, 52 pendentes e 1 em curso; esta checklist separa os pendentes de apresentação das evoluções futuras.
