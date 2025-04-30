<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MTG Card Tracker</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <style>
        body {
            background: black;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: hidden;
        }

        #info {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            padding: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div id="info">⌛ A iniciar...</div>

    <script>
        let video;
        let info;
        let contours = [];

        function setup() {
            createCanvas(640, 480);
            video = createCapture(VIDEO);
            video.size(width, height);
            video.hide(); // Oculta o elemento de vídeo

            info = select('#info');
            info.html("🔍 A procurar carta...");

            // Inicializa a detecção
            frameRate(10);
        }

        function draw() {
            image(video, 0, 0); // Exibe o vídeo na tela
            let img = get(); // Captura o quadro atual
            img.filter(GRAY); // Converte para escala de cinza

            // Detecta os contornos na imagem
            contours = detectContours(img);

            if (contours.length > 0) {
                info.html("✅ Carta detectada!");
                stroke(255, 0, 0);
                noFill();
                beginShape();
                contours.forEach(c => {
                    vertex(c.x, c.y);
                });
                endShape(CLOSE);
            } else {
                info.html("🔍 A procurar carta...");
            }
        }

        // Função para detectar contornos usando o P5.js
        function detectContours(img) {
            let contours = [];
            img.loadPixels();
            for (let y = 0; y < img.height; y++) {
                for (let x = 0; x < img.width; x++) {
                    let index = (x + y * img.width) * 4;
                    let r = img.pixels[index];
                    let g = img.pixels[index + 1];
                    let b = img.pixels[index + 2];

                    if (r < 80 && g < 80 && b < 80) { // Detecção simples por cor
                        contours.push(createVector(x, y)); // Se encontrar um contorno, armazena o ponto
                    }
                }
            }
            return contours;
        }
    </script>
</body>
</html>
