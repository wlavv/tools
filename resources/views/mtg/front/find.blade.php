<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
            width: 500px;
            height: 700px;
            pointer-events: none;
            z-index: 2;
        }

        #info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            z-index: 3;
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
            z-index: 3;
            width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <div id="container">
        <img id="logo" src="https://1000logos.net/wp-content/uploads/2022/10/Magic-The-Gathering-logo-500x325.png" alt="MTG Logo">
        <video id="video" autoplay playsinline muted></video>
        <canvas id="overlay" width="500" height="700"></canvas>
        <div id="info">Carregando...</div>
    </div>
    <script async src="https://docs.opencv.org/4.x/opencv.js" type="text/javascript"></script>
<script>
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const overlayCtx = overlay.getContext('2d');
    const infoBox = document.getElementById('info');

    async function startCamera() {
        try {
            infoBox.innerText = '🟡 A iniciar câmara...';
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                infoBox.innerText = '🟢 Câmara pronta. A carregar OpenCV...';
                waitForOpenCV();
            };
        } catch (err) {
            infoBox.innerText = '🔴 Erro na câmara!';
            console.error('Erro ao aceder à câmara:', err);
        }
    }

    function waitForOpenCV() {
        const check = setInterval(() => {
            if (typeof cv !== 'undefined' && typeof cv.imread !== 'undefined') {
                clearInterval(check);
                infoBox.innerText = '🟢 OpenCV pronto. A iniciar deteção...';
                console.log('✅ OpenCV carregado.');
                detectLoop();
            } else {
                console.log('⌛ A aguardar OpenCV...');
            }
        }, 200);
    }

    function detectLoop() {
        try {
            const width = video.videoWidth;
            const height = video.videoHeight;
            if (!width || !height) {
                console.warn('❌ Vídeo sem dimensões disponíveis ainda.');
                setTimeout(detectLoop, 1000);
                return;
            }

            const src = new cv.Mat(height, width, cv.CV_8UC4);
            const cap = new cv.VideoCapture(video);
            cap.read(src);

            // Verificar se o frame está preto
            const total = cv.countNonZero(cv.cvtColor(src.clone(), new cv.Mat(), cv.COLOR_RGBA2GRAY));
            if (total === 0) {
                infoBox.innerText = '🔴 Frame vazio (câmara bloqueada?)';
                console.warn('❌ Frame captado está completamente preto.');
                src.delete();
                setTimeout(detectLoop, 2000);
                return;
            }

            let gray = new cv.Mat();
            let blurred = new cv.Mat();
            let edges = new cv.Mat();
            let contours = new cv.MatVector();
            let hierarchy = new cv.Mat();

            cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
            cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);
            cv.Canny(blurred, edges, 75, 200);
            cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);

            console.log(`🔍 ${contours.size()} contornos detetados.`);

            let found = false;
            for (let i = 0; i < contours.size(); i++) {
                let cnt = contours.get(i);
                let approx = new cv.Mat();
                cv.approxPolyDP(cnt, approx, 0.02 * cv.arcLength(cnt, true), true);
                if (approx.rows === 4 && cv.contourArea(approx) > 5000) {
                    found = true;
                    overlayCtx.beginPath();
                    overlayCtx.strokeStyle = 'red';
                    overlayCtx.lineWidth = 3;

                    for (let j = 0; j < 4; j++) {
                        const x1 = approx.data32S[j * 2];
                        const y1 = approx.data32S[j * 2 + 1];
                        const x2 = approx.data32S[((j + 1) % 4) * 2];
                        const y2 = approx.data32S[((j + 1) % 4) * 2 + 1];
                        overlayCtx.moveTo(x1, y1);
                        overlayCtx.lineTo(x2, y2);
                    }
                    overlayCtx.stroke();
                    console.log('✅ Detetado quadrilátero potencial de carta.');
                    approx.delete();
                    break;
                }
                approx.delete();
            }

            infoBox.innerText = found ? '🟢 Carta detetada!' : '🔎 Nenhuma carta detetada.';
            if (!found) console.log('⚠️ Nenhum quadrilátero com 4 pontos foi encontrado.');

            // Limpar
            src.delete(); gray.delete(); blurred.delete(); edges.delete(); contours.delete(); hierarchy.delete();
        } catch (err) {
            infoBox.innerText = '🔴 Erro no OpenCV.';
            console.error('Erro no loop de deteção:', err);
        }

        setTimeout(detectLoop, 1500);
    }

    document.addEventListener('DOMContentLoaded', startCamera);
</script>
</body>
</html>
