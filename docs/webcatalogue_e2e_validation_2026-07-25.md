# WebCatalogue — validação E2E

Data: 2026-07-25

Estado: **Concluído**

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

- [x] Homepage da store sem sobreposição ou conteúdo cortado;
- [x] filtros, pesquisa e paginação utilizáveis;
- [x] galeria e recursos abrem corretamente;
- [x] viewer carrega e os controlos respondem;
- [x] scan pede autorização e mostra imagem da câmara;
- [x] captura e resultado completam o percurso;
- [x] consola do browser sem erros bloqueadores.

### Telemóvel

- [x] navegação, botões e filtros ajustam-se ao ecrã;
- [x] não existe scroll horizontal involuntário;
- [x] imagens e galeria mantêm proporção;
- [x] viewer responde a toque e orientação;
- [x] autorização, seleção e troca de câmara funcionam;
- [x] captura, resultado e reporte de “não encontrado” funcionam;
- [x] consola remota/logs sem erros bloqueadores.

Validação manual confirmada pelo responsável do projeto em 2026-07-25.

## Critério de fecho

A tarefa S1.3 cumpre o critério: validação automática e manual concluídas em desktop e telemóvel, sem erros bloqueadores reportados.
