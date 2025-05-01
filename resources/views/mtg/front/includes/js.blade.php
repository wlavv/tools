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

    if (!isCapturing) return;

    let img = video.get();
    img.loadPixels();

    // Converter o frame para OpenCV
    let canvas = img.canvas;
    let src = cv.matFromImageData(canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height));
    let gray = new cv.Mat();
    let blurred = new cv.Mat();
    let thresh = new cv.Mat();
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();

    cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
    cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);
    cv.threshold(blurred, thresh, 120, 255, cv.THRESH_BINARY);

    // Encontra os contornos
    cv.findContours(thresh, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    let maxArea = 0;
    let bestContour = null;

    // Encontrar o contorno com maior área
    for (let i = 0; i < contours.size(); i++) {
        let cnt = contours.get(i);
        let area = cv.contourArea(cnt);
        if (area > maxArea) {
            maxArea = area;
            bestContour = cnt;
        }
    }

    if (bestContour && maxArea > 10000) { // Verifica se o contorno é grande o suficiente
        let rect = cv.boundingRect(bestContour);
        boundingBox = {
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height
        };

        // Mostrar a bounding box no canvas
        noFill();
        stroke('lime');
        strokeWeight(3);
        rectMode(CORNER);
        rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);

        // Crop a imagem usando a bounding box
        let cropped = img.get(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);
        let base64Image = cropped.canvas.toDataURL('image/jpeg');

        // Enviar para backend
        $.ajax({
            url: "{{ route('mtg.processImage') }}",
            type: 'POST',
            data: JSON.stringify({
                image: base64Image,
                boundingBox: boundingBox
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                info.html("📛 pHash: " + response.pHash);

                let imgElement = document.createElement("img");
                imgElement.src = response.croppedImageUrl;
                imgElement.style.width = '100%';
                imgElement.style.height = 'auto';

                let cropZone = document.getElementById('cropZone');
                cropZone.innerHTML = '';
                cropZone.appendChild(imgElement);
            },
            error: function () {
                info.html("❌ Erro ao enviar imagem");
            }
        });

        // Pausar captura por 5 segundos
        isCapturing = false;
        setTimeout(() => { isCapturing = true }, 5000);
    }

    // Libertar memória
    src.delete(); gray.delete(); blurred.delete(); thresh.delete();
    contours.delete(); hierarchy.delete();
};

</script>
