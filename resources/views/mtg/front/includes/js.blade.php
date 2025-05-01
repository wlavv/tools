<script>

let video;
let info;
let canvasOverlay;
let boundingBox = { x: 0, y: 0, width: 0, height: 0 };
let isCapturing = true;
const cropWidth = 500;
const cropHeight = 750;

window.setup = function () {
    const canvas = createCanvas(cropWidth, cropHeight);
    canvasOverlay = canvas.elt;
    canvasOverlay.style.position = 'absolute';
    canvasOverlay.style.top = '0';
    canvasOverlay.style.left = '0';
    canvasOverlay.style.zIndex = '10'; // Sobre o vídeo

    video = createCapture(VIDEO, () => {
        const videoContainer = document.getElementById('videoContainer');
        videoContainer.style.position = 'relative';
        video.elt.style.position = 'absolute';
        video.elt.style.top = '0';
        video.elt.style.left = '0';
        video.elt.width = cropWidth;
        video.elt.height = cropHeight;
        videoContainer.appendChild(video.elt);
        videoContainer.appendChild(canvasOverlay); // sobrepõe o canvas ao vídeo
    });

    video.size(cropWidth, cropHeight);
    // NÃO usamos video.hide() — queremos vê-lo

    info = select('#info');
    info.html("🔍 A procurar carta...");
};

window.draw = function () {
    clear(); // limpa o canvas a cada frame

    if (!isCapturing) {
        // Mostra bounding box, se existir
        if (boundingBox.width > 0 && boundingBox.height > 0) {
            noFill();
            stroke('lime');
            strokeWeight(3);
            rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height);
        }
        return;
    }

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
