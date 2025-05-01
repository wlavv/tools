<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Object Tracking with p5.js</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
</head>
<body>
    <div id="info">⌛ A iniciar...</div>
    <script src="tracking.js"></script>
    <script>
        let video;
        let tracker;
        let boundingBox = { x: 0, y: 0, width: 0, height: 0 };

        // Função setup - chamada uma vez ao iniciar
        function setup() {
            createCanvas(640, 480);  // Define o tamanho do canvas
            video = createCapture(VIDEO);
            video.size(640, 480);
            video.hide();  // Oculta o vídeo para mostrar apenas no canvas

            // Configura o rastreador de cor
            tracker = new tracking.ColorTracker();
            tracker.on('track', function(event) {
                // Verifica se há algum objeto detectado
                if (event.data.length === 0) {
                    boundingBox = { x: 0, y: 0, width: 0, height: 0 };
                } else {
                    // Obtém as coordenadas do bounding box
                    boundingBox = event.data[0].bounds;
                }
            });

            // Começa a rastrear a cor magenta
            tracking.track(video.elt, tracker);
        }

        // Função draw - chamada a cada frame
        function draw() {
            image(video, 0, 0);  // Desenha o vídeo no canvas

            // Desenha o bounding box em torno do objeto detectado
            if (boundingBox.width > 0 && boundingBox.height > 0) {
                noFill();
                stroke('lime');
                strokeWeight(3);
                rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);
            }
        }
    </script>
</body>
</html>
