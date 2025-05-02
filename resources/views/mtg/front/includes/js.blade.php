
<script>
//     template = loadImage('/images/mtg/templates/land artefact.png');  // Substitua pelo caminho correto do template
let video;
let templateImg;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

// Carregar template (carta Magic: The Gathering)
function preload() {
    console.log("Carregando template...");
    templateImg = loadImage("/images/mtg/templates/land artefact 1.png", () => {
        console.log("Template carregado com sucesso!");
    }, (error) => {
        console.log("Erro ao carregar o template:", error);
    });
}

// Configurar o vídeo
function setup() {
    console.log("Iniciando setup...");
    const canvas = createCanvas(cropWidth, cropHeight);  // Criação do canvas com tamanho fixo
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.zIndex = '10';

    // Garantir que o vídeo seja exibido corretamente na tela
    video = createCapture(VIDEO, () => {
        console.log("Vídeo capturado com sucesso!");
        const videoContainer = document.getElementById('videoContainer');
        videoContainer.style.position = 'relative';
        video.elt.style.position = 'absolute';
        video.elt.style.top = '0';
        video.elt.style.left = '0';
        video.elt.width = cropWidth;
        video.elt.height = cropHeight;

        // Adicionar o vídeo e o canvas ao container
        videoContainer.appendChild(video.elt);
        videoContainer.appendChild(canvas.elt);  // Usando canvas.elt para garantir a compatibilidade
    });

    video.size(cropWidth, cropHeight);
}

// Função para processar cada frame e detectar a carta
function draw() {
    clear();  // Limpa o canvas

    if (!isCapturing) return;

    // Captura o frame atual do vídeo
    let img = video.get();
    console.log("Captura de imagem:", img);

    // Achar a correspondência do template na imagem
    let matchResult = findTemplateMatch(img, templateImg);
    console.log("Resultado da correspondência do template:", matchResult);

    if (matchResult.found) {
        // Exibir aviso
        $('#info').html("Carta detectada!");
        console.log("Carta detectada na posição:", matchResult.x, matchResult.y);

        // Desenhar o contorno (retângulo) ao redor da carta encontrada
        noFill();
        stroke(0, 255, 0);  // Contorno verde
        rect(matchResult.x, matchResult.y, matchResult.width, matchResult.height);
        console.log("Desenhando contorno ao redor da carta...");

        // Capturar o crop da carta encontrada
        let croppedImg = img.get(matchResult.x, matchResult.y, matchResult.width, matchResult.height);
        console.log("Imagem recortada da carta:", croppedImg);

        let croppedBase64 = croppedImg.canvas.toDataURL('image/jpeg');
        console.log("Imagem recortada convertida para base64.");

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
                console.log("Resposta do servidor:", response);
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
                console.log("Erro ao enviar imagem para o servidor.");
                $('#info').html("❌ Erro ao enviar imagem");
            }
        });
    } else {
        console.log("Nenhuma carta detectada.");
    }
}

// Função para encontrar a correspondência do template na imagem
function findTemplateMatch(img, template) {
    console.log("Iniciando busca pelo template...");

    let matchResult = { found: false, x: 0, y: 0, width: template.width, height: template.height };

    // Verificar dimensões da imagem e do template
    console.log("Dimensões da imagem: ", img.width, img.height);
    console.log("Dimensões do template: ", template.width, template.height);

    // Verificar se a imagem é suficientemente grande para conter o template
    if (img.width < template.width || img.height < template.height) {
        console.error("A imagem é menor do que o template. O template não pode ser encontrado.");
        return matchResult;
    }

    // Carregar pixels das imagens (certifique-se que loadPixels é chamado)
    img.loadPixels();
    template.loadPixels();
    console.log("Pixels carregados: img.pixels.length =", img.pixels.length, "template.pixels.length =", template.pixels.length);

    // Certificar-se de que os loops de iteração estão sendo executados
    for (let y = 0; y < img.height - template.height; y++) {
        console.log(`Iniciando iteração em y=${y}`);
        for (let x = 0; x < img.width - template.width; x++) {
            console.log(`Iniciando iteração em x=${x}`);
            let sum = 0;
            
            // Comparar cada pixel do template com a região correspondente na imagem
            for (let ty = 0; ty < template.height; ty++) {
                for (let tx = 0; tx < template.width; tx++) {
                    let imgIndex = ((y + ty) * img.width + (x + tx)) * 4;  // Índice no array de pixels da imagem
                    let templateIndex = (ty * template.width + tx) * 4;   // Índice no array de pixels do template

                    // Verificar se os índices são válidos
                    if (imgIndex < img.pixels.length && templateIndex < template.pixels.length) {
                        // Cálculo de diferença de cor (simples soma de diferenças de RGB)
                        let rDiff = Math.abs(img.pixels[imgIndex] - template.pixels[templateIndex]);
                        let gDiff = Math.abs(img.pixels[imgIndex + 1] - template.pixels[templateIndex + 1]);
                        let bDiff = Math.abs(img.pixels[imgIndex + 2] - template.pixels[templateIndex + 2]);

                        sum += rDiff + gDiff + bDiff;  // Soma das diferenças de cores
                    }
                }
            }

            // Log para verificar o valor da soma
            console.log(`Soma das diferenças (x: ${x}, y: ${y}):`, sum);

            // Se a soma das diferenças for pequena o suficiente, consideramos que encontramos o template
            if (sum < 10000) {  // Ajuste este valor conforme necessário
                matchResult.found = true;
                matchResult.x = x;
                matchResult.y = y;
                console.log(`Template encontrado! Posição: (${x}, ${y})`);
                return matchResult;
            }
        }
    }
    
    console.log("Template não encontrado.");
    return matchResult;  // Retorna que não encontrou
}

</script>
