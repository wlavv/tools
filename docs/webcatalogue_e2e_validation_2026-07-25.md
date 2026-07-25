# WebCatalogue — validação E2E

Data: 2026-07-25

Estado: **Validação técnica concluída; validação visual/manual pendente**

## Ambiente

- Aplicação local: `http://tools.local`;
- store real usada: `tcg-collectors`;
- produto real usado: `mirrodin-full-set`;
- perfis HTTP: desktop Chrome/Windows e mobile Safari/iPhone.

## Percursos públicos verificados

Os seguintes endereços responderam HTTP 200 nos dois perfis, com conteúdo HTML e sem exceções renderizadas:

- página inicial da store;
- lista completa de produtos;
- detalhe do produto;
- viewer do produto;
- página de scan.

## Teste automatizado acrescentado

`tests/Feature/WebCataloguePublicJourneyTest.php`

O teste cria dados isolados e valida, em desktop e mobile:

- página da store;
- lista de produtos;
- catálogo;
- detalhe do produto;
- viewer;
- scan.

## Resultados automáticos

- Suite WebCatalogue: 5 testes, 44 asserções, todas aprovadas.
- Suite global: 7 testes, 49 asserções, todas aprovadas.
- Publicação/preview/unpublish: aprovado.
- Criação de sessão de scan: aprovado.
- Associação manual de produto: aprovado.
- Eliminação de sessão e filhos: aprovado.

## Checklist manual necessária

### Desktop

- [ ] Homepage da store sem sobreposição ou conteúdo cortado;
- [ ] filtros, pesquisa e paginação utilizáveis;
- [ ] galeria e recursos abrem corretamente;
- [ ] viewer carrega e os controlos respondem;
- [ ] scan pede autorização e mostra imagem da câmara;
- [ ] captura e resultado completam o percurso;
- [ ] consola do browser sem erros bloqueadores.

### Telemóvel

- [ ] navegação, botões e filtros ajustam-se ao ecrã;
- [ ] não existe scroll horizontal involuntário;
- [ ] imagens e galeria mantêm proporção;
- [ ] viewer responde a toque e orientação;
- [ ] autorização, seleção e troca de câmara funcionam;
- [ ] captura, resultado e reporte de “não encontrado” funcionam;
- [ ] consola remota/logs sem erros bloqueadores.

## Critério de fecho

A tarefa S1.3 pode ser marcada como concluída após preencher a checklist manual em pelo menos um browser desktop e um telemóvel real, registando dispositivo, browser e eventuais problemas.
