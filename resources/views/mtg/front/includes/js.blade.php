
<script>
//     template = loadImage('/images/mtg/templates/land artefact.png');  // Substitua pelo caminho correto do template

let video;
let canvasOverlay;
let templateImg;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

// Definir a proporção alvo (1.5 para cartas MTG)
const targetAspectRatio = 1.5; // Largura / Altura
const minWidth = 100;  // Largura mínima em pixels
const minHeight = 150; // Altura mínima em pixels

// Carregar template (carta Magic: The Gathering)
function preload() {
    templateImg = loadImage("/images/mtg/templates/land artefact.png");  // Caminho correto para o template
}

// Configurar o vídeo
function setup() {
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
}

// Função para processar cada frame e detectar a carta
function draw() {
    clear();  // Limpa o canvas

    if (!isCapturing) return;

    let img = video.get();
    let canvas = document.createElement('canvas');
    canvas.width = cropWidth;
    canvas.height = cropHeight;
    let ctx = canvas.getContext('2d');
    ctx.drawImage(img.canvas, 0, 0, cropWidth, cropHeight);  // Desenha no canvas temporário

    // Realizar Template Matching
    let result = cv.matchTemplate(canvas, templateImg.canvas, cv.TM_CCOEFF_NORMED);
    
    // Definir um limite para o quanto a carta precisa se encaixar
    let threshold = 0.8; // Ajuste conforme necessário
    let maxVal = -1;
    let maxLoc = {x: 0, y: 0};

    for (let y = 0; y < result.rows; y++) {
        for (let x = 0; x < result.cols; x++) {
            let value = result.ucharPtr(y, x)[0];
            if (value > maxVal) {
                maxVal = value;
                maxLoc = {x, y};
            }
        }
    }

    // Se encontrou uma correspondência significativa
    if (maxVal > threshold) {
        // Mostrar aviso
        $('#info').html("Carta detectada!");

        // Desenhar o contorno (retângulo) ao redor da carta encontrada
        let rectX = maxLoc.x;
        let rectY = maxLoc.y;
        let rectWidth = templateImg.width;
        let rectHeight = templateImg.height;

        // Desenhar o retângulo
        stroke(0, 255, 0);  // Cor verde para o contorno
        noFill();
        rect(rectX, rectY, rectWidth, rectHeight);

        // Enviar a imagem recortada
        let croppedImg = img.get(rectX, rectY, rectWidth, rectHeight);
        let croppedBase64 = croppedImg.canvas.toDataURL('image/jpeg');

        // Enviar o crop para o servidor
        $.ajax({
            url: "{{ route('mtg.processImage') }}",
            type: 'POST',
            data: JSON.stringify({
                image: croppedBase64,
                boundingBox: {x: rectX, y: rectY, width: rectWidth, height: rectHeight}
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

                // Pausa a captura por 5 segundos após o envio
                isCapturing = false;
                setTimeout(() => { isCapturing = true }, 5000);
            },
            error: function () {
                $('#info').html("❌ Erro ao enviar imagem");
            }
        });
    }
}

</script>
