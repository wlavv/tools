
<script>
//     template = loadImage('/images/mtg/templates/land artefact.png');  // Substitua pelo caminho correto do template

let video;
let templateCanvas;
let isCapturing = true;
const cropWidth = 1200;
const cropHeight = 900;

// Função de setup para iniciar o vídeo e o canvas
function setup() {
    // Criando o canvas para o vídeo
    let canvas = createCanvas(cropWidth, cropHeight);
    canvas.style('display', 'block');  // Garantir que o canvas seja visível

    // Configurar o vídeo com p5.js
    video = createCapture(VIDEO, () => {
        video.size(cropWidth, cropHeight);
    });
    video.hide();  // Esconder o vídeo original

    // Criando um canvas para o template (não visível na tela)
    templateCanvas = createCanvas(cropWidth, cropHeight);
    templateCanvas.style('display', 'none');  // Esconder o canvas do template
}

// Função para realizar o template matching
function matchTemplate(frameData, templateData) {
    let width = frameData.width;
    let height = frameData.height;

    let maxVal = 0;
    let bestMatch = { x: 0, y: 0 };

    // Loop através da imagem do frame
    for (let y = 0; y < height - templateData.height; y++) {
        for (let x = 0; x < width - templateData.width; x++) {
            let sum = 0;
            // Comparar pixel a pixel entre o template e o frame
            for (let j = 0; j < templateData.height; j++) {
                for (let i = 0; i < templateData.width; i++) {
                    let framePixel = frameData.get(x + i, y + j);
                    let templatePixel = templateData.get(i, j);
                    sum += dist(framePixel[0], framePixel[1], framePixel[2], templatePixel[0], templatePixel[1], templatePixel[2]);
                }
            }

            // Armazenar a melhor correspondência
            if (sum < maxVal || maxVal === 0) {
                maxVal = sum;
                bestMatch = { x: x, y: y };
            }
        }
    }

    return bestMatch;
}

// Função para desenhar no canvas e processar os frames
function draw() {
    clear();  // Limpa o canvas a cada novo frame
    if (!isCapturing) return;

    // Obter o frame atual do vídeo
    let img = video.get();

    // Desenhar o frame no canvas
    image(img, 0, 0, cropWidth, cropHeight);

    // Criar um template para a comparação (exemplo)
    let template = loadImage('/images/mtg/templates/land artefact.png', () => {
        templateCanvas.image(template, 0, 0, cropWidth, cropHeight);  // Coloca o template no canvas
        let templateData = templateCanvas.get();  // Obtém os dados do template

        // Obter os dados do frame atual
        let frameData = get();  // Obter o frame atual do canvas
        let frameDataPixels = frameData.loadPixels();

        // Comparar o template com o frame atual
        let match = matchTemplate(frameData, templateData);

        // Desenhar o contorno do melhor match encontrado
        stroke(0, 255, 0);  // Cor verde para o contorno
        noFill();
        rect(match.x, match.y, template.width, template.height);

        // Exibir informações sobre a correspondência
        textSize(16);
        fill(255, 0, 0);
        text(`Match found at: (${match.x}, ${match.y})`, 20, height - 20);
    });
}


</script>
