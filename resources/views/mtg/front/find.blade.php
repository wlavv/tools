<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camera Stream with Auto Capture</title>
    <style>
        video, canvas {
            width: 100%;
            max-width: 400px;
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

        function captureLoop() {
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = canvas.toDataURL('image/jpeg');
            
            // Aqui podes gerar a hash da imagem capturada ou enviá-la via AJAX
            console.log('Imagem capturada:', imageData.substring(0, 50), '...');

            // Repetir a cada X milissegundos
            setTimeout(captureLoop, 2000); // captura de 2 em 2 segundos
        }

        document.addEventListener('DOMContentLoaded', startCamera);
    </script>
</body>
</html>
