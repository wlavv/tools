# WebCatalogue v2.26 Recognition Improvements

This release upgrades Visual Recognition from a single pHash comparison to a composite local fingerprint pipeline.

## New table

- `wc_resource_fingerprints`

Stores precomputed image profiles for resources so recognition can compare against all product images faster and with richer debug information.

## Algorithm

`composite_phash_color_edge_object_crop_v2_26`

The final score is calculated from:

- pHash / DCT structural similarity
- edge/shape hash similarity
- color histogram similarity

Default weights:

- pHash: 45%
- edge/shape: 35%
- color: 20%

## New env options

```env
WEBCATALOGUE_RECOGNITION_OBJECT_CROP=true
WEBCATALOGUE_RECOGNITION_OBJECT_CROP_THRESHOLD=28
WEBCATALOGUE_RECOGNITION_WEIGHT_PHASH=0.45
WEBCATALOGUE_RECOGNITION_WEIGHT_EDGE=0.35
WEBCATALOGUE_RECOGNITION_WEIGHT_COLOR=0.20
```

Existing thresholds still apply:

```env
WEBCATALOGUE_RECOGNITION_AUTO_THRESHOLD=70
WEBCATALOGUE_RECOGNITION_SUGGESTION_THRESHOLD=50
WEBCATALOGUE_RECOGNITION_DEBUG_TOP=20
```

## Backoffice debug

Recognition session details now show score breakdown per match:

- pHash score
- edge score
- color score
- final score

This makes it easier to understand whether the mismatch is caused by lighting/background, object shape, or color.
