# WebCatalogue Thesis Evidence Package

Date: 2026-05-23

## Purpose

This package collects the evidence generated during the iterative implementation of the WebCatalogue visual recognition and immersive catalogue prototype.

The most important contribution is not a single final result, but the documented engineering/scientific path:

1. Measure the baseline.
2. Identify the bottleneck or failure mode.
3. Change one part of the pipeline.
4. Re-test.
5. Compare latency, accuracy, ranking behaviour and user impact.

This allows the thesis to explain why the final architecture follows a progressive exclusion pipeline instead of a single broad visual matcher.

## Package Contents

### 01_reports

Human-readable reports in Markdown, HTML and PDF.

- `webcatalogue_recognition_full_evolution_report_2026-05-23.*`
  Complete recognition evolution report, including baseline, intermediate phases and diagnostic ZIP A/B tests.

- `webcatalogue_recognition_evolution_report.*`
  Earlier concise evolution report.

- `RecognitionPipelineV2.md`
  Technical recognition pipeline documentation.

### 02_data

Spreadsheet data for charts and quantitative analysis.

- `webcatalogue_recognition_full_evolution_2026-05-23.xlsx`
  Main workbook for thesis charts.

  Important sheets:

  - `Evolucao`: phase-level evolution.
  - `Scans_Chat`: scan-level batches reported during testing.
  - `Dados_Grafico`: clean series for latency/accuracy graphs.
  - `ZIP_Sessions`: diagnostic sessions extracted from ZIP manifests.
  - `AB_Tests`: before/after comparisons such as 412 -> 413, 414 -> 415, 417 -> 418.
  - `Top5_Candidates`: candidate ranking evidence.
  - `Tempos`: measured component timings.
  - `Timeline`: implementation changes and observed outcomes.

- `webcatalogue_recognition_chat_detailed_data.xlsx`
  Detailed data reconstructed from chat test batches.

- `webcatalogue_recognition_evolution_data.xlsx`
  Earlier chart dataset.

### 03_generators

Python scripts used to generate reports and XLSX files.

These scripts are included for reproducibility and auditability. They do not require external Python packages for XLSX generation.

### 04_environment

Mirrodin VR environment evidence and deployment SQL.

- `mirrodin_environment_online.sql`
  SQL to create/update the Mirrodin catalogue environment.

- `mirrodin_vr_environment_pack_2026-05-23.zip`
  Runtime asset pack with 360 background/audio and SQL.

### 05_assets

Current Mirrodin immersive assets:

- `mirrodin_artifact_vault_360_4k.jpg`
  4096x2048 equirectangular background used in VR.

- `mirrodin_artifact_vault_360.mp3`
  Background audio loop for the Mirrodin environment.

## Key Recognition Evolution Summary

| Phase | Main Change | Observed Result |
|---|---|---|
| Baseline | Broad visual matching, weak exclusion | Around 25s latency and roughly 3-4 correct scans in 12 |
| Metrics/logging | Persistent scan metrics, candidate scores and timings | Pipeline became auditable and thesis-ready |
| Precomputed markers | Hashes, colour, OCR/visual marker data persisted | Reduced unnecessary recalculation |
| ORB batching | ORB moved from candidate-by-candidate to batched/top-candidate strategy | Latency dropped from around 25s to around 5s |
| Auto scan/card lock | Mobile UX improved, camera moved higher, auto scan introduced | Better UX, but bad frame lock still caused failures |
| Progressive exclusion | Scope, quality, hash, OCR/marker and final scoring stages | Median latency around 3s; top candidates became explainable |
| Ground truth/ZIP diagnostics | Diagnostic ZIPs with capture, crop, top 5 and true product | Enabled concrete failure analysis |
| Candidate protection | Strong short-hash candidates protected in final ranking | Addressed regression where true product left top 5 |
| Capture validation | Reject invalid normalisation/poor contour confidence | Improved top-1 behaviour in A/B test 417 -> 418 |

## Important Quantitative Milestones

- Initial reported latency: approximately 25 seconds.
- Later median latency: approximately 2.9 seconds.
- Initial reported accuracy: approximately 3-4 correct scans in 12.
- Later reported batch: 11 correct scans in 15.
- Diagnostic A/B evidence:
  - `412 -> 413`: regression where correct candidate left top 5.
  - `414 -> 415`: regression caused by invalid capture/crop.
  - `417 -> 418`: improvement from missed top 5 to top-1 match.

## Suggested Thesis Integration

Use this evidence in the thesis as:

1. A proof of iterative scientific method.
2. A benchmark history for latency reduction.
3. A benchmark history for accuracy improvement.
4. Justification for progressive exclusion and quality gates.
5. Evidence that capture quality and domain-specific normalisation are as important as the matcher itself.
6. A bridge from recognition to immersive product presentation through catalogue-specific VR environments.

## Notes

- The recognition data comes from practical test batches reported during development and diagnostic ZIP manifests.
- Some early phases use manually reported aggregate accuracy from the test session.
- Later phases include richer top-5 and A/B evidence.
- The Mirrodin VR environment is included because it represents the current product-facing extension of the recognition work.
