<script>

let video;
let canvasOverlay;
let isCapturing = true;
let detector;

const cropWidth = 1200;
const cropHeight = 900;

async function onOpenCvReady() {
    // Carregar o modelo COCO-SSD para detecção de objetos
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
    
    // Verifica se a imagem foi capturada corretamente
    if (!img) {
        console.error("Erro ao capturar o vídeo.");
        return;
    }

    img.loadPixels();  // Certifique-se de carregar os pixels da imagem

    // Usando o modelo COCO-SSD para detectar objetos no frame atual
    detector.detect(img.canvas).then(predictions => {
        predictions.forEach(prediction => {
            // Verifica se a classe do objeto detectado é uma carta
            if (prediction.class.toLowerCase() === 'card') {
                // Desenha a borda verde ao redor do objeto detectado
                noFill();
                stroke(0, 255, 0);  // Cor verde
                strokeWeight(3);     // Espessura da borda
                rectMode(CORNER);
                rect(prediction.bbox[0], prediction.bbox[1], prediction.bbox[2], prediction.bbox[3]);

                // Exibe o nome do objeto e a confiança
                textSize(18);
                text(prediction.class, prediction.bbox[0], prediction.bbox[1] - 10);

                // Captura o crop da imagem com base na bounding box
                const croppedImage = img.get(prediction.bbox[0], prediction.bbox[1], prediction.bbox[2], prediction.bbox[3]);

                // Verifica se o recorte foi feito corretamente
                if (!croppedImage) {
                    console.error("Erro ao fazer o recorte da imagem.");
                    return;
                }

                // Converte a imagem recortada para base64
                const base64Image = croppedImage.canvas.toDataURL('image/jpeg');

                // Envia a imagem para o backend via AJAX
                $.ajax({
                    url: "{{ route('mtg.processImage') }}",
                    type: 'POST',
                    data: JSON.stringify({
                        image: base64Image,
                        boundingBox: prediction.bbox
                    }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        // Exibe a resposta do backend (pHash, etc.)
                        $('#info').html("📛 pHash: " + response.pHash);

                        // Atualiza a imagem recortada no front-end
                        let imgElement = document.createElement("img");
                        imgElement.src = response.croppedImageUrl;
                        imgElement.style.width = '100%';
                        imgElement.style.height = 'auto';

                        let cropZone = document.getElementById('cropZone');
                        cropZone.innerHTML = '';
                        cropZone.appendChild(imgElement);
                    },
                    error: function () {
                        $('#info').html("❌ Erro ao enviar imagem");
                    }
                });

                // Pausa a captura por 5 segundos após o envio
                isCapturing = false;
                setTimeout(() => { isCapturing = true }, 5000);
            }
        });
    }).catch(err => {
        console.error("Erro na detecção de objetos: ", err);
    });
};

</script>
