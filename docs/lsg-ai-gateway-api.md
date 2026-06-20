# LSG AI Gateway API

Documentacao tecnica v1 para integracao do Webtools com o servidor AI LSG.

## Contexto

O servidor AI LSG corre num servidor OVH Rise-S com Ubuntu Server 24.04 e Docker.

Stack atual:

- Nginx Proxy Manager
- n8n
- Redis
- Qdrant
- Ollama
- Modelo Ollama `qwen2.5:7b`
- FastAPI Gateway
- OCR Service
- Vision Service

Base URL publica:

```txt
https://api-ai.lsg-labs.com
```

Base URL interna Docker:

```txt
http://lsg_fastapi_gateway:8000
```

## Autenticacao

Endpoints AI protegidos usam:

```http
x-lsg-ai-token: TOKEN
```

Configuracao Laravel/Webtools:

```env
LSG_AI_GATEWAY_URL=https://api-ai.lsg-labs.com
LSG_AI_GATEWAY_TOKEN=COLOCAR_TOKEN_AQUI
LSG_AI_GATEWAY_TIMEOUT=180
LSG_AI_DEFAULT_MODEL=qwen2.5:7b
```

Nunca colocar tokens em controllers, views, JavaScript ou logs.

## Health

```http
GET /health
```

Resposta esperada:

```json
{
  "status": "ok",
  "service": "LSG AI Gateway",
  "default_model": "qwen2.5:7b"
}
```

## LLM

### Gerar Texto

```http
POST /api/llm/generate
```

Body:

```json
{
  "prompt": "Responde em portugues europeu numa frase: o servidor AI LSG esta operacional?",
  "model": "qwen2.5:7b"
}
```

### Chat

```http
POST /api/llm/chat
```

Body:

```json
{
  "model": "qwen2.5:7b",
  "messages": [
    {"role": "system", "content": "Es um assistente interno do grupo LSG."},
    {"role": "user", "content": "Resume este documento."}
  ]
}
```

## OCR

### OCR de Imagem

```http
POST /api/ocr/image
```

Multipart:

| Campo | Tipo | Obrigatorio | Descricao |
| --- | --- | --- | --- |
| `file` | file | Sim | JPG, PNG, WEBP, TIFF ou BMP |
| `lang` | string | Nao | Default `por+eng` |
| `preprocess` | bool | Nao | Default `true` |

### OCR de PDF

```http
POST /api/ocr/pdf
```

Multipart:

| Campo | Tipo | Obrigatorio | Descricao |
| --- | --- | --- | --- |
| `file` | file | Sim | PDF |
| `lang` | string | Nao | Default `por+eng` |
| `preprocess` | bool | Nao | Default `true` |
| `max_pages` | int | Nao | Default `5`, maximo recomendado `20` |

## Vision

### Analisar Imagem

```http
POST /api/vision/analyze
```

Multipart:

| Campo | Tipo | Obrigatorio |
| --- | --- | --- |
| `file` | file | Sim |

Possiveis `quality_status`:

```txt
ok
too_small
blurry
too_dark
too_bright
low_contrast
```

### Gerar pHash

```http
POST /api/vision/phash
```

### Comparar pHash

```http
POST /api/vision/compare-phash
```

Parametros:

| Campo | Tipo | Obrigatorio |
| --- | --- | --- |
| `hash_a` | string | Sim |
| `hash_b` | string | Sim |

Possiveis `similarity`:

```txt
very_similar
similar
possibly_related
different
```

## Documents / Expense Extraction

```http
POST /api/documents/extract-expense
```

Fluxo:

```txt
Imagem/PDF -> OCR -> LLM qwen2.5:7b -> JSON estruturado para Expense Manager
```

Multipart:

| Campo | Tipo | Obrigatorio | Descricao |
| --- | --- | --- | --- |
| `file` | file | Sim | Imagem ou PDF |
| `lang` | string | Nao | Default `por+eng` |
| `preprocess` | bool | Nao | Default `true` |
| `max_pages` | int | Nao | Usado em PDF, default `5` |

Exemplo de resposta estruturada:

```json
{
  "status": "ok",
  "gateway": "LSG AI Gateway",
  "service": "documents.extract_expense",
  "result": {
    "status": "ok",
    "document_type": "image",
    "ocr": {
      "status": "ok",
      "type": "image",
      "llm_ready": true,
      "text": "Fatura LSG Labs - Total 79,94 EUR - Servidor AI OVH",
      "language": "por+eng",
      "preprocess": true,
      "processing_time_ms": 108.98,
      "text_length": 51
    },
    "expense": {
      "supplier_name": "LSG Labs",
      "supplier_vat": null,
      "invoice_number": null,
      "invoice_date": null,
      "currency": "EUR",
      "subtotal": null,
      "tax_amount": null,
      "total": 79.94,
      "document_type": "invoice",
      "category_suggestion": "Servidor",
      "confidence": 0.85,
      "notes": "Falta o numero da fatura e a data."
    },
    "llm_model": "qwen2.5:7b"
  }
}
```

## Services Laravel

Services principais no Webtools:

- `App\Services\AI\AiGatewayService`
- `App\Services\AI\DocumentOcrService`
- `App\Services\AI\DocumentAiService`
- `App\Services\AI\ExpenseExtractionService`

Responsabilidades:

- Cliente HTTP central para health, LLM, OCR, Vision e extract-expense.
- Integrar Documents Manager com OCR.
- Guardar sugestoes AI em `document_manager_ai_results`.
- Abrir fluxo de criacao de despesa com validacao humana.

## Boas Praticas

- Laravel chama sempre `https://api-ai.lsg-labs.com`.
- Nao chamar containers internos diretamente.
- Token sempre no `.env`.
- Nao expor token em logs, views ou JavaScript.
- Guardar payloads AI em JSON para auditoria.
- Nao criar despesas automaticamente sem validacao humana.
- Usar filas/jobs para documentos grandes.
- Validar sempre tipo de ficheiro.
- Guardar OCR original e resposta estruturada da AI.

## Prioridade de Integracao

```txt
1. /api/documents/extract-expense
2. /api/ocr/image
3. /api/ocr/pdf
4. /api/vision/analyze
5. /api/vision/phash
6. /api/vision/compare-phash
7. /api/llm/generate
8. /api/llm/chat
```
