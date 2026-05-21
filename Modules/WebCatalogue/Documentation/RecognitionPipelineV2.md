# WebCatalogue Recognition Pipeline v2

## Objetivo

A pipeline v2 acrescenta uma camada auditavel ao reconhecimento visual existente. O fluxo atual continua disponivel, mas cada scan passa a poder guardar qualidade do frame, timings, candidatos, scores por comparador, decisao final e ground truth.

## Configuracao

As opcoes principais vivem em `config('webcatalogue.recognition.pipeline_v2')`:

- `enabled`: ativa a pipeline v2 mantendo compatibilidade com o output antigo.
- `profile`: perfil operacional atual, por defeito `mtg_v1`.
- `quality.reject_below`: rejeita frames com score inferior a 60.
- `weights`: pesos oficiais para pHash, aHash/dHash, OCR, ORB, layout e cor.
- `decision`: thresholds de accepted, ambiguous e rejected.
- `performance_targets`: metas de referencia para dashboard e tese.

## Tabelas

- `wc_recognition_scans`: registo principal do scan, qualidade, decisao, score final e ground truth.
- `wc_recognition_scan_candidates`: top candidatos com score ponderado e score por comparador.
- `wc_recognition_scan_timings`: tempos por etapa em milissegundos.

## Exemplo de request de teste

```http
POST /catalogue/scan/session
Content-Type: application/json

{
  "device_type": "mobile",
  "expected_product_id": 123,
  "expected_card_id": "MRD-001",
  "scenario_label": "natural_light"
}
```

Depois usar o `session_token` no upload/capture e no match normal:

```http
POST /catalogue/scan/match
Content-Type: application/json

{
  "session_token": "..."
}
```

## Exemplo de response

O endpoint mantem os campos antigos (`matched`, `auto_match`, `suggestions`) e acrescenta:

```json
{
  "pipeline_v2": {
    "scan_id": "uuid",
    "status": "accepted",
    "decision_reason": "score_above_auto_accept_threshold",
    "quality": {
      "score": 82,
      "blur": 90,
      "brightness": 75,
      "glare": 10,
      "perspective": 84
    },
    "timings_ms": {
      "total": 1450,
      "quality_check": 70,
      "hash_search": 800,
      "scoring": 20,
      "database": 15
    },
    "candidates": [
      {
        "product_id": 123,
        "name": "Example Card",
        "score_final": 94,
        "scores": {
          "phash": 91,
          "ahash_dhash": 88,
          "ocr_collector": 100,
          "ocr_name": null,
          "orb": 87,
          "layout": 95,
          "color": 78
        }
      }
    ],
    "ground_truth": {
      "expected_product_id": 123,
      "expected_card_id": "MRD-001",
      "scenario_label": "natural_light",
      "top_1_correct": true,
      "top_3_correct": true,
      "false_positive": false,
      "false_negative": false
    }
  }
}
```

## Dashboard

- HTML: `/webcatalogue/recognition/pipeline`
- JSON: `/webcatalogue/recognition/pipeline/summary`

## Benchmark inicial

1. Rebuild de fingerprints do produto ou store de teste.
2. Criar pelo menos 10 scans por cenario: `ideal_light`, `natural_light`, `low_light`, `glare`, `sleeve`, `foil`, `angled`, `complex_background`, `motion_blur`, `distance_variation`.
3. Registar `expected_product_id` em todos os scans.
4. Comparar no dashboard: top-1, top-3, false positive, false negative, tempo medio, p95 e p99.

## Comandos uteis

```bash
php artisan migrate
php artisan route:list --name=webcatalogue.recognition.pipeline
php artisan webcatalogue:recognition-rebuild-fingerprints --product=123 --sync
php artisan webcatalogue:recognition-rebuild-fingerprints --store=1 --sync
```

## Instrumentacao interna

A pipeline guarda agora timings separados quando o matcher interno e usado:

- `input_preparation_time_ms`
- `perspective_correction_time_ms`
- `hash_generation_time_ms`
- `hash_search_time_ms`
- `ocr_time_ms`
- `orb_time_ms`
- `scoring_time_ms`
- `database_time_ms`

O resultado do matcher tambem inclui `internal_timings_ms` e `internal_counters`, que a pipeline copia para `wc_recognition_scan_timings` e `wc_recognition_scans`.

## Comparadores modulares

Os comparadores visuais foram separados para preparar testes e benchmark por componente:

- `HashComparator`: compara hashes perceptuais/binarios.
- `ColorHistogramComparator`: compara histogramas por interseccao.
- `EmbeddingComparator`: compara embeddings internos por produto escalar normalizado.
- `CompositeVisualComparator`: combina embedding, pHash, edge hash e cor com os pesos atuais.
- `OrbMarkerComparator`: encapsula comparacao ORB, batch ORB, confidence score e boost de markers.

O `InternalImageMatchService` continua a ser o orquestrador para manter compatibilidade, mas ja delega estes calculos para classes dedicadas.

## Proxima fase tecnica

A proxima fase deve isolar OCR/layout e criar um comando de benchmark que execute cenarios com ground truth e exporte CSV/JSON para analise na tese.
