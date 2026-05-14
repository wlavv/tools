<?php

return [
    'admin' => [
        'title' => 'Admin',
        'subtitle' => 'Roadmap de ferramentas administrativas, controlo interno, processos e suporte transversal.',
        'icon' => 'fa-solid fa-people-roof',
        'topics' => [
            [
                'title' => 'Controlo e gestao',
                'items' => [
                    'Dashboard Central - KPIs de todos os departamentos em tempo real.',
                    'Gestor de Reunioes - agendamento automatico, atas por IA e follow-ups.',
                    'Arquivo Digital Inteligente - OCR, tags automaticas e pesquisa documental.',
                    'Gestor de Contratos - criacao, assinatura digital, renovacoes e expiracoes.',
                    'Onboarding Automatico - fluxo digital para entrada de colaboradores.',
                ],
            ],
            [
                'title' => 'Acessos e comunicacao',
                'items' => [
                    'Gestor de Acessos - permissoes por departamento, funcao e ferramenta.',
                    'Portal de Comunicacao Interna - anuncios, politicas, manuais e updates.',
                    'Gestor de Tarefas Transversal - tarefas entre departamentos e acompanhamento.',
                    'Relatorios Automaticos - relatorios semanais/mensais consolidados.',
                    'Calendario Corporativo - feriados, eventos, prazos legais e datas criticas.',
                ],
            ],
            [
                'title' => 'Operacao e custos',
                'items' => [
                    'Gestor de Fornecedores - avaliacoes, contratos e historico.',
                    'Controlo de Custos Operacionais - despesas fixas/variaveis por centro de custo.',
                    'Workflow de Aprovacoes - pedidos, compras, viagens e autorizacoes.',
                    'Gestor de Seguros e Licencas - renovacoes obrigatorias e alertas.',
                    'Auditoria Interna Digital - checklists e relatorios de conformidade.',
                ],
            ],
            [
                'title' => 'Ativos e melhoria',
                'items' => [
                    'Registo de Ativos - equipamentos, viaturas e propriedades.',
                    'Assistente IA para Admin - respostas automaticas a pedidos internos.',
                    'Gestor de Viagens e Despesas - reservas, reembolsos e politicas.',
                    'Monitor de Prazos Legais - alertas fiscais, laborais e regulatorios.',
                    'Sistema de Feedback Interno - sugestoes e satisfacao dos colaboradores.',
                ],
            ],
        ],
    ],
    'webmaster' => [
        'title' => 'Webmaster',
        'subtitle' => 'Roadmap de ferramentas para performance, seguranca, deploy, SEO tecnico e gestao multi-loja.',
        'icon' => 'fa-solid fa-code',
        'topics' => [
            [
                'title' => 'Performance e operacao',
                'items' => [
                    'Monitor de Performance - velocidade, uptime e Core Web Vitals por loja.',
                    'Gestor Multi-loja - painel unico para instancias WooCommerce/WordPress.',
                    'Deploy Automatico - pipeline staging para producao com rollback.',
                    'Gestor de Backups - backups diarios e restore com 1 clique.',
                    'Monitor de Erros - alertas de 404, 500 e quebras de layout.',
                ],
            ],
            [
                'title' => 'SEO e infraestrutura',
                'items' => [
                    'Gestor de Plugins/Extensoes - versoes, compatibilidade e updates.',
                    'Auditoria SEO Tecnico - crawler automatico por loja.',
                    'Gestor de DNS e Dominios - expiracoes, propagacao e configuracoes.',
                    'Editor de Templates Globais - design aplicado em multiplas lojas.',
                    'Gestor de CDN e Cache - invalidacao e otimizacao por loja.',
                ],
            ],
            [
                'title' => 'Seguranca e qualidade',
                'items' => [
                    'Monitor de Seguranca - malware, SSL e vulnerabilidades.',
                    'Log de Alteracoes - historico tecnico por loja.',
                    'Gestor de Emails Transacionais - templates, logs e entrega.',
                    'Testes A/B de Layouts - variacoes de paginas com dados reais.',
                    'Monitor de Integracoes - APIs, pagamentos, ERP e servicos externos.',
                ],
            ],
            [
                'title' => 'Analise e staging',
                'items' => [
                    'Gestor de Redirecionamentos - redirects em massa e monitorizacao.',
                    'Heatmap e Gravacao de Sessoes - comportamento por loja.',
                    'Gestor de Certificados SSL - expiracao e renovacao automatica.',
                    'Documentacao Tecnica Viva - wiki gerada/atualizada por loja.',
                    'Ambiente de Staging por Loja - preview antes de publicar.',
                ],
            ],
        ],
    ],
    'sales' => [
        'title' => 'Sales',
        'subtitle' => 'Roadmap de ferramentas para CRM, vendas, propostas, clientes, metas e performance comercial.',
        'icon' => 'fa-solid fa-chart-line',
        'topics' => [
            [
                'title' => 'CRM e pipeline',
                'items' => [
                    'CRM Centralizado - pipeline por cliente, loja e oportunidade.',
                    'Dashboard de Vendas em Tempo Real - faturacao, conversoes e performance.',
                    'Gestor de Propostas Comerciais - templates, envio e tracking.',
                    'Scoring de Clientes - potencial de compra e historico.',
                    'Alertas de Abandono de Carrinho - recuperacao automatica.',
                ],
            ],
            [
                'title' => 'Promocoes e previsao',
                'items' => [
                    'Gestor de Descontos e Promocoes - campanhas de preco por loja.',
                    'Previsao de Vendas com IA - historico, sazonalidade e tendencias.',
                    'Monitor de Metas - objetivos por equipa e vendedor.',
                    'Automacao de Follow-ups - sequencias email/SMS para leads e clientes.',
                    'Comparador de Performance por Loja - benchmarking do grupo.',
                ],
            ],
            [
                'title' => 'Clientes e rentabilidade',
                'items' => [
                    'Gestor de Clientes B2B - condicoes, credito e historico.',
                    'Relatorio de Produtos Mais Vendidos - loja, categoria e periodo.',
                    'Calculadora de Margens - rentabilidade por produto e canal.',
                    'Gestor de Devolucoes - workflow e analise de motivos.',
                    'Mapa de Calor de Vendas - origem geografica das vendas.',
                ],
            ],
            [
                'title' => 'Crescimento comercial',
                'items' => [
                    'Upsell/Cross-sell Automatico - sugestoes por comportamento.',
                    'Gestor de Afiliados - comissoes e performance de parceiros.',
                    'Chatbot de Vendas - qualificacao de leads 24/7.',
                    'Relatorio de Churn - clientes em risco de abandono.',
                    'Integracao Multi-canal - online, marketplace e fisico.',
                ],
            ],
        ],
    ],
    'finance' => [
        'title' => 'Finance',
        'subtitle' => 'Roadmap de ferramentas para tesouraria, faturacao, controlo orcamental e consolidacao financeira.',
        'icon' => 'fa-solid fa-wallet',
        'topics' => [
            [
                'title' => 'Visao financeira',
                'items' => [
                    'Dashboard Financeiro em Tempo Real - P&L, cash flow e balanco consolidado.',
                    'Gestor de Tesouraria - saldos, liquidez prevista e alertas.',
                    'Faturacao Automatica - emissao, envio e reconciliacao com SAF-T.',
                    'Controlo Orcamental - orcamentos por departamento e alertas.',
                    'Reconciliacao Bancaria Automatica - importacao e matching de extratos.',
                ],
            ],
            [
                'title' => 'Pagamentos e cobrancas',
                'items' => [
                    'Gestor de Pagamentos a Fornecedores - aprovacao e agendamento.',
                    'Monitor de Cobrancas - faturas vencidas e lembretes automaticos.',
                    'Relatorio de Rentabilidade por Loja - margem, custos e resultado liquido.',
                    'Previsao Financeira com IA - cenarios a 3, 6 e 12 meses.',
                    'Gestor de IVA e Obrigacoes Fiscais - calculo e prazos.',
                ],
            ],
            [
                'title' => 'Custos e auditoria',
                'items' => [
                    'Controlo de Despesas por Centro de Custo - categorizacao e relatorios.',
                    'Gestor de Investimentos e CapEx - ROI de projetos e equipamentos.',
                    'Relatorio de Fluxo de Caixa - projecao diaria, semanal e mensal.',
                    'Auditoria Financeira Interna - checklists e deteccao de anomalias.',
                    'Gestor de Salarios - processamento, simulacoes e historico.',
                ],
            ],
            [
                'title' => 'KPIs e consolidacao',
                'items' => [
                    'KPIs Financeiros Automaticos - EBITDA, ROI, CAC e LTV.',
                    'Alertas de Desvio de Margem - produtos ou lojas abaixo do limiar.',
                    'Gestor de Seguros Financeiros - apolices, premios e cobertura.',
                    'Consolidacao Multi-empresa - relatorios agregados do grupo.',
                    'Portal do Contabilista - acesso externo controlado a documentos fiscais.',
                ],
            ],
        ],
    ],
    'marketing' => [
        'title' => 'Marketing',
        'subtitle' => 'Roadmap de ferramentas para publicacoes, campanhas, homepages, assets, criativos e performance.',
        'icon' => 'fa-solid fa-bullhorn',
        'topics' => [
            [
                'title' => 'Planeamento e conteudo',
                'items' => [
                    'Calendario de Publicacoes - mapa mensal por loja, canal, data e status.',
                    'Tema Mensal por Loja - temas, briefings e distribuicao automatica.',
                    'Editor de Homepages - editor visual por loja com aprovacao.',
                    'Proposta de Atualizacao de Homepages - IA com sazonalidade e tendencias.',
                    'Gestor de Campanhas Publicitarias - Google/Meta por loja.',
                ],
            ],
            [
                'title' => 'Criacao e assets',
                'items' => [
                    'Gerador de Conteudo com IA - redes sociais, emails, blogs e anuncios.',
                    'Estudio de Banners e Criativos - banners, icones e brand guidelines.',
                    'Gerador de Logos e Identidade Visual - variantes e adaptacoes por loja.',
                    'Produtor de Videos Automatico - videos a partir de imagens e texto.',
                    'Renders 3D e Animacoes - produtos, espacos e campanhas.',
                ],
            ],
            [
                'title' => 'Canais e reputacao',
                'items' => [
                    'Gestor de Redes Sociais - agendamento e publicacao multi-plataforma.',
                    'Monitor de Reputacao Online - mencoes, reviews e sentimento por loja.',
                    'Gestor de Email Marketing - segmentacao, automacao e analise.',
                    'Biblioteca de Assets - repositorio de imagens, videos e materiais.',
                    'Analise de Concorrencia - precos, campanhas e posicionamento.',
                ],
            ],
            [
                'title' => 'Performance e crescimento',
                'items' => [
                    'Relatorio de Performance de Campanhas - ROI, CTR, CPC e conversoes.',
                    'Gestor de Influencers - contratos, briefings e metricas.',
                    'SEO Content Planner - keywords e plano editorial por loja.',
                    'Construtor de Landing Pages - paginas de campanha sem codigo.',
                    'Briefing Automatico de Campanhas - contexto, publico e objetivos.',
                ],
            ],
        ],
    ],
    'support' => [
        'title' => 'Support',
        'subtitle' => 'Roadmap de ferramentas para helpdesk, clientes, SLAs, conhecimento e melhoria da experiencia.',
        'icon' => 'fa-solid fa-headset',
        'topics' => [
            [
                'title' => 'Atendimento e triagem',
                'items' => [
                    'Help Desk Centralizado - tickets de todas as lojas num painel.',
                    'Chatbot de Suporte com IA - respostas frequentes 24/7.',
                    'Base de Conhecimento - FAQ e artigos por loja com pesquisa inteligente.',
                    'Monitor de SLA - alertas de resposta e resolucao.',
                    'Historico do Cliente - vista 360 com interacoes e compras.',
                ],
            ],
            [
                'title' => 'Resolucao e qualidade',
                'items' => [
                    'Roteamento Inteligente de Tickets - prioridade, loja e especialidade.',
                    'Templates de Resposta - respostas rapidas por categoria.',
                    'Gestor de Devolucoes e Reclamacoes - workflow completo.',
                    'Avaliacao de Satisfacao (CSAT) - inqueritos e tendencias.',
                    'Monitor de Reviews - agregacao e resposta a avaliacoes.',
                ],
            ],
            [
                'title' => 'Escalamento e canais',
                'items' => [
                    'Escalamento Automatico - regras para tickets criticos.',
                    'Relatorio de Motivos de Contacto - causas principais e evolucao.',
                    'Inbox Omnicanal - email, chat, redes sociais, telefone e formularios.',
                    'Portal de Autoatendimento - estado de pedidos, devolucoes e respostas.',
                    'Co-browsing e Assistencia Remota - apoio guiado ao cliente.',
                ],
            ],
            [
                'title' => 'Conhecimento e equipa',
                'items' => [
                    'Analisador de Sentimento - tom e urgencia nas mensagens.',
                    'Gestor de Incidentes Recorrentes - produtos, lojas e fornecedores problematicos.',
                    'Formacao de Agentes - trilhos por tema, produto e loja.',
                    'Gamificacao de Suporte - rankings, metas e qualidade de resposta.',
                    'Dashboard de KPIs de Suporte - resposta, resolucao, CSAT e reaberturas.',
                ],
            ],
        ],
    ],
    'hr' => [
        'title' => 'HR',
        'subtitle' => 'Roadmap de ferramentas para pessoas, recrutamento, onboarding, desempenho e cultura.',
        'icon' => 'fa-solid fa-user-group',
        'topics' => [
            [
                'title' => 'Base de RH',
                'items' => [
                    'HRIS/HRMS - dados de colaboradores, licencas, beneficios e historico.',
                    'ATS de Recrutamento - vagas, candidatos, triagem e contratacao.',
                    'Plataforma de Onboarding - documentos, acessos e formacao inicial.',
                    'Gestao de Desempenho - metas, avaliacoes e feedback continuo.',
                    'LMS - cursos, formacao e desenvolvimento profissional.',
                ],
            ],
            [
                'title' => 'Operacao de pessoas',
                'items' => [
                    'Gestao de Beneficios - seguros, planos e beneficios internos.',
                    'Tempo e Assiduidade - entradas, saidas, ferias, faltas e horas.',
                    'Payroll - salarios, impostos e contribuicoes.',
                    'Portal Self-Service - recibos, ferias e dados pessoais.',
                    'Engajamento e Cultura - moral, feedback e cultura organizacional.',
                ],
            ],
            [
                'title' => 'Desenvolvimento',
                'items' => [
                    'Feedback 360 Graus - avaliacao ampla de desempenho.',
                    'Gestao de Carreira e Sucessao - talentos e futuras liderancas.',
                    'Compensacao e Remuneracao - salarios, bonus e estruturas.',
                    'Talent Management Suite - recrutamento, desempenho, aprendizagem e sucessao.',
                    'Comunicacao Interna - chat, anuncios e interacao entre equipas.',
                ],
            ],
            [
                'title' => 'Compliance e bem-estar',
                'items' => [
                    'Gestao de Documentos de RH - contratos, avaliacoes e confidenciais.',
                    'Pesquisa de Clima e Pulse Surveys - feedback regular.',
                    'Bem-estar e Saude Mental - recursos e programas de apoio.',
                    'Compliance de RH - leis laborais, saude e seguranca.',
                    'Dashboard de KPIs de RH - rotatividade, retencao, formacao e satisfacao.',
                ],
            ],
        ],
    ],
    'purchasing' => [
        'title' => 'Purchasing',
        'subtitle' => 'Roadmap de ferramentas para compras, fornecedores, contratos, inventario, sourcing e KPIs.',
        'icon' => 'fa-solid fa-cart-flatbed',
        'topics' => [
            [
                'title' => 'Procurement core',
                'items' => [
                    'Software P2P - ciclo requisicao, fatura e pagamento.',
                    'SRM - dados, desempenho, contratos e relacao com fornecedores.',
                    'E-Procurement e Catalogo Eletronico - compras self-service aprovadas.',
                    'Gestao de Contratos CLM - versoes, prazos, renovacoes e compliance.',
                    'Spend Analytics - padroes de gastos e oportunidades de poupanca.',
                ],
            ],
            [
                'title' => 'Sourcing e inventario',
                'items' => [
                    'Sourcing e RFI/RFP/RFQ - pedidos de informacao, propostas e cotacoes.',
                    'Gestao de Inventario - stock, encomendas e pontos de reposicao.',
                    'Avaliacao de Fornecedores - qualidade, etica, risco e certificacao.',
                    'Risco da Cadeia de Abastecimento - interrupcoes, precos e qualidade.',
                    'Workflows de Compras - aprovacao de requisicoes, POs e faturas.',
                ],
            ],
            [
                'title' => 'Ordens, logistica e faturas',
                'items' => [
                    'PO Management - criar, enviar, rastrear e reconciliar ordens de compra.',
                    'Otimizacao de Logistica e Frete - transportes, rotas e custos.',
                    'Conciliacao Automatica de Faturas - faturas vs POs vs rececoes.',
                    'Leiloes Reversos - concorrencia entre fornecedores para melhores precos.',
                    'Previsao de Demanda e Necessidades - integrado com vendas e logistica.',
                ],
            ],
            [
                'title' => 'Fornecedor e compliance',
                'items' => [
                    'Portal de Fornecedores - comunicacao, documentos e tracking de pedidos.',
                    'QMS Integrado - qualidade dos produtos e servicos adquiridos.',
                    'Compliance e Etica em Compras - politicas internas e regulacoes.',
                    'RPA para Compras - entrada de dados, reconciliacoes e relatorios.',
                    'Dashboard de KPIs de Compras - savings, ciclo, risco e desempenho.',
                ],
            ],
        ],
    ],
];
