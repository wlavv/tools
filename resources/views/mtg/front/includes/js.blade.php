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
        video.elt.width = cropWidth;
        video.elt.height = cropHeight;
        videoContainer.appendChild(video.elt);
        videoContainer.appendChild(canvasOverlay);
    });

    video.size(cropWidth, cropHeight);
};

window.draw = function () {
    clear();  // Limpa o canvas

    if (!isCapturing) return;

    // Captura o vídeo em cada frame
    let img = video.get();
    img.loadPixels();

    // Acessa os pixels da captura
    let canvas = document.createElement('canvas');
    let ctx = canvas.getContext('2d');
    canvas.width = img.width;
    canvas.height = img.height;
    ctx.drawImage(img.canvas, 0, 0);

    // Converte a imagem do canvas para ImageData
    let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

    // Cria a Mat a partir do ImageData
    let src = cv.matFromImageData(imageData);

    let gray = new cv.Mat();
    let blurred = new cv.Mat();
    let thresh = new cv.Mat();
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();

    // Converte a imagem para escala de cinza
    cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
    cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);
    cv.threshold(blurred, thresh, 120, 255, cv.THRESH_BINARY);

    // Encontra os contornos da imagem
    cv.findContours(thresh, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    let maxArea = 0;
    let bestContour = null;

    // Encontrar o maior contorno
    for (let i = 0; i < contours.size(); i++) {
        let cnt = contours.get(i);
        let area = cv.contourArea(cnt);
        if (area > maxArea) {
            maxArea = area;
            bestContour = cnt;
        }
    }

    // Se um contorno válido for encontrado, ajusta o bounding box
    if (bestContour && maxArea > 10000) {  // Tamanho mínimo do contorno
        let rect = cv.boundingRect(bestContour);
        boundingBox = {
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height
        };

        // Desenha a borda verde ao redor do contorno detectado
        noFill();
        stroke(0, 255, 0);  // Cor verde
        strokeWeight(3);     // Espessura da borda
        rectMode(CORNER);
        rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);  // Desenha o retângulo

        // Recorta a imagem com base na bounding box detectada
        let cropped = img.get(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);
        let base64Image = cropped.canvas.toDataURL('image/jpeg');

        // Envia a imagem para o backend via AJAX
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
                // Atualiza a imagem recortada no front-end
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

        // Pausa a captura por 5 segundos
        isCapturing = false;
        setTimeout(() => { isCapturing = true }, 5000);
    }

    // Liberando memória após o uso das Matrices
    src.delete(); gray.delete(); blurred.delete(); thresh.delete();
    contours.delete(); hierarchy.delete();
};


</script>