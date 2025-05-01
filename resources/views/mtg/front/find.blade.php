<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Object Tracking in Video with Tracking.js</title>
    <script src="https://cdn.jsdelivr.net/npm/tracking@1.1.2/build/tracking-min.js"></script>
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
        let tracker;

        // Função para inicializar a captura de vídeo
        function startVideo() {
            video = document.getElementById('video');
            canvas = document.getElementById('canvasOverlay');
            ctx = canvas.getContext('2d');

            // Inicia o vídeo da webcam
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    console.log("Erro ao acessar a câmera: ", err);
                });
        }

        // Função para iniciar o tracking com a biblioteca tracking.js
        function startTracking() {
            // Cria o tracker (pode ser ColorTracker ou ObjectTracker)
            tracker = new tracking.ObjectTracker('all');

            // Quando o objeto é detectado, desenha a bounding box
            tracker.on('track', function(event) {
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Limpa o canvas

                event.data.forEach(function(rect) {
                    // Desenha a borda verde ao redor do objeto
                    ctx.strokeStyle = 'lime';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(rect.x, rect.y, rect.width, rect.height);
                });
            });

            // Inicia o tracker para o vídeo
            tracking.track('#video', tracker);
        }

        // Função chamada quando a página carrega
        window.onload = function() {
            startVideo();
            startTracking();
        };
    </script>

</body>
</html>
