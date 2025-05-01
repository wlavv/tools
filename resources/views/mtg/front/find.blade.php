<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detecção de Pontos Chave com ORB</title>
    <script async src="https://docs.opencv.org/4.x/opencv.js"></script>
    <style>
        #videoContainer {
            position: relative;
        }
        #canvasOverlay {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
        }
        #info {
            font-size: 20px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Detecção de Pontos Chave com ORB</h1>
    <div id="videoContainer">
        <!-- O vídeo será exibido aqui -->
    </div>
    <canvas id="canvasOverlay"></canvas>
    <div id="info"></div>
    <script>
        let video;
        let canvasOverlay;
        let orb;
        let isCapturing = true;
        let cropWidth = 1200;
        let cropHeight = 900;

        // Configuração inicial
        window.onload = function() {
            orb = new cv.ORB(); // Inicializa o detector ORB

            // Configura o vídeo
            const videoContainer = document.getElementById('videoContainer');
            video = document.createElement('video');
            video.width = cropWidth;
            video.height = cropHeight;
            videoContainer.appendChild(video);

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then((stream) => {
                        video.srcObject = stream;
                        video.play();
                        video.addEventListener('play', startProcessing);
                    })
                    .catch((err) => {
                        console.error('Erro ao acessar a câmera: ', err);
                    });
            }
        };

        // Função para começar a processar os frames
        function startProcessing() {
            canvasOverlay = document.getElementById('canvasOverlay');
            const ctx = canvasOverlay.getContext('2d');
            canvasOverlay.width = video.width;
            canvasOverlay.height = video.height;

            // Chama a função para processar cada frame
            setInterval(processFrame, 1000 / 30); // 30 FPS
        }

        // Função para processar cada frame
        function processFrame() {
            if (!isCapturing) return;

            // Captura o quadro do vídeo
            const frame = cv.imread(video);
            const gray = new cv.Mat();
            cv.cvtColor(frame, gray, cv.COLOR_RGBA2GRAY);

            // Detecta os pontos chave e descritores usando ORB
            let keypoints = new cv.KeyPointVector();
            let descriptors = new cv.Mat();
            orb.detectAndCompute(gray, new cv.Mat(), keypoints, descriptors);

            // Desenha os pontos chave
            drawKeypoints(frame, keypoints);

            // Exibe a imagem processada
            cv.imshow(canvasOverlay, frame);

            // Libera a memória
            frame.delete();
            gray.delete();
            descriptors.delete();
        }

        // Função para desenhar os pontos chave na imagem
        function drawKeypoints(image, keypoints) {
            const color = new cv.Scalar(0, 255, 0); // Cor verde para os pontos chave
            cv.drawKeypoints(image, keypoints, image, color, cv.DrawMatchesFlags_DRAW_RICH_KEYPOINTS);
        }
    </script>
</body>
</html>
