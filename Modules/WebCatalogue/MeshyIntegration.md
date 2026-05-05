# WebCatalogue · Meshy 3D Provider

## Environment

Add these values to `.env`:

```env
WEBCATALOGUE_3D_GENERATION_MODE=meshy
WEBCATALOGUE_3D_GENERATION_DISPATCH=queue
WEBCATALOGUE_MESHY_API_KEY=your_meshy_api_key
WEBCATALOGUE_MESHY_AI_MODEL=latest
WEBCATALOGUE_MESHY_SHOULD_TEXTURE=true
WEBCATALOGUE_MESHY_ENABLE_PBR=true
WEBCATALOGUE_MESHY_MAX_IMAGES=4
WEBCATALOGUE_MESHY_POLL_ATTEMPTS=60
WEBCATALOGUE_MESHY_POLL_SLEEP_SECONDS=10
```

For quick validation without queue workers, use:

```env
WEBCATALOGUE_3D_GENERATION_DISPATCH=sync
```

`sync` waits during the HTTP request while Meshy generates the model. For production, `queue` is recommended.

## Flow

1. Create a 3D Studio job.
2. Upload 1 to 4 source images of the same product.
3. The Laravel job submits a Multi-Image to 3D task to Meshy.
4. The module polls Meshy until success/failure.
5. When completed, it downloads:
   - GLB as `model_3d`
   - USDZ as `ar_file` when available
   - thumbnail as `thumbnail` when available
   - VR scene JSON as `vr_scene`
6. All outputs are registered in `wc_resources` and linked to the product.
7. `notifications_send(...)` is called when the job completes.

## Database

Run migrations after updating. Existing installs get the complementary migration:

```txt
2026_05_04_000003_update_wc_3d_generation_jobs_meshy_fields.php
```

It adds:

```txt
provider_task_id
provider_status
progress
started_at
completed_at
failed_at
```

## Notes

Meshy supports publicly accessible image URLs or base64 data URI inputs. This module sends local uploaded source images as base64 data URI inputs, so it does not require product source images to be publicly accessible.
