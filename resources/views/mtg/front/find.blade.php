<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MTG Card Tracker com pHash</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/blockhash@1.0.3/blockhash.js"></script>
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
            video.hide();

            info = select('#info');
            info.html("🔍 A procurar carta...");

            frameRate(10);
        }

        function draw() {
            image(video, 0, 0);
            let img = get();
            img.filter(GRAY);

            contours = detectContours(img);

            if (contours.length > 0) {
                stroke(255, 0, 0);
                noFill();
                beginShape();
                contours.forEach(c => {
                    vertex(c.x, c.y);
                });
                endShape(CLOSE);

                let minX = Math.min(...contours.map(c => c.x));
                let maxX = Math.max(...contours.map(c => c.x));
                let minY = Math.min(...contours.map(c => c.y));
                let maxY = Math.max(...contours.map(c => c.y));

                let croppedImage = img.get(minX, minY, maxX - minX, maxY - minY);

                // Copia para um canvas HTML para gerar a hash
                let croppedCanvas = document.createElement('canvas');
                croppedCanvas.width = croppedImage.width;
                croppedCanvas.height = croppedImage.height;
                let ctx = croppedCanvas.getContext('2d');

                croppedImage.loadPixels();
                let imageData = ctx.createImageData(croppedImage.width, croppedImage.height);
                for (let i = 0; i < imageData.data.length; i++) {
                    imageData.data[i] = croppedImage.pixels[i];
                }
                ctx.putImageData(imageData, 0, 0);

                let imgElement = new Image();
                imgElement.onload = () => {
                    getPerceptualHash(imgElement).then(hash => {
                        info.html(`✅ Carta detectada!<br>Hash: ${hash}`);
                        console.log("pHash:", hash);
                    });
                };
                imgElement.src = croppedCanvas.toDataURL();
                noLoop(); // Evita repetição enquanto hash é gerada
            } else {
                info.html("🔍 A procurar carta...");
            }
        }

        function detectContours(img) {
            let contours = [];
            img.loadPixels();
            for (let y = 0; y < img.height; y++) {
                for (let x = 0; x < img.width; x++) {
                    let index = (x + y * img.width) * 4;
                    let r = img.pixels[index];
                    let g = img.pixels[index + 1];
                    let b = img.pixels[index + 2];

                    if (r < 80 && g < 80 && b < 80) {
                        contours.push(createVector(x, y));
                    }
                }
            }
            return contours;
        }

        async function getPerceptualHash(imgElement) {
            const canvas = document.createElement('canvas');
            canvas.width = imgElement.width;
            canvas.height = imgElement.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(imgElement, 0, 0);

            const hash = blockhash.bmvbhash(canvas, 16);
            return hash;
        }
    </script>
</body>
</html>
