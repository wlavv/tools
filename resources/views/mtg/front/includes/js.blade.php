<script>

let video;
let canvasOverlay;
let boundingBox = { x: 0, y: 0, width: 0, height: 0 };
let isCapturing = true;
let lastRequestTime = 0;

const cropWidth = 1200;
const cropHeight = 900;

function onOpenCvReady() {
    console.log('OpenCV.js loaded ✅');
}

window.setup = function () {
    const canvas = createCanvas(cropWidth, cropHeight);
    canvasOverlay = canvas.elt;
    canvasOverlay.style.position = 'absolute';
    canvasOverlay.style.top = '0';
    canvasOverlay.style.left = '0';
    canvasOverlay.style.zIndex = '10';

    video = createCapture(VIDEO, () => {
        const videoContainer = document.getElementById('videoContainer');
        videoContainer.style.position = 'relative';
        video.elt.style.position = 'absolute';
        video.elt.style.top = '0';
        video.elt.style.left = '0';
        video.elt.width = 1200;
        video.elt.height = 900;
        videoContainer.appendChild(video.elt);
        videoContainer.appendChild(canvasOverlay);
    });

    video.size(cropWidth, cropHeight);
};

window.draw = function () {
    clear();

    let img = video.get();
    img.loadPixels();

    if (typeof cv === 'undefined' || !cv.imread) {
        return;
    }

    const src = cv.imread(img.canvas);
    const gray = new cv.Mat();
    const edges = new cv.Mat();

    cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
    cv.GaussianBlur(gray, gray, new cv.Size(5, 5), 0);
    cv.Canny(gray, edges, 50, 150);

    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();

    cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    let maxArea = 0;
    let bestRect = null;

    for (let i = 0; i < contours.size(); i++) {
        let cnt = contours.get(i);
        let rect = cv.boundingRect(cnt);

        const aspectRatio = rect.width / rect.height;
        const area = rect.width * rect.height;

        // Filtro: proporção aproximada de carta + área mínima
        if (aspectRatio > 0.6 && aspectRatio < 0.8 && area > 10000) {
            if (area > maxArea) {
                maxArea = area;
                bestRect = rect;
            }
        }
        cnt.delete();
    }

    if (bestRect) {
        boundingBox = {
            x: bestRect.x,
            y: bestRect.y,
            width: bestRect.width,
            height: bestRect.height
        };

        // Só envia para servidor 1x por minuto
        const now = Date.now();
        if (now - lastRequestTime > 60000 && isCapturing) {
            const croppedCanvas = document.createElement("canvas");
            croppedCanvas.width = bestRect.width;
            croppedCanvas.height = bestRect.height;
            const croppedCtx = croppedCanvas.getContext("2d");
            croppedCtx.drawImage(video.elt, bestRect.x, bestRect.y, bestRect.width, bestRect.height, 0, 0, bestRect.width, bestRect.height);
            const base64Image = croppedCanvas.toDataURL('image/jpeg');

            $.ajax({
                url: "{{ route('mtg.processImage') }}",
                type: 'POST',
                data: JSON.stringify({ image: base64Image }),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    const imgElement = document.createElement("img");
                    imgElement.src = response.croppedImageUrl;
                    imgElement.style.width = '100%';
                    imgElement.style.height = 'auto';

                    const cropZone = document.getElementById('cropZone');
                    cropZone.innerHTML = '';
                    cropZone.appendChild(imgElement);

                    lastRequestTime = now;
                }
            });
        }
    }

    noFill();
    stroke('lime');
    strokeWeight(3);
    rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);

    src.delete();
    gray.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
};

</script>
