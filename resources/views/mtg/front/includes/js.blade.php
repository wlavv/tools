<script>

let video;
let canvasOverlay;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

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

            // Desenhar o retângulo ao redor da carta
            cv.drawContours(mat, contours, i, [0, 255, 0, 255], 3);

            // Aqui você pode ajustar o crop da imagem com base nos pontos do retângulo
            let boundingRect = cv.boundingRect(contour);
            const croppedImage = img.get(boundingRect.x, boundingRect.y, boundingRect.width, boundingRect.height);

            // Exibir o cropped (apenas a carta)
            let croppedBase64 = croppedImage.canvas.toDataURL('image/jpeg');

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
                    // Exibe a resposta do backend (pHash, etc.)
                    $('#info').html("📛 pHash: " + response.pHash);
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

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
};

</script>