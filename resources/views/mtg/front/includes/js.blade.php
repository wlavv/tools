<script>

let video;
let template;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

// Carregar o template da carta MTG
function preload() {
    // Aqui você carrega o template da carta que será usada para o matching
    template = loadImage('/images/mtg/templates/land artefact.png');  // Substitua pelo caminho correto do template
}

function setup() {
    const canvas = createCanvas(cropWidth, cropHeight);
    canvas.id('canvasOverlay');
    document.body.appendChild(canvas);

    // Configurar captura de vídeo
    video = createCapture(VIDEO, () => {
        video.size(cropWidth, cropHeight);
        video.hide();
        document.getElementById('videoContainer').appendChild(video.elt);
    });

    loop();  // Iniciar o loop para processar o vídeo
}

function draw() {
    if (!isCapturing) return;

    let img = video.get();  // Captura um frame do vídeo
    image(img, 0, 0);

    // Realizar a detecção com Template Matching
    let match = findTemplateMatch(img, template);

    if (match) {
        // Se houver uma correspondência, desenha a caixa ao redor
        rect(match.x, match.y, template.width, template.height);
        fill(0, 255, 0, 100); // Cor de preenchimento para o contorno
        rect(match.x, match.y, template.width, template.height);
        console.log("Carta detectada! Enviando ao servidor...");
        sendToServer(img.get(match.x, match.y, template.width, template.height));
    }
}

// Função para realizar o Template Matching
function findTemplateMatch(image, template) {
    let match = null;
    let threshold = 0.8;  // Limite de similaridade para detectar uma correspondência

    for (let x = 0; x < image.width - template.width; x++) {
        for (let y = 0; y < image.height - template.height; y++) {
            // Cria uma subimagem da região atual
            let region = image.get(x, y, template.width, template.height);

            // Comparar a subimagem com o template
            let similarity = compareImages(region, template);

            if (similarity >= threshold) {
                match = { x: x, y: y };
                break;
            }
        }
        if (match) break;  // Interrompe se encontrar a primeira correspondência
    }

    return match;
}

// Função simples para comparar a similaridade entre duas imagens
function compareImages(region, template) {
    let similarity = 0;
    region.loadPixels();
    template.loadPixels();

    for (let i = 0; i < region.pixels.length; i++) {
        similarity += (region.pixels[i] === template.pixels[i]) ? 1 : 0;
    }

    return similarity / region.pixels.length;  // Retorna a porcentagem de similaridade
}

// Função para enviar a imagem recortada ao servidor
function sendToServer(croppedImage) {
    let croppedBase64 = croppedImage.canvas.toDataURL('image/jpeg');
    $.ajax({
        url: "{{ route('mtg.processImage') }}",
        type: 'POST',
        data: JSON.stringify({ image: croppedBase64 }),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#info').html("📛 Informações da carta: " + response.info);
        },
        error: function() {
            $('#info').html("❌ Erro ao enviar imagem");
        }
    });
}


</script>
