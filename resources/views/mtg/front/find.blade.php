<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MTG Card Detection</title>
    <style>
        body {
            background-color: black;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }

        #container {
            position: relative;
            width: 90%;
            height: 90%;
            width: 750px;
            height: 1050px;
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border: 1px solid white;
        }

        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        #info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            z-index: 2;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            max-width: 300px;
        }

        #logo {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 2;
            width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <div id="container">
        <img id="logo" src="https://1000logos.net/wp-content/uploads/2022/10/Magic-The-Gathering-logo-500x325.png" alt="MTG Logo">

        <video id="video" autoplay playsinline muted></video>
        <canvas id="overlay"></canvas>

        <div id="info">
            Carregando informações...
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const overlayCtx = overlay.getContext('2d');
        const infoBox = document.getElementById('info');

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
                video.srcObject = stream;

                video.onloadedmetadata = () => {

                    overlay.width = video.videoWidth;
                    overlay.height = video.videoHeight;

                    captureLoop();
                };
            } catch (err) {
                console.error('Erro ao aceder à câmara:', err);
            }
        }

        // Função para detecção de bordas e identificar a carta
        function detectCardPosition(imageData) {
            const width = imageData.width;
            const height = imageData.height;
            let cardX = -1, cardY = -1;
            let cardWidth = 0, cardHeight = 0;
            let maxEdge = 0;

            // Algoritmo para detectar uma região retangular (cartão) baseado em contraste
            for (let y = 0; y < height - 1; y++) {
                for (let x = 0; x < width - 1; x++) {
                    // Pega os valores de cor dos pixels atuais e adjacentes
                    const r1 = imageData.data[(y * width + x) * 4 + 0];
                    const g1 = imageData.data[(y * width + x) * 4 + 1];
                    const b1 = imageData.data[(y * width + x) * 4 + 2];

                    const r2 = imageData.data[((y + 1) * width + x) * 4 + 0];
                    const g2 = imageData.data[((y + 1) * width + x) * 4 + 1];
                    const b2 = imageData.data[((y + 1) * width + x) * 4 + 2];

                    // Calcula a diferença de cor entre pixels adjacentes (vertical)
                    const diff = Math.abs(r1 - r2) + Math.abs(g1 - g2) + Math.abs(b1 - b2);
                    if (diff > 100) {  // Limite para considerar um contorno
                        maxEdge++;
                        if (cardX === -1) cardX = x;
                        if (cardY === -1) cardY = y;
                        cardWidth = x - cardX;
                        cardHeight = y - cardY;
                    }
                }
            }

            // Se encontrarmos bordas suficientes, retornamos a posição da carta
            if (maxEdge > 1000) {
                return { x: cardX, y: cardY, width: cardWidth, height: cardHeight };
            }

            return null;
        }

        function captureLoop() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Desenha o vídeo no canvas
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Obtém os dados da imagem
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

            // Detecta a posição da carta
            const cardPosition = detectCardPosition(imageData);

            if (cardPosition) {
                // Ajusta a posição da carta
                const { x, y, width, height } = cardPosition;

                // Limpa o overlay
                overlayCtx.clearRect(0, 0, overlay.width, overlay.height);

                // Desenha a borda da carta detectada
                overlayCtx.strokeStyle = 'red';
                overlayCtx.lineWidth = 2;
                overlayCtx.strokeRect(x, y, width, height);

                // Captura a região da carta e converte em uma imagem
                const cardCanvas = document.createElement('canvas');
                cardCanvas.width = width;
                cardCanvas.height = height;
                const cardCtx = cardCanvas.getContext('2d');

                // Ajusta o foco para a carta detectada
                cardCtx.drawImage(video, x, y, width, height, 0, 0, width, height);
                const imageDataURL = cardCanvas.toDataURL('image/jpeg');

                console.log('Imagem capturada com sucesso', imageDataURL.substring(0, 50));

                // Envia a imagem para o servidor via AJAX para comparação
                $.ajax({
                    url: '/mtg/front/compare-hash',
                    method: 'POST',
                    data: {
                        image: imageDataURL,
                        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function(response) {
                        console.log('Resultado da comparação:', response);
                        infoBox.innerHTML = 'Carta detectada com sucesso!';
                    },
                    error: function(xhr) {
                        console.error('Erro ao enviar imagem:', xhr.responseText);
                        infoBox.innerHTML = 'Erro ao processar a carta.';
                    }
                });
            } else {
                infoBox.innerHTML = 'Nenhuma carta detectada.';
            }

            // Repete a captura
            setTimeout(captureLoop, 2000);
        }

        // Função para carregar as informações via AJAX
        function loadInfo() {

            /**
            $.ajax({
                url: '/mtg/front/get-info',
                method: 'GET',
                success: function(response) {
                    infoBox.innerHTML = response.data; // Exibe as informações carregadas
                },
                error: function(xhr) {
                    console.error('Erro ao carregar as informações:', xhr.responseText);
                }
            });
            **/
        }

        document.addEventListener('DOMContentLoaded', () => {
            startCamera();
            loadInfo();
        });
    </script>
</body>
</html>
