<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MTG Card Tracker</title>
    <style>
        body {
            background: black;
            margin: 0;
            overflow: hidden;
            color: white;
            font-family: Arial, sans-serif;
        }

        #container {
            position: relative;
            width: 500px;
            height: 700px;
            margin: 20px auto;
        }

        video, canvas {
            position: absolute;
            width: 500px;
            height: 700px;
        }

        #info {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            padding: 8px;
            border-radius: 4px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div id="container">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="canvas"></canvas>
        <div id="info">⌛ A iniciar...</div>
    </div>

    <!-- Carregar OpenCV.js -->
    <script async src="https://docs.opencv.org/4.x/opencv.js" type="text/javascript"></script>
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const info = document.getElementById('info');

        // Função para garantir que OpenCV está completamente carregado
        function checkOpenCV() {
            if (typeof cv === 'undefined') {
                setTimeout(checkOpenCV, 100);  // Verifica a cada 100ms se o OpenCV foi carregado
                return;
            }
            console.log("OpenCV carregado com sucesso!");
            initialize();  // Chama a função para iniciar quando o OpenCV estiver disponível
        }

        // Função que inicia o fluxo de captura de vídeo e processamento
        function initialize() {
            startVideo();
            processVideo();
        }

        // Função para iniciar a captura de vídeo
        function startVideo() {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(stream => {
                    video.srcObject = stream;
                    video.onloadedmetadata = () => {
                        video.play();
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                    };
                })
                .catch(err => {
                    console.error("Erro ao acessar a câmera: ", err);
                    info.innerText = "Erro ao acessar a câmera.";
                });
        }

        // Função para processar o vídeo e realizar a detecção
        function processVideo() {
            // Usando o método correto para criar a Mat a partir do vídeo
            const FPS = 10;

            function detect() {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                // Criando a Mat a partir dos dados da imagem com cv.matFromImageData()
                let src = cv.matFromImageData(imageData);  // Agora usando o método correto para criar a Mat

                let gray = new cv.Mat();
                let edges = new cv.Mat();
                let contours = new cv.MatVector();
                let hierarchy = new cv.Mat();

                cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
                cv.Canny(gray, edges, 75, 150);
                cv.findContours(edges, contours, hierarchy, cv.RETR_TREE, cv.CHAIN_APPROX_SIMPLE);

                // Limpar canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.strokeStyle = "red";
                ctx.lineWidth = 2;

                let found = false;

                // Iterar sobre os contornos encontrados
                for (let i = 0; i < contours.size(); i++) {
                    let cnt = contours.get(i);
                    let approx = new cv.Mat();
                    cv.approxPolyDP(cnt, approx, 0.02 * cv.arcLength(cnt, true), true);

                    if (approx.rows === 4 && cv.contourArea(approx) > 10000) {
                        // Desenha o contorno da carta detectada
                        let points = [];
                        for (let j = 0; j < 4; j++) {
                            let pt = approx.data32S.slice(j * 2, j * 2 + 2);
                            points.push({ x: pt[0], y: pt[1] });
                        }

                        ctx.beginPath();
                        ctx.moveTo(points[0].x, points[0].y);
                        for (let j = 1; j < 4; j++) ctx.lineTo(points[j].x, points[j].y);
                        ctx.closePath();
                        ctx.stroke();

                        found = true;
                        approx.delete();
                        break;
                    }

                    approx.delete();
                }

                info.innerText = found ? "✅ Carta detectada!" : "🔍 A procurar carta...";

                // Libera os recursos da imagem
                src.delete();
                gray.delete();
                edges.delete();
                contours.delete();
                hierarchy.delete();

                setTimeout(detect, 1000 / FPS);
            }

            detect();
        }

        // Chama a função para verificar o carregamento do OpenCV
        checkOpenCV();
    </script>
</body>
</html>
