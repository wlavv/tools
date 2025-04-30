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
            // Vamos usar a largura do vídeo como referência para calcular a altura
            const videoAspectRatio = video.videoWidth / video.videoHeight;
            const
