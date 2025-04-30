<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detecção de Cartas</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        #canvas-container {
            position: relative;
        }
    </style>
</head>
<body>
    <div id="canvas-container">
        <canvas id="defaultCanvas0"></canvas>
    </div>

    <script>
        let img;  // Variável para armazenar a imagem
        let contours = [];  // Para armazenar os pontos de contorno detectados
        let croppedImageCanvas;  // Canvas para a imagem cortada

        function setup() {
            createCanvas(640, 480);
            noLoop();  // Não precisa de atualização contínua
            loadImage('https://via.placeholder.com/640x480.png', (loadedImage) => {
                img = loadedImage;  // Carrega a imagem da URL
                img.resize(width, height);  // Ajusta o tamanho da imagem para o tamanho do canvas
                processImage();
            });
        }

        function detectContours() {
            contours = [];
            img.loadPixels();
            for (let y = 0; y < img.height; y++) {
                for (let x = 0; x < img.width; x++) {
                    let index = (x + y * img.width) * 4;
                    let r = img.pixels[index];
                    let g = img.pixels[index + 1];
                    let b = img.pixels[index + 2];

                    // Detecção simples por cor (pixels escuros)
                    if (r < 80 && g < 80 && b < 80) {
                        contours.push(createVector(x, y)); // Armazena o ponto
                    }
                }
            }
        }

        function processImage() {
            detectContours();

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
        }

        function draw() {
            background(255);  // Define o fundo branco

            if (img) {
                image(img, 0, 0);  // Exibe a imagem original

                // Exibe o contorno da carta
                stroke(255, 0, 0);  // Cor da borda (vermelho)
                noFill();
                beginShape();
                for (let i = 0; i < contours.length; i++) {
                    vertex(contours[i].x, contours[i].y);
                }
                endShape(CLOSE);
            }
        }
    </script>
</body>
</html>
