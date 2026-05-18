# WebCatalogue OpenCV Recognition Microservice

Small FastAPI service used by WebCatalogue to normalize camera captures before matching.

## Endpoints

- `GET /health`
- `POST /recognition/normalize`
- `POST /recognition/identifiers`
- `POST /recognition/markers`
- `POST /recognition/compare-markers`

`/recognition/normalize` receives multipart field `image` and returns:

- `normalized_image_base64`
- `debug_image_base64`
- `contour`
- `confidence`
- `used_perspective`

## Local Run

```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
uvicorn app:app --host 0.0.0.0 --port 8010
```

Linux:

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app:app --host 0.0.0.0 --port 8010
```

## OVH / VPS

Use a process manager such as `systemd`, `supervisor`, or Docker.

Example environment:

```env
WEBCATALOGUE_OPENCV_TOKEN=change-this-token
WEBCATALOGUE_OPENCV_MAX_IMAGE_BYTES=12000000
```

Laravel `.env`:

```env
WEBCATALOGUE_RECOGNITION_OPENCV_ENABLED=true
WEBCATALOGUE_RECOGNITION_OPENCV_BASE_URL=https://opencv.your-domain.com
WEBCATALOGUE_RECOGNITION_OPENCV_TOKEN=change-this-token
WEBCATALOGUE_RECOGNITION_OPENCV_TIMEOUT=20
WEBCATALOGUE_RECOGNITION_OPENCV_STORE_DEBUG=true
```

## Docker Deploy

```bash
cd /opt/webcatalogue-opencv
cp .env.example .env
nano .env
docker compose up -d --build
curl http://127.0.0.1:8010/health
```

The provided `docker-compose.yml` binds the service to `127.0.0.1:8010`, so expose it with Nginx and HTTPS.

## Systemd Deploy

```bash
sudo mkdir -p /opt/webcatalogue-opencv
sudo cp app.py requirements.txt .env.example /opt/webcatalogue-opencv/
cd /opt/webcatalogue-opencv
sudo cp .env.example .env
sudo nano .env
sudo python3 -m venv .venv
sudo .venv/bin/pip install -r requirements.txt
sudo cp deploy/systemd/webcatalogue-opencv.service /etc/systemd/system/webcatalogue-opencv.service
sudo systemctl daemon-reload
sudo systemctl enable --now webcatalogue-opencv
curl http://127.0.0.1:8010/health
```

Adjust the `User`, `Group`, and paths in `deploy/systemd/webcatalogue-opencv.service` if your OVH user is not `www-data`.

## Nginx Reverse Proxy

Copy `deploy/nginx/webcatalogue-opencv.conf` to your Nginx sites folder, replace `opencv.example.com`, then enable HTTPS:

```bash
sudo cp deploy/nginx/webcatalogue-opencv.conf /etc/nginx/sites-available/webcatalogue-opencv.conf
sudo ln -s /etc/nginx/sites-available/webcatalogue-opencv.conf /etc/nginx/sites-enabled/webcatalogue-opencv.conf
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d opencv.example.com
```

## Notes

The first implementation focuses on rectangular objects:

- cards
- labels
- manuals
- product front faces
- boxes photographed from the front

If no reliable contour is found, the service returns a resized fallback image so Laravel can continue with the local recognition pipeline.

`/recognition/identifiers` uses OpenCV QR/barcode detectors when available and returns normalized payload items with `format`, `rawValue`, `source`, optional `points`, and `confidence`.
