<script>
let video;
let canvasOverlay;
let isCapturing = true;

const cropWidth = 1920;  // Aumentei para uma resolução maior
const cropHeight = 1080; // Aumentei para uma resolução maior

// Definir a proporção alvo (1.5 para cartas MTG) e o tamanho mínimo da carta (em pixels)
const targetAspectRatio = 1.5; // Largura / Altura
const minWidth = 100;  // Largura mínima em pixels
const minHeight = 150; // Altura mínima em pixels

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

    // Ajuste da resolução da captura de vídeo
    video.size(cropWidth, cropHeight);
};

// Função para processar cada frame e detectar a carta
window.draw = function () {
    clear();  // Limpa o canvas

    if (!isCapturing || !video.loadedmetadata) return;  // Garante que o vídeo foi carregado corretamente

    let img = video.get();  // Captura o frame atual do vídeo
    if (!img) {
        console.error("Erro ao capturar a imagem do vídeo.");
        return;
    }

    let canvas = document.createElement('canvas');
    canvas.width = cropWidth;
    canvas.height = cropHeight;
    let ctx = canvas.getContext('2d');
    ctx.drawImage(img.canvas, 0, 0, cropWidth, cropHeight);  // Desenha no canvas temporário

    let mat;
    try {
        mat = cv.imread(canvas);  // Agora usa esse canvas com OpenCV
    } catch (err) {
        console.error("Erro ao ler a imagem com OpenCV:", err);
        return;
    }

    // Converter a imagem para escala de cinza
    let gray = new cv.Mat();
    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);

    // Aplicar a detecção de bordas (Canny) - ajustar o threshold para garantir que pegamos bordas
    let edges = new cv.Mat();
    cv.Canny(gray, edges, 100, 200);  // Ajustei os valores para bordas mais visíveis

    // Encontrar contornos (para detectar a carta)
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();
    try {
        cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);
    } catch (err) {
        console.error("Erro ao encontrar contornos:", err);
        return;
    }

    // Desenhar todos os contornos encontrados, sem verificar a proporção da carta
    cv.drawContours(mat, contours, -1, [255, 0, 0, 255], 2);  // Contornos em vermelho para visualização

    // Processar contornos e detectar o retângulo da carta (aqui só estamos a testar os contornos)
    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);
        let approx = new cv.Mat();

        // Aproximar o contorno para um polígono (para garantir que temos um retângulo)
        try {
            cv.approxPolyDP(contour, approx, 0.02 * cv.arcLength(contour, true), true);
        } catch (err) {
            console.error("Erro ao aproximar o contorno:", err);
            continue;
        }

        // Verificar se é um retângulo
        if (approx.rows == 4) {
            // Desenhar o retângulo
            let boundingRect = cv.boundingRect(contour);
            cv.rectangle(mat, boundingRect, [0, 255, 0, 255], 3);  // Desenhar o retângulo em verde
        }
    }

    // Exibir o resultado (vídeo com contornos e retângulos desenhados)
    try {
        cv.imshow(canvasOverlay, mat);  // Exibe a imagem no canvasOverlay
    } catch (err) {
        console.error("Erro ao exibir a imagem no canvasOverlay:", err);
    }

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
};
</script>
