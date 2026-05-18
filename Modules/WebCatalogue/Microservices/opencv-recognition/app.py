import base64
import os
from typing import Optional

import cv2
import numpy as np
from fastapi import FastAPI, File, Form, Header, HTTPException, UploadFile
from pydantic import BaseModel


APP_TOKEN = os.getenv("WEBCATALOGUE_OPENCV_TOKEN", "")
MAX_IMAGE_BYTES = int(os.getenv("WEBCATALOGUE_OPENCV_MAX_IMAGE_BYTES", "12000000"))

app = FastAPI(title="WebCatalogue OpenCV Recognition", version="0.1.0")


class MarkerPayload(BaseModel):
    keypoints: list
    descriptors: list
    width: Optional[int] = None
    height: Optional[int] = None


class CompareMarkersPayload(BaseModel):
    query: MarkerPayload
    reference: MarkerPayload


class BatchReferenceMarkersPayload(MarkerPayload):
    id: str


class CompareMarkersBatchPayload(BaseModel):
    query: MarkerPayload
    references: list[BatchReferenceMarkersPayload]


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


@app.post("/recognition/markers")
async def markers(
    image: UploadFile = File(...),
    max_markers: int = Form(250),
    preprocess: str = Form("clahe"),
    authorization: Optional[str] = Header(default=None),
):
    require_token(authorization)

    content = await image.read()
    if len(content) > MAX_IMAGE_BYTES:
        raise HTTPException(status_code=413, detail="Image too large")

    source = decode_image(content)
    if source is None:
        raise HTTPException(status_code=422, detail="Could not decode image")

    marker_set = extract_orb_markers(source, max_markers=max_markers, preprocess=preprocess)
    return {
        "ok": True,
        "algorithm": "orb_v1",
        "descriptor_type": "ORB",
        "preprocess": marker_set.get("preprocess", normalize_marker_preprocess(preprocess)),
        "width": int(source.shape[1]),
        "height": int(source.shape[0]),
        **marker_set,
    }


@app.post("/recognition/identifiers")
async def identifiers(
    image: UploadFile = File(...),
    authorization: Optional[str] = Header(default=None),
):
    require_token(authorization)

    content = await image.read()
    if len(content) > MAX_IMAGE_BYTES:
        raise HTTPException(status_code=413, detail="Image too large")

    source = decode_image(content)
    if source is None:
        raise HTTPException(status_code=422, detail="Could not decode image")

    detected = extract_identifiers(source)
    return {
        "ok": True,
        "provider": "opencv",
        "width": int(source.shape[1]),
        "height": int(source.shape[0]),
        "identifiers": detected,
    }


@app.post("/recognition/compare-markers")
async def compare_markers(
    payload: CompareMarkersPayload,
    authorization: Optional[str] = Header(default=None),
):
    require_token(authorization)

    result = compare_orb_marker_sets(payload.query.descriptors, payload.reference.descriptors)
    return {"ok": True, **result}


@app.post("/recognition/compare-markers-batch")
async def compare_markers_batch(
    payload: CompareMarkersBatchPayload,
    authorization: Optional[str] = Header(default=None),
):
    require_token(authorization)

    results = []
    for reference in payload.references[:500]:
        result = compare_orb_marker_sets(payload.query.descriptors, reference.descriptors)
        results.append({"id": reference.id, **result})

    return {"ok": True, "count": len(results), "results": results}


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


def extract_identifiers(image):
    working = resize_max_side(image, 1600)
    identifiers = []
    identifiers.extend(detect_qr_codes(working))
    identifiers.extend(detect_barcodes(working))

    unique = {}
    for item in identifiers:
        raw_value = str(item.get("rawValue", "")).strip()
        if not raw_value:
            continue
        fmt = str(item.get("format", "unknown")).strip() or "unknown"
        unique[(fmt.lower(), raw_value)] = {
            "format": fmt,
            "rawValue": raw_value,
            "source": item.get("source", "opencv"),
            "points": item.get("points"),
            "confidence": item.get("confidence"),
        }

    return list(unique.values())


def detect_qr_codes(image):
    detector = cv2.QRCodeDetector()
    results = []

    try:
        ok, decoded_info, points, _ = detector.detectAndDecodeMulti(image)
        if ok and decoded_info:
            for index, raw_value in enumerate(decoded_info):
                raw_value = str(raw_value or "").strip()
                if not raw_value:
                    continue
                item_points = points[index] if points is not None and len(points) > index else None
                results.append(
                    {
                        "format": "qr_code",
                        "rawValue": raw_value,
                        "source": "opencv_qrcode_detector",
                        "points": points_payload(item_points),
                        "confidence": 1.0,
                    }
                )
    except Exception:
        pass

    if results:
        return results

    try:
        raw_value, points, _ = detector.detectAndDecode(image)
        raw_value = str(raw_value or "").strip()
        if raw_value:
            results.append(
                {
                    "format": "qr_code",
                    "rawValue": raw_value,
                    "source": "opencv_qrcode_detector",
                    "points": points_payload(points),
                    "confidence": 1.0,
                }
            )
    except Exception:
        pass

    return results


