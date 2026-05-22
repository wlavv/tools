# WebCatalogue Visual Recognition - Relatorio de Evolucao

Data: 2026-05-22

## Resumo executivo

A pipeline de reconhecimento visual do WebCatalogue evoluiu de um matcher exploratorio para uma pipeline mensuravel e auditavel para reconhecimento de cartas MTG. Os primeiros testes tinham uma latencia aproximada de 25 segundos e uma accuracy na ordem de 3-4 acertos em 12 scans. O benchmark consolidado mais recente reporta 11 acertos em 15 scans, com mediana de latencia perto de 3 segundos.

Em termos quantitativos, a latencia caiu cerca de 88.5% e a accuracy subiu 44.1 pontos percentuais entre o baseline e o ultimo lote consolidado. Isto representa uma melhoria direta para o utilizador: menos espera, mais confianca e menor necessidade de repetir o scan.

Esta evolucao e relevante academicamente porque cada melhoria foi motivada por um gargalo ou modo de falha observado: chamadas ORB excessivas, captura/crop instavel, pools de candidatos demasiado amplas, compressao de score e normalizacao de perspetiva insuficiente.

## Metodologia

Os valores foram obtidos a partir dos lotes de teste reportados durante a evolucao do modulo. Quando existia lista de scans, foi calculada media ou mediana sobre os tempos apresentados. A fase P5 representa uma alteracao ja preparada/implementada, mas ainda pendente de novo benchmark online apos redeploy do microservico OpenCV.

## Tabela de evolucao

| Fase | Alteracao principal | Latencia | Accuracy | Interpretacao |
|---|---|---:|---:|---|
| P0 Baseline inicial | Pipeline visual ainda ampla; ORB/comparacao pesada por candidato; crop/perspetiva genericos. | 25000.0 ms | 29.2% | Resposta demasiado lenta; baixa confianca; pouca utilidade operacional. |
| P1 ORB batch | ORB deixou de chamar o microservico candidato a candidato; comparacao batch para markers. | 4951.6 ms | 58.3% | Reducao drastica de espera; ainda com muitas respostas ambiguas. |
| P2 Card lock e limites de candidatos | Lock visual antes do scan; crop bloqueado; reducao de pools: verification, marker e scored candidates. | 4266.5 ms | 66.7% | Menos scroll/intervencao; captura mais controlada; tempo ainda perto de 4s. |
| P3 Exclusao progressiva explicita | Fases hash, marker, verification e final stage com contadores e cortes reais. | 2946.0 ms | 50.0% | Melhor explicabilidade; regressao temporaria de accuracy usada para calibrar pesos e score. |
| P4 Recalibracao scoring e consenso | Mais peso para pHash/edge; ORB fraco deixa de penalizar; boost por consenso; decisao por score+margem. | 2883.0 ms | 73.3% | Resposta em cerca de 3s; accuracy consolidada reportada 11/15; melhor base para prova de conceito. |
| P5 Normalizacao MTG e ORB condicional | OpenCV mtg_card com ratio/cantos/perspetiva 672x936; ORB apenas quando hash/edge/color nao dao margem forte. | 2200.0 ms | - | Melhor consistencia esperada; menor custo ORB em scans faceis. |

## Relevancia cientifica

O historico de benchmarks fornece evidencia de um metodo iterativo de engenharia: medir, identificar gargalos, alterar uma parte do sistema e voltar a medir. A reducao de latencia valida a estrategia de exclusao progressiva. A melhoria de accuracy valida a passagem de matching visual generico para reconhecimento orientado ao dominio das cartas.

## Principais aprendizagens

- ORB e util como sinal de confianca, mas e demasiado caro e ruidoso para correr contra pools amplas.
- pHash e edge/hash sao bons sinais iniciais para ranking de cartas MTG quando o crop e estavel.
- Um Top 3 visivel com scores por comparador e essencial para calibracao e auditabilidade cientifica.
- A normalizacao de perspetiva especifica para cartas e a proxima alavanca principal de accuracy.

## Proximo benchmark

Depois de redeploy/restart do microservico OpenCV com normalizacao `mtg_card`, deve ser executado um novo benchmark controlado com pelo menos 3 repeticoes por cenario: luz ideal, luz natural, pouca luz, glare, sleeve, carta inclinada e motion blur.

O Excel gerado em paralelo contem uma folha de evolucao completa, uma folha simplificada para graficos e uma folha de notas interpretativas. Para apresentacao, recomenda-se um grafico combinado com latencia em linha descendente e accuracy em linha ascendente.
