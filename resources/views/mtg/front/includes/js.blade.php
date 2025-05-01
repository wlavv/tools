
<script>
//     template = loadImage('/images/mtg/templates/land artefact.png');  // Substitua pelo caminho correto do template


let video;
let canvasOverlay;
let isCapturing = true;
let templateImage;
let matchThreshold = 0.8; // Limite de correspondência

const cropWidth = 1200;
const cropHeight = 900;

// Definir a proporção alvo (1.5 para cartas MTG) e o tamanho mínimo da carta (em pixels)
const minWidth = 100;  // Largura mínima em pixels
const minHeight = 150; // Altura mínima em pixels

// Carregar a imagem do template (a carta de Magic)
function preload() {
    template = loadImage('/images/mtg/templates/land artefact.png');
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

// Função para processar cada frame e detectar a carta
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

    // Converter a imagem capturada para escala de cinza
    let gray = new cv.Mat();
    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);

    // Converter o template para escala de cinza também
    let templateGray = new cv.Mat();
    cv.cvtColor(cv.imread(templateImage.canvas), templateGray, cv.COLOR_RGBA2GRAY);

    // Aplicar Template Matching
    let result = new cv.Mat();
    cv.matchTemplate(gray, templateGray, result, cv.TM_CCOEFF_NORMED);

    // Obter os pontos de coincidência
    let minMax = cv.minMaxLoc(result);
    let maxPoint = minMax.maxLoc;
    let matchVal = minMax.maxVal;

    // Se a correspondência for suficientemente boa, desenhar o retângulo ao redor
    if (matchVal >= matchThreshold) {
        // Desenhar um retângulo verde em volta da carta detectada
        let point1 = new cv.Point(maxPoint.x, maxPoint.y);
        let point2 = new cv.Point(maxPoint.x + templateImage.width, maxPoint.y + templateImage.height);
        cv.rectangle(mat, point1, point2, [0, 255, 0, 255], 3);
        
        // Adicionar mais lógica para capturar o crop ou outras ações, se necessário
        console.log("Carta detectada com valor de correspondência: ", matchVal);
    }

    // Exibir a imagem com o retângulo no canvas
    cv.imshow(canvasOverlay, mat);

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete();
    result.delete();
    templateGray.delete();
};


</script>
