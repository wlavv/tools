<script>
let video;
let canvasOverlay;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

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

    // Exibir a imagem original para depuração
    cv.imshow('canvasOverlay', mat);  // Exibe a imagem original no overlay

    // Converter a imagem para escala de cinza
    let gray = new cv.Mat();
    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);

    // Aplicar a detecção de bordas (Canny)
    let edges = new cv.Mat();
    cv.Canny(gray, edges, 50, 100);

    // Encontrar contornos (para detectar a carta)
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();
    cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    // Log de contornos encontrados
    console.log("Contornos encontrados: ", contours.size());

    // Processar contornos e detectar o retângulo da carta
    let largestBoundingRect = null;
    let largestArea = 0;

    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);
        let approx = new cv.Mat();

        // Aproximar o contorno para um polígono (para garantir que temos um retângulo)
        cv.approxPolyDP(contour, approx, 0.02 * cv.arcLength(contour, true), true);

        // Verificar se é um retângulo
        if (approx.rows == 4) {
            // Obter os pontos do retângulo
            let boundingRect = cv.boundingRect(contour);

            // Calcular a área do retângulo
            let area = boundingRect.width * boundingRect.height;

            // Verificar se a área do retângulo é a maior encontrada até agora
            if (area > largestArea) {
                largestBoundingRect = boundingRect;
                largestArea = area;
            }

            // Desenhar o retângulo ao redor da carta
            cv.drawContours(mat, contours, i, [0, 255, 0, 255], 5);  // Contorno verde com espessura maior
        }
    }

    // Se um retângulo válido foi encontrado, fazer o crop da maior carta detectada
    if (largestBoundingRect !== null) {
        console.log('Corte da maior carta:', largestBoundingRect);

        // Desenhar o retângulo da maior carta detectada
        cv.rectangle(mat, 
            new cv.Point(largestBoundingRect.x, largestBoundingRect.y), 
            new cv.Point(largestBoundingRect.x + largestBoundingRect.width, largestBoundingRect.y + largestBoundingRect.height), 
            new cv.Scalar(0, 255, 0), 5);  // Contorno verde com espessura maior

        // Captura o crop da imagem com base no maior boundingRect
        let croppedMat = mat.roi(largestBoundingRect); // Recorta a imagem no OpenCV

        // Converter a imagem recortada para base64
        let croppedImage = new cv.Mat();
        cv.cvtColor(croppedMat, croppedImage, cv.COLOR_RGBA2BGR);
        let croppedCanvas = document.createElement('canvas');
        cv.imshow(croppedCanvas, croppedImage);  // Exibe no canvas
        let croppedBase64 = croppedCanvas.toDataURL('image/jpeg');

        console.log('BoundingRect:', largestBoundingRect);
        console.log('Aspect Ratio:', largestBoundingRect.width / largestBoundingRect.height);

        // Enviar o crop para o servidor
        $.ajax({
            url: "{{ route('mtg.processImage') }}",
            type: 'POST',
            data: JSON.stringify({
                image: croppedBase64,
                boundingBox: largestBoundingRect
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#info').html("📛 pHash: " + response.pHash);

                let imgElement = document.createElement("img");
                imgElement.src = response.croppedImageUrl;
                imgElement.style.width = '100%';
                imgElement.style.height = 'auto';

                let cropZone = document.getElementById('cropZone');
                cropZone.innerHTML = '';
                cropZone.appendChild(imgElement);

                // Adiciona um delay de 10 segundos após a resposta do AJAX
                setTimeout(function() {
                    console.log('Atraso de 10 segundos completado');
                    isCapturing = true;
                }, 10000);
            },
            error: function () {
                $('#info').html("❌ Erro ao enviar imagem");
            }
        });

        // Pausa a captura por 5 segundos após o envio
        isCapturing = false;
        setTimeout(() => { isCapturing = true }, 5000);
    } else {
        console.log("Nenhum contorno válido encontrado");
    }

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
};

</script>