def detect_barcodes(image):
    if not hasattr(cv2, "barcode") or not hasattr(cv2.barcode, "BarcodeDetector"):
        return []

    detector = cv2.barcode.BarcodeDetector()
    results = []

    try:
        detected = detector.detectAndDecode(image)
    except Exception:
        return []

    if not isinstance(detected, tuple) or len(detected) < 3:
        return []

    decoded_info = detected[0]
    decoded_type = detected[1] if len(detected) > 1 else None
    points = detected[2] if len(detected) > 2 else None

    if isinstance(decoded_info, str):
        decoded_values = [decoded_info]
    else:
        decoded_values = list(decoded_info or [])

    if isinstance(decoded_type, str):
        decoded_types = [decoded_type]
    else:
        decoded_types = list(decoded_type or [])

    for index, raw_value in enumerate(decoded_values):
        raw_value = str(raw_value or "").strip()
        if not raw_value:
            continue
        fmt = decoded_types[index] if len(decoded_types) > index and decoded_types[index] else "barcode"
        item_points = points[index] if points is not None and len(points) > index else points
        results.append(
            {
                "format": normalize_barcode_format(fmt),
                "rawValue": raw_value,
                "source": "opencv_barcode_detector",
                "points": points_payload(item_points),
                "confidence": 1.0,
            }
        )

    return results


def normalize_barcode_format(value):
    value = str(value or "barcode").strip().lower().replace("-", "_")
    mapping = {
        "ean_13": "ean_13",
        "ean13": "ean_13",
        "ean_8": "ean_8",
        "ean8": "ean_8",
        "upc_a": "upc_a",
        "upca": "upc_a",
        "upc_e": "upc_e",
        "upce": "upc_e",
        "code_128": "code_128",
        "code128": "code_128",
        "code_39": "code_39",
        "code39": "code_39",
        "qr_code": "qr_code",
    }
    return mapping.get(value, value or "barcode")


def points_payload(points):
    if points is None:
        return None

    array = np.array(points).reshape(-1, 2)
    return [[int(round(float(point[0]))), int(round(float(point[1])))] for point in array]


def extract_orb_markers(image, max_markers: int = 250, preprocess: str = "clahe"):
    max_markers = max(20, min(1000, int(max_markers or 250)))
    preprocess = normalize_marker_preprocess(preprocess)
    normalized = resize_max_side(image, 1200)
    gray = preprocess_marker_image(normalized, preprocess)
    orb = cv2.ORB_create(nfeatures=max_markers, scaleFactor=1.2, nlevels=8, edgeThreshold=16, patchSize=31)
    keypoints, descriptors = orb.detectAndCompute(gray, None)

    if descriptors is None or not keypoints:
        return {
            "marker_count": 0,
            "marker_hash": None,
            "preprocess": preprocess,
            "keypoints": [],
            "descriptors": [],
        }

    ranked = sorted(
        zip(keypoints, descriptors.tolist()),
        key=lambda item: item[0].response,
        reverse=True,
    )[:max_markers]

    keypoints_payload = []
    descriptors_payload = []
    for keypoint, descriptor in ranked:
        keypoints_payload.append(
            {
                "x": round(float(keypoint.pt[0]), 2),
                "y": round(float(keypoint.pt[1]), 2),
                "size": round(float(keypoint.size), 2),
                "angle": round(float(keypoint.angle), 2),
                "response": round(float(keypoint.response), 6),
                "octave": int(keypoint.octave),
            }
        )
        descriptors_payload.append([int(value) for value in descriptor])

    return {
        "marker_count": len(descriptors_payload),
        "marker_hash": marker_hash(descriptors_payload),
        "preprocess": preprocess,
        "keypoints": keypoints_payload,
        "descriptors": descriptors_payload,
    }


def normalize_marker_preprocess(value):
    value = str(value or "clahe").strip().lower().replace("-", "_")
    if value in {"gray", "none", "raw"}:
        return "gray"
    if value in {"equalize", "equalize_hist", "hist"}:
        return "equalize"
    if value in {"blur", "gaussian", "gaussian_blur"}:
        return "blur"
    return "clahe"


def preprocess_marker_image(image, mode: str):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    if mode == "gray":
        return gray
    if mode == "equalize":
        return cv2.equalizeHist(gray)
    if mode == "blur":
        blurred = cv2.GaussianBlur(gray, (3, 3), 0)
        return cv2.equalizeHist(blurred)

    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    return clahe.apply(gray)


def marker_hash(descriptors):
    if not descriptors:
        return None

    array = np.array(descriptors, dtype=np.uint8)
    means = np.mean(array, axis=0)
    bits = "".join("1" if value >= 127 else "0" for value in means[:64])
    return bits[:64]


def compare_orb_marker_sets(query_descriptors, reference_descriptors):
    if not query_descriptors or not reference_descriptors:
        return {"score": 0.0, "matches": 0, "good_matches": 0, "inlier_ratio": 0.0}

    query = np.array(query_descriptors, dtype=np.uint8)
    reference = np.array(reference_descriptors, dtype=np.uint8)
    if len(query.shape) != 2 or len(reference.shape) != 2:
        return {"score": 0.0, "matches": 0, "good_matches": 0, "inlier_ratio": 0.0}

    matcher = cv2.BFMatcher(cv2.NORM_HAMMING, crossCheck=False)
    raw_matches = matcher.knnMatch(query, reference, k=2)
    good = []
    for match_pair in raw_matches:
        if len(match_pair) < 2:
            continue
        first, second = match_pair
        if first.distance < 0.76 * second.distance:
            good.append(first)

    denominator = max(1, min(len(query), len(reference)))
    match_ratio = len(good) / denominator
    score = min(100.0, match_ratio * 100.0)

    return {
        "score": round(float(score), 4),
        "matches": len(raw_matches),
        "good_matches": len(good),
        "inlier_ratio": round(float(match_ratio), 4),
    }
