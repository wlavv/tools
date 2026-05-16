import base64
import os
from typing import Optional

import cv2
import numpy as np
from fastapi import FastAPI, File, Form, Header, HTTPException, UploadFile


APP_TOKEN = os.getenv("WEBCATALOGUE_OPENCV_TOKEN", "")
MAX_IMAGE_BYTES = int(os.getenv("WEBCATALOGUE_OPENCV_MAX_IMAGE_BYTES", "12000000"))

app = FastAPI(title="WebCatalogue OpenCV Recognition", version="0.1.0")


@app.get("/health")
def health():
    return {"ok": True, "service": "webcatalogue-opencv-recognition"}


@app.post("/recognition/normalize")
async def normalize(
    image: UploadFile = File(...),
    mode: str = Form("rectangular_object"),
    debug: str = Form("1"),
    authorization: Optional[str] = Header(default=None),
):
    require_token(authorization)

    content = await image.read()
    if len(content) > MAX_IMAGE_BYTES:
        raise HTTPException(status_code=413, detail="Image too large")

    source = decode_image(content)
    if source is None:
        raise HTTPException(status_code=422, detail="Could not decode image")

    result = normalize_rectangular_object(source)
    normalized = result["normalized"] if result["ok"] else resize_max_side(source, 1200)
    debug_image = draw_debug(source, result) if debug == "1" else None

    response = {
        "ok": True,
        "mode": mode,
        "normalized_image_base64": encode_jpeg(normalized),
        "confidence": result.get("confidence", 0.0),
        "contour": result.get("contour"),
        "source_width": int(source.shape[1]),
        "source_height": int(source.shape[0]),
        "normalized_width": int(normalized.shape[1]),
        "normalized_height": int(normalized.shape[0]),
        "used_perspective": bool(result["ok"]),
    }

    if debug_image is not None:
        response["debug_image_base64"] = encode_jpeg(debug_image)

    return response


def require_token(authorization: Optional[str]) -> None:
    if not APP_TOKEN:
        return

    expected = f"Bearer {APP_TOKEN}"
    if authorization != expected:
        raise HTTPException(status_code=401, detail="Unauthorized")


def decode_image(content: bytes):
    buffer = np.frombuffer(content, np.uint8)
    return cv2.imdecode(buffer, cv2.IMREAD_COLOR)


def encode_jpeg(image) -> str:
    ok, buffer = cv2.imencode(".jpg", image, [int(cv2.IMWRITE_JPEG_QUALITY), 92])
    if not ok:
        raise HTTPException(status_code=500, detail="Could not encode image")
    return base64.b64encode(buffer.tobytes()).decode("ascii")


def resize_max_side(image, max_side: int):
    height, width = image.shape[:2]
    scale = min(1.0, max_side / float(max(width, height)))
    if scale >= 1:
        return image
    return cv2.resize(image, (int(width * scale), int(height * scale)), interpolation=cv2.INTER_AREA)


