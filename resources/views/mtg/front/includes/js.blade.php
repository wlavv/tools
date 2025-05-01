<script>

let video;
let canvasOverlay;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

let tracker;
let isTracking = false;  // Controla se o objeto está sendo rastreado

// Configurar o vídeo
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

// Função para iniciar o rastreamento (geralmente no primeiro clique ou em um quadro detectado)
function startTracking(boundingRect) {
    // Usar o TrackerMIL (suportado no OpenCV.js)
    tracker = new cv.TrackerMIL();
    tracker.start(video.get().canvas, boundingRect);
    isTracking = true;
}

// Função para processar cada frame e rastrear o objeto
window.draw = function () {
    clear();  // Limpa o canvas

    if (!isCapturing) return;

    let img = video.get();
    let canvas = document.createElement('canvas');
    canvas.width = cropWidth;
    canvas.height = cropHeight;
    let ctx = canvas.getContext('2d');
    ctx.drawImage(img.canvas, 0, 0, cropWidth, cropHeight);  // Desenha no canvas temporário
    let mat = cv.imread(canvas);  // Agora usa esse canvas com OpenCV

    // Criar a imagem em escala de cinza (gray) antes de realizar qualquer operação
    let gray = new cv.Mat();
    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);  // Converte a imagem para escala de cinza

    // Inicializar a variável edges aqui
    let edges = new cv.Mat();

    // Verificar se estamos rastreando
    if (isTracking) {
        let trackingResult = tracker.update(img.canvas);
        
        if (trackingResult) {
            // Desenhar o retângulo de rastreamento no canvas
            let rect = tracker.getPosition();
            cv.rectangle(mat, rect, [0, 255, 0, 255], 2);
        }
    } else {
        // Aplicar a detecção de bordas (Canny)
        cv.Canny(gray, edges, 50, 100);

        // Encontrar contornos
        let contours = new cv.MatVector();
        let hierarchy = new cv.Mat();
        cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

        // Processar os contornos e detectar o retângulo
        for (let i = 0; i < contours.size(); i++) {
            let contour = contours.get(i);
            let approx = new cv.Mat();
            cv.approxPolyDP(contour, approx, 0.02 * cv.arcLength(contour, true), true);

            // Verificar se é um retângulo
            if (approx.rows == 4) {
                let boundingRect = cv.boundingRect(contour);

                // Iniciar o rastreamento do primeiro objeto detectado
                startTracking(boundingRect);
            }
        }

        // Liberar os recursos de OpenCV
        contours.delete();
        hierarchy.delete();
    }

    // Exibe o canvas no overlay
    cv.imshow(canvasOverlay, mat);

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete(); // Libera a variável gray corretamente
    edges.delete(); // Libera a variável edges corretamente
};


</script>
