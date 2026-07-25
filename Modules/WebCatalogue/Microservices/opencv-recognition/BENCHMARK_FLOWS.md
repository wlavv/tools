# WebCatalogue OpenCV Benchmark Flows

## Goal

Keep three comparable recognition flows:

1. `legacy`: old VPS.
2. `rise_s_base`: Rise-S baseline, frozen after the first migration.
3. `rise_s_incremental`: Rise-S experimental flow, receives each new optimization step.

The baseline must not receive algorithmic changes. Otherwise the academic comparison loses its reference point.

## Rise-S Directory Layout

Recommended layout:

```txt
/opt/lsg-ai-stack/services/webcatalogue-opencv-base
/opt/lsg-ai-stack/services/webcatalogue-opencv-incremental
```

Use the current production Rise-S service as `webcatalogue-opencv-base`.
Copy new code only into `webcatalogue-opencv-incremental`.

## Ports

```txt
base:        127.0.0.1:8010 -> container 8010
incremental: 127.0.0.1:8011 -> container 8010
```

Both containers must join `lsg_ai_net` so the FastAPI Gateway can call them by container name:

```txt
http://webcatalogue-opencv-base:8010
http://webcatalogue-opencv-incremental:8010
```

## Gateway Routes

Expose separate public prefixes:

```txt
https://api-ai.lsg-labs.com/opencv-base
https://api-ai.lsg-labs.com/opencv-incremental
```

The gateway should forward:

```txt
/opencv-base/recognition/*        -> http://webcatalogue-opencv-base:8010/recognition/*
/opencv-base/health               -> http://webcatalogue-opencv-base:8010/health

/opencv-incremental/recognition/* -> http://webcatalogue-opencv-incremental:8010/recognition/*
/opencv-incremental/health        -> http://webcatalogue-opencv-incremental:8010/health
```

This keeps the Laravel benchmark client simple, because each `base_url` still receives `/recognition/quality`, `/recognition/normalize`, etc.

## Laravel `.env`

```env
WEBCATALOGUE_RECOGNITION_OPENCV_LEGACY_BASE_URL=http://OLD_VPS_URL
WEBCATALOGUE_RECOGNITION_OPENCV_LEGACY_TOKEN=...

WEBCATALOGUE_RECOGNITION_OPENCV_BASE_URL=https://api-ai.lsg-labs.com/opencv-base
WEBCATALOGUE_RECOGNITION_OPENCV_TOKEN=...

WEBCATALOGUE_RECOGNITION_OPENCV_INCREMENTAL_BASE_URL=https://api-ai.lsg-labs.com/opencv-incremental
WEBCATALOGUE_RECOGNITION_OPENCV_INCREMENTAL_TOKEN=...
WEBCATALOGUE_RECOGNITION_INCREMENTAL_STAGE=dark_card_border_crop_v1
```

Then run:

```bash
php artisan optimize:clear
```

## Rise-S Deployment Steps

Freeze current service as base:

```bash
cd /opt/lsg-ai-stack/services
mv webcatalogue-opencv webcatalogue-opencv-base
cp -a webcatalogue-opencv-base webcatalogue-opencv-incremental
```

Copy the new experimental code only into:

```txt
/opt/lsg-ai-stack/services/webcatalogue-opencv-incremental
```

Create a parent compose file at:

```txt
/opt/lsg-ai-stack/services/docker-compose.webcatalogue-opencv-benchmark.yml
```

Use `deploy/docker-compose.benchmark-flows.yml` as the template.

Start both services:

```bash
cd /opt/lsg-ai-stack/services
docker compose -f docker-compose.webcatalogue-opencv-benchmark.yml up -d --build
```

Validate internally:

```bash
curl http://127.0.0.1:8010/health
curl http://127.0.0.1:8011/health
docker exec lsg_fastapi_gateway python -c "import urllib.request; print(urllib.request.urlopen('http://webcatalogue-opencv-base:8010/health', timeout=5).read().decode())"
docker exec lsg_fastapi_gateway python -c "import urllib.request; print(urllib.request.urlopen('http://webcatalogue-opencv-incremental:8010/health', timeout=5).read().decode())"
```

Validate publicly:

```bash
curl https://api-ai.lsg-labs.com/opencv-base/health
curl https://api-ai.lsg-labs.com/opencv-incremental/health
```

## Benchmark Rule

Only `webcatalogue-opencv-incremental` receives new algorithms, model calls, thresholds, crop improvements or performance experiments.

`webcatalogue-opencv-base` receives only operational fixes that do not change recognition behavior, and those fixes must be documented.
