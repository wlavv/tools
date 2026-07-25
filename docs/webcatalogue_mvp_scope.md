# WebCatalogue — âmbito do MVP e da apresentação

Estado: **Aprovado**

Data: 2026-07-25

## Objetivo do produto

O WebCatalogue é uma plataforma autónoma para criar, gerir, publicar e explorar catálogos digitais de produtos, com integração com lojas online e capacidades diferenciadoras de apresentação visual e reconhecimento de produtos.

## Utilizadores principais

- Gestor de organização ou loja;
- operador de catálogo;
- integrador técnico;
- visitante/cliente final;
- administrador operacional do WebCatalogue.

## Âmbito obrigatório para a entrega

### Produto autónomo

- Backoffice próprio no domínio do WebCatalogue;
- identidade visual, navegação e autenticação próprias;
- isolamento por organização e store;
- papéis e permissões;
- configuração, operação e auditoria sem depender da interface do BO agregador.

### Instalação e configuração de lojas

- criação de organização, store e equipa;
- onboarding guiado;
- branding, idioma, moeda e dados públicos;
- configuração da origem dos produtos;
- criação do primeiro catálogo;
- preview, validação e publicação;
- checklist de ativação e possibilidade de retomar passos incompletos.

### Frontoffice institucional

- landing page que explique e promova o WebCatalogue;
- proposta de valor, funcionalidades e casos de uso;
- demonstração visual;
- integrações suportadas;
- FAQ, contacto, login e chamada à ação;
- páginas legais, privacidade, SEO e responsividade.

### Catálogo público

- página pública da store;
- navegação e pesquisa de catálogo;
- filtros adequados ao tipo de store;
- página de produto;
- preços, imagens e recursos;
- visualizações 3D/AR/VR apenas onde existam ativos e suporte real;
- estados vazios, erros e experiência móvel.

### API pública v1

- documentação OpenAPI;
- autenticação e credenciais por store;
- scopes, rotação e revogação;
- gestão de stores, catálogos, produtos, preços, stock e media;
- publicação e consulta do estado da sincronização;
- paginação, filtros e erros normalizados;
- idempotência, rate limiting e auditoria;
- webhooks assinados, retries e recuperação;
- exemplos, coleção de testes e ambiente sandbox.

### Integrações de e-commerce

- framework comum de conectores;
- WooCommerce;
- Shopify;
- PrestaShop;
- Magento/Adobe Commerce, sujeito à prioridade e tempo disponível;
- instalação e remoção documentadas;
- sincronização inicial e incremental;
- produtos, variantes, preços, stock, categorias e media;
- logs, retries, conflitos e dashboard operacional.

### Reconhecimento visual

- captura através de dispositivo compatível;
- criação e consulta de sessões;
- normalização e análise OpenCV;
- candidatos e resultado;
- tratamento de resultado incorreto ou não encontrado;
- revisão manual no backoffice;
- fingerprints e markers atualizados;
- benchmark final reproduzível;
- rollback operacional documentado.

### Qualidade e validação

- suite automática WebCatalogue funcional;
- validação ponta a ponta em desktop e telemóvel;
- segurança, privacidade e permissões revistas;
- acessibilidade e compatibilidade dos browsers da apresentação;
- processo guiado de testes com utilizadores;
- feedback classificado, corrigido e novamente testado;
- dados e roteiro de demonstração estáveis;
- contingência offline.

## Critérios globais de aceitação

- Um novo gestor consegue criar e publicar uma store através do onboarding.
- Um operador consegue importar, corrigir, organizar e publicar produtos.
- Um integrador consegue ligar uma store custom usando apenas a API documentada.
- Pelo menos um conector prioritário completa instalação, sincronização, webhook, recuperação de erro e remoção numa sandbox.
- Um visitante compreende o produto pela landing e utiliza o catálogo público.
- O reconhecimento visual completa o percurso demonstrado e produz evidência auditável.
- Os testes críticos estão verdes e não existem problemas de severidade S0 em aberto.
- O sistema demonstrado funciona no domínio e backoffice próprios.

## Fora do âmbito obrigatório

Estes itens pertencem ao roadmap pós-MVP, salvo alteração formal:

- OCR completo com PaddleOCR e Tesseract;
- validação visual com Qwen2.5-VL;
- decisão textual por IA;
- dashboard central avançado de todos os serviços de IA;
- automações comerciais avançadas;
- aplicações móveis nativas;
- suporte completo para todas as plataformas de e-commerce;
- encerramento definitivo da VPS antiga antes do período de observação.

## Limitações que podem ser aceites se documentadas

- Magento/Adobe Commerce pode ficar após os três conectores prioritários;
- USDZ/iOS Quick Look pode ficar fora da entrega;
- recursos 3D/AR/VR só são apresentados quando existirem ativos reais;
- funcionalidades de IA futuras não podem ser apresentadas como concluídas;
- resultados de benchmark devem distinguir medições reais de estimativas.

## Decisões ainda necessárias

- ~~domínio principal~~: definido como `ar-webcatalogue.com`;
- ~~subdomínio da API e respetiva documentação~~: definido como `api.ar-webcatalogue.com`;
- ~~subdomínios dos backoffices~~: `studio.ar-webcatalogue.com` para clientes e `control.ar-webcatalogue.com` para administração;
- ~~comportamento de `www`~~: redirecionar para `ar-webcatalogue.com`;
- Data concreta da apresentação, para calcular a data de congelamento funcional.

## Decisões aprovadas

- frontoffice institucional em `ar-webcatalogue.com`;
- uma store por subdomínio no formato `{store_slug}.ar-webcatalogue.com`;
- exemplos iniciais: `prestashop`, `shopify` e `magento`;
- documentação para lojas custom e API pública em `api.ar-webcatalogue.com`, com endpoints em `/v1`;
- BO dos clientes em `studio.ar-webcatalogue.com`;
- BO administrativo interno em `control.ar-webcatalogue.com`;
- criação automática de `{store_slug}.ar-webcatalogue.com` através de wildcard DNS/TLS;
- autenticação própria no MVP, mantendo SSO como evolução futura;
- registo por convite no MVP, deixando self-service para uma fase posterior;
- conectores obrigatórios: PrestaShop, Shopify e WooCommerce;
- Magento/Adobe Commerce após o MVP, salvo disponibilidade;
- demonstração num ambiente staging no domínio real;
- congelamento funcional sete dias antes da apresentação;
- OCR, Qwen e decisão avançada por IA tratados como evolução pós-MVP.
- regras e reservas detalhadas em `docs/webcatalogue_domain_structure.md`.

## Aprovação

- [x] Âmbito obrigatório aprovado;
- [x] itens fora do âmbito aprovados;
- [x] limitações aceitáveis aprovadas;
- [x] decisões em aberto resolvidas ou atribuídas;
- [x] roadmap atualizado de acordo com a aprovação.
