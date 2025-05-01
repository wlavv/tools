<script>
    let video;

    function setup() {
        noCanvas();

        video = createCapture(VIDEO);
        video.size(windowWidth, windowHeight);
        video.hide();

        const button = createButton('📸 Capturar imagem');
        button.style('font-size', '18px');
        button.style('margin', '20px');
        button.mousePressed(() => {
            captureAndSend(video);
        });
    }

    function captureAndSend(video) {
        const canvas = createGraphics(video.width, video.height);
        canvas.image(video, 0, 0, video.width, video.height);

        canvas.loadPixels();
        const base64Image = canvas.canvas.toDataURL("image/png");

        $('#info').text('📤 A enviar imagem...');

        $.ajax({
            url: "{{ route('mtg.process-image') }}",
            type: "POST",
            data: {
                image: base64Image,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#info').text(`✅ pHash: ${response.pHash}`);

                if (response.boundingBox) {
                    drawBoundingBox(response.boundingBox);
                }
            },
            error: function () {
                $('#info').text("❌ Erro ao enviar imagem.");
            }
        });
    }

    function drawBoundingBox(box) {
        
        removeExistingBox(); 

        const overlay = document.createElement('div');
        overlay.id = 'boundingBoxOverlay';
        overlay.style.position = 'absolute';
        overlay.style.border = '3px solid red';
        overlay.style.left = box.x + 'px';
        overlay.style.top = box.y + 'px';
        overlay.style.width = box.width + 'px';
        overlay.style.height = box.height + 'px';
        overlay.style.zIndex = 9999;

        document.body.appendChild(overlay);
    }

    function removeExistingBox() {
        const oldBox = document.getElementById('boundingBoxOverlay');
        if (oldBox) {
            oldBox.remove();
        }
    }

    window.addEventListener("resize", () => {
        if (video) {
            video.size(windowWidth, windowHeight);
        }
    });
</script>
