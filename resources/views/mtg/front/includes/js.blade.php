<script>
    let video;
    let info;
    let croppedImageElement;
    let croppedImageCanvas;
    let boundingBox = { x: 0, y: 0, width: 0, height: 0 };
    let isCapturing = true;

    const cropWidth = 500;
    const cropHeight = 750;

    window.setup = function () {
        createCanvas(1, 1); // Canvas invisível

        video = createCapture(VIDEO, () => {
            // Quando o vídeo estiver pronto, insere no DOM
            document.getElementById('videoContainer').appendChild(video.elt);
        });
        video.size(640, 480);
        video.hide(); // Impede que o p5.js o mostre fora do lugar

        info = select('#info');
        croppedImageElement = document.getElementById('croppedImage');
        info.html("🔍 A procurar carta...");

        frameRate(30);
    };

    window.draw = function () {
        if (!isCapturing) return;

        let img = video.get();
        img.loadPixels();

        let cropX = Math.floor((video.width - cropWidth) / 2);
        let cropY = Math.floor((video.height - cropHeight) / 2);

        let cropped = img.get(cropX, cropY, cropWidth, cropHeight);
        cropped.filter(GRAY);

        let base64Image = cropped.canvas.toDataURL('image/jpeg');

        $.ajax({
            url: "{{ route('mtg.processImage') }}",
            type: 'POST',
            data: JSON.stringify({ image: base64Image }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                info.html("📛 pHash: " + response.pHash);

                boundingBox = response.boundingBox || { x: 0, y: 0, width: 0, height: 0 };

                if (boundingBox.width > 0 && boundingBox.height > 0) {
                    let fullFrame = video.get();
                    let detected = fullFrame.get(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);

                    if (!croppedImageCanvas) {
                        croppedImageCanvas = createGraphics(boundingBox.width, boundingBox.height);
                    } else {
                        croppedImageCanvas.resizeCanvas(boundingBox.width, boundingBox.height);
                    }

                    croppedImageCanvas.image(detected, 0, 0);
                    croppedImageElement.innerHTML = "";
                    croppedImageElement.appendChild(croppedImageCanvas.canvas);
                }

                isCapturing = false;
                setTimeout(() => {
                    isCapturing = true;
                }, 5000);
            },
            error: function(xhr, status, error) {
                console.error("Erro ao enviar imagem:", error);
                info.html("❌ Erro ao enviar imagem");
            }
        });

        isCapturing = false;
    };
</script>
