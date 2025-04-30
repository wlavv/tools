<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camera Stream with Auto Capture</title>
    <style>
        video, canvas {
            width: 100%;
            max-width: 100%;
            display: block;
            margin: 1rem auto;
        }
    </style>
</head>
<body>
    <video id="video" autoplay playsinline muted></video>
    <canvas id="canvas" style="display: none;"></canvas>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
                video.srcObject = stream;

                video.onloadedmetadata = () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    // Começa a capturar automaticamente em loop
                    captureLoop();
                };
            } catch (err) {
                console.error('Erro ao aceder à câmara:', err);
            }
        }
        const video = document.querySelector('video'); // referência à câmera
const canvas = document.createElement('canvas');
const ctx = canvas.getContext('2d');

// Função para detectar a posição da carta
function detectCardPosition(imageData) {
    const width = imageData.width;
    const height = imageData.height;

    // Vamos percorrer os dados da imagem
    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            const pixel = imageData.data[(y * width + x) * 4]; // Acessa o valor do pixel

            // Aqui buscamos um padrão simples (por exemplo, cor clara de uma borda)
            if (pixel > 200) {
                // Retorna a posição (X, Y) onde a carta pode estar
                return { x, y }; 
            }
        }
    }

    return null; // Se não encontrar a carta
}

// Função de captura contínua
function captureLoop() {
    // Define as dimensões do canvas
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Desenha o vídeo no canvas
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Pega os dados da imagem do canvas
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    
    // Detecta a posição da carta
    const cardPosition = detectCardPosition(imageData);

    if (cardPosition) {
        const cardX = cardPosition.x;
        const cardY = cardPosition.y;
        const cardWidth = 300; // largura da carta
        const cardHeight = 400; // altura da carta

        // Desenha a borda de 1 pixel ao redor da carta detectada
        ctx.strokeStyle = 'red'; // Cor da borda
        ctx.lineWidth = 1; // Largura da borda
        ctx.strokeRect(cardX, cardY, cardWidth, cardHeight); // Desenha a borda

        // Captura a região da carta
        ctx.drawImage(video, cardX, cardY, cardWidth, cardHeight, 0, 0, cardWidth, cardHeight);
        
        const imageDataURL = canvas.toDataURL('image/jpeg');

        // Aqui você pode gerar a hash da imagem da carta e enviá-la para o servidor
        console.log('Imagem capturada com sucesso', imageDataURL.substring(0, 50));

        // Você pode verificar o hash dessa imagem e compará-lo com as imagens na BD, etc.
    }

    setTimeout(captureLoop, 2000); // Executa a captura novamente após 2 segundos
}

        document.addEventListener('DOMContentLoaded', startCamera);
    </script>
</body>
</html>
