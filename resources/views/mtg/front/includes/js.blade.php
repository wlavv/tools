
<script>
//     template = loadImage('/images/mtg/templates/land artefact.png');  // Substitua pelo caminho correto do template

let video;
let templateImg;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

// Carregar template (carta Magic: The Gathering)
function preload() {
    templateImg = loadImage("/images/mtg/templates/land artefact.png");  // Caminho correto para o template
}

// Configurar o vídeo
function setup() {
    const canvas = createCanvas(cropWidth, cropHeight);
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.zIndex = '10';

    video = createCapture(VIDEO, () => {
        const videoContainer = document.getElementById('videoContainer');
        videoContainer.style.position = 'relative';
        video.elt.style.position = 'absolute';
        video.elt.style.top = '0';
        video.elt.style.left = '0';
        video.elt.width = cropWidth;
        video.elt.height = cropHeight;
        videoContainer.appendChild(video.elt);
        videoContainer.appendChild(canvas);
    });

    video.size(cropWidth, cropHeight);
}

// Função para processar cada frame e detectar a carta
function draw() {
    clear();  // Limpa o canvas

    if (!isCapturing) return;

    // Captura o frame atual do vídeo
    let img = video.get();
    
    // Achar a correspondência do template na imagem
    let matchResult = findTemplateMatch(img, templateImg);

    if (matchResult.found) {
        // Exibir aviso
        $('#info').html("Carta detectada!");

        // Desenhar o contorno (retângulo) ao redor da carta encontrada
        noFill();
        stroke(0, 255, 0);  // Contorno verde
        rect(matchResult.x, matchResult.y, matchResult.width, matchResult.height);

        // Capturar o crop da carta encontrada
        let croppedImg = img.get(matchResult.x, matchResult.y, matchResult.width, matchResult.height);
        let croppedBase64 = croppedImg.canvas.toDataURL('image/jpeg');

        // Enviar o crop para o servidor
        $.ajax({
            url: "{{ route('mtg.processImage') }}",
            type: 'POST',
            data: JSON.stringify({
                image: croppedBase64,
                boundingBox: matchResult
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

// Função para encontrar a correspondência do template na imagem
function findTemplateMatch(img, template) {
    let matchResult = { found: false, x: 0, y: 0, width: template.width, height: template.height };
    
    img.loadPixels();
    template.loadPixels();

    for (let y = 0; y < img.height - template.height; y++) {
        for (let x = 0; x < img.width - template.width; x++) {
            let sum = 0;
            // Comparar cada pixel do template com a região correspondente na imagem
            for (let ty = 0; ty < template.height; ty++) {
                for (let tx = 0; tx < template.width; tx++) {
                    let imgIndex = ((y + ty) * img.width + (x + tx)) * 4;
                    let templateIndex = (ty * template.width + tx) * 4;

                    // Cálculo de diferença de cor (simples soma de diferenças de RGB)
                    let rDiff = Math.abs(img.pixels[imgIndex] - template.pixels[templateIndex]);
                    let gDiff = Math.abs(img.pixels[imgIndex + 1] - template.pixels[templateIndex + 1]);
                    let bDiff = Math.abs(img.pixels[imgIndex + 2] - template.pixels[templateIndex + 2]);
                    
                    sum += rDiff + gDiff + bDiff;
                }
            }

            // Se a soma das diferenças for pequena o suficiente, consideramos que encontramos o template
            if (sum < 10000) {  // Ajuste este valor conforme necessário
                matchResult.found = true;
                matchResult.x = x;
                matchResult.y = y;
                return matchResult;
            }
        }
    }
    
    return matchResult;  // Retorna que não encontrou
}

</script>
