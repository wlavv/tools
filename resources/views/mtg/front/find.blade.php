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
            width: 500px;
            height: 700px;
        }

        #video {
            width: 500px;
            height: 700px;
            object-fit: cover;
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

        let lastCardPosition = null;

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
                video.srcObject = stream;

                video.onloadedmetadata = () => {

                    overlay.width = 500;
                    overlay.height = 700;

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

            let minX = width;
            let maxX = 0;
            let minY = height;
            let maxY = 0;
            let foundEdges = 0;

            for (let y = 0; y < height - 1; y++) {
                for (let x = 0; x < width - 1; x++) {
                    const i = (y * width + x) * 4;
                    const r1 = imageData.data[i];
                    const g1 = imageData.data[i + 1];
                    const b1 = imageData.data[i + 2];

                    const i2 = ((y + 1) * width + x) * 4;
                    const r2 = imageData.data[i2];
                    const g2 = imageData.data[i2 + 1];
                    const b2 = imageData.data[i2 + 2];

                    const diff = Math.abs(r1 - r2) + Math.abs(g1 - g2) + Math.abs(b1 - b2);
                    if (diff > 100) {
                        foundEdges++;
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    }
                }
            }

            const boxWidth = maxX - minX;
            const boxHeight = maxY - minY;

            if (foundEdges > 500 && boxWidth > 100 && boxHeight > 150) {
                return { x: minX, y: minY, width: boxWidth, height: boxHeight };
            }

            return null;
        }


        function captureLoop() {
            const width = 500;
            const height = 700;

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(video, 0, 0, width, height);

            const imageData = ctx.getImageData(0, 0, width, height);
            const cardPosition = detectCardPosition(imageData);

            overlayCtx.clearRect(0, 0, width, height);

            if (cardPosition) {
                // Suaviza a transição do retângulo entre frames
                if (lastCardPosition) {
                    const smoothFactor = 0.2; // Quanto menor, mais suave
                    cardPosition.x = lastCardPosition.x + (cardPosition.x - lastCardPosition.x) * smoothFactor;
                    cardPosition.y = lastCardPosition.y + (cardPosition.y - lastCardPosition.y) * smoothFactor;
                    cardPosition.width = lastCardPosition.width + (cardPosition.width - lastCardPosition.width) * smoothFactor;
                    cardPosition.height = lastCardPosition.height + (cardPosition.height - lastCardPosition.height) * smoothFactor;
                }

                lastCardPosition = cardPosition;

                const { x, y, width: w, height: h } = cardPosition;

                overlayCtx.strokeStyle = 'red';
                overlayCtx.lineWidth = 2;
                overlayCtx.strokeRect(x, y, w, h);
            } else {
                lastCardPosition = null;
            }

            requestAnimationFrame(captureLoop); // substitui setTimeout para tracking contínuo
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
