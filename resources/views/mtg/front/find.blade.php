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
            max-width: 1200px;
            max-height: 800px;
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
            width: 50px;
            height: auto;
        }
    </style>
</head>
<body>
    <div id="container">
        <!-- Logo do MTG -->
        <img id="logo" src="https://upload.wikimedia.org/wikipedia/commons/a/ae/Magic_The_Gathering_Logo.svg" alt="MTG Logo">

        <!-- Vídeo da câmera -->
        <video id="video" autoplay playsinline muted></video>
        <!-- Overlay para a borda da carta detectada -->
        <canvas id="overlay"></canvas>

        <!-- Informações carregadas via AJAX -->
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
                    // Ajuste do canvas para o tamanho do vídeo
                    overlay.width = video.videoWidth;
                    overlay.height = video.videoHeight;

                    // Inicia o loop de captura de vídeo
                    captureLoop();
                };
            } catch (err) {
                console.error('Erro ao aceder à câmara:', err);
            }
        }

        // Função simples para capturar a carta com base em bordas
        function detectCardPosition(imageData) {
            const width = imageData.width;
            const height = imageData.height;
            let cardX = 0;
            let cardY = 0;
            let maxEdge = 0;

            // Algoritmo básico para detectar bordas claras (simulando detecção de uma carta)
            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const r = imageData.data[(y * width + x) * 4 + 0];
                    const g = imageData.data[(y * width + x) * 4 + 1];
                    const b = imageData.data[(y * width + x) * 4 + 2];

                    // Detectando pixels claros que representam bordas
                    if (r > 200 && g > 200 && b > 200) {
                        maxEdge++;
                        cardX = x;
                        cardY = y;
                    }
                }
            }

            // Se detectarmos um número suficiente de bordas, retornamos a posição
            if (maxEdge > 1000) {
                return { x: cardX, y: cardY, width: 300, height: 400 }; // Tamanho da carta padrão
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
        }

        document.addEventListener('DOMContentLoaded', () => {
            startCamera();
            loadInfo();
        });
    </script>
</body>
</html>
