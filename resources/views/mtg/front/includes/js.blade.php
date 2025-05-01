<script>
    let video;
    let info;
    let croppedImageElement = document.getElementById('croppedImage');
    let croppedImageCanvas;
    let boundingBox = { x: 0, y: 0, width: 0, height: 0 };
    let isCapturing = true;  // Flag para controlar a captura contínua

    function setup() {
        // Criação da tela e captura do vídeo
        //createCanvas(windowWidth, windowHeight); // Ajusta para ocupar toda a largura e altura da página
        video = createCapture(VIDEO);
        video.size(windowWidth, windowHeight); // Ajusta o vídeo para preencher o canvas
        //video.parent('croppedImage'); // Coloca o vídeo diretamente no div com id 'croppedImage'

        info = select('#info');
        info.html("🔍 A procurar carta...");

        frameRate(30); // Ajusta para capturar 30 quadros por segundo
    }

    function draw() {
        if (isCapturing) {
            image(video, 0, 0); // Exibe o vídeo ao vivo

            // Captura o quadro atual do vídeo
            let img = get();
            img.loadPixels();

            // Realiza a detecção da carta
            detectCard(img);

            // Se a borda da carta for detectada, desenha ela na tela
            if (boundingBox.width > 0 && boundingBox.height > 0) {
                noFill();
                stroke(255, 0, 0); // Define a cor da borda para vermelho
                strokeWeight(4);   // Ajusta a espessura da linha da borda
                rect(boundingBox.x, boundingBox.y, boundingBox.width, boundingBox.height); // Desenha a borda ao redor da carta
            }
        }
    }

    function detectCard(img) {
        // Aqui você captura o quadro da câmera e envia para o servidor para detecção
        img.filter(GRAY); // Converte para escala de cinza, se necessário

        // Converte a imagem em base64
        let base64Image = img.canvas.toDataURL('image/jpeg');

        // Envia a imagem para o servidor via AJAX
        $.ajax({
            url: "{{ route('mtg.processImage') }}", // Rota para processar a imagem
            type: 'POST',
            data: JSON.stringify({ image: base64Image }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // Adiciona o token CSRF no cabeçalho
            },
            success: function(response) {
                // Exibe o pHash da carta detectada
                info.html("pHash da carta: " + response.pHash);

                // Atualiza a posição e tamanho da borda
                boundingBox = response.boundingBox;

                // Interrompe a captura por 5 segundos
                isCapturing = false;
                setTimeout(function() {
                    isCapturing = true; // Reinicia a captura após 5 segundos
                }, 5000);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao enviar imagem:', error);
                info.html('Erro ao enviar imagem!');
            }
        });
    }

    // Função para ajustar o tamanho do canvas ao redimensionar a janela
    function windowResized() {
        resizeCanvas(windowWidth, windowHeight);  // Ajusta o canvas para a nova largura e altura da página
        video.size(windowWidth, windowHeight);  // Ajusta o vídeo para a nova largura e altura
    }
</script>
