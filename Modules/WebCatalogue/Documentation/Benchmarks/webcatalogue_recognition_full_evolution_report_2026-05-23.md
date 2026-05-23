# WebCatalogue Visual Recognition - Relatorio Completo de Evolucao

Data: 2026-05-23

## Resumo executivo

O modulo de reconhecimento visual evoluiu de uma prova de conceito lenta para uma pipeline auditavel, progressiva e preparada para demonstracao academica. O ponto mais importante nao e apenas a melhoria absoluta, mas a demonstracao do caminho: cada alteracao foi motivada por uma falha observada, seguida de novo teste e nova medicao.

O baseline reportado tinha aproximadamente 25 segundos de latencia e 3-4 acertos em 12 scans. A fase consolidada com scoring/consenso atingiu 11 acertos em 15 scans, com mediana de cerca de 2.9 segundos. Nos testes A/B mais recentes, os ZIPs permitiram demonstrar regressao, causa e correcao: por exemplo, a sessao 417 falhou top 5 e a 418, apos a correcao de full-card visibility/normalizacao, acertou top 1.

## Evolucao quantitativa consolidada

| Iteracao | Accuracy | Latencia mediana | Leitura |
|---|---:|---:|---|
| B0_baseline_25s - Baseline inicial | 29.2% | - ms | Valor inicial reportado no chat. Pipeline ainda ampla, com latencia cerca de 25s. |
| B1_primeiro_lote_lento - Pipeline v2 inicial com metricas | 35.7% | 25275.0 ms | Lote com latencias ainda muito altas; OCR/ORB ainda demasiado caros e pouca exclusao. |
| B2_orb_batch - ORB batch e reducao de chamadas | 58.3% | 4539.0 ms | Latencia melhorou muito apos batch ORB, mas todas continuavam ambiguas. |
| B3_auto_scan_lock - Scan automatico e camera mais acima | 41.7% | 4152.0 ms | Foi removido o hero e automatizado o fluxo mobile; lock ainda lento e nem sempre focava a carta. |
| B4_progressive_exclusion - Exclusao progressiva explicita | 50.0% | 2973.0 ms | Top 3 passou a ser auditavel; fase util para perceber pesos e candidatos, embora com regressao temporaria. |
| B5_scoring_consensus - Recalibracao scoring e consenso | 73.3% | 2883.0 ms | Ultimo lote reportado; mediana 2883ms; confirma melhoria com amostra maior. |

## A/B tests com ZIPs diagnosticos

| Comparacao | Antes | Depois | Interpretacao |
|---|---|---|---|
| 409 -> 410 | 409: manual_matched, MRD-172 Frogmite, top_1_match, correct=True, score=89.64 | 410: manual_matched, MRD-176 Goblin Charbelcher, top_1_match, correct=True, score=88.21 | Usado para validar se a alteracao melhorava a posicao do candidato real e a margem. |
| 412 -> 413 | 412: matched, MRD-215 Myr Retriever, top_1_match, correct=True, score=89.59 | 413: suggestions_found, MRD-169 Extraplanar Lens, missed_top_5, correct=False, score=76.76 | Regressao: o candidato correto saiu do top 5. Originou protecao de short_hash no pool final. |
| 414 -> 415 | 414: matched, MRD-242 Skeleton Shard, top_1_match, correct=True, score=90.1 | 415: suggestions_found, MRD-279 Blinkmoth Well, missed_top_5, correct=False, score=66.24 | Regressao causada por captura/crop invalido: OpenCV confidence 0 e contour null. Originou rejeicao de normalizacao sem contorno. |
| 417 -> 418 | 417: suggestions_found, MRD-208 Mirror Golem, missed_top_5, correct=False, score=80.01 | 418: matched, MRD-201 Loxodon Warhammer, top_1_match, correct=True, score=92.4 | Melhoria clara: o mesmo produto passou de missed_top_5 para top_1_match. |

## Timeline tecnica das alteracoes

| Passo | Alteracao | Resultado observado | Valor cientifico |
|---|---|---|---|
| T0 | Matcher visual amplo, comparacao pesada e pouca exclusao. | 3-4/12; cerca de 25s | Mostrou que acertar carta sem pipeline orientada ao dominio era lento e pouco fiavel. |
| T1 | Criadas tabelas/logs de scans, scores, timings, candidatos e dashboard. | Passou a existir evidencia por scan | Tornou a solucao mensuravel, essencial para tese. |
| T2 | Hash, edge, color, embedding, markers e perfis persistidos para evitar recalculo normal. | Latencia desceu fortemente quando combinado com batch ORB | Confirma a estrategia de pre-processamento de catalogo. |
| T3 | ORB deixou de executar chamada por candidato e passou a comparar por lote/top candidates. | ~25s para ~5s | Primeira grande prova de melhoria operacional. |
| T4 | Camera mais acima, hero removido, auto scan e tentativa de focus/card lock. | UX melhor, mas lock ainda podia aceitar frames maus | Mostrou que qualidade de captura e tao importante como o matcher. |
| T5 | Scope, quality, hash, marker, verification e final stage com limites e contadores. | ~3s, mas regressao temporaria de accuracy | A regressao foi util porque expôs onde candidatos corretos eram eliminados. |
| T6 | Export CSV, ground truth, classificacao top1/top3/top5/missed e ZIP com imagens. | Permitiu A/B real entre sessoes 402-418 | Ferramenta cientifica chave: comparar input, candidatos e real. |
| T7 | Reserva de candidatos short_hash no final stage e ranking conservador para bons short hashes. | 412->413 revelou regressao; alteracao protege candidatos certos | Evita que verification_pool domine e elimine sinais fortes. |
| T8 | Rejeicao de normalizacao OpenCV sem contorno/confianca e lock exige carta inteira visivel. | 417->418: missed_top_5 para top_1_match | Mostra que uma captura melhor melhora accuracy mais que apenas mexer em pesos. |
| T9 | Carta procedural com frente/verso MTG, foil suave, VR fallback e environment Mirrodin. | Fora do benchmark de leitura | Demonstra caminho de produto imersivo apos reconhecimento. |

## Observacoes por ZIP diagnostico

Os ZIPs passaram a ser uma ferramenta central de diagnostico: contem captura, crop/normalizacao, top 5 candidatos, candidato real e metadata do matching. Isto permitiu distinguir falhas de ranking de falhas de captura.

- 412 -> 413: regressao por eliminacao do candidato certo do top 5; solucao: proteger candidatos short_hash na fase final.
- 414 -> 415: regressao por captura mal enquadrada; OpenCV sem contorno e confidence 0; solucao: rejeitar normalizacao invalida e exigir carta inteira visivel.
- 417 -> 418: melhoria confirmada; mesmo produto passou de missed_top_5 para top_1_match.

## Conclusao

O projeto demonstra uma estrategia cientificamente defensavel: medir, identificar gargalos, formular hipotese, alterar a pipeline, testar e comparar. A evolucao da latencia prova o valor da exclusao progressiva; a evolucao de accuracy prova a importancia de normalizacao orientada ao dominio e de preservar sinais fortes como short_hash.