def normalize_rectangular_object(image):
    working = resize_max_side(image, 1400)
    ratio_x = image.shape[1] / working.shape[1]
    ratio_y = image.shape[0] / working.shape[0]

    gray = cv2.cvtColor(working, cv2.COLOR_BGR2GRAY)
    gray = cv2.GaussianBlur(gray, (5, 5), 0)
    edges = cv2.Canny(gray, 55, 145)
    kernel = np.ones((3, 3), np.uint8)
    edges = cv2.dilate(edges, kernel, iterations=1)

    contours, _ = cv2.findContours(edges, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    image_area = working.shape[0] * working.shape[1]
    candidates = []

    for contour in contours:
        area = cv2.contourArea(contour)
        if area < image_area * 0.08 or area > image_area * 0.96:
            continue

        perimeter = cv2.arcLength(contour, True)
        approx = cv2.approxPolyDP(contour, 0.025 * perimeter, True)
        rect = cv2.minAreaRect(contour)
        box = cv2.boxPoints(rect)
        box = np.array(box, dtype="float32")
        ordered = order_points(box)
        width_a = np.linalg.norm(ordered[2] - ordered[3])
        width_b = np.linalg.norm(ordered[1] - ordered[0])
        height_a = np.linalg.norm(ordered[1] - ordered[2])
        height_b = np.linalg.norm(ordered[0] - ordered[3])
        max_width = max(width_a, width_b)
        max_height = max(height_a, height_b)
        aspect = max_height / max(1.0, max_width)

        if aspect < 1.05 or aspect > 1.95:
            continue

        fill_ratio = area / max(1.0, max_width * max_height)
        center = np.mean(ordered, axis=0)
        center_penalty = (
            abs(center[0] - (working.shape[1] / 2)) / working.shape[1]
            + abs(center[1] - (working.shape[0] / 2)) / working.shape[0]
        )
        score = (area / image_area) * 0.62 + fill_ratio * 0.28 + (1 - min(0.75, center_penalty)) * 0.10

        candidates.append(
            {
                "score": float(score),
                "area": float(area),
                "points": ordered,
                "approx_points": int(len(approx)),
                "aspect": float(aspect),
            }
        )

    if not candidates:
        return {"ok": False, "normalized": None, "confidence": 0.0, "contour": None}

    best = max(candidates, key=lambda item: item["score"])
    points = best["points"].copy()
    points[:, 0] *= ratio_x
    points[:, 1] *= ratio_y

    normalized = four_point_transform(image, points)
    normalized = normalize_card_ratio(normalized)

    return {
        "ok": True,
        "normalized": normalized,
        "confidence": round(float(min(1.0, best["score"])), 4),
        "contour": [[int(point[0]), int(point[1])] for point in points],
    }


def order_points(points):
    rect = np.zeros((4, 2), dtype="float32")
    s = points.sum(axis=1)
    rect[0] = points[np.argmin(s)]
    rect[2] = points[np.argmax(s)]
    diff = np.diff(points, axis=1)
    rect[1] = points[np.argmin(diff)]
    rect[3] = points[np.argmax(diff)]
    return rect


def four_point_transform(image, points):
    rect = order_points(points)
    tl, tr, br, bl = rect
    width_a = np.linalg.norm(br - bl)
    width_b = np.linalg.norm(tr - tl)
    height_a = np.linalg.norm(tr - br)
    height_b = np.linalg.norm(tl - bl)
    max_width = max(1, int(max(width_a, width_b)))
    max_height = max(1, int(max(height_a, height_b)))
    destination = np.array(
        [[0, 0], [max_width - 1, 0], [max_width - 1, max_height - 1], [0, max_height - 1]],
        dtype="float32",
    )
    matrix = cv2.getPerspectiveTransform(rect, destination)
    return cv2.warpPerspective(image, matrix, (max_width, max_height))


def normalize_card_ratio(image):
    height, width = image.shape[:2]
    aspect = height / max(1, width)
    if aspect < 1.15 or aspect > 1.8:
        return resize_max_side(image, 1200)

    target_height = 900
    target_width = int(target_height / 1.395)
    return cv2.resize(image, (target_width, target_height), interpolation=cv2.INTER_AREA)


def draw_debug(image, result):
    debug = resize_max_side(image.copy(), 1200)
    if not result.get("contour"):
        return debug

    ratio_x = debug.shape[1] / image.shape[1]
    ratio_y = debug.shape[0] / image.shape[0]
    points = np.array(
        [[int(x * ratio_x), int(y * ratio_y)] for x, y in result["contour"]],
        dtype=np.int32,
    )
    cv2.polylines(debug, [points], True, (0, 220, 255), 4)
    for index, point in enumerate(points):
        cv2.circle(debug, tuple(point), 8, (0, 80, 255), -1)
        cv2.putText(debug, str(index + 1), tuple(point), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (255, 255, 255), 2)

    return debug
