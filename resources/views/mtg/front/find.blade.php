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
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <video id="video" autoplay playsinline muted></video>
    <canvas id="canvas" style="display: none;"></canvas>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        // Proporção de carta MTG: 63.5mm x 88.9mm ≈ 1:1.4
        const CARD_RATIO = 1.4;
        const CARD_DISPLAY_WIDTH = 300; // largura que usaremos no ecrã
        const CARD_DISPLAY_HEIGHT = CARD_DISPLAY_WIDTH * CARD_RATIO;

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
                video.srcObject = stream;

                video.onloadedmetadata = () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    captureLoop();
                };
            } catch (err) {
                console.error('Erro ao aceder à câmara:', err);
            }
        }

        function captureLoop() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Desenha vídeo no canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Calcula posição central da carta
            const cardX = (canvas.width - CARD_DISPLAY_WIDTH) / 2;
            const cardY = (canvas.height - CARD_DISPLAY_HEIGHT) / 2;

            // Desenha contorno da carta (1px)
            ctx.strokeStyle = 'red';
            ctx.lineWidth = 1;
            ctx.strokeRect(cardX, cardY, CARD_DISPLAY_WIDTH, CARD_DISPLAY_HEIGHT);

            // Captura apenas a região da carta
            const cardCanvas = document.createElement('canvas');
            cardCanvas.width = CARD_DISPLAY_WIDTH;
            cardCanvas.height = CARD_DISPLAY_HEIGHT;

            const cardCtx = cardCanvas.getContext('2d');
            cardCtx.drawImage(video, cardX, cardY, CARD_DISPLAY_WIDTH, CARD_DISPLAY_HEIGHT, 0, 0, CARD_DISPLAY_WIDTH, CARD_DISPLAY_HEIGHT);

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
