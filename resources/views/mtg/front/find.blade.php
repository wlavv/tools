<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detect Card and Focus on It</title>
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
    <canvas id="overlay"></canvas>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const overlay = document.getElementById('overlay');
        const ctx = canvas.getContext('2d');
        const overlayCtx = overlay.getContext('2d');

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

        // Função simples de detecção de bordas para encontrar um retângulo
        function detectCardPosition(imageData) {
            const width = imageData.width;
            const height = imageData.height;
            let maxEdge = 0;
            let cardX = 0;
            let cardY = 0;

            // Algoritmo básico de detecção de bordas (busca bordas mais escuras e claras)
            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const pixel = imageData.data[(y * width + x) * 4]; // Acessa o valor do pixel
                    const r = imageData.data[(y * width + x) * 4 + 0];
                    const g = imageData.data[(y * width + x) * 4 + 1];
                    const b = imageData.data[(y * width + x) * 4 + 2];

                    // Detectar bordas claras para simular a detecção da carta (um exemplo básico)
                    if (r > 200 && g > 200 && b > 200) {
                        maxEdge++;
                        cardX = x;
                        cardY = y;
                    }
                }
            }

            if (maxEdge > 1000) {
                // Retorna a posição da carta como (X, Y)
                return { x: cardX, y: cardY, width: 300, height: 400 }; // Ajustar as dimensões se necessário
            }

            return null; // Se não encontrar
        }

        function captureLoop() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Desenha o vídeo no canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Pega os dados da imagem
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

            // Detecta a posição da carta
            const cardPosition = detectCardPosition(imageData);

            if (cardPosition) {
                // Ajusta a posição e tamanho da carta
                const { x, y, width, height } = cardPosition;

                // Limpa o overlay
                overlayCtx.clearRect(0, 0, overlay.width, overlay.height);

                // Desenha a borda da carta detectada no overlay
                overlayCtx.strokeStyle = 'red';
                overlayCtx.lineWidth = 2;
                overlayCtx.strokeRect(x, y, width, height);

                // Captura a região da carta
                const cardCanvas = document.createElement('canvas');
                cardCanvas.width = width;
                cardCanvas.height = height;
                const cardCtx = cardCanvas.getContext('2d');

                // Ajusta o foco para a carta
                cardCtx.drawImage(video, x, y, width, height, 0, 0, width, height);
                const imageDataURL = cardCanvas.toDataURL('image/jpeg');

                console.log('Imagem capturada com sucesso', imageDataURL.substring(0, 50));

                // Envia para o servidor para comparação
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
            }

            setTimeout(captureLoop, 2000); // Repete a captura
        }

        document.addEventListener('DOMContentLoaded', startCamera);
    </script>
</body>
</html>
