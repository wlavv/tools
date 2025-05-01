<script>

let video;
let info;
let canvasOverlay;
let boundingBox = { x: 0, y: 0, width: 0, height: 0 };
let isCapturing = true;
const cropWidth = 1200;
const cropHeight = 900;
let isTracking = false; // Variável para controlar o estado de rastreamento

// Variáveis para controle de requisições AJAX
let lastRequestTime = 0;  // Armazena o tempo da última requisição
const minRequestInterval = 60000; // Intervalo de 1 minuto (em milissegundos)

window.setup = function () {
    const canvas = createCanvas(cropWidth, cropHeight);
    canvasOverlay = canvas.elt;
    canvasOverlay.style.position = 'absolute';
    canvasOverlay.style.top = '0';
    canvasOverlay.style.left = '0';
    canvasOverlay.style.zIndex = '10'; // Sobre o vídeo

    video = createCapture(VIDEO, () => {
        const videoContainer = document.getElementById('videoContainer');
        videoContainer.style.position = 'relative';
        video.elt.style.position = 'absolute';
        video.elt.style.top = '0';
        video.elt.style.left = '0';
        video.elt.width = 1200;
        video.elt.height = 900;
        videoContainer.appendChild(video.elt);
        videoContainer.appendChild(canvasOverlay); // sobrepõe o canvas ao vídeo
    });

    video.size(cropWidth, cropHeight);
    // NÃO usamos video.hide() — queremos vê-lo

    info = select('#info');
    info.html("🔍 A procurar carta...");
};

window.draw = function () {

    clear(); // limpa o canvas a cada frame

    if (!isCapturing) {
        // Mostra bounding box, se existir
        if (boundingBox.width > 0 && boundingBox.height > 0) {
            noFill();
            stroke('lime');
            strokeWeight(3);
            rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);
        }
        return;
    }

    let img = video.get();
    img.loadPixels();

    let cropX = Math.floor((video.width - cropWidth) / 2);
    let cropY = Math.floor((video.height - cropHeight) / 2);

    let cropped = img.get(cropX, cropY, cropWidth, cropHeight);
    cropped.filter(GRAY);
    cropped.loadPixels();

    // --- tracking baseado em contraste ---
    let maxDiff = 0;
    let bestX = 0;
    let bestY = 0;
    let boxSize = 100; // Tamanho da área de deteção

    for (let y = 0; y < cropped.height - boxSize; y += 10) {
        for (let x = 0; x < cropped.width - boxSize; x += 10) {
            let sum = 0;
            for (let j = 0; j < boxSize; j++) {
                for (let i = 0; i < boxSize; i++) {
                    let index = 4 * ((y + j) * cropped.width + (x + i));
                    let brightness = cropped.pixels[index]; // escala de cinza
                    sum += brightness;
                }
            }
            let avg = sum / (boxSize * boxSize);
            let diff = Math.abs(avg - 128);

            if (diff > maxDiff) {
                maxDiff = diff;
                bestX = x;
                bestY = y;
            }
        }
    }

    // Atualiza boundingBox com base no ponto de maior contraste
    boundingBox = {
        x: bestX,
        y: bestY,
        width: boxSize,
        height: boxSize
    };

    // Desenha a bounding box no canvas
    noFill();
    stroke('lime');
    strokeWeight(3);
    rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);

    // Gera imagem base64 para enviar
    let base64Image = cropped.canvas.toDataURL('image/jpeg');


    // Verifica se o intervalo de 1 minuto foi atingido antes de enviar a requisição
    const currentTime = Date.now();
    if (currentTime - lastRequestTime >= minRequestInterval) {

        $.ajax({
            url: "{{ route('mtg.processImage') }}",
            type: 'POST',
            data: JSON.stringify({
                image: base64Image,
                boundingBox: boundingBox // envia o objeto com x, y, width, height
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                info.html("📛 pHash: " + response.pHash);

                // Atualiza a bounding box com as novas coordenadas
                boundingBox = response.boundingBox || { x: 0, y: 0, width: 0, height: 0 };

                // Atualiza a #cropZone com a imagem recortada
                let croppedImageUrl = response.croppedImageUrl;
                let imgElement = document.createElement("img");
                imgElement.src = croppedImageUrl;  // URL da imagem recortada
                imgElement.style.width = '100%';  // Ajusta o tamanho da imagem
                imgElement.style.height = 'auto'; // Mantém a proporção

                // Adiciona a imagem à #cropZone
                let cropZone = document.getElementById('cropZone');
                cropZone.innerHTML = ''; // Limpa qualquer conteúdo anterior
                cropZone.appendChild(imgElement);

                // Atualiza o tempo da última requisição
                lastRequestTime = currentTime;

                // Continuar capturando frames
                isCapturing = true;
            },
            error: function(xhr, status, error) {
                console.error("Erro ao enviar imagem:", error);
                info.html("❌ Erro ao enviar imagem");
                isCapturing = true; // Reinicia a captura se houver erro
            }
        });
    } else {
        console.log("Aguardando 1 minuto antes de enviar nova requisição...");
    }
};

</script>
