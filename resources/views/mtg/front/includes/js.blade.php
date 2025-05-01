<script>
let video;
let canvasOverlay;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

// Definir a proporção alvo (0.667 para cartas MTG) e o tamanho mínimo da carta (em pixels)
const targetAspectRatio = 0.667; // Largura / Altura
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

    // Converter a imagem para escala de cinza
    let gray = new cv.Mat();
    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);

    // Aplicar CLAHE para aumentar o contraste de maneira local
    let clahe = new cv.CLAHE();
    clahe.setClipLimit(2.0);
    clahe.setTileGridSize(new cv.Size(8, 8));
    clahe.apply(gray, gray);

    // Aplicar a detecção de bordas (Canny) com parâmetros ajustados
    let edges = new cv.Mat();
    cv.Canny(gray, edges, 100, 200);  // Ajuste os limiares de Canny

    // Encontrar contornos (para detectar a carta)
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();
    cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    // Desenhar todos os contornos encontrados (debug)
    cv.drawContours(mat, contours, -1, [255, 0, 0, 255], 2); // Contornos em vermelho

    // Processar contornos e detectar o retângulo da carta
    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);
        let approx = new cv.Mat();

        // Aproximar o contorno para um polígono (para garantir que temos um retângulo)
        cv.approxPolyDP(contour, approx, 0.02 * cv.arcLength(contour, true), true);

        // Verificar se é um retângulo (4 pontos)
        if (approx.rows == 4) {
            // Obter os pontos do retângulo
            let points = [];
            for (let j = 0; j < 4; j++) {
                points.push(new cv.Point(approx.data32S[j * 2], approx.data32S[j * 2 + 1]));
            }

            // Desenhar o contorno ao redor da carta
            let boundingRect = cv.boundingRect(contour);
            cv.rectangle(mat, boundingRect, [0, 255, 0, 255], 3);  // Contorno verde ao redor da carta

            // Calcular a proporção do retângulo (largura / altura)
            let aspectRatio = boundingRect.width / boundingRect.height;

            // Verificar se a proporção do retângulo está dentro da faixa de uma carta (0.667 ± 0.1)
            if (aspectRatio >= targetAspectRatio * 0.9 && aspectRatio <= targetAspectRatio * 1.1) {
                // Verificar se a largura e altura do retângulo estão dentro do tamanho mínimo
                if (boundingRect.width >= minWidth && boundingRect.height >= minHeight) {
                    // O retângulo é válido e tem o tamanho mínimo necessário
                    let croppedMat = mat.roi(boundingRect); // Recorta a imagem no OpenCV

                    // Verifique a imagem recortada
                    let croppedImage = new cv.Mat();
                    cv.cvtColor(croppedMat, croppedImage, cv.COLOR_RGBA2BGR);
                    console.log('Imagem recortada:', croppedImage);

                    // Converter para base64
                    let croppedCanvas = document.createElement('canvas');
                    cv.imshow(croppedCanvas, croppedImage);  // Exibe no canvas
                    let croppedBase64 = croppedCanvas.toDataURL('image/jpeg');

                    // Enviar o crop para o servidor
                    $.ajax({
                        url: "{{ route('mtg.processImage') }}",
                        type: 'POST',
                        data: JSON.stringify({
                            image: croppedBase64,
                            boundingBox: boundingRect
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
                }
            }
        }
    }

    // Libere a memória do OpenCV
    mat.delete();
    gray.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
}
</script>
