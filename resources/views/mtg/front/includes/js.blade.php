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
    
    // Verificar se a imagem foi desenhada corretamente no canvas
    console.log("Imagem desenhada no canvas:", canvas);

    let mat;
    try {
        mat = cv.imread(canvas);  // Agora usa esse canvas com OpenCV
    } catch (e) {
        console.error("Erro ao ler a imagem com OpenCV:", e);
        return;
    }

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

    // Processar contornos e detectar o retângulo da carta
    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);
        let approx = new cv.Mat();

        // Aproximar o contorno para um polígono (para garantir que temos um retângulo)
        cv.approxPolyDP(contour, approx, 0.02 * cv.arcLength(contour, true), true);

        // Verificar se é um retângulo
        if (approx.rows == 4) {
            // Desenhar o retângulo ao redor da carta
            let boundingRect = cv.boundingRect(contour);
            console.log('Bounding Rect:', boundingRect);
            //cv.rectangle(mat, boundingRect, new cv.Scalar(0, 255, 0), 5);  // Contorno verde com espessura maior

            // Calcular a proporção do retângulo
            let aspectRatio = boundingRect.width / boundingRect.height;
            console.log("Aspect Ratio:", aspectRatio);

            // Verificar se a proporção do retângulo está dentro da faixa de uma carta (1.5 ± 0.1)
            if (aspectRatio >= targetAspectRatio * 0.9 && aspectRatio <= targetAspectRatio * 1.1) {
                // Verificar se a largura e altura do retângulo estão dentro do tamanho mínimo
                if (boundingRect.width >= minWidth && boundingRect.height >= minHeight) {
                    // O retângulo é válido e tem o tamanho mínimo necessário
                    // Captura o crop da imagem com base na boundingRect
                    let croppedMat = mat.roi(boundingRect); // Recorta a imagem no OpenCV
                    console.log("Cropped Mat:", croppedMat);

                    // Converter a imagem recortada para base64
                    let croppedImage = new cv.Mat();
                    cv.cvtColor(croppedMat, croppedImage, cv.COLOR_RGBA2BGR);
                    let croppedCanvas = document.createElement('canvas');
                    cv.imshow(croppedCanvas, croppedImage);  // Exibe no canvas
                    let croppedBase64 = croppedCanvas.toDataURL('image/jpeg');

                    console.log('Base64 da imagem recortada:', croppedBase64);

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
                    setTimeout(() => { isCapturing = true }, 5000);
                }
            }
        }
    }

    // Exibir o resultado final com contornos
    cv.imshow(canvasOverlay, mat);  // Exibe a imagem no canvasOverlay

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
};
</script>
