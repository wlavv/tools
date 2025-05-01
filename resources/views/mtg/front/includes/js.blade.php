<script>

let video;
let canvasOverlay;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

// Definir a proporção alvo (1.5 para cartas MTG) e o tamanho mínimo da carta (em pixels)
const targetAspectRatio = 1.5; // Largura / Altura
const minWidth = 50;  // Largura mínima em pixels
const minHeight = 50; // Altura mínima em pixels

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
    img.loadPixels();

    // Converte a imagem para Mat (OpenCV)
    let mat = cv.imread(img.canvas);

    // Verifica se a conversão foi bem-sucedida
    if (mat.empty()) {
        console.error("Erro ao carregar a imagem para o formato cv.Mat.");
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
            // Obter os pontos do retângulo
            let points = [];
            for (let j = 0; j < 4; j++) {
                points.push(new cv.Point(approx.data32S[j * 2], approx.data32S[j * 2 + 1]));
            }

            // Desenhar o retângulo ao redor da carta (bordas verdes) na imagem original (mat)
            cv.drawContours(mat, contours, i, [0, 255, 0, 255], 3);

            // Aqui você pode ajustar o crop da imagem com base nos pontos do retângulo
            let boundingRect = cv.boundingRect(contour);

            // Calcular a proporção do retângulo
            let aspectRatio = boundingRect.width / boundingRect.height;

            // Verificar se a proporção do retângulo está dentro da faixa de uma carta (1.5 ± 0.1)
            if (aspectRatio >= targetAspectRatio * 0.9 && aspectRatio <= targetAspectRatio * 1.1) {
                // Verificar se a largura e altura do retângulo estão dentro do tamanho mínimo
                if (boundingRect.width >= minWidth && boundingRect.height >= minHeight) {
                    // O retângulo é válido e tem o tamanho mínimo necessário
                    // Captura o crop da imagem com base na boundingRect
                    const croppedImage = mat.roi(boundingRect);  // Usando ROI para cortar a área desejada

                    // Verificar se a imagem foi corretamente cortada
                    if (croppedImage.empty()) {
                        console.error("Erro ao cortar a imagem para o formato cv.Mat.");
                        return;
                    }

                    // Codificar a imagem para Base64 usando imencode
                    let buf = new cv.Mat();
                    try {
                        cv.imencode('.jpg', croppedImage, buf); // Codifica para JPG e armazena em 'buf'
                    } catch (err) {
                        console.error("Erro ao codificar a imagem: ", err);
                        return;
                    }

                    // Converter o buffer para base64
                    let base64String = 'data:image/jpeg;base64,' + buf.toString('base64'); // Converte para base64

                    console.log(boundingRect);

                    // Enviar o crop para o servidor
                    $.ajax({
                        url: "{{ route('mtg.processImage') }}",
                        type: 'POST',
                        data: JSON.stringify({
                            image: base64String,
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

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
};

</script>
