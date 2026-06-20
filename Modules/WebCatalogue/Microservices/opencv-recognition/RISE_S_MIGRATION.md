# WebCatalogue OpenCV -> Rise-S Migration

## Goal

Move the existing WebCatalogue OpenCV recognition service from the old VPS to the Rise-S AI server while keeping the Laravel WebCatalogue integration compatible.

## Current Laravel Contract

The WebCatalogue client expects `WEBCATALOGUE_RECOGNITION_OPENCV_BASE_URL` to expose:

```txt
GET  /health
POST /recognition/normalize
POST /recognition/quality
POST /recognition/markers
POST /recognition/identifiers
POST /recognition/compare-markers
POST /recognition/compare-markers-batch
```

Authentication currently uses:

```http
Authorization: Bearer TOKEN
```

## Recommended Rise-S Layout

```txt
/opt/lsg-ai-stack/services/webcatalogue-opencv
```

The service should run as an internal Docker container. External traffic should go through the AI Gateway or the server reverse proxy, not directly to the container.

## Deploy Files

Copy these files to the Rise-S service directory:

```txt
app.py
Dockerfile
requirements.txt
docker-compose.yml
.env.example
```

Create `.env` on the Rise-S server:

```env
WEBCATALOGUE_OPENCV_TOKEN=replace-with-strong-token
WEBCATALOGUE_OPENCV_MAX_IMAGE_BYTES=12000000
```

## Docker Start

```bash
cd /opt/lsg-ai-stack/services/webcatalogue-opencv
cp .env.example .env
nano .env
docker compose up -d --build
docker compose logs -f webcatalogue-opencv
```

Health check:

```bash
curl http://127.0.0.1:8010/health
```

Expected:

```json
{
  "ok": true,
  "service": "webcatalogue-opencv-recognition"
}
```

## Gateway / Proxy

Preferred public URL:

```txt
https://api-ai.lsg-labs.com
```

For compatibility, the Gateway should forward:

```txt
/recognition/*
```

to:

```txt
http://webcatalogue-opencv:8010/recognition/*
```

If the Gateway is not ready yet, expose the container temporarily through reverse proxy with TLS and token protection, then move behind the Gateway later.

## Laravel Cutover

Update WebCatalogue `.env`:

```env
WEBCATALOGUE_RECOGNITION_OPENCV_ENABLED=true
WEBCATALOGUE_RECOGNITION_OPENCV_BASE_URL=https://api-ai.lsg-labs.com
WEBCATALOGUE_RECOGNITION_OPENCV_TOKEN=replace-with-strong-token
WEBCATALOGUE_RECOGNITION_OPENCV_TIMEOUT=20
WEBCATALOGUE_RECOGNITION_OPENCV_STORE_DEBUG=true
```

Then in Backoffice:

```txt
Settings -> System Tools -> Optimize Clear
```

## Validation

1. `GET /health` returns OK.
2. `POST /recognition/quality` succeeds with a real image.
3. `POST /recognition/normalize` returns `normalized_image_base64`.
4. `POST /recognition/markers` returns ORB markers.
5. WebCatalogue scan creates a session with OpenCV metadata.
6. Benchmark top-1/top-3 accuracy and latency are equal or better than the old VPS.

## Rollback

Keep the old VPS running until validation is complete.

Rollback is only:

```env
WEBCATALOGUE_RECOGNITION_OPENCV_BASE_URL=http://old-vps-url
```

Then run `Optimize Clear`.

## Close Old VPS

Only after:

- Rise-S OpenCV passes real scans.
- Benchmark is accepted.
- Logs are stable for a few days.
- No `.env` or docs reference the old IP.
