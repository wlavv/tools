<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MTG Card Tracker with pHash</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            z-index: 10;
        }

        #croppedImage {
            position: absolute;
            top: 300px;
            left: 10px;
            border: 2px solid white;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10;
        }

        canvas {
            display: block;
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div id="info">⌛ A iniciar...</div>
    <div id="croppedImage"></div>

    <script>
        let video;
        let info;
        let contours = [];
        let croppedImageElement = document.getElementById('croppedImage');
        let croppedImageCanvas;

        let zoom = 1;
        let zoomX = 0;
        let zoomY = 0;

        function setup() {
            createCanvas(windowWidth, windowHeight);
            video = createCapture(VIDEO);
            video.size(width, height);
            video.hide();

            info = select('#info');
            info.html("🔍 A procurar carta...");
            frameRate(10);
        }

        function draw() {
            background(0);
            push();
            translate(zoomX, zoomY);
            scale(zoom);

            image(video, 0, 0, width, height);
            let img = get();
            //img.filter(GRAY);
            contours = detectContours(img);

            if (contours.length > 0) {
                info.html("✅ Carta detectada!");
                stroke(255, 0, 0);
                noFill();
                beginShape();
                contours.forEach(c => vertex(c.x, c.y));
                endShape(CLOSE);

                let minX = Math.min(...contours.map(c => c.x));
                let maxX = Math.max(...contours.map(c => c.x));
                let minY = Math.min(...contours.map(c => c.y));
                let maxY = Math.max(...contours.map(c => c.y));

                let croppedImage = img.get(minX, minY, maxX - minX, maxY - minY);

                if (!croppedImageCanvas) {
                    croppedImageCanvas = createGraphics(maxX - minX, maxY - minY);
                }
                croppedImageCanvas.image(croppedImage, 0, 0);
                image(croppedImageCanvas, 10, 300);

                sendImageToServer(croppedImageCanvas);
            } else {
                info.html("🔍 A procurar carta...");
            }
            pop();
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

        let lastRequestTime = 0;
        function sendImageToServer(croppedImageCanvas) {
            const currentTime = Date.now();
            if (currentTime - lastRequestTime >= 10000) {
                croppedImageCanvas.loadPixels();
                let imageData = croppedImageCanvas.get();
                const base64Image = imageData.canvas.toDataURL('image/jpeg');

                $.ajax({
                    url: '/mtg/find-card-base64',
                    type: 'POST',
                    data: JSON.stringify({ base64_image: base64Image }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#info').text('pHash da carta: ' + response.pHash);
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao enviar imagem:', error);
                        $('#info').text('Erro ao enviar imagem!');
                    }
                });

                lastRequestTime = currentTime;
            }
        }

        // Redimensiona canvas dinamicamente
        function windowResized() {
            resizeCanvas(windowWidth, windowHeight);
        }

        // Zoom com scroll do rato
        function mouseWheel(event) {
            const zoomFactor = 0.05;
            zoom += event.delta > 0 ? -zoomFactor : zoomFactor;
            zoom = constrain(zoom, 0.5, 3);
            return false; // Impede scroll da página
        }
    </script>
</body>
</html>
