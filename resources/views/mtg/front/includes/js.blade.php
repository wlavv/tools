<script>
let video;
let canvasOverlay;
let isCapturing = true;
const cropWidth = 1200;
const cropHeight = 900;

async function onOpenCvReady() {
    console.log('OpenCV carregado com sucesso!');
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
    onOpenCvReady();
};

function draw() {
    if (!isCapturing) return;

    // Captura o frame atual com o método getImageData
    let src = cv.matFromImageData(getImageData(video));
    let gray = new cv.Mat();
    cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);  // Converte para escala de cinza

    let edges = new cv.Mat();
    cv.Canny(gray, edges, 100, 200);  // Detecta bordas

    // Encontrar contornos
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();
    cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    // Filtra os contornos para encontrar quadriláteros (forma da carta)
    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);
        let approx = new cv.Mat();
        cv.approxPolyDP(contour, approx, 0.02 * cv.arcLength(contour, true), true); // Aproxima o contorno

        // Se encontrar um quadrilátero (carta), aplica a transformação
        if (approx.rows == 4) {
            let points = [];
            for (let j = 0; j < 4; j++) {
                points.push([approx.data32S[j * 2], approx.data32S[j * 2 + 1]]);
            }

            points = orderPoints(points);  // Ordena os pontos

            // Aplica a transformação de perspectiva
            let width = 224;  // Tamanho da carta
            let height = 324;
            
            // Converte os pontos para o formato adequado
            let srcPoints = cv.matFromArray([
                points[0][0], points[0][1],
                points[1][0], points[1][1],
                points[2][0], points[2][1],
                points[3][0], points[3][1]
            ], cv.CV_32F);

            let dstPoints = cv.matFromArray([
                0, 0,
                width - 1, 0,
                width - 1, height - 1,
                0, height - 1
            ], cv.CV_32F);

            let M = cv.getPerspectiveTransform(srcPoints, dstPoints);  // Matriz de transformação

            let dstImg = new cv.Mat();
            cv.warpPerspective(src, dstImg, M, new cv.Size(width, height));

            // Exibe a imagem recortada com a carta
            cv.imshow('canvasOverlay', dstImg);

            // Limpeza dos recursos OpenCV
            src.delete(); gray.delete(); edges.delete(); contours.delete(); hierarchy.delete();

            // Envia a imagem processada via AJAX (para backend)
            sendProcessedImageToBackend(dstImg);

            return dstImg;  // Retorna a imagem transformada (corte)
        }
    }

    // Limpeza dos recursos do OpenCV
    src.delete(); gray.delete(); edges.delete(); contours.delete(); hierarchy.delete();
}

// Função para ordenar os pontos do quadrilátero (como fizemos no Python)
function orderPoints(points) {
    let rect = new Array(4);
    let s = points.map(p => p[0] + p[1]);
    let diff = points.map(p => p[0] - p[1]);

    rect[0] = points[s.indexOf(Math.min(...s))];  // topo-esquerda
    rect[2] = points[s.indexOf(Math.max(...s))];  // fundo-direita
    rect[1] = points[diff.indexOf(Math.min(...diff))];  // topo-direita
    rect[3] = points[diff.indexOf(Math.max(...diff))];  // fundo-esquerda

    return rect;
}
</script>
