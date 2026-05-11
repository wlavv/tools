# DocumentManager Blueprint

DocumentManager is the LSG enterprise document operating knowledge system. It is not only a file manager: each document is an operational object with storage, versions, metadata, workflow, relations, timeline, OCR, AI, embeddings, security and audit context.

## Architecture

- `Config`: route prefix, queues, providers, workflow states, panels and expected tables.
- `Database/Migrations`: idempotent table creation using `document_core_*`, `document_ai_*`, `document_logs_*` and `document_workflow_*`.
- `Models`: Eloquent models for documents, versions, folders, workspaces, categories, tags, relations, permissions, shares, OCR, embeddings, summaries, analysis, workflow and logs.
- `Services`: storage, OCR, AI, workflow, permissions, search, timeline, preview, audit, diagnostics, relations, shares and embeddings.
- `Jobs`: queue-ready processing stages for OCR, AI, embeddings, preview and cleanup.
- `Events`: uploaded, ready and workflow state changed.
- `UI`: LSG dashboard, explorer, document workspace, AI, workflow, search, workspaces and diagnostics.

## Core Tables

- `document_core_workspaces`
- `document_core_folders`
- `document_core_documents`
- `document_core_versions`
- `document_core_categories`
- `document_core_tags`
- `document_core_document_tags`
- `document_core_metadata`
- `document_core_relations`
- `document_core_permissions`
- `document_core_shares`
- `document_logs_activity`
- `document_logs_access`
- `document_logs_downloads`
- `document_logs_ai`
- `document_ai_ocr`
- `document_ai_embeddings`
- `document_ai_summaries`
- `document_ai_analysis`
- `document_workflow_states`
- `document_workflow_approvals`
- `document_workflow_tasks`

## Processing Pipeline

The configured target flow is:

`uploaded -> checksum -> thumbnail -> ocr -> text_extraction -> ai_classification -> tagging -> embeddings -> relations -> indexing -> ready`

Queues:

- `dms_ocr`
- `dms_ai`
- `dms_embeddings`
- `dms_preview`
- `dms_notifications`
- `dms_cleanup`

## Storage Strategy

V1 stores files through Laravel Storage with checksum validation and version rows. The configured disk is `DOCUMENT_MANAGER_DISK` and the root path is `DOCUMENT_MANAGER_STORAGE_ROOT`.

Future providers should support local, S3-compatible object storage, CDN-backed previews, retention cleanup and orphan scanning.

## AI And OCR Strategy

V1 ships provider abstractions and stub services. The target provider adapters are:

- OCR: Tesseract, Google Vision, AWS Textract.
- AI: OpenAI, Anthropic, local models and AI Consensus integration.
- Embeddings: OpenAI, local embeddings, vector DB adapters.

AI outputs are stored separately in OCR, embeddings, summaries, analysis and AI log tables so retries are safe and observable.

## Semantic Graph

Semantic relations use `document_core_relations` with:

- `document_id`
- `relation_type`
- `related_type`
- `related_id`
- `related_document_id`
- `source`
- `confidence`

This supports links such as contract to supplier, invoice to order, certificate to product, image to product, warranty to customer and compliance to legal entity.

## Diagnostics

Diagnostics are available at `/document-manager/diagnostics` and through:

```bash
php artisan document-manager:diagnostics
```

Checks include tables, routes, storage, queue names, OCR provider, AI provider, failed jobs table, PHP/Laravel versions and the module log at `storage/logs/document-manager.log`.

## Roadmap

V1:
- Core schema, dashboard, explorer, uploads, versions, timeline, diagnostics, service abstractions.

V2:
- Full CRUD for folders, workspaces, categories, tags, permissions and shares.
- Bulk actions and richer explorer tree.

V3:
- Real OCR adapters, preview generation and retryable processing jobs.
- Search indexing with Scout/Meilisearch.

V4:
- AI classification, summaries, relation suggestions, auto tagging and AI Consensus integration.
- Semantic graph visual explorer.

V5:
- Compliance layer: retention policies, legal hold, immutable versions, watermarking, restricted downloads and audit exports.
- Vector search, pgvector/vector DB support, large scale worker topology and storage lifecycle tooling.
