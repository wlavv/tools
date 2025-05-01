<script>

let video;
let canvasOverlay;
let isCapturing = true;
let lastRequestTime = 0;
let detector;

const cropWidth = 1200;
const cropHeight = 900;

async function onOpenCvReady() {
    // Carregando o modelo COCO-SSD para detecção de objetos
    detector = await cocoSsd.load();
    console.log('Modelo COCO-SSD carregado com sucesso!');
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

    // Aguardar o carregamento do modelo COCO-SSD
    onOpenCvReady();
};

window.draw = function () {
    clear();  // Limpa o canvas

    if (!isCapturing || !detector) return;

    // Captura o vídeo em cada frame
    let img = video.get();
    img.loadPixels();

    // Usando o modelo COCO-SSD para detectar objetos no frame atual
    detector.detect(img.canvas).then(predictions => {
        // Loop pelas predições e desenha as bordas ao redor dos objetos
        predictions.forEach(prediction => {
            // Desenha a borda verde ao redor do objeto detectado
            noFill();
            stroke(0, 255, 0);  // Cor verde
            strokeWeight(3);     // Espessura da borda
            rectMode(CORNER);
            rect(prediction.bbox[0], prediction.bbox[1], prediction.bbox[2], prediction.bbox[3]);

            // Exibe o nome do objeto e a confiança
            textSize(18);
            text(prediction.class, prediction.bbox[0], prediction.bbox[1] - 10);
        });
    }).catch(err => {
        console.error("Erro na detecção de objetos: ", err);
    });
};



</script>