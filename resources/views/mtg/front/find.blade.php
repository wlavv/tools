<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Color Tracking with p5.js</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
</head>
<body>
    <div id="info">⌛ A iniciar...</div>
    <script>
        let video;
        let prevImg;
        let minSize = 50;
        let maxSize = 200;

        // Função setup - chamada uma vez ao iniciar
        function setup() {
            createCanvas(640, 480);  // Define o tamanho do canvas
            video = createCapture(VIDEO);
            video.size(640, 480);
            video.hide();  // Oculta o vídeo para mostrar apenas no canvas
        }

        // Função draw - chamada a cada frame
        function draw() {
            image(video, 0, 0);  // Desenha o vídeo no canvas

            // Captura o frame atual
            loadPixels();
            video.loadPixels();

            // Detectar a cor desejada (aqui, magenta)
            for (let i = 0; i < video.width; i++) {
                for (let j = 0; j < video.height; j++) {
                    let pixelColor = video.get(i, j);
                    let r = red(pixelColor);
                    let g = green(pixelColor);
                    let b = blue(pixelColor);

                    // Se o pixel for magenta, marque a área
                    if (r > 150 && g < 50 && b > 150) {
                        set(i, j, color(255, 0, 0));  // Marca o pixel em vermelho
                    }
                }
            }

            // Desenha um bounding box baseado nas regiões detectadas
            findBoundingBox();
        }

        // Função que encontra a área da cor magenta
        function findBoundingBox() {
            let left = width, right = 0, top = height, bottom = 0;

            // Verifica os limites da área detectada
            loadPixels();
            for (let i = 0; i < width; i++) {
                for (let j = 0; j < height; j++) {
                    let pixelColor = get(i, j);
                    if (red(pixelColor) === 255 && green(pixelColor) === 0 && blue(pixelColor) === 0) {
                        left = min(left, i);
                        right = max(right, i);
                        top = min(top, j);
                        bottom = max(bottom, j);
                    }
                }
            }

            // Se a área for grande o suficiente, desenha um bounding box
            if (right - left > minSize && bottom - top > minSize) {
                stroke(0, 255, 0);  // Cor verde para o bounding box
                noFill();
                rect(left, top, right - left, bottom - top);
            }
        }
    </script>
</body>
</html>
