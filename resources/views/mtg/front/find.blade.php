<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camera Stream with Auto Capture</title>
    <style>
        video, canvas {
            max-width: 100%;
            display: block;
            margin: 1rem auto;
        }
        /* Layer adicional de contorno */
        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none; /* Não interfere com a interação do usuário */
            display: block;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <video id="video" autoplay playsinline muted></video>
    <canvas id="canvas" style="display: none;"></canvas>
    <canvas id="overlay"></canvas> <!-- Novo layer de contorno -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const overlay = document.getElementById('overlay');
        const ctx = canvas.getContext('2d');
        const overlayCtx = overlay.getContext('2d');

        // Proporção de carta MTG: 63.5mm x 88.9mm ≈ 1:1.4
        const CARD_WIDTH_MM = 63.5; // largura real da carta em mm
        const CARD_HEIGHT_MM = 88.9; // altura real da carta em mm

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
                video.srcObject = stream;

                video.onloadedmetadata = () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    overlay.width = video.videoWidth;
                    overlay.height = video.videoHeight;

                    captureLoop();
                };
            } catch (err) {
                console.error('Erro ao aceder à câmara:', err);
            }
        }

        // Função para calcular o tamanho da carta na tela com base na proporção
        function getCardDimensions() {
            const videoAspectRatio = video.videoWidth / video.videoHeight;

            // Definir uma largura fixa para a carta, por exemplo, 300px
            const cardDisplayWidth = 300;

            // Ajustar a altura da carta com base na proporção da carta MTG (1:1.4)
            const cardDisplayHeight = cardDisplayWidth * CARD_HEIGHT_MM / CARD_WIDTH_MM;
            return { cardDisplayWidth, cardDisplayHeight };
        }

        function captureLoop() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Desenha vídeo no canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Limpa o overlay a cada loop
            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);

            // Calcula o tamanho e a posição da carta
            const { cardDisplayWidth, cardDisplayHeight } = getCardDimensions();
            const cardX = (canvas.width - cardDisplayWidth) / 2;
            const cardY = (canvas.height - cardDisplayHeight) / 2;

            // Desenha o retângulo de contorno com as dimensões exatas da carta
            overlayCtx.strokeStyle = 'red'; // Cor da borda
            overlayCtx.lineWidth = 2; // Largura da borda
            overlayCtx.strokeRect(cardX, cardY, cardDisplayWidth, cardDisplayHeight); // Desenha a borda

            // Captura apenas a região da carta
            const cardCanvas = document.createElement('canvas');
            cardCanvas.width = cardDisplayWidth;
            cardCanvas.height = cardDisplayHeight;

            const cardCtx = cardCanvas.getContext('2d');
            cardCtx.drawImage(video, cardX, cardY, cardDisplayWidth, cardDisplayHeight, 0, 0, cardDisplayWidth, cardDisplayHeight);

            const imageDataURL = cardCanvas.toDataURL('image/jpeg');

            console.log('Imagem capturada com sucesso', imageDataURL.substring(0, 50));

            // Envia para o backend
            $.ajax({
                url: '/mtg/front/compare-hash',
                method: 'POST',
                data: {
                    image: imageDataURL,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function(response) {
                    console.log('Resultado da comparação:', response);
                },
                error: function(xhr) {
                    console.error('Erro ao enviar imagem:', xhr.responseText);
                }
            });

            // Repetir após 2 segundos
            setTimeout(captureLoop, 2000);
        }

        document.addEventListener('DOMContentLoaded', startCamera);
    </script>
</body>
</html>
