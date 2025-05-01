<script>
    let video;
    let info;
    let croppedImageElement = document.getElementById('croppedImage');
    let croppedImageCanvas;
    let boundingBox = { x: 0, y: 0, width: 0, height: 0 };
    let isCapturing = true;

    // Dimensões fixas da zona de crop
    const cropWidth = 500;
    const cropHeight = 750;

    function setup() {
        createCanvas(1, 1); // Canvas mínimo, não será exibido visualmente

        video = createCapture(VIDEO);
        video.size(640, 480);
        video.hide(); // Oculta o vídeo original (vamos apenas usar o frame)

        info = select('#info');
        info.html("🔍 A procurar carta...");

        frameRate(30);
    }

    function draw() {
        if (!isCapturing) return;

        // Captura o quadro atual
        let img = video.get();
        img.loadPixels();

        // Ponto superior esquerdo da zona de crop (centralizado no vídeo)
        let cropX = Math.max((video.width - cropWidth) / 2, 0);
        let cropY = Math.max((video.height - cropHeight) / 2, 0);

        // Garante que a área de crop está dentro dos limites
        cropX = Math.floor(cropX);
        cropY = Math.floor(cropY);

        // Extrai apenas a área de interesse
        let cropped = img.get(cropX, cropY, cropWidth, cropHeight);

        // Aplica filtro de cinza, se desejado
        cropped.filter(GRAY);

        // Converte em base64
        let base64Image = cropped.canvas.toDataURL('image/jpeg');

        // Envia via AJAX
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

                // Atualiza bounding box
                boundingBox = response.boundingBox || { x: 0, y: 0, width: 0, height: 0 };

                // Mostra a imagem recortada (original, não a crop prévia)
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

                // Aguarda 5 segundos antes de nova deteção
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

        isCapturing = false; // Impede chamadas múltiplas simultâneas
    }
</script>
