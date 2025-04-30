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

        #croppedImage {
            position: absolute;
            top: 300px;
            left: 10px;
            border: 2px solid white;
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
    <div id="info">⌛ A iniciar...</div>
    <div id="croppedImage"></div>

    <script>
        let video;
        let info;
        let contours = [];
        let croppedImageElement = document.getElementById('croppedImage');
        let croppedImageCanvas;

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
                stroke(255, 0, 0);  // Define a cor da borda como vermelho
                noFill();           // Não preenche a área da carta
                beginShape();       // Inicia o desenho da borda
                contours.forEach(c => {
                    vertex(c.x, c.y);
                });
                endShape(CLOSE);    // Finaliza o desenho da borda

                // Calcula o bounding box (caixa de limite) para o crop
                let minX = Math.min(...contours.map(c => c.x));
                let maxX = Math.max(...contours.map(c => c.x));
                let minY = Math.min(...contours.map(c => c.y));
                let maxY = Math.max(...contours.map(c => c.y));

                // Realiza o crop na imagem
                let croppedImage = img.get(minX, minY, maxX - minX, maxY - minY);

                // Cria uma nova imagem cropped e a exibe na tela
                if (!croppedImageCanvas) {
                    croppedImageCanvas = createGraphics(maxX - minX, maxY - minY);
                }
                croppedImageCanvas.image(croppedImage, 0, 0);
                image(croppedImageCanvas, 10, 300); // Exibe a imagem cortada
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