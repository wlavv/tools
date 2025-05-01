<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Object Tracking in Video</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script async src="https://docs.opencv.org/4.x/opencv.js" onload="onOpenCvReady();" type="text/javascript"></script>
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
            position: absolute;
            z-index: 20;
            color: white;
            font-size: 20px;
        }
    </style>
</head>
<body>

    <div id="info">⌛ A iniciar...</div>
    <div id="videoContainer">
        <video id="video" width="640" height="480" autoplay></video>
        <canvas id="canvasOverlay" width="640" height="480"></canvas>
    </div>

    <script>
        let video;
        let canvas;
        let ctx;
        let tracking = false;
        let boundingBox = { x: 0, y: 0, width: 0, height: 0 };

        function onOpenCvReady() {
            console.log("OpenCV.js loaded ✅");

            video = document.getElementById('video');
            canvas = document.getElementById('canvasOverlay');
            ctx = canvas.getContext('2d');

            // Inicia o vídeo
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    console.log("Erro ao acessar a câmera: ", err);
                });
        }

        function trackObject() {
            if (!video || !video.videoWidth || !video.videoHeight) return;

            // Captura o quadro atual do vídeo
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            let src = cv.imread(canvas);
            let gray = new cv.Mat();
            let blurred = new cv.Mat();
            let thresh = new cv.Mat();
            let contours = new cv.MatVector();
            let hierarchy = new cv.Mat();

            // Converte a imagem para escala de cinza e aplica desfoque
            cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
            cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);
            cv.threshold(blurred, thresh, 120, 255, cv.THRESH_BINARY);

            // Encontra os contornos na imagem
            cv.findContours(thresh, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

            let maxArea = 0;
            let bestContour = null;

            // Encontrar o maior contorno (isso vai representar o objeto que estamos rastreando)
            for (let i = 0; i < contours.size(); i++) {
                let cnt = contours.get(i);
                let area = cv.contourArea(cnt);
                if (area > maxArea) {
                    maxArea = area;
                    bestContour = cnt;
                }
            }

            // Se um contorno válido for encontrado, desenha a bounding box ao redor
            if (bestContour && maxArea > 1000) {
                let rect = cv.boundingRect(bestContour);
                boundingBox = { x: rect.x, y: rect.y, width: rect.width, height: rect.height };

                // Desenha a borda verde ao redor do objeto
                ctx.strokeStyle = 'lime';
                ctx.lineWidth = 3;
                ctx.strokeRect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);
            }

            // Limpa a memória após o uso
            src.delete(); gray.delete(); blurred.delete(); thresh.delete();
            contours.delete(); hierarchy.delete();
        }

        // Executa o rastreamento em intervalos
        function startTracking() {
            tracking = true;
            setInterval(function() {
                if (tracking) {
                    trackObject();
                }
            }, 100);  // Atualiza a cada 100ms
        }

        // Inicia o rastreamento assim que a página carregar
        window.onload = function() {
            startTracking();
        };
    </script>

</body>
</html>